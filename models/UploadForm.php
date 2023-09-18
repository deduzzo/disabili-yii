<?php

namespace app\models;

use app\helpers\Utils;
use app\models\enums\DatiTipologia;
use app\models\enums\FileRicoveri;
use app\models\enums\TipologiaDatiCategoria;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\XLSX\Sheet;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class UploadForm extends Model
{
    /**
     * @var UploadedFile[]
     */
    public $files;
    public $tipo;

    public function rules()
    {
        return [
            [['files'], 'file', 'skipOnEmpty' => false, 'extensions' => 'xlsx, xls', 'maxFiles' => 10],
            [['tipo'], 'string'],
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
                $nome_file_originale =  $file->baseName . '.' . $file->extension;
                $nome_file_temp = bin2hex(openssl_random_pseudo_bytes(30)). '.' . $file->extension;
                $file->saveAs($path. '/' . $nome_file_temp);
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
                $nome_file_originale =  $file->baseName . '.' . $file->extension;
                $nome_file_temp = bin2hex(openssl_random_pseudo_bytes(30)). '.' . $file->extension;
                // verify if folder $path exists
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $file->saveAs($path. '/' . $nome_file_temp);
                $okFiles[] = $path. '/' . $nome_file_temp;
            }
            switch ($this->tipo) {
                case TipologiaDatiCategoria::RICOVERI:
                    // put in $okFiles the files of the path "../import/ricoveri"
                    //$okFiles = glob("../import/ricoveri" . '/*.xlsx');
                    $stats = $this->importaRicoveri($okFiles);
            }
        }
        return $stats;
    }

    private function importaRicoveri($files, $clearAll = false)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $nonTrovati = [];
        $errors = [];
        $stats =["aggiunti" => 0, "aggiornati" => 0];
        if ($clearAll)
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
                                        $ricovero->save();
                                        if ($ricovero->errors)
                                            $errors = array_merge($errors, ['ricovero' => $ricovero->errors]);
                                        else
                                            $stats["aggiunti"]++;
                                    } else {
                                        if ($newRow[$header[FileRicoveri::getLabel(FileRicoveri::DATA_DIMISSIONE)]] !== "" || $newRow[$header[FileRicoveri::getLabel(FileRicoveri::DATA_DIMISSIONE)]] !== null) {
                                            $ricoveroPresente->a = Utils::convertDateFromFormat($newRow[$header[FileRicoveri::getLabel(FileRicoveri::DATA_DIMISSIONE)]]);
                                            $ricoveroPresente->contabilizzare = 1;
                                            $ricoveroPresente->save();
                                            if ($ricoveroPresente->errors)
                                                $errors = array_merge($errors, ['ricoveroModifica' => $ricoveroPresente->errors]);
                                            else
                                                $stats["aggiornati"]++;
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
            unlink($filename);
            $reader->close();
        }
        //var_dump(['nonTrovati' => $nonTrovati, 'errors' => $errors]);
        // save $nonTrovati as Json File
        // var date with today date in format yyyy-mm-dd_hh-mm-ss
        $date = date('Y-m-d_H-i-s');
        // export ['nonTrovati' => $nonTrovati, 'errors' => $errors] in file json with the same name and path of the original + "_report"
        $folder = Yii::getAlias('@webroot') . '/' . Yii::$app->params['importPath'] . '/';
        $fp = fopen($folder . 'esito-importazione_' . $date . '.json', 'w');
        fwrite($fp, json_encode(['nonTrovati' => $nonTrovati, 'errors' => $errors]));
        fclose($fp);
        Yii::$app->session->setFlash('success', "Importazione completata. Ricoveri aggiunti: ".$stats["aggiunti"].", aggiornati: ".$stats["aggiornati"]. " errori: ".count($errors));
        return ['nonTrovati' => $nonTrovati, 'errors' => $errors,'statsfilename' => 'esito-importazione_' . $date . '.json'];
    }
}