<?php

namespace app\controllers;

use app\helpers\Utils;
use app\models\Movimento;
use app\models\Recupero;
use app\models\RecuperoSearch;
use Carbon\Carbon;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * RecuperoController implements the CRUD actions for Recupero model.
 */
class RecuperoController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Recupero models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new RecuperoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Recupero model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Recupero model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Recupero();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionCreateByIstanza($id)
    {
        $errors = [];
        if ($this->request->isPost) {
            $istanza = \app\models\Istanza::findOne($id);
            if ($istanza) {
                $dati = $this->request->post();
                $recupero = new Recupero();
                $recupero->id_istanza = $id;
                $recupero->importo = $dati['tipologia'] === "negativo" ? - floatval($dati['importo']) : floatval($dati['importo']);
                $recupero->recuperato = 0;
                $recupero->rateizzato = array_key_exists('rateizzato', $dati) ? 1 : 0;
                $recupero->num_rate = $recupero->rateizzato == 1 ? ($dati['numRate'] ?? $dati['numRate_hidden']) : null;
                $recupero->importo_rata = ($recupero->num_rate && $recupero->num_rate > 0) ? floatval($dati['importoRata'] ?? $dati['importoRata_hidden']) : null;
                $recupero->save();
                $errors = array_merge($errors, $recupero->errors);
                if ($recupero->rateizzato == 1 && $recupero->num_rate && $recupero->num_rate > 1) {
                    $date = Carbon::now()->subMonth()->startofmonth();
                    for ($i = 0; $i < intval($dati['numRatePagate'] ?? 0); $i++) {
                        $movimento = new Movimento();
                        $movimento->id_recupero = $recupero->id;
                        $movimento->importo = $dati['tipologia'] === "negativo" ? -$recupero->importo_rata : $recupero->importo_rata;
                        $movimento->data = $date->toDateString();
                        $movimento->num_rata = intval($dati['numRatePagate']) - $i ;
                        $movimento->contabilizzare = 0;
                        $movimento->id_conto = $istanza->getContoValido()->id;
                        $movimento->save();
                        $errors = array_merge($errors, $movimento->errors);
                        $date->subMonth()->startOfMonth();
                    }
                }
            }
            return $this->redirect(['/istanza/scheda', 'id' => $id]);
        }
    }

    /**
     * Updates an existing Recupero model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public
    function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model
        ]);
    }

    /**
     * Deletes an existing Recupero model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public
    function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Recupero model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Recupero the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected
    function findModel($id)
    {
        if (($model = Recupero::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
