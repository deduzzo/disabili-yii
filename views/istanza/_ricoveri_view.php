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
    'options' => ['class' => 'grid-view small'],
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
                    return "<b>" . ($numGiorni['mesi'] > 0 ? $numGiorni["mesi"] . " mes" . ($numGiorni["mesi"] === 1 ? "e e " : "i e ") : "") . $numGiorni["giorni"] . " giorni" . "</b>";
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
                return $model->contabilizzare ?
                    ($model->recupero ? ("<span class='badge bg-success'>Si, det. " . $model->determina->numero . "</span>") : "<span class='badge bg-warning'>DA RECUPERARE</span>") : "<span class='badge bg-primary'>IMPORT. PREC.</span>";

            }
        ],
    ],
    'emptyText' => 'Nessun altro dato presente',

]);