<?php

use app\models\Distretto;
use app\models\enums\DatiTipologia;
use app\models\enums\IseeType;
use app\models\Gruppo;
use app\models\Istanza;
use yii\bootstrap5\Html;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\SimulazioneDeterminaSearch $searchModel */

$this->title = 'Simulazione determina';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'simulazione-determina']) ?>
<?php $formatter = \Yii::$app->formatter; ?>

<div class="card">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-toolbar">
                        <h4 class="card-title">Opzioni</h4>
                    </div>
                </div>
                <div class="card-body">
                    <!-- add select box for distretto -->
                    <?= Html::beginForm(['determina/'], 'get', ['data-pjax' => '', 'class' => 'form-inline']) ?>
                    <?= Html::dropDownList('distretto', $distretto, ArrayHelper::map(Distretto::find()->all(), 'id', 'nome'), ['class' => 'form-control', 'prompt' => 'Tutti i distretti']) ?>
                    <?= Html::submitButton('Filtra', ['class' => 'btn btn-primary']) ?>
                    <?= Html::endForm() ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <!-- add card header title "Verifica dati" -->
            <div clas="card">
                <div class="card-header">
                    <div class="card-toolbar">
                        <h4 class="card-title">Sommario</h4>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>TOTALI Attivi e Non chiusi</span>
                            <span class="badge bg-info badge-pill badge-round ms-1"><?= Istanza::getTotaliAttivi(DatiTipologia::LISTA_TOTALI_ATTIVI_NON_CHIUSI) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Minori di 18 anni</span>
                            <span class="badge bg-warning badge-pill badge-round ms-1"><?= Istanza::getTotaliAttivi(DatiTipologia::LISTA_MINORI18) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Maggiori di 18 anni</span>
                            <span class="badge bg-warning badge-pill badge-round ms-1"><?= Istanza::getTotaliAttivi(DatiTipologia::LISTA_MAGGIORI_18) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>NO data nascita</span>
                            <span class="badge bg-warning badge-pill badge-round ms-1"><?= Istanza::getTotaliAttivi(DatiTipologia::LISTA_NO_DATA_NASCITA) ?></span>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <div class="card-toolbar">
            <!-- show datagrid title "Elenco istanze" -->
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'layout' => Html::beginForm(['istanza/index'], 'get', ['data-pjax' => '', 'class' => 'form-inline']) .
                    "<div class='dataTable-top'>
<!--                                <div class='dataTable-search'>
                                    <input class='dataTable-input' placeholder='Search...' type='text'>
                                </div>-->
                           </div>
                           " . Html::endForm() .
                    "<div class='table-container'>{items}</div>
                            <div class='dataTable-bottom'>
                                  <div class='dataTable-info'>{summary}</div>
                                  <nav class='dataTable-pagination'>
                                        {pager}
                                  </nav>
                            </div>",
                'pager' => [
                    'class' => 'yii\bootstrap5\LinkPager',
                    'firstPageLabel' => 'PRIMA',
                    'lastPageLabel' => 'ULTIMA',
                    'nextPageLabel' => '>>',
                    'prevPageLabel' => '<<',
                    'linkOptions' => ['class' => 'page-link'],
                ],
                'options' => [
                    'tag' => 'div',
                    'class' => 'dataTable-wrapper dataTable-loading no-footer sortable searchable fixed-columns',
                    'id' => 'datatable',
                ],
                'tableOptions' => [
                    'class' => 'table table-striped dataTable-table',
                    'id' => 'table1',
                ],
                'columns' => [
                    'id',
                    'cf',
                    'cognome',
                    'nome',
                    'distretto',
                    'isee',
                    'eta',
                    'gruppo',
                    [
                        'attribute' => 'importoPrecedente',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return !$model['importoPrecedente'] ? "<span class='badge bg-danger'>NESSUNO</span>" : "<span class='badge bg-".($model['importoPrecedente'] == $model['importo'] ? "success" : "warning")."'>" . ($model['importoPrecedente'] == $model['importo'] ? "=" : $model['importoPrecedente']) . "</span>";
                        },
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'attribute' => 'importo',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return !$model['importo'] ? "<span class='badge bg-danger'>ALERT</span>" : "<span class='badge bg-success'>" . $model['importo'] . "</span>";
                        },
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'attribute' => 'operazione',
                        'format' => 'raw',
                        'label' => "Operazione",
                        'value' => function ($model) {
                            return "<span class='badge bg-" . ($model['opArray']['alert'] ? 'danger' : 'warning') . "'>" . $model['operazione'] . "</span>";
                        },
                        'contentOptions' => ['class' => 'text-center'],
                    ]
                ]
            ]);
            ?>
        </div>
    </div>
    <div class="card-body" id="card-content">
    </div>
</div>
<?php Pjax::end() ?>

