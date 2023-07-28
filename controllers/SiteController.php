<?php

namespace app\controllers;

use app\helpers\Utils;
use app\models\Anagrafica;
use app\models\Conto;
use app\models\ContoCessionario;
use app\models\Distretto;
use app\models\enums\FileParisi;
use app\models\enums\PagamentiConElenchi;
use app\models\Gruppo;
use app\models\Isee;
use app\models\Istanza;
use app\models\Pagamento;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\XLSX\Sheet;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

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

    public function actionImport()
    {
        $parisiOk = $this->importaFileParisi('../import/parisi/out4.xlsx');
        //$conElenchi = $this->importaFileConElenchi('../import/pagamenti/con_elenchi/al 30-06-2023_con_elenchi.xlsx');
        echo ($parisiOk);
    }

    public function importaFileConElenchi($path)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $reader = ReaderEntityFactory::createReaderFromFile($path);
        $reader->open($path);
        $header = null;
        $rowIndex = 0;
        Pagamento::deleteAll();
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
                } else if ($newRow[$header[PagamentiConElenchi::IMPORTO]] !== "") {

                }
                $rowIndex++;
            }
        }

    }


    public function importaFileParisi($path)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $reader = ReaderEntityFactory::createReaderFromFile($path);
        $reader->open($path);
        $header = null;
        $rowIndex = 0;
        Isee::deleteAll();
        ContoCessionario::deleteAll();
        Conto::deleteAll();
        Istanza::deleteAll();
        Anagrafica::deleteAll();
        Gruppo::deleteAll();
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
                    $cessionario = null;
                    // find distretto where "nome" Like the first 2 character of $newRow[$header[FileParisi::DISTRETTO]]
                    $distretto = Distretto::find()->where(['like', 'nome', strtoupper(substr($newRow[$header[FileParisi::DISTRETTO]], 0, 2)). '%', false])->one();
                    //$distretto = Distretto::findOne(['nome' => $newRow[$header[FileParisi::DISTRETTO]]]);
                    $gruppo = Gruppo::findOne(['descrizione_gruppo_old' => substr($newRow[$header[FileParisi::GRUPPO]], 0, 1)]);
                    if (!$gruppo) {
                        $gruppo = new Gruppo();
                        $gruppo->descrizione_gruppo_old = substr($newRow[$header[FileParisi::GRUPPO]], 0, 1);
                        $gruppo->descrizione_gruppo = $gruppo->descrizione_gruppo_old;
                        $gruppo->save();
                        $errors = array_merge($errors, $gruppo->errors);
                    }
                    $disabile = Anagrafica::findOne(['codice_fiscale' => $newRow[$header[FileParisi::CF_DISABILE]]]);
                    if (!$disabile) {
                        $disabile = new Anagrafica();
                        $disabile->codice_fiscale = $newRow[$header[FileParisi::CF_DISABILE]];
                        $disabile->cognome_nome = $newRow[$header[FileParisi::DISABILE_NOME_COGNOME]];
                        // convert $newRow[$header[FileParisi::DISABILE_DATA_NASCITA]] from string format dd/mm/yyyy to int
                        $disabile->data_nascita = Utils::convertDateFromFormat($newRow[$header[FileParisi::DISABILE_DATA_NASCITA]]);
                        $disabile->save();
                        $errors = array_merge($errors, $disabile->errors);
                    }
                    if ($newRow[$header[FileParisi::CF_CESSIONARIO]] !== "") {
                        $cessionario = Anagrafica::findOne(['codice_fiscale' => $newRow[$header[FileParisi::CF_CESSIONARIO]]]);
                        if (!$cessionario) {
                            $cessionario = new Anagrafica();
                            $cessionario->codice_fiscale = $newRow[$header[FileParisi::CF_CESSIONARIO]];
                            $cessionario->cognome_nome = $newRow[$header[FileParisi::CESSIONARIO_NOME_COGNOME]];
                            // convert $newRow[$header[FileParisi::DISABILE_DATA_NASCITA]] from string format dd/mm/yyyy to int
                            $cessionario->data_nascita = Utils::convertDateFromFormat($newRow[$header[FileParisi::CESSIONARIO_DATA_NASCITA]]);
                            $cessionario->save();
                            $errors = array_merge($errors, $cessionario->errors);
                        }
                    }
                    if ($disabile && $distretto && $gruppo) {
                        $istanza = new Istanza();
                        $istanza->id_distretto = $distretto->id;
                        $istanza->riconosciuto = 1;
                        $istanza->id_gruppo = $gruppo->id;
                        $istanza->id_anagrafica_disabile = $disabile->id;
                        $istanza->attivo = $newRow[$header[FileParisi::ATTIVO]] === "SI" ? 1 : 0;
                        $istanza->data_decesso = Utils::convertDateFromFormat($newRow[$header[FileParisi::DISABILE_DATA_DECESSO]]);
                        if ($istanza->data_decesso)
                            $istanza->attivo = 0;
                        $istanza->note = $newRow[$header[FileParisi::NOTE]] . "<br />" . $newRow[$header[FileParisi::NOTE_ESCLUSIONE]] . "<br />" . $newRow[$header[FileParisi::NOTA_ALLERT]];
                        $istanza->nota_chiusura = $newRow[$header[FileParisi::NOTA_CHIUSO]];
                        $istanza->save();
                        $errors = array_merge($errors, $istanza->errors);
                        $conto = new Conto();
                        $conto->id_istanza = $istanza->id;
                        if ($cessionario)
                            $conto->iban = $newRow[$header[FileParisi::IBAN]];
                        if ($conto->iban !== "" || !$cessionario)
                            $conto->iban = $newRow[$header[FileParisi::DISABILE_IBAN]];
                        $conto->save();
                        $errors = array_merge($errors, $conto->errors);
                        $contoCessionario = new ContoCessionario();
                        $contoCessionario->id_conto = $conto->id;
                        if ($cessionario)
                            $contoCessionario->id_cessionario = $cessionario->id;
                        else
                            $contoCessionario->id_cessionario = $disabile->id;
                        $contoCessionario->save();
                        $errors = array_merge($errors, $contoCessionario->errors);
                        if (count($newRow) > $header[FileParisi::ISEE_INF] && $newRow[$header[FileParisi::ISEE_INF]] !== "") {
                            $isee = new Isee();
                            $isee->id_istanza = $istanza->id;
                            $isee->maggiore_25mila = $newRow[$header[FileParisi::ISEE_INF]] === 1 ? 0 : 1;
                            $isee->valido = 1;
                            $isee->save();
                            $errors = array_merge($errors, $isee->errors);
                        }
                    }
                    else
                        $errors[] = "Errore riga " . $rowIndex . ": " . $newRow[$header[FileParisi::DISABILE_NOME_COGNOME]] . " " . $newRow[$header[FileParisi::DISABILE_DATA_NASCITA]];
                    if (count($errors) > 0) {
                        echo("errore");
                    }
                }
                $rowIndex++;
            }
        }
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
    public
    function actionAbout()
    {
        return $this->render('about');
    }
}
