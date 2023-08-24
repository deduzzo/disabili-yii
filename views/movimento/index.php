<?php

use app\models\Movimento;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\MovimentoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Movimenti';
$this->params['breadcrumbs'][] = $this->title;
$selectedPageSize = isset(Yii::$app->request->queryParams['pageSize']) ? Yii::$app->request->queryParams['pageSize'] : 20;

?>
<div class="card">
    <div class="card-header">
        <!--begin::Card title-->
        <!--        <h5 class="card-title">
            <?php /*= $this->title */ ?>
        </h5>-->
        <!--begin::Card title-->
        <!--            <div class="card-toolbar">
                <?php /*= Html::a(Yii::t('app', 'Aggiungi'), ['create'], ['class' => 'btn btn-success fa fa-plus']) */ ?>
            </div>-->
    </div>
    <div class="card-body" id="card-content">
        <?php Pjax::begin(); ?>
        <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => Html::beginForm(['movimento/index'], 'get', ['data-pjax' => '', 'class' => 'form-inline']) .
                "<div class='dataTable-top'>
                                <div class='dataTable-dropdown'>
                                        <label>Mostra</label>&nbsp;
                                        <select id='pageSize' name='pageSize' class='dataTable-selector form-select' onchange='this.form.submit()'>
                                                <option value='10' " . ($selectedPageSize == 10 ? 'selected' : '') . ">10</option>
                                                <option value='20' " . ($selectedPageSize == 20 ? 'selected' : '') . ">20</option>
                                                <option value='40' " . ($selectedPageSize == 40 ? 'selected' : '') . ">40</option>
                                                <option value='100' " . ($selectedPageSize == 100 ? 'selected' : '') . ">100</option>
                                                <option value='-1' " . ($selectedPageSize == -1 ? 'selected' : '') . ">Tutti</option>
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
            'summary' => 'Mostro elementi da <b>{begin}</b> a <b>{end}</b> di <b>{totalCount}</b>',
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'conto.istanza.anagraficaDisabile.cognome_nome',
                'conto.istanza.distretto.nome',
                'importo',
                'conto.iban',
                'periodo_da:date',
                'periodo_a:date',
                [
                    'attribute' => 'gruppoPagamentoDescrizione',
                    'value' => 'gruppoPagamento.descrizione',
                    'label' => 'Descrizione Gruppo Pagamento',
                ],
                [
                    'class' => ActionColumn::className(),
                    'urlCreator' => function ($action, Movimento $model, $key, $index, $column) {
                        return Url::toRoute([$action, 'id' => $model->id]);
                    }
                ],
            ],
        ]); ?>

        <?php Pjax::end(); ?>

    </div>
</div>