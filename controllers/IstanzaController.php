<?php

namespace app\controllers;

use app\helpers\GdriveHelper;
use app\helpers\Utils;
use app\models\Determina;
use app\models\Istanza;
use app\models\IstanzaSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * IstanzaController implements the CRUD actions for Istanza model.
 */
class IstanzaController extends Controller
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
     * Lists all Istanza models.
     *
     * @return string
     */
    public function actionIndex()
    {
        // disable main layout
        Utils::verificaChiusuraAutomaticaIstanze();
        $searchModel = new IstanzaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, isset($this->request->post()['exportWDG']));

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Istanza model.
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
     * Creates a new Istanza model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Istanza();

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

    /**
     * Updates an existing Istanza model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id, $fromScheda = false)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            if ($fromScheda) {
                Yii::$app->session->setFlash('success', 'Istanza aggiornata con successo.');
                return $this->redirect(['scheda', 'id' => $model->id]);
            } else
                return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Istanza model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Istanza model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Istanza the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Istanza::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    //&rifinalizzaDetermina=true&idDetermina=1
    public function actionScheda($id, $rifinalizzaDetermina = false, $idDetermina = null)
    {
        Utils::verificaChiusuraAutomaticaIstanze();
        $model = $this->findModel($id);
        if ($rifinalizzaDetermina) {
            $determina = Determina::find();
            if ($idDetermina)
                $determina = $determina->where(['id' => $idDetermina]);
            $determina = $determina->orderBy(['data' => SORT_DESC])->one();
            if ($determina && $model) {
                $model->finalizzaMensilita($determina->id);
            }
            // show success message
            Yii::$app->session->setFlash('success', 'Istanza aggiornata con successo.');
            return $this->redirect(['scheda', 'id' => $id]);
        } else if ($model && (Yii::$app->params['gdrive_enabled'] || !isset(Yii::$app->params['gdrive_enabled']))) {
            $gdhelper = new GdriveHelper();
            $folder = $gdhelper->existFolderWithNameThatStartWith("#$model->id", $gdhelper->folderId);
            if (!$folder)
                $folder = $gdhelper->createFolderIfNotExist("#$model->id - " . $model->getNominativoDisabile(), $gdhelper->folderId);
            if ($folder->getName() !== "#$model->id - " . $model->getNominativoDisabile())
                $gdhelper->renameFolder($folder->getId(), "#$model->id - " . $model->getNominativoDisabile());
            $files = $gdhelper->getAllFilesInFolder($folder->getId());
        }
        return $this->render('scheda', [
            'istanza' => $model,
            'files' => $files ?? [],
        ]);
    }

    public function invalidaContiNonValidati() {
        $conti = Istanza::find()->where(['id_istanza' => $this->id,'attivo' => true, 'validato' => false])->all();
        foreach ($conti as $conto) {
            $conto->attivo = false;
            $conto->data_disattivazione = date('Y-m-d');
            $conto->note = "Conto non validato, disattivato da sistema";
            $conto->save();
        }
    }

    public function actionModifica()
    {
        if ($this->request->isPost) {
            $istanza = $this->findModel($this->request->post('id-istanza'));
            if ($istanza) {
                $istanza->patto_di_cura = $this->request->post('patto-di-cura') === "on";
                $istanza->data_firma_patto = $this->request->post('data-patto-cura') ?? null;
                $istanza->rinuncia = $this->request->post('rinuncia') !== null;
                $istanza->data_chiusura = $this->request->post('data-chiusura') ?? null;
                $istanza->attivo = $this->request->post('stato') === "attivo";
                $istanza->riconosciuto = $this->request->post('riconosciuto') === "on";
                $istanza->chiuso = $this->request->post('aperto-chiuso') === "chiuso";
                $istanza->data_decesso = $this->request->post('data-decesso') ?? null;
                $istanza->liquidazione_decesso_completata = $this->request->post('liquidazione-decesso-completata') === "on";
                $istanza->data_liquidazione_decesso = $this->request->post('data-liquidazione') ?? null;
                $istanza->save();
                if ($istanza->errors)
                    Yii::$app->session->setFlash('error', 'Errore durante il salvataggio dell\'istanza.');
                else
                    Yii::$app->session->setFlash('success', 'Istanza aggiornata con successo.');
            }
            return $this->redirect(['scheda', 'id' => $istanza->id]);
        }
    }
}
