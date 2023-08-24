<?php


use app\models\Istanza;
use app\models\MovimentoSearch;
use yii\bootstrap5\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var Istanza $istanza */

$searchModel = new MovimentoSearch();
$dataProvider = $searchModel->search(Yii::$app->request->queryParams, $istanza);

Pjax::begin();

$totalImporto = 0;

foreach($dataProvider->models as $model) {
    $totalImporto += $model->importo;
}

$selectedPageSize = isset(Yii::$app->request->queryParams['pageSize']) ? Yii::$app->request->queryParams['pageSize'] : 20;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layout' => Html::beginForm(['istanza/scheda','id' => Yii::$app->request->queryParams['id']], 'get', ['data-pjax' => '', 'class' => 'form-inline']) .
                "<div class='dataTable-top'>
                                <div class='dataTable-dropdown'>
                                        <label>Mostra</label>&nbsp;
                                        <select id='pageSize' name='pageSize' class='dataTable-selector form-select' onchange='this.form.submit()'>
                                                <option value='10' ". ($selectedPageSize == 10 ? 'selected' : '').">10</option>
                                                <option value='20' ".($selectedPageSize == 20 ? 'selected' : '').">20</option>
                                                <option value='40' ". ($selectedPageSize == 40 ? 'selected' : '').">40</option>
                                                <option value='100' ". ($selectedPageSize == 100 ? 'selected' : '').">100</option>
                                                <option value='-1' ". ($selectedPageSize == -1 ? 'selected' : '').">Tutti</option>
                                        </select>
                                       
                                 </div>
                           </div>
                           " . Html::endForm() . "<div class='table-container'>{items}</div>
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
        'class' => 'grid-view small dataTable-wrapper dataTable-loading no-footer sortable searchable fixed-columns',
        'id' => 'datatable',
    ],
    'tableOptions' => [
        'class' => 'table table-striped dataTable-table',
        'id' => 'table1',
    ],
    'summary' => 'Mostro elementi da <b>{begin}</b> a <b>{end}</b> di <b>{totalCount}</b><br /><b>SOMMA TOTALE</b>: <b>' . Yii::$app->formatter->asCurrency($totalImporto) . '</b><bt /> (degli elementi mostrati)',
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
        // importo currency and bold
        [
            'attribute' => 'importo',
            'format' => 'currency',
            'contentOptions' => ['style' => 'font-weight:bold; width: 150px;'],
            'headerOptions' => ['style' => 'font-weight:bold;'],
        ],
        [
            'attribute' => 'gruppoPagamentoDescrizione',
            'value' => 'gruppoPagamento.descrizione',
            'label' => 'Descrizione Gruppo Pagamento',
        ],
    ],
    'emptyText' => 'Nessun movimento presente',

]);

Pjax::end();