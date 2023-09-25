<?php

use app\models\Distretto;
use app\models\enums\IseeType;
use app\models\Gruppo;
use app\models\Istanza;
use kartik\export\ExportMenu;
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
<?php Pjax::begin(['id' => 'datatable-pjax']) ?>
<?php $formatter = \Yii::$app->formatter; ?>

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
        <?php
        $selectedPageSize = isset(Yii::$app->request->queryParams['pageSize']) ? Yii::$app->request->queryParams['pageSize'] : 100;  // Assumo 100 come default
        ?>
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle me-1" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Primary
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="">
                <a class="dropdown-item" href="#">Option 1</a>
                <a class="dropdown-item" href="#">Option 2</a>
                <a class="dropdown-item" href="#">Option 3</a>
            </div>
        </div>
            <?php
            echo ExportMenu::widget([
                'dataProvider' => $dataProvider,
                'asDropdown' => false,
            ])
            ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => Html::beginForm(['istanza/index'], 'get', ['data-pjax' => '', 'class' => 'form-inline']) .
                "<div class='dataTable-top'>
                                <div class='dataTable-dropdown'>
                                        <select id='pageSize' name='pageSize' class='dataTable-selector form-select' onchange='this.form.submit()'>
                                                <option value='50' " . ($selectedPageSize == 50 ? 'selected' : '') . ">50</option>
                                                <option value='100' " . ($selectedPageSize == 100 ? 'selected' : '') . ">100</option>
                                                <option value='150' " . ($selectedPageSize == 150 ? 'selected' : '') . ">150</option>
                                                <option value='200' " . ($selectedPageSize == 200 ? 'selected' : '') . ">200</option>
                                                <option value='250' " . ($selectedPageSize == 250 ? 'selected' : '') . ">250</option>
                                        </select>
                                        <label> per pagina</label>
                                 </div>
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
                [
                    'attribute' => 'attivo',
                    'filter' => Html::activeDropDownList($searchModel, 'attivo', ['1' => "ATTIVO", '0' => "NON ATTIVO"], ['class' => 'form-control', 'prompt' => 'Tutti']),
                    'content' => function ($model) {
                        return Html::tag('span', $model->attivo ? 'Attivo' : 'Non attivo', [
                                'class' => $model->attivo ? 'badge bg-success' : 'badge bg-danger'
                            ]) . ($model->chiuso ? Html::tag('span', 'Chiuso', [
                                'class' => 'badge bg-danger'
                            ]) : (!$model->attivo ? Html::tag('span', 'Aperto', [
                                'class' => 'badge bg-success'
                            ]) : ""));
                    },
                    'contentOptions' => ['style' => 'width:150px; text-align:center;'],
                ],
                [
                    'attribute' => 'cognomeNome',
                    'label' => "Nominativo",
                    'filter' => Html::activeTextInput($searchModel, 'cognomeNome', ['class' => 'form-control']),
                    // set column size max 100px and text center
                    'contentOptions' => function ($model) {
                        return ['style' => 'width:400px; text-align:center;'];
                    },
                    'value' => function ($model) {
                        return $model->getNominativoDisabile();
                    },
                ],
                [
                    'attribute' => 'gruppo.descrizione_gruppo',
                    // filter, select2 with all names of table "gruppo"
                    'filter' => Html::activeDropDownList($searchModel, 'id_gruppo', ArrayHelper::map(Gruppo::find()->orderBy('descrizione_gruppo')->all(), 'id', "descrizioneCompleta"), ['class' => 'form-control', 'prompt' => 'Tutti']),
                    'label' => "Gruppo",
                    'format' => 'raw',
                    'value' => function ($model) {
                        return '<div style="display: flex; align-items: center; justify-content: center;"><h5 style="margin-right: 10px;"><span class="badge bg-primary">' . $model->gruppo->descrizione_gruppo_old . '</span></h5><h6><span class="badge bg-primary">' . $model->gruppo->descrizione_gruppo . '</span></h6></div>';
                    },
                    // set column size max 100px and text center
                    'contentOptions' => function ($model) {
                        return ['style' => 'width:150px; text-align:center;'];
                    },
                ],
                [
                    'value' => 'distretto.nome',
                    'attribute' => 'distretto',
                    'filter' => Html::activeDropDownList($searchModel, 'id_distretto', ArrayHelper::map(Distretto::find()->orderBy('nome')->all(), 'id', "nome"), ['class' => 'form-control', 'prompt' => 'Tutti']),
                    'label' => "Distretto",
                    'format' => 'raw',
                    // set column size max 100px and text center
                    'contentOptions' => function ($model) {
                        return ['style' => 'width:150px; text-align:center;'];
                    },
                ],
                [
                    'attribute' => 'isee',
                    'filter' => Html::activeDropDownList($searchModel, 'isee', ['Maggiore' => IseeType::MAGGIORE_25K, 'Minore' => IseeType::MINORE_25K, "N/D" => IseeType::NO_ISEE], ['class' => 'form-control', 'prompt' => 'Tutti']),
                    'label' => "ISEE",
                    'format' => 'raw',
                    'value' => function ($model) {
                        $isee = $model->getLastIseeType();
                        return '<span class="badge ' . ($isee === IseeType::MAGGIORE_25K ? IseeType::MAGGIORE_25K_COLOR : ($isee === IseeType::MINORE_25K ? IseeType::MINORE_25K_COLOR : IseeType::NO_ISEE_COLOR)) . '">' . Html::encode($model->getLastIseeType()) . '</span>';
                    },
                    // set column size max 100px and text center
                    'contentOptions' => function ($model) {
                        return ['style' => 'width:150px; text-align:center;'];
                    },
                ],
                [
                    'attribute' => 'cf',
                    'value' => 'anagraficaDisabile.codice_fiscale',
                    'label' => "CF",
                    'filter' => Html::activeTextInput($searchModel, 'cf', ['class' => 'form-control', 'value' => $searchModel['cf']]),
                    'contentOptions' => function ($model) {
                        return ['style' => 'width:200px; text-align:center;'];
                    },
                ],
                // eta
                [
                    'attribute' => 'eta',
                    'label' => "Età",
                    'value' => function ($model) {
                        return $model->anagraficaDisabile->getEta();
                    },
                    'contentOptions' => function ($model) {
                        return ['style' => 'width:100px; text-align:center;'];
                    },
                ],
                [
                    'attribute' => 'recuperos',
                    'label' => "Recupero?",
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->haRecuperiInCorso()
                            ? '<span class="badge bg-warning text-dark h6">SI</span>'
                            : '<span class="badge bg-success">NO</span>';
                    }
                ],
                [
                    'class' => ActionColumn::className(),
                    'template' => '<div class="btn-group btn-group-sm">{scheda}</div>',
                    'urlCreator' => function ($action, Istanza $model, $key, $index, $column) {
                        return Url::toRoute([$action, 'id' => $model->id]);
                    },
                    'buttons' => [
                        'scheda' => function ($url, $model) {
                            return Html::a('<i class="fa fa-solid fa-eye" style="color: #ffffff;"></i>', $url, [
                                'title' => Yii::t('yii', 'Vai alla scheda'),
                                'class' => 'btn btn-icon btn-sm btn-primary',
                            ]);
                        },
                    ]
                ],
            ],
        ]); ?>
    </div>
</div>

