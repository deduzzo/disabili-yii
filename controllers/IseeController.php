<?php

namespace app\controllers;

use app\models\Isee;
use app\models\IseeSearch;
use app\models\Istanza;
use app\models\Movimento;
use Carbon\Carbon;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * IseeController implements the CRUD actions for Isee model.
 */
class IseeController extends Controller
{
    /**
     * @inheritDoc
     */

    /**
     * Lists all Isee models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new IseeSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Isee model.
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
     * Creates a new Isee model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Isee();

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
     * Updates an existing Isee model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Isee model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $isee = $this->findModel($id);
        if ($isee) {
            $idIstanza = $isee->id_istanza;
            $isee->delete();
            if (Yii::$app->request->isAjax) {
                Yii::$app->session->setFlash('success', 'ISEE cancellato con successo.');
                return $this->redirect(['/istanza/scheda', 'id' => $idIstanza]);
            } else {
                return $this->redirect(['index']);
            }
        }
        return $this->redirect(['index']);
    }


    public function actionAggiungiDaIstanza()
    {
        $data = $this->request->post();
        $istanza = Istanza::findOne($data['id_istanza']);
        if ($istanza && $this->request->isPost && ($data['tipologia'] === 'maggiore' || $data['tipologia'] === 'minore')) {
            $iseeIstanza = Isee::find()->where(['id_istanza' => $istanza->id]);
            if (isset($data['anno_riferimento']) && $data['anno_riferimento'] !== "")
                $iseeIstanza->andWhere(['anno_riferimento' => $data['anno_riferimento']]);
            $iseeIstanza->all();
            if ($iseeIstanza->count() === 0)
                $iseeIstanza = Isee::find()->where(['id_istanza' => $istanza->id])->all();
            foreach ($iseeIstanza as $isee) {
                if ($isee->valido) {
                    $isee->valido = false;
                    $isee->valido_fino_a = Carbon::now()->toDateString();
                    $isee->save();
                }
            }
            $model = new Isee();
            $model->id_istanza = $istanza->id;
            $model->data_presentazione = Carbon::createFromFormat('Y-m-d', $data['data_presentazione'])->toDateString();
            $model->anno_riferimento = (isset($data['anno_riferimento']) && $data['anno_riferimento'] !== "") ? intval($data['anno_riferimento']) : null;
            $model->valido = true;
            $model->maggiore_25mila = $data['tipologia'] === 'maggiore';
            $model->save();
            if (count($model->errors) === 0) {
                Yii::$app->session->setFlash('success', 'ISEE aggiunto correttamente');
                return $this->redirect(['/istanza/scheda', 'id' => $istanza->id]);
            }
        }
        return $this->redirect(['/istanza/index', 'id' => $istanza->id]);
    }

    /**
     * Finds the Isee model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Isee the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Isee::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
