<?php


use app\models\Istanza;
use app\models\MovimentoSearch;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var Istanza $istanza */

$searchModel = new MovimentoSearch();
$dataProvider = $searchModel->search(Yii::$app->request->queryParams, $istanza);

Pjax::begin();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'options' => ['class' => 'grid-view small'],
    'columns' => [
        [
            'format' => 'raw',
            'attribute' => 'periodo_da',
            'label' => 'Periodo',
            'value' => function ($model) {
                if ($model->data)
                    return Yii::$app->formatter->asDate($model->data);
                else
                    return Yii::$app->formatter->asDate($model->periodo_da) . ' - ' . Yii::$app->formatter->asDate($model->periodo_a);
            }
        ],
        'importo:currency',
        'note:ntext',
    ],
    'emptyText' => 'Nessun movimento presente',

]);

Pjax::end();