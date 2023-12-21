<?php

use app\models\IstanzaSearch;
use richardfan\widget\JSRegister;
use yii\bootstrap5\Html;
use yii\grid\CheckboxColumn;
use yii\grid\GridView;


/** @var yii\web\View $this */
/** @var string $result */
/** @var IstanzaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */


$this->title = 'Liquidazione Deceduti';
$this->params['breadcrumbs'][] = $this->title;
$formatter = \Yii::$app->formatter;
?>
<?= Html::beginForm(['determina/liquidazione-deceduti'], 'post'); ?>
    <div class="card">
        <div class="card-header">
            <div class="card-toolbar">
                <!--begin::Button-->
                <?= Html::submitButton('Liquidazione Deceduti', ['class' => 'btn btn-primary me-3']) ?>
            </div>
            <div class="card-body">
                <?= GridView::widget([
                    'id' => 'elenco-disabili',
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'layout' =>
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
                    ],
                    'tableOptions' => [
                        'class' => 'table table-striped dataTable-table',
                    ],
                    'columns' => [
                        [
                            'class' => CheckboxColumn::class,
                            'checkboxOptions' => function($model) {
                                    return ['value' => $model->id];
                            },
                        ],
                        'id',
                        'distretto.nome',
                        'data_decesso:date',
                        [
                            'label' => 'Nominativo',
                            'value' => function ($model) {
                                return $model->getNominativoDisabile();
                            }
                        ],
                        [
                            'label' => 'Data Ultimo pagamento',
                            'value' => function ($model) {
                                $last = $model->getLastMovimentoBancario();
                                if (!$last)
                                    return "-";
                                return Yii::$app->formatter->asDate($last->data);
                            }
                        ],
                        [
                            'label' => 'Importo Ultimo pagamento',
                            'value' => function ($model) {
                                $last = $model->getLastMovimentoBancario();
                                if (!$last)
                                    return "-";
                                return Yii::$app->formatter->asCurrency($last->importo);
                            }
                        ],
                        [
                            'label' => 'Pagamenti Tornati indietro?',
                            'value' => function ($model) {
                                $last = $model->getLastMovimentoBancario();
                                if (!$last)
                                    return "NO";
                                return $last->tornato_indietro ? "SI" : "NO";
                            }
                        ]

                    ],
                ]); ?>
            </div>
        </div>
    </div>
<?= Html::endForm() ?>