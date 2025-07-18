<?php

use app\models\AnagraficaAltricampi;
use app\models\RicoveroSearch;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Istanza $model */

$searchModel = new RicoveroSearch();
$searchModel->id_istanza = $model->id;
$dataProvider = $searchModel->search(Yii::$app->request->queryParams);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'options' => [
        'tag' => 'div',
        'class' => 'grid-view small dataTable-wrapper dataTable-loading no-footer sortable searchable fixed-columns',
        'id' => 'datatable',
    ],
    'tableOptions' => [
        'class' => 'table table-striped dataTable-table',
        'id' => 'table1',
    ],
    'columns' => [
        'id',
        [
            'format' => 'raw',
            'attribute' => 'da',
            'label' => 'Da - A',
            'value' => function ($model) {
                return Yii::$app->formatter->asDate($model->da) . '<br />' . ($model->a ? Yii::$app->formatter->asDate($model->a) : " in corso");
            }
        ],
        [
            'label' => 'Durata',
            'value' => function ($model) {
                $numGiorni = $model->getNumGiorni();
                if ($numGiorni)
                    return "<b>" . ($numGiorni['mesi'] > 0 ? $numGiorni["mesi"] . " mes" . ($numGiorni["mesi"] === 1 ? "e " : "i") : "") . ($numGiorni["giorni"] >0 ? (($numGiorni["mesi"] >0 ? " e " : "").$numGiorni["giorni"] . " giorn" . ($numGiorni["giorni"] === 1 ? "o" : "i")) : "") . "</b>";
                else
                    return "-";
            },
            'format' => 'raw'
        ],
        [
            'label' => 'Importo',
            'value' => function ($model) {
                return Yii::$app->formatter->asCurrency($model->getImportoRicovero());
            },
            'format' => 'raw'
        ],
        'cod_struttura',
        [
            'attribute' => 'contabilizzare',
            'label' => 'Recuperato?',
            'format' => 'raw',
            'value' => function ($model) {
                return !$model->contabilizzare ?
                    ($model->recupero ?
                        ("<span class='badge bg-success'>".($model->determina ? ("Si, det. " . $model->determina->numero . " - ") : ""). "#". $model->id_recupero. " - ". ($model->recupero->chiuso ? "Recuperato" : (" Residuo:". Yii::$app->formatter->asCurrency($model->recupero->getImportoResiduo()))). "</span>") : "<span class='badge bg-warning'>IMPORT. PRECEDENTE</span>") :
                    "<span class='badge bg-primary'>DA CONTABILIZZARE</span>";

            }
        ],
        [
            'attribute' => 'note',
            'format' => 'raw',
            'value' => function ($model) {
                return '<i class="bi bi-info-circle" style="margin-left: 5px" data-bs-toggle="tooltip" data-bs-html="true" data-bs-original-title="'.Html::encode($model->note).'"></i>';
            }
        ]
    ],
    'emptyText' => 'Nessun altro dato presente',

]);