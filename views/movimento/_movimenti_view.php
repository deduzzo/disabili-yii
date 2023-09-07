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



$totalImporto = 0;

foreach ($dataProvider->models as $model) {
    $totalImporto += $model->importo;
}

$selectedPageSize = isset(Yii::$app->request->queryParams['pageSize']) ? Yii::$app->request->queryParams['pageSize'] : 20;
?>
<?= Html::beginForm(['istanza/scheda', 'id' => Yii::$app->request->queryParams['id']], 'get', ['data-pjax' => '', 'id'=>'movimentiForm', 'class' => 'form-inline']) ?>
<?= Html::activeHiddenInput($searchModel, 'is_movimento_bancario', ['id' => 'filtroMovimenti']) ?>

<?php echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layout' => "<div class='dataTable-top'>
                                <div class='dataTable-dropdown'>
                                        <label>Mostra</label>&nbsp;
                                        <select id='pageSize' name='pageSize' class='dataTable-selector form-select' onchange='this.form.submit();'>
                                                <option value='10' " . ($selectedPageSize == 10 ? 'selected' : '') . ">10</option>
                                                <option value='20' " . ($selectedPageSize == 20 ? 'selected' : '') . ">20</option>
                                                <option value='40' " . ($selectedPageSize == 40 ? 'selected' : '') . ">40</option>
                                                <option value='100' " . ($selectedPageSize == 100 ? 'selected' : '') . ">100</option>
                                                <option value='-1' " . ($selectedPageSize == -1 ? 'selected' : '') . ">Tutti</option>
                                        </select>
                                       
                                 </div>
                           </div>
                            <div class='table-container'>{items}</div>
                            <div class='dataTable-bottom'>
                                  <div class='dataTable-info'>{summary}</div>
                                  <nav class='dataTable-pagination'>
                                        {pager}
                                  </nav>
                            </div>" . Html::endForm(),
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
            'attribute' => 'id',
            'label' => '#',
            'contentOptions' => ['style' => 'font-weight:bold; width: 50px;'],
        ],
        [
            'attribute' => 'data',
            'format' => 'date',
            'label' => 'Data',
        ],
        [
            'format' => 'raw',
            'attribute' => 'periodo_da',
            'label' => 'Periodo',
            'value' => function ($model) {
                if ($model->periodo_da && $model->periodo_a)
                    return Yii::$app->formatter->asDate($model->periodo_da) . ' - ' . Yii::$app->formatter->asDate($model->periodo_a);
                else
                    return '-';
            }
        ],
        [
            'label' => 'Tipo',
            'attribute' => 'is_movimento_bancario',
            'filter' => Html::activeDropDownList($searchModel, 'is_movimento_bancario', ['0' => "CONTABILE", '1' => "BANCARIO"], ['class' => 'form-control', 'prompt' => 'Tutti','onchange'=>'this.form.submit()']),
            'value' => function ($model) {
            $out = "";
                if (!$model->is_movimento_bancario) {
                    $out = '<span class="badge badge bg-secondary">CONTABILE</span><br />';
                    if ($model->recupero)
                        $out .= '<span class="badge bg-success">RECUPERO'.($model->recupero->rateizzato ? (' ['.$model->num_rata . " di " . $model->recupero->num_rate.']') : '').'</span>';
                }
                else {
                    $out.= '<span class="badge bg-primary">BANCARIO</span>';
                }
                return $out;
            },
            'format' => 'raw',
            // center the content in the column vertical and horizontal
            'contentOptions' => ['style' => 'text-align:center; vertical-align:middle;'],
        ],
        [
            'attribute' => 'importo',
            'format' => 'currency',
            'contentOptions' => ['style' => 'font-weight:bold;'],
            'headerOptions' => ['style' => 'font-weight:bold;'],
            'label' => 'Importo',
        ],
        [
            'label' => 'iban',
            // show the value of $model->conto->iban on mouse hover
            'format' => 'raw',
            'value' => function ($model) {
                return '<div data-toggle="tooltip" data-placement="top" title="' . $model->conto->iban . '">' .
                    '*****' . substr($model->conto->iban, -4) . '</div>';
            }
        ],
        [
            'attribute' => 'gruppoPagamentoDescrizione',
            'value' => function ($model) {
                return $model->gruppoPagamento ? $model->gruppoPagamento->descrizione : '-';
            },
            'label' => 'Descrizione Gruppo Pagamento',
        ],
        [
            'attribute' => 'determina',
            'value' => function ($model) {
                return $model->determina ? $model->determina->numero : '-';
            },
        ]
    ],
    'emptyText' => 'Nessun movimento presente',

]);

?>