<?php

use app\models\Distretto;
use kartik\select2\Select2;
use yii\bootstrap5\Html;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $soloProblematici */
/** @var array $statistiche */
/** @var app\models\SimulazioneDeterminaSearch $searchModel */


$this->title = 'Simulazione determina';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'simulazione-determina']) ?>
<?php $formatter = \Yii::$app->formatter;
$distretto = Yii::$app->request->get()['distretto'] ?? null;
?>


<div class="card">
    <div class="card-header">
        <div class="card-toolbar">
            <div class="row">
                <div class="col-4 col-sm-12 col-md-4">
                    <div class="list-group" role="tablist">
                        <?php foreach (Distretto::find()->all() as $di): ?>
                            <a class="list-group-item list-group-item-action d-flex justify-content-between"
                               id="<?= "dettagli_" . $di->id . "_list" ?>" data-bs-toggle="list"
                               href="#<?= "dettagli_" . $di->id ?>" role="tab">
                                <?= $di->nome ?>
                                <span class="badge bg-warning badge-pill badge-round ms-1"><?= $statistiche[$di->id] ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-8 col-sm-12 col-md-8 mt-1">
                    <div class="tab-content text-justify" id="nav-tabContent">
                        <?php foreach (Distretto::find()->all() as $di2): ?>
                            <div class="tab-pane show" id="<?= "dettagli_" . $di2->id ?>" role="tabpanel"
                                 aria-labelledby="<?= "dettagli_" . $di2->id . "_list" ?>">
                                <?= $di2->nome ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <!-- add select box for distretto -->
        <?= Html::beginForm(['determina/'], 'post', ['data-pjax' => '', 'class' => 'form-inline']) ?>

        <div class="row">
            <div class="divider">
                <div class="divider-text">Filtri</div>
            </div>
            <div class="col-md-6">
                <?= Select2::widget([
                        'name' => 'distretti',
                    'data' => ArrayHelper::map(Distretto::find()->all(), 'id', 'nome'),
                    'value' => $distretti,
                    'options' => ['placeholder' => 'Seleziona un distretto ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'multiple' => true,
                        'class' => 'form-control'
                    ],
                ]); ?>
            </div>
            <div class="col-md-3">
                <input class="form-check-input" type="checkbox" role="switch" name="soloProblematici"
                       id="soloProblematici" <?= $soloProblematici == "on" ? "checked" : "" ?>>
                <label class="form-check-label text-danger bold"
                       for="solo-problematici">Mostra solo istanze con variazioni</label>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Filtra</button>
            </div>
            <div class="divider">
                <div class="divider-text"></div>
            </div>
        </div>
        <?= Html::endForm() ?>

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
                                  <div class='dataTable-info'>{summary}<br />TOTALE:</div>
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
                'style' => 'font-size: 14px;'
            ],
            'columns' => [
                'id',
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
                        return !$model['importoPrecedente'] ? "<span class='badge bg-danger'>NESSUNO</span>" : "<span class='badge bg-" . ($model['importoPrecedente'] == $model['importo'] ? "success" : "warning") . "'>" . ($model['importoPrecedente'] == $model['importo'] ? "=" : $model['importoPrecedente']) . "</span>";
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
                ],
                [
                    'class' => ActionColumn::className(),
                    'template' => '<div class="btn-group btn-group-sm">{scheda}</div>',
                    'urlCreator' => function ($action, $model, $key, $index, $column) {
                        return Url::toRoute(['istanza/' . $action, 'id' => $model['id']]);
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
            ]
        ]);
        ?>
    </div>
</div>
<?php Pjax::end() ?>

