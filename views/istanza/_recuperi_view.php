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
                return $model->recuperato ? 'Si' : ('No, mancano '.$model->getRateMancanti().' rate');
            }
        ],
        [
            'attribute' => 'rateizzato',
            'label' => 'Rateizzato?',
            'value' => function ($model) {
                return $model->rateizzato ? (($model->getUltimaRataSeDiversa() ? $model->num_rate -1 : $model->num_rate).' rate '.($model->importo_rata ? ' da '.Yii::$app->formatter->asCurrency($model->importo_rata) .
                        ($model->getUltimaRataSeDiversa() ? ('<br />Ultima rata: '.Yii::$app->formatter->asCurrency($model->getUltimaRataSeDiversa())) : '')
                        : ' variabili')) : 'No';
            },
            'format' => 'raw',
        ],
        // button edit
        'note:ntext',
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update}',
            'buttons' => [
                'update' => function ($url, $model) {
                    return Html::a('<i class="bi bi-pencil"></i>', ['recupero/update', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary']);
                },
            ],
        ],
    ],
    'emptyText' => 'Nessun altro dato presente',

]);