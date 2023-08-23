<?php

use app\models\AnagraficaAltricampi;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Istanza $model */

echo GridView::widget([
    'dataProvider' => new ArrayDataProvider([
        'allModels' => $model->recuperos,
        'pagination' => false,
    ]),
    'options' => ['class' => 'grid-view small'],
    'columns' => [
        'importo:currency',
        [
            'attribute' => 'recuperato',
            'label' => 'Recuperato?',
            'value' => function ($model) {
                return $model->recuperato ? 'Si' : 'No';
            }
        ],
        [
            'attribute' => 'rateizzato',
            'label' => 'Rateizzato?',
            'value' => function ($model) {
                return $model->rateizzato ? ('Si, in '.$model->num_rate.' rate '.($model->importo_rata ? ' da '.Yii::$app->formatter->asCurrency($model->importo_rata) : ' variabili')) : 'No';
            }
        ],
        'note:ntext',
    ],
    'emptyText' => 'Nessun altro dato presente',

]);