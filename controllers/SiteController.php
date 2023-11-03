<?php

namespace app\controllers;


use Amp\Loop;
use Amp\Process\Process;
use app\helpers\GdriveHelper;
use app\helpers\Utils;
use app\models\Anagrafica;
use app\models\AnagraficaAltricampi;
use app\models\Conto;
use app\models\ContoCessionario;
use app\models\DeterminaGruppoPagamento;
use app\models\Distretto;
use app\models\enums\FileParisi;
use app\models\enums\ImportoBase;
use app\models\enums\PagamentiConElenchi;
use app\models\enums\PagamentiConIban;
use app\models\Gruppo;
use app\models\GruppoPagamento;
use app\models\Isee;
use app\models\Istanza;
use app\models\Movimento;
use app\models\Recupero;
use app\models\Ricovero;
use app\models\UploadForm;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\XLSX\Sheet;
use Google_Client;
use Google_Service_Drive;
use PHP_IBAN\IBAN;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\models\LoginForm;
use app\models\ContactForm;
use yii\web\UploadedFile;

class SiteController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
    // add permission

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    public function actionImport($importaElenchi = false, $importaFileParisi = false, $importaPagamenti = false, $append = true)
    {
        $attivo = false;
        if ($attivo) {
            // TEST
            //select i.id,i.id_distretto, a.cognome_nome, i.id_gruppo from istanza i, anagrafica a where i.id_anagrafica_disabile = a.id AND i.attivo= 1 AND i.id not in (
            //select DISTINCT c.id_istanza from movimento m, conto c where m.id_conto = c.id AND periodo_da >= "2023-07-01")
            if (!$append)
                DeterminaGruppoPagamento::deleteAll();
            if ($importaElenchi)
                GruppoPagamento::deleteAll();
            if (!$append) {
                Movimento::deleteAll();
                ContoCessionario::deleteAll();
                Conto::deleteAll();
            }
            if ($importaFileParisi)
                $this->importaFileParisi('../import/parisi/out2.xlsx', true);
            else if ($importaPagamenti)
                $this->importaPagamenti($importaElenchi, $importaPagamenti);
        }
    }

    /*    public function actionRicoveri() {
            $ricovero = new Ricovero();
            $ricovero->da = "2024-01-01";
            $ricovero->a = "2024-02-10";
            print_r($ricovero->getNumGiorni());
        }*/

    public function actionAggiornaIsee()
    {
        $nonTrovati = [];
        $errors = [];
        $res = Yii::$app->getDb()->createCommand("select distinct istanza.id from istanza where istanza.attivo = true AND istanza.id not in
                                     (select distinct istanza.id from istanza, isee where isee.id_istanza = istanza.id)")->queryAll();
        foreach ($res as $istanza) {
            $istanza = Istanza::findOne($istanza['id']);
            $iseeMaggiore25 = null;
            $lastMovimento = $istanza->getLastMovimentoBancario();
            if ($lastMovimento) {
                if ($lastMovimento->importo === floatval(ImportoBase::MINORE_25K_V1))
                    $iseeMaggiore25 = false;
                else if ($lastMovimento->importo === floatval(ImportoBase::MAGGIORE_25K_V1))
                    $iseeMaggiore25 = true;
                if ($iseeMaggiore25 !== null) {
                    $isee = new Isee();
                    $isee->id_istanza = $istanza->id;
                    $isee->maggiore_25mila = $iseeMaggiore25;
                    $isee->valido = true;
                    $isee->save();
                    if ($isee->errors)
                        $errors = array_merge($errors, ['isee-' . $istanza->id => $isee->errors]);
                } else
                    $nonTrovati[] = $istanza->id;
            } else
                $nonTrovati[] = $istanza->id;
        }
        // save $nonTrovati keys as text file
        $fp = fopen('../import/non_trovati.txt', 'w');
        fwrite($fp, implode("\t", $nonTrovati));
        fclose($fp);
    }

    public function actionImportaSoloAlcuniPagamenti()
    {
        $azzeraPagamenti = true;
        $ids = Istanza::find()->where(['id_distretto' => 7])->select(['id'])->column();
        if ($azzeraPagamenti) {
            foreach ($ids as $id) {
                $istanza = Istanza::findOne(intval($id));
                $istanza->cancellaMovimentiCollegati();
            }
        }
        $this->importaFileConElenchi('../import/pagamenti/con_iban/con_iban.xlsx', $ids);
        echo "OK";
    }

    private function importaPagamenti($importaElenchi, $importaPagamenti)
    {
        if ($importaElenchi)
            $this->importaFilePagamenti('../import/pagamenti/con_elenchi/con_elenchi.xlsx');
        if ($importaPagamenti)
            $this->importaFileConElenchi('../import/pagamenti/con_iban/con_iban.xlsx');
    }

    public function importaFilePagamenti($path)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $reader = ReaderEntityFactory::createReaderFromFile($path);
        $reader->open($path);
        $header = null;
        $rowIndex = 0;
        $out = [];
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
                } else if ($newRow[$header[PagamentiConElenchi::IMPORTO]] !== "") {
                    if (!array_key_exists($newRow[$header[PagamentiConElenchi::PROGRESSIVO]], $out)) {
                        $gruppoPagamento = new GruppoPagamento();
                        $gruppoPagamento->descrizione = $newRow[$header[PagamentiConElenchi::DESCRIZIONE]];
                        $gruppoPagamento->progressivo = $newRow[$header[PagamentiConElenchi::PROGRESSIVO]];
                        $gruppoPagamento->save();
                        $out[$newRow[$header[PagamentiConElenchi::PROGRESSIVO]]] = $gruppoPagamento;
                    }
                }
                $rowIndex++;
            }
        }
        return $out;
    }

    private function importaFileConElenchi($path, $soloQuestiId = [])
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $reader = ReaderEntityFactory::createReaderFromFile($path);
        $reader->open($path);
        $header = null;
        $rowIndex = 0;
        $nonTrovati = [];
        $errors = [];
        $gruppiPagamento = GruppoPagamento::find([])->all();
        $gruppiPagamentoMap = [];
        $istanze = null;
        $lastCf = null;
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
                    $consideraSoloAttivi = true;
                    if ($lastCf !== strtoupper(trim($newRow[$header[PagamentiConIban::CODICE_FISCALE]]))) {
                        $istanze = Istanza::find()->innerJoin('anagrafica a', 'a.id = istanza.id_anagrafica_disabile')->where(['a.codice_fiscale' => strtoupper(trim($newRow[$header[PagamentiConIban::CODICE_FISCALE]]))]);
                        if ($consideraSoloAttivi)
                            $istanze = $istanze->andWhere(['istanza.attivo' => true]);
                        $istanze = $istanze->all();

                    }
                    if ($istanze && count($soloQuestiId) > 0) {
                        if (count($istanze) === 1) {
                            $istanza = $istanze[0];
                            $lastCf = strtoupper(trim($newRow[$header[PagamentiConIban::CODICE_FISCALE]]));
                            if (!in_array($istanza->id, $soloQuestiId))
                                $istanze = null;
                        } else
                            $error = true;
                    }
                    if ($istanze && count($istanze) === 1) {
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
                        $movimento = new Movimento();
                        $movimento->id_conto = $conto->id;
                        $movimento->is_movimento_bancario = true;
                        $movimento->periodo_da = Utils::convertDateFromFormat($newRow[$header[PagamentiConIban::DAL]]);
                        $movimento->periodo_a = Utils::convertDateFromFormat($newRow[$header[PagamentiConIban::AL]]);
                        $movimento->data = $movimento->periodo_a;
                        $movimento->importo = $newRow[$header[PagamentiConIban::IMPORTO]];
                        $movimento->id_gruppo_pagamento = isset($gruppiPagamentoMap[$newRow[$header[PagamentiConIban::ID_ELENCO]]]) ? $gruppiPagamentoMap[$newRow[$header[PagamentiConIban::ID_ELENCO]]]->id : null;
                        if (isset($gruppiPagamentoMap[$newRow[$header[PagamentiConIban::ID_ELENCO]]]) && !$gruppiPagamentoMap[$newRow[$header[PagamentiConIban::ID_ELENCO]]]->data) {
                            $gruppiPagamentoMap[$newRow[$header[PagamentiConIban::ID_ELENCO]]]->data = Utils::convertDateFromFormat($newRow[$header[PagamentiConIban::AL]]);
                            $gruppiPagamentoMap[$newRow[$header[PagamentiConIban::ID_ELENCO]]]->save();
                            if ($gruppiPagamentoMap[$newRow[$header[PagamentiConIban::ID_ELENCO]]]->errors)
                                $errors = array_merge($errors, ['gruppoPagamento-' . $newRow[$header[PagamentiConIban::CODICE_FISCALE]] => $gruppiPagamentoMap[$newRow[$header[PagamentiConIban::ID_ELENCO]]]->errors]);
                        }
                        $movimento->contabilizzare = 0;
                        $movimento->save();
                        if ($movimento->errors)
                            $errors = array_merge($errors, ['movimento-' . $newRow[$header[PagamentiConIban::CODICE_FISCALE]] => $movimento->errors]);
                    } else {
                        if (($istanze && count($soloQuestiId) > 0 && count($istanze) !== 1) || count($soloQuestiId) === 0)
                            if (!array_key_exists(strtoupper(trim($newRow[$header[PagamentiConIban::CODICE_FISCALE]])), $nonTrovati))
                                $nonTrovati[strtoupper(trim($newRow[$header[PagamentiConIban::CODICE_FISCALE]]))] = $newRow;
                    }
                }
                $rowIndex++;
            }
        }
        $reader->close();
        // save $nonTrovati as Json File
        $fp = fopen('../import/pagamenti/con_iban/non_trovati.json', 'w');
        $fp2 = fopen('../import/pagamenti/con_iban/errori.json', 'w');
        fwrite($fp, json_encode($nonTrovati));
        fwrite($fp2, json_encode($errors));
        fclose($fp);
        return $nonTrovati;
    }


    public function importaFileParisi($path, $aggiorna = false)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $reader = ReaderEntityFactory::createReaderFromFile($path);
        $reader->open($path);
        $header = null;
        $rowIndex = 0;
        if (!$aggiorna) {
            AnagraficaAltricampi::deleteAll();
            Ricovero::deleteAll();
            Isee::deleteAll();
            ContoCessionario::deleteAll();
            Conto::deleteAll();
            Movimento::deleteAll();
            Recupero::deleteAll();
            Istanza::deleteAll();
            Anagrafica::deleteAll();
            //Gruppo::deleteAll();
        }
        $errors = [];

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
                } else if ($newRow[$header[FileParisi::CHIUSO]] !== "") {
                    $d = strtoupper(substr($newRow[$header[FileParisi::DISTRETTO]], 1, 5));
                    if (!$aggiorna || strpos($d, "IST") !== false) {
                        $cessionario = null;
                        // find distretto where "nome" Like the first 2 character of $newRow[$header[FileParisi::DISTRETTO]]
                        // if d includes "MISTRETTA"
                        $distretto = Distretto::find()->where(['like', 'nome', '%' . strtoupper(substr($newRow[$header[FileParisi::DISTRETTO]], 1, 4)) . '%', false])->one();
                        //$distretto = Distretto::findOne(['nome' => $newRow[$header[FileParisi::DISTRETTO]]]);
                        $gruppo = Gruppo::findOne(['descrizione_gruppo_old' => substr($newRow[$header[FileParisi::GRUPPO]], 0, 1)]);
                        if (!$gruppo) {
                            $gruppo = new Gruppo();
                            $gruppo->descrizione_gruppo_old = substr($newRow[$header[FileParisi::GRUPPO]], 0, 1);
                            $gruppo->descrizione_gruppo = $gruppo->descrizione_gruppo_old;
                            $gruppo->save();
                            if ($gruppo->errors)
                                $errors = array_merge($errors, ['gruppo' => $gruppo->errors]);
                        }
                        $disabile = Anagrafica::findOne(['codice_fiscale' => $newRow[$header[FileParisi::CF_DISABILE]]]);
                        if (!$disabile) {
                            $disabile = new Anagrafica();
                            $disabile->codice_fiscale = strtoupper(trim($newRow[$header[FileParisi::CF_DISABILE]]));
                            $disabile->cognome_nome = strtoupper(trim($newRow[$header[FileParisi::DISABILE_NOME_COGNOME]]));
                            // convert $newRow[$header[FileParisi::DISABILE_DATA_NASCITA]] from string format dd/mm/yyyy to int
                            $disabile->data_nascita = Utils::convertDateFromFormat($newRow[$header[FileParisi::DISABILE_DATA_NASCITA]]);
                            $disabile->save();
                            if ($disabile->errors)
                                $errors = array_merge($errors, ['disabile-' . $newRow[$header[FileParisi::CF_DISABILE]] => $disabile->errors]);
                        }
                        if (strtoupper(trim($newRow[$header[FileParisi::CF_CESSIONARIO]])) !== "") {
                            $cessionario = Anagrafica::findOne(['codice_fiscale' => strtoupper(trim($newRow[$header[FileParisi::CF_CESSIONARIO]]))]);
                            if (!$cessionario) {
                                $cessionario = new Anagrafica();
                                $cessionario->codice_fiscale = strtoupper(trim($newRow[$header[FileParisi::CF_CESSIONARIO]]));
                                $cessionario->cognome_nome = strtoupper(trim($newRow[$header[FileParisi::CESSIONARIO_NOME_COGNOME]]));
                                // convert $newRow[$header[FileParisi::DISABILE_DATA_NASCITA]] from string format dd/mm/yyyy to int
                                $cessionario->data_nascita = Utils::convertDateFromFormat($newRow[$header[FileParisi::CESSIONARIO_DATA_NASCITA]]);
                                $cessionario->save();
                                if ($cessionario->errors)
                                    $errors = array_merge($errors, ['cessionario-' . $newRow[$header[FileParisi::CF_DISABILE]] => $cessionario->errors]);
                            }
                        }
                        if ($disabile && $distretto && $gruppo) {
                            $istanza = new Istanza();
                            $istanza->id_distretto = $distretto->id;
                            $istanza->riconosciuto = 1;
                            $istanza->id_gruppo = $gruppo->id;
                            $istanza->classe_disabilita = $newRow[$header[FileParisi::CLASSE_DISABILITA]];
                            $istanza->patto_di_cura = 1;
                            $istanza->id_anagrafica_disabile = $disabile->id;
                            if ($cessionario)
                                $istanza->id_caregiver = $cessionario->id;
                            $istanza->attivo = $newRow[$header[FileParisi::ATTIVO]] === "SI" ? 1 : 0;
                            $istanza->data_decesso = Utils::convertDateFromFormat($newRow[$header[FileParisi::DISABILE_DATA_DECESSO]]);
                            $istanza->attivo = $newRow[$header[FileParisi::CHIUSO]] === "SI" ? 0 : 1;
                            $istanza->note = $newRow[$header[FileParisi::NOTE]] . "<br />" . $newRow[$header[FileParisi::NOTE_ESCLUSIONE]] . "<br />" . $newRow[$header[FileParisi::NOTA_ALLERT]];
                            $istanza->nota_chiusura = ($newRow[$header[FileParisi::RINUNZIA]] === "SI" ? "RINUNCIA - " : "") . $newRow[$header[FileParisi::NOTA_CHIUSO]];
                            $istanza->save();
                            if ($istanza->errors)
                                $errors = array_merge($errors, ['istanza-' . $newRow[$header[FileParisi::CF_DISABILE]] => $istanza->errors]);
                            $contoValido = new IBAN(strtoupper(trim($newRow[$header[FileParisi::IBAN]])) !== "" ? strtoupper(trim($newRow[$header[FileParisi::IBAN]])) : strtoupper(trim($newRow[$header[FileParisi::DISABILE_IBAN]])));
                            if ($contoValido->Verify()) {
                                $conto = new Conto();
                                $conto->id_istanza = $istanza->id;
                                if ($cessionario)
                                    $conto->iban = strtoupper(trim($newRow[$header[FileParisi::IBAN]]));
                                if ($conto->iban === "" || $conto->iban === null || !$cessionario)
                                    $conto->iban = strtoupper(trim($newRow[$header[FileParisi::DISABILE_IBAN]]));
                                $conto->save();
                                if ($conto->errors)
                                    $errors = array_merge($errors, ['conto-' . $newRow[$header[FileParisi::CF_DISABILE]] => $conto->errors]);
                                $contoCessionario = new ContoCessionario();
                                $contoCessionario->id_conto = $conto->id;
                                if ($cessionario)
                                    $contoCessionario->id_cessionario = $cessionario->id;
                                else
                                    $contoCessionario->id_cessionario = $disabile->id;
                                $contoCessionario->save();
                                if ($contoCessionario->errors)
                                    $errors = array_merge($errors, ['contoCessionario-' . $newRow[$header[FileParisi::CF_DISABILE]] => $contoCessionario->errors]);
                            }
                            if (count($newRow) > $header[FileParisi::ISEE_INF] && $newRow[$header[FileParisi::ISEE_INF]] !== "") {
                                $isee = new Isee();
                                $isee->id_istanza = $istanza->id;
                                $isee->maggiore_25mila = $newRow[$header[FileParisi::ISEE_INF]] === 1 ? 0 : 1;
                                $isee->valido = 1;
                                $isee->save();
                                if ($isee->errors)
                                    $errors = array_merge($errors, ['isee-' . $newRow[$header[FileParisi::CF_DISABILE]] => $isee->errors]);
                            }
                        } else
                            $errors = array_merge($errors, ["Errore riga " . $rowIndex . ": " . $newRow[$header[FileParisi::DISABILE_NOME_COGNOME]] . " " . $newRow[$header[FileParisi::CF_DISABILE]]]);


                        // aggiorna
                        $istanza = Istanza::find()->innerJoin('anagrafica a', 'a.id = istanza.id_anagrafica_disabile')->where(['a.codice_fiscale' => strtoupper(trim($newRow[$header[FileParisi::CF_DISABILE]]))])->one();
                        if ($istanza) {
                            $rawData = [];
                            foreach ($header as $head => $valHeader) {
                                $rawData[$head] = $newRow[$valHeader];
                            }
                            // save $rawData as Json in $istanza->rawdata_json
                            $istanza->rawdata_json = json_encode($rawData);
                            $istanza->save();
                        }
                    }
                }
                $rowIndex++;
            }
        }
        $reader->close();
        $fp = fopen('../import/parisi/esito-importazione.json', 'w');
        fwrite($fp, json_encode($errors));
        fclose($fp);
        if (count($errors) > 0)
            return false;
        else
            return true;
    }


    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionUpload()
    {
        $model = new UploadForm();
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $model->files = UploadedFile::getInstances($model, 'files');
            $model->upload();
        }

        return $this->render('upload', [
            'files' => $model,
        ]);
    }

    public function actionErrore()
    {
        $this->layout = "errore";
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            return $this->render('errore', ['exception' => $exception]);
        } else
            return $this->redirect(['site/index']);
    }

    public function actionAuthGoogle()
    {
        $client = new Google_Client();
        $client->setClientId(Yii::$app->params['gdrive_clientID']);
        $client->setClientSecret(Yii::$app->params['gdrive_secret']);
        $client->setRedirectUri('https://disabili.robertodedomenico.it/site/auth-google');
        $client->addScope(Google_Service_Drive::DRIVE);

        if (isset($_GET['code'])) {
            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            Yii::$app->session->set('access_token', $token);
            header('Location: ' . filter_var('https://disabili.robertodedomenico.it', FILTER_SANITIZE_URL));
            exit();
        }

        if (!Yii::$app->session->get('access_token')) {
            $authUrl = $client->createAuthUrl();
            header('Location: ' . $authUrl);
            exit();
        }
    }

    public function actionBackupdb() {
        Utils::dumpDb();
    }

    public function actionTestGoogle()
    {
        $spid = "1ofNJ8KOG-mCMdnS5mum0V_mBmZ5alvKB62FvZKxzB3A";
        $gdrive = new GdriveHelper();
        $gdrive->getSpreeadsheetData($spid);
    }

    public function actionTestNode() {
        Loop::run(function () {
            // Crea il comando per eseguire lo script Node.js
            $command = Yii::$app->params['nodeBinPath'].' index.js';

            $process = new Process($command, "../node/");
            yield $process->start(); // Avvia il processo asincronamente

            $output = '';
            $error = '';

            // Legge stdout
            while (($chunk = yield $process->getStdout()->read()) !== null) {
                $output .= $chunk;
            }

            // Legge stderr
            while (($chunk = yield $process->getStderr()->read()) !== null) {
                $error .= $chunk;
            }

            // Attendere che il processo termini
            yield $process->join();

            if ($error) {
                echo "Errore dello script Node.js: " . trim($error) . "\n";
            } else {
                // Stampa l'output se non ci sono errori
                echo "Output dello script Node.js: " . trim($output) . "\n";
            }

            // Termina il loop di eventi
            Loop::stop();
        });

    }

}
