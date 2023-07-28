<?php

use app\models\Istanza;
use yii\bootstrap5\Html;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\IstanzaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Istanzas';
$this->params['breadcrumbs'][] = $this->title;
?>


<!--    <div class="container-fluid">
        <?php /*echo $this->render('_search', ['model' => $searchModel]); */ ?>
    </div>-->
<?php Pjax::begin() ?>
<?php $formatter = \Yii::$app->formatter; ?>
<div class="card">
    <div class="card-header">
        <!--begin::Card title-->
        <h5 class="card-title">
            <?= $this->title ?>
        </h5>
        <!--begin::Card title-->
        <!--            <div class="card-toolbar">
                <?php /*= Html::a(Yii::t('app', 'Aggiungi'), ['create'], ['class' => 'btn btn-success fa fa-plus']) */ ?>
            </div>-->
    </div>
    <div class="card-body">

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => "<div class='dataTable-top'>
                                <div class='dataTable-dropdown'>
                                        <select class='dataTable-selector form-select'>
                                            <option value='5'>5</option>
                                            <option value='10' selected=''>10</option>
                                            <option value='15'>15</option>
                                            <option value='20'>20</option>
                                            <option value='25'>25</option>
                                        </select>
                                        <label> entries per page</label>
                                 </div>
                                <div class='dataTable-search'>
                                    <input class='dataTable-input' placeholder='Search...' type='text'>
                                </div>
                           </div>
                           <div class='table-container'>{items}</div>
                            <div class='dataTable-bottom'>
                                  <div class='dataTable-info'>{summary}</div>
                                  <nav class='dataTable-pagination'>
                                        {pager}
                                  </nav>
                            </div>",
            'pager' => [
                'linkOptions' => ['class' => 'page-link'],
                'activePageCssClass' => 'paginate_button page-item active',
                'disabledPageCssClass' => 'paginate_button page-item disabled',
                'prevPageCssClass' => 'previus',
                'nextPageCssClass' => 'mynext',
                'firstPageCssClass' => 'first',
                'lastPageCssClass' => 'last',
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
                [
                    'attribute' => 'attivo',
                    'content' => function ($model) {
                        return Html::tag('span', $model->attivo ? 'Attivo' : 'Non attivo', [
                            'class' => $model->attivo ? 'badge bg-success' : 'badge bg-danger'
                        ]);
                    }
                ],
                [
                    'attribute' => 'gruppo.descrizione_gruppo',
                    'filter' => Html::activeTextInput($searchModel, 'descrizione_gruppo', ['class' => 'form-control']),
                ],
                'gruppo.descrizione_gruppo',
                'distretto.nome',
                'anagraficaDisabile.cognome_nome',

                //'patto_di_cura',
                //'data_firma_patto',
                //'attivo',
                //'data_decesso',
                //'liquidazione_decesso_completata',
                //'data_liquidazione_decesso',
                //'chiuso',
                //'data_chiusura',
                //'nota_chiusura:ntext',
                //'note:ntext',
                //'id_anagrafica_disabile',
                //'id_distretto',
                //'id_gruppo',
                //'id_caregiver',
                [
                    'class' => ActionColumn::className(),
                    'urlCreator' => function ($action, Istanza $model, $key, $index, $column) {
                        return Url::toRoute([$action, 'id' => $model->id]);
                    }
                ],
            ],
        ]); ?>

        <?php Pjax::end() ?>
    </div>
</div>

