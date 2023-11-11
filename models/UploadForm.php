<?php

namespace app\models;

use app\helpers\SepaParser;
use app\helpers\Utils;
use app\models\enums\DatiTipologia;
use app\models\enums\FileRicoveri;
use app\models\enums\PagamentiConIban;
use app\models\enums\TipologiaDatiCategoria;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\XLSX\Sheet;
use Carbon\Carbon;
use Yii;
use yii\base\Model;
use yii\helpers\Json;
use yii\web\UploadedFile;

class UploadForm extends Model
{
    /**
     * @var UploadedFile[]
     */
    public $files;
    public $tipo;
    public $simulazione;

    public function rules()
    {
        return [
            [['files'], 'file', 'skipOnEmpty' => false, 'extensions' => 'xlsx, xls', 'maxFiles' => 10],
            [['tipo'], 'string'],
            [['simulazione'], 'boolean'],
        ];
    }

    /**
     * @return bool
     */
    public function uploadSingle()
    {
        $path = Yii::$app->params['importPath'];
        if ($this->validate()) {
            if (isset($this->files[0])) {
                $file = $this->files[0];
                $nome_file_originale = $file->baseName . '.' . $file->extension;
                $nome_file_temp = bin2hex(openssl_random_pseudo_bytes(30)) . '.' . $file->extension;
                $file->saveAs($path . '/' . $nome_file_temp);
            }
            return true;
        } else {
            return false;
        }
    }


    public function upload()
    {
        $okFiles = [];
        $path = Yii::$app->params['importPath'];
        $stats = null;
        if ($this->validate()) {
            foreach ($this->files as $index => $file) {
                $nome_file_originale = $file->baseName . '.' . $file->extension;
                $nome_file_temp = bin2hex(openssl_random_pseudo_bytes(30)) . '.' . $file->extension;
                // verify if folder $path exists
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $file->saveAs($path . '/' . $nome_file_temp);
                $okFiles[] = $path . '/' . $nome_file_temp;
            }
            switch ($this->tipo) {
                case TipologiaDatiCategoria::RICOVERI:
                    $stats = $this->importaRicoveri($okFiles);
                    break;
                case TipologiaDatiCategoria::MOVIMENTI_CON_IBAN:
                    foreach ($okFiles as $file)
                        $stats = $this->importaFileConElenchi($file);
                    break;
                case TipologiaDatiCategoria::TRACCIATO_SEPA:
                    $stats = $this->importaTracciatoSepa($okFiles);
                    break;
            }
        }
        if ($this->errors)
            // show with setFlash the errors array
            Yii::$app->session->setFlash('error', json_encode($this->errors));
        return $stats;
    }

