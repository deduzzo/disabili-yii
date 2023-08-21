<?php

use app\models\Distretto;
use app\models\Gruppo;
use app\models\Istanza;
use yii\bootstrap5\Html;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\IstanzaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Istanze';
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
<!--        <h5 class="card-title">
            <?php /*= $this->title */?>
        </h5>-->
        <!--begin::Card title-->
        <!--            <div class="card-toolbar">
                <?php /*= Html::a(Yii::t('app', 'Aggiungi'), ['create'], ['class' => 'btn btn-success fa fa-plus']) */ ?>
            </div>-->
    </div>
    <div class="card-body" id="card-content">

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => Html::beginForm(['ps'], 'post', ['data-pjax' => '', 'class' => 'form-inline']).
                             "<div class='dataTable-top'>
                                <div class='dataTable-dropdown'>
                                        <select id='pagesize' class='dataTable-selector form-select' onchange='this.form.submit()'>
                                            <option value='50'>50</option>
                                            <option value='100'selected=''>100</option>
                                            <option value='150'>150</option>
                                            <option value='200'>200</option>
                                            <option value='250'>250</option>
                                        </select>
                                        <label> per pagina</label>
                                 </div>
                                <div class='dataTable-search'>
                                    <input class='dataTable-input' placeholder='Search...' type='text'>
                                </div>
                           </div>
                           ".Html::endForm().
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
                [
                    'attribute' => 'attivo',
                    'filter' => Html::activeDropDownList($searchModel, 'attivo', ['1' => "ATTIVO",'0'=>"CHIUSO"], ['class' => 'form-control', 'prompt' => 'Tutti']),
                    'content' => function ($model) {
                        return Html::tag('span', $model->attivo ? 'Attivo' : 'Non attivo', [
                            'class' => $model->attivo ? 'badge bg-success' : 'badge bg-danger'
                        ]);
                    },
                    'contentOptions' => ['style' => 'width:150px; text-align:center;'],
                ],
                [
                    'attribute' => 'gruppo.descrizione_gruppo',
                    // filter, select2 with all names of table "gruppo"
                    'filter' => Html::activeDropDownList($searchModel, 'id_gruppo', ArrayHelper::map(Gruppo::find()->orderBy('descrizione_gruppo')->all(),'id',"descrizioneCompleta"), ['class' => 'form-control', 'prompt' => 'Tutti']),
                    'label' => "Gruppo",
                    'format' => 'raw',
                    'value' => function ($model) {
                        return '<div style="display: flex; align-items: center; justify-content: center;"><h5 style="margin-right: 10px;"><span class="badge bg-primary">'.$model->gruppo->descrizione_gruppo_old.'</span></h5><h6><span class="badge bg-primary">'. $model->gruppo->descrizione_gruppo . '</span></h6></div>';
                    },
                    // set column size max 100px and text center
                    'contentOptions' => function ($model) {
                        return ['style' => 'width:150px; text-align:center;'];
                    },
                ],
                [
                    'attribute' => 'distretto.nome',
                    'filter' => Html::activeDropDownList($searchModel, 'id_distretto', ArrayHelper::map(Distretto::find()->orderBy('nome')->all(),'id',"nome"), ['class' => 'form-control', 'prompt' => 'Tutti']),
                    'label' => "Distretto",
                    'format' => 'raw',
                    // set column size max 100px and text center
                    'contentOptions' => function ($model) {
                        return ['style' => 'width:150px; text-align:center;'];
                    },
                ],
                [
                    'attribute' => 'anagraficaDisabile.codice_fiscale',
                    'label' => "CF",
                    'filter' => Html::activeTextInput($searchModel, 'cf', ['class' => 'form-control', 'value' => $searchModel['cf']]),
                    'contentOptions' => function ($model) {
                        return ['style' => 'width:200px; text-align:center;'];
                    },
                ],
                [
                    'attribute' => 'anagraficaDisabile.cognome_nome',
                    'label' => "Nominativo",
                    'filter' => Html::activeTextInput($searchModel, 'cognomeNome', ['class' => 'form-control']),
                    // set column size max 100px and text center
                    'contentOptions' => function ($model) {
                        return ['style' => 'width:400px; text-align:center;'];
                    },
                ],
                [
                    'class' => ActionColumn::className(),
                    'template'   => '<div class="btn-group btn-group-sm">{scheda}</div>',
                    'urlCreator' => function ($action, Istanza $model, $key, $index, $column) {
                        return Url::toRoute([$action, 'id' => $model->id]);
                    },
                    'buttons' => [
                        'update' => function ($url, $model) {
                            return Html::a('<i class="fa fa-solid fa-edit" style="color: #ffffff;"></i>', $url, [
                                'title' => Yii::t('yii', 'Modifica'),
                                'class' => 'btn btn-icon btn-sm btn-primary',
                            ]);
                        },
                        'scheda' => function ($url, $model) {
                            return Html::a('<i class="fa fa-solid fa-eye" style="color: #ffffff;"></i>', $url, [
                                'title' => Yii::t('yii', 'Elimina'),
                                'class' => 'btn btn-icon btn-sm btn-primary',
                            ]);
                        },
                    ]
                ],
            ],
        ]); ?>

        <?php Pjax::end() ?>
    </div>
</div>

