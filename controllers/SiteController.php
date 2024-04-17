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
use app\models\enums\TipologiaDatiCategoria;
use app\models\Gruppo;
use app\models\GruppoPagamento;
use app\models\Isee;
use app\models\Istanza;
use app\models\Movimento;
use app\models\Recupero;
use app\models\Ricovero;
use app\models\UploadForm;
use app\models\User;
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
        Utils::verificaChiusuraAutomaticaIstanze();
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

    public function actionIstanzeContoNonValido() {
        $out = "";
        foreach (Istanza::find()->where(['attivo' => true])->all() as $istanza) {
            /* @var $istanza Istanza */
            $conto = $istanza->getContoValido();
            if (!$conto)
                $out .= $istanza->id . "\n";
        }
        echo $out;
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
            if ($model->tipo == TipologiaDatiCategoria::MOVIMENTI_CON_IBAN)
                $model->setScenario(UploadForm::SCENARIO_IMPORT_PAGAMENTI);
            $model->files = UploadedFile::getInstances($model, 'files');
            if ($model->files && $model->validate())
                $model->upload();
            else
                Yii::$app->session->setFlash('error', 'Verificare gli errori nel form di upload..');
        }

        return $this->render('upload', [
            'files' => $model,
        ]);
    }

    public function actionCreateUser() {
        $user = new User();
        $user->email = "mauro.mandolfino@asp.messina.it";
        $user->username = "mauro.mandolfino";
        $user->password_hash = Yii::$app->security->generatePasswordHash("Mauro1234.");
        $user->auth_key = Yii::$app->security->generateRandomString();
        $user->save();
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