    private function importaFileConElenchi($path)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $reader = ReaderEntityFactory::createReaderFromFile($path);
        $reader->open($path);
        $header = null;
        $rowIndex = 0;
        $nonTrovati = [];
        $errors = [];
        $alert = [];
        $gruppiPagamento = GruppoPagamento::find([])->all();
        $gruppiPagamentoMap = [];
        $istanze = null;
        $lastCf = null;
        $transaction = Yii::$app->db->beginTransaction();
        foreach ($gruppiPagamento as $gruppo) {
            $gruppiPagamentoMap[$gruppo->progressivo] = $gruppo;
        }
        foreach ($reader->getSheetIterator() as $sheet) {
            /* @var Sheet $sheet */
            foreach ($sheet->getRowIterator() as $row) {
                $newRow = [];
                foreach ($row->getCells() as $idxcel => $cel) {
                    $newRow[$idxcel] = $cel->getValue();
                }
                if ($rowIndex === 0) {
                    foreach ($newRow as $idx => $cell)
                        $header[$cell] = $idx;
                } else if ($newRow[$header[PagamentiConIban::IMPORTO]] !== "") {
                    if ($lastCf !== strtoupper(trim($newRow[$header[PagamentiConIban::CODICE_FISCALE]]))) {
                        $istanze = Istanza::find()->innerJoin('anagrafica a', 'a.id = istanza.id_anagrafica_disabile')->where(['a.codice_fiscale' => strtoupper(trim($newRow[$header[PagamentiConIban::CODICE_FISCALE]]))]);
                        $istanze = $istanze->andWhere(['istanza.chiuso' => false]);
                        $istanze = $istanze->all();
                    }
                    if (count($istanze) === 0) {
                        if (!array_key_exists(strtoupper(trim($newRow[$header[PagamentiConIban::CODICE_FISCALE]])), $nonTrovati))
                            $nonTrovati[strtoupper(trim($newRow[$header[PagamentiConIban::CODICE_FISCALE]]))] = $newRow;
                    } else if (count($istanze) > 1)
                        $errors[] = ['errore' => 'Trovate più istanze con lo stesso codice fiscale ' . strtoupper(trim($newRow[$header[PagamentiConIban::CODICE_FISCALE]]))];
                    if ($istanze && count($istanze) === 1) {
                        $lastCf = strtoupper(trim($newRow[$header[PagamentiConIban::CODICE_FISCALE]]));
                        $istanza = $istanze[0];
                        $ultimoConto = $istanza->getContoValido();
                        $iban = $newRow[$header[PagamentiConIban::IBAN1]] . $newRow[$header[PagamentiConIban::IBAN2]] . $newRow[$header[PagamentiConIban::IBAN3]] . $newRow[$header[PagamentiConIban::IBAN4]] . $newRow[$header[PagamentiConIban::IBAN5]] . $newRow[$header[PagamentiConIban::IBAN6]];
                        if ($iban === "")
                            $iban = $newRow[$header[PagamentiConIban::CODICE_FISCALE]];
                        $conto = Conto::findOne(['iban' => $iban, 'id_istanza' => $istanza->id]);
                        if (!$conto) {
                            $conto = new Conto();
                            $conto->id_istanza = $istanza->id;
                            if ($iban === "")
                                $iban = $newRow[$header[PagamentiConIban::CODICE_FISCALE]];
                            $conto->iban = $iban;
                            $conto->attivo = $ultimoConto ? 0 : 1;
                            $conto->save();
                            if ($conto->errors)
                                $errors = array_merge($errors, ['conto' . $newRow[$header[PagamentiConIban::CODICE_FISCALE]] => $conto->errors]);
                            $contoCessionario = new ContoCessionario();
                            $contoCessionario->id_conto = $conto->id;
                            $contoCessionario->attivo = 0;
                            $contoCessionario->save();
                            if ($contoCessionario->errors)
                                $errors = array_merge($errors, ['contoCessionario-' . $newRow[$header[PagamentiConIban::CODICE_FISCALE]] => $contoCessionario->errors]);
                        }
                        $movimentoExists = Movimento::find()->where(['id_conto' => $conto->id, 'periodo_da' => Utils::convertDateFromFormat($newRow[$header[PagamentiConIban::DAL]]), 'periodo_a' => Utils::convertDateFromFormat($newRow[$header[PagamentiConIban::AL]])])->one();
                        if ($movimentoExists)
                            $alert[] = ["Istanza #" . $istanza->id . " pagata ma ha già un pagamento, verificare"];
                        $movimento = null;
                        $movimento = new Movimento();
                        $movimento->id_conto = $conto->id;
                        $movimento->is_movimento_bancario = true;
                        $movimento->periodo_da = Utils::convertDateFromFormat($newRow[$header[PagamentiConIban::DAL]]);
                        $movimento->periodo_a = Utils::convertDateFromFormat($newRow[$header[PagamentiConIban::AL]]);
                        $movimento->note = "Bonifico di ". Carbon::parse($movimento->periodo_da)->locale('it')->monthName. ' ' . Carbon::parse($movimento->periodo_da)->year;
                        $movimento->data = $movimento->periodo_a;
                        $movimento->importo = $newRow[$header[PagamentiConIban::IMPORTO]];
                        $movimento->escludi_contabilita = true;
                        $movimento->id_gruppo_pagamento = isset($gruppiPagamentoMap[$newRow[$header[PagamentiConIban::ID_ELENCO]]]) ? $gruppiPagamentoMap[$newRow[$header[PagamentiConIban::ID_ELENCO]]]->id : null;
                        if ($movimento->id_gruppo_pagamento === null) {
                            $gruppoPagamento = new GruppoPagamento();
                            $gruppoPagamento->descrizione = "# " . $newRow[$header[PagamentiConIban::ID_ELENCO]];
                            $gruppoPagamento->progressivo = $newRow[$header[PagamentiConIban::ID_ELENCO]];
                            $gruppoPagamento->save();
                            $gruppiPagamentoMap[$newRow[$header[PagamentiConIban::ID_ELENCO]]] = $gruppoPagamento;
                            if ($gruppoPagamento->errors)
                                $errors = array_merge($errors, ['gruppoPagamento-' . $newRow[$header[PagamentiConIban::CODICE_FISCALE]] => $gruppoPagamento->errors]);
                        }
                        if (isset($gruppiPagamentoMap[$newRow[$header[PagamentiConIban::ID_ELENCO]]]) && !$gruppiPagamentoMap[$newRow[$header[PagamentiConIban::ID_ELENCO]]]->data) {
                            $gruppiPagamentoMap[$newRow[$header[PagamentiConIban::ID_ELENCO]]]->data = Utils::convertDateFromFormat($newRow[$header[PagamentiConIban::AL]]);
                            $gruppiPagamentoMap[$newRow[$header[PagamentiConIban::ID_ELENCO]]]->save();
                            if ($gruppiPagamentoMap[$newRow[$header[PagamentiConIban::ID_ELENCO]]]->errors)
                                $errors = array_merge($errors, ['gruppoPagamento-' . $newRow[$header[PagamentiConIban::CODICE_FISCALE]] => $gruppiPagamentoMap[$newRow[$header[PagamentiConIban::ID_ELENCO]]]->errors]);
                        }
                        $movimento->contabilizzare = 0;
                        if ($istanza->data_decesso !== null || $istanza->attivo === false)
                            $alert[] = ["Istanza #" . $istanza->id . " pagata ma non è attiva o il disabile è deceduto"];
                        $movimento->save();
                        if ($movimento->errors)
                            $errors = array_merge($errors, ['movimento-' . $newRow[$header[PagamentiConIban::CODICE_FISCALE]] => $movimento->errors]);
                    }
                }
                $rowIndex++;
            }
        }
        $reader->close();
        if (count($errors) > 0 || $this->simulazione) {
            $transaction->rollBack();
        } else {
            $transaction->commit();
        }
        // put in var $date the date in format yyyy-mm-dd_hh-mm-ss
        $date = date('Y-m-d_H-i-s');
        $fp = fopen('../import/pagamenti/con_iban/res_' . $date . '.json', 'w');
        fwrite($fp, json_encode(["simulazione" => $this->simulazione, "nonTrovati" => $nonTrovati, "errors" => $errors, "alert" => $alert]));
        fclose($fp);
        // send download of file fp
        Yii::$app->response->sendFile('../import/pagamenti/con_iban/res_' . $date . '.json');
        return ["nonTrovati" => $nonTrovati, "errors" => $errors];
    }


    private function importaRicoveri($files, $clearAll = false)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $nonTrovati = [];
        $errors = [];
        $warnings = [];
        $stats = ["aggiunti" => 0, "aggiornati" => 0];
        if ($clearAll && !$this->simulazione)
            Ricovero::deleteAll();
        // for each files with extension ".xlsx" in folder: $path
        foreach ($files as $filename) {
            $reader = ReaderEntityFactory::createReaderFromFile($filename);
            $reader->open($filename);
            foreach ($reader->getSheetIterator() as $sheet) {
                /* @var Sheet $sheet */
                $header = [];
                foreach ($sheet->getRowIterator() as $idxRow => $row) {
                    try {
                        $newRow = [];
                        foreach ($row->getCells() as $idxcel => $cel) {
                            $newRow[$idxcel] = $cel->getValue();
                        }
                        if (in_array(FileRicoveri::getLabel(FileRicoveri::COD_FISCALE), $newRow)) {
                            foreach ($newRow as $idx => $cell)
                                $header[$cell] = $idx;
                        } else if (count($header) > 0) {
                            if ($newRow[$header[FileRicoveri::getLabel(FileRicoveri::COD_FISCALE)]] !== "") {
                                $istanza = Istanza::find()->innerJoin('anagrafica a', 'a.id = istanza.id_anagrafica_disabile')->where(['a.codice_fiscale' => $newRow[$header[FileRicoveri::getLabel(FileRicoveri::COD_FISCALE)]]])->one();
                                if ($istanza) {
                                    $ricoveroPresente = Ricovero::find()->where([
                                        'id_istanza' => $istanza->id,
                                        'cod_struttura' => isset($header[FileRicoveri::getLabel(FileRicoveri::COD_STRUTTURA)]) ? strval($newRow[$header[FileRicoveri::getLabel(FileRicoveri::COD_STRUTTURA)]]) : null,
                                        'da' => Utils::convertDateFromFormat($newRow[$header[FileRicoveri::getLabel(FileRicoveri::DATA_RICOVERO)]])
                                    ])->one();
                                    if (!$ricoveroPresente) {
                                        $ricovero = new Ricovero();
                                        $ricovero->id_istanza = $istanza->id;
                                        $ricovero->cod_struttura = isset($header[FileRicoveri::getLabel(FileRicoveri::COD_STRUTTURA)]) ? strval($newRow[$header[FileRicoveri::getLabel(FileRicoveri::COD_STRUTTURA)]]) : null;
                                        $ricovero->da = Utils::convertDateFromFormat($newRow[$header[FileRicoveri::getLabel(FileRicoveri::DATA_RICOVERO)]]);
                                        $ricovero->a = Utils::convertDateFromFormat($newRow[$header[FileRicoveri::getLabel(FileRicoveri::DATA_DIMISSIONE)]]);
                                        $ricovero->contabilizzare = 1;
                                        $ricovero->note = "Comunicazione con file " . basename($filename) . " - " . $sheet->getName() . ' riga ' . $idxRow;
                                        if (!$this->simulazione)
                                            $ricovero->save();
                                        if ($ricovero->errors)
                                            $errors = array_merge($errors, ['ricovero' => $ricovero->errors]);
                                        else
                                            $stats["aggiunti"]++;
                                    } else {
                                        if ($newRow[$header[FileRicoveri::getLabel(FileRicoveri::DATA_DIMISSIONE)]] !== "" || $newRow[$header[FileRicoveri::getLabel(FileRicoveri::DATA_DIMISSIONE)]] !== null) {
                                            $a = Utils::convertDateFromFormat($newRow[$header[FileRicoveri::getLabel(FileRicoveri::DATA_DIMISSIONE)]]);
                                            if ($ricoveroPresente->a !== $a || $ricoveroPresente->a === null) {
                                                $precA = $ricoveroPresente->a;
                                                $ricoveroPresente->a = $a;
                                                $ricoveroPresente->contabilizzare = 1;
                                                if (!$this->simulazione)
                                                    $ricoveroPresente->save();
                                                if ($ricoveroPresente->errors)
                                                    $errors = array_merge($errors, [$ricoveroPresente->errors]);
                                                else {
                                                    $stats["aggiornati"]++;
                                                    $warnings[] = ['a_precedente' => $precA, 'nuovo' => $ricoveroPresente->attributes];
                                                }
                                            }
                                        }
                                    }
                                } else
                                    $nonTrovati[] = [
                                        'codFiscale' => $newRow[$header[FileRicoveri::getLabel(FileRicoveri::COD_FISCALE)]] ?? null,
                                        'row' => $newRow,
                                        'file' => $filename,
                                        'sheet' => $sheet->getName(),
                                    ];
                            }
                        }
                    } catch (\Exception $e) {
                        $errors[] = [$errors, ['errore' => basename($filename) . " - " . $sheet->getName() . ' riga ' . $idxRow . " - " . $e->getMessage(), 'header' => $header]];
                    }
                }
                if (count($header) === 0)
                    $errors[] = [$errors, ['errore' => [basename($filename) . " - " . $sheet->getName() . " - " . "Header non trovato"]]];
            }
            $reader->close();
            unlink($filename);
        }
        //var_dump(['nonTrovati' => $nonTrovati, 'errors' => $errors]);
        // save $nonTrovati as Json File
        // var date with today date in format yyyy-mm-dd_hh-mm-ss
        $date = date('Y-m-d_H-i-s');
        // export ['nonTrovati' => $nonTrovati, 'errors' => $errors] in file json with the same name and path of the original + "_report"
        $folder = Yii::getAlias('@webroot') . '/' . Yii::$app->params['importPath'] . '/';
        $fp = fopen($folder . 'esito-importazione_' . $date . '.json', 'w');
        fwrite($fp, json_encode(['stats' => $stats,'nonTrovati' => $nonTrovati, 'errors' => $errors, 'warnings' => $warnings]));
        fclose($fp);
        Yii::$app->session->setFlash('success', "Importazione completata. Ricoveri aggiunti: " . $stats["aggiunti"] . ", aggiornati: " . $stats["aggiornati"] . " errori: " . count($errors));
        //return ['nonTrovati' => $nonTrovati, 'errors' => $errors, 'statsfilename' => 'esito-importazione_' . $date . '.json'];
        // send file $fp as download
        Yii::$app->response->sendFile($folder . 'esito-importazione_' . $date . '.json');
    }

    /**
     * @throws \Exception
     */
    private function importaTracciatoSepa(array $okFiles)
    {
        $sepaParser = new SepaParser($okFiles[0]);
        $parsedData = $sepaParser->parseToArray();
        print_r($parsedData);
        return $parsedData;
    }
}