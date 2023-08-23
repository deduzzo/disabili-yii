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
        'allModels' => $model->ricoveros,
        'pagination' => false,
    ]),
    'options' => ['class' => 'grid-view small'],
    'columns' => [
        [
            'format' => 'raw',
            'attribute' => 'da',
            'label' => 'Da - A',
            'value' => function ($model) {
                return Yii::$app->formatter->asDate($model->da) . '<br />' . Yii::$app->formatter->asDate($model->a);
            }
        ],
        [
            'label' => 'Giorni',
            'value' => function ($model) {
                return $model->getNumGiorni();
            }
        ],
        [
            'label' => 'Importo',
            'value' => function ($model) {
                return Yii::$app->formatter->asCurrency($model->getImportoRicovero());
            }
        ],
        'descr_struttura',
        [
            'attribute' => 'recupero',
            'label' => 'Recuperato?',
            'value' => function ($model) {
                return $model->recupero ? ('Si, det. '.$model->determina->numero) : 'No';
            }
        ],
    ],
    'emptyText' => 'Nessun altro dato presente',

]);