<?php

use app\models\AnagraficaAltricampi;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Anagrafica $model */
/** @var string $categoria */

$anagraficaAltricampi = AnagraficaAltricampi::find()->innerJoin('tipologia_dati t','t.id = anagrafica_altricampi.id_tipologia')
    ->where(['id_anagrafica' => $model->id ?? null,'t.categoria' => $categoria])->all();
echo GridView::widget([
    'dataProvider' => new \yii\data\ArrayDataProvider([
        'allModels' => $anagraficaAltricampi,
        'pagination' => false,
    ]),
    'columns' => [
        'tipologia.descrizione',
        'valore',
    ],
    'emptyText' => 'Nessun altro dato presente',
    'showHeader' => false,
    'showFooter'=> false,
    'summary' => '',
]);