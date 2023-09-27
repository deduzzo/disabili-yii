<?php

use app\components\ExportWidget;
use app\models\Distretto;
use app\models\enums\IseeType;
use kartik\select2\Select2;
use richardfan\widget\JSRegister;
use yii\bootstrap5\Html;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $soloRecuperi */
/** @var string $soloVariazioni */
/** @var string $soloProblematici */
/** @var array $distretti */
/** @var array $stats */
/** @var array $istanzeArray */
/** @var app\models\SimulazioneDeterminaSearch $searchModel */


$this->title = 'Simulazione determina';
$this->params['breadcrumbs'][] = $this->title;
$formatter = \Yii::$app->formatter;
?>

<div class="modal fade text-left" id="concludi-determina" tabindex="-1" aria-labelledby="label-modifica"
     style="display: none;"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document" style="min-width: 800px">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title white" id="label-modifica">
                    Finalizza Determina
                </h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="feather feather-x">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <?= Html::beginForm(['/determina/finalizza'], 'post', ['id' => 'finalizza-determina', 'class' => 'form-horizontal']) ?>
                <div class="divider">
                    <div class="divider-text">Dati Determina</div>
                </div>
                <div class="row">
                    <!-- attributes: numero determina, data determina, data inizio, data fine -->
                    <div class="col-md-6">
                        <label for="numero_determina">Numero Determina</label>
                        <input class="form-control" type="text" name="numero_determina" id="numero_determina"/>
                    </div>
                    <div class="col-md-6">
                        <label for="data_determina">Data Determina</label>
                        <input class="form-control" type="date" name="data_determina" id="data_determina"/>
                    </div>
                    <div class="col-md-6">
                        <label for="data_inizio">Data Inizio Pagamento</label>
                        <input class="form-control" type="date" name="data_inizio" id="data_inizio"/>
                    </div>
                    <div class="col-md-6">
                        <label for="data_fine">Data Fine Pagamento</label>
                        <input class="form-control" type="date" name="data_fine" id="data_fine"/>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x d-block d-sm-none"></i>
                    <span class="d-none d-sm-block">Annulla</span>
                </button>

                <button type="submit" class="btn btn-warning ms-1">
                    <i class="bx bx-check d-block d-sm-none"></i>
                    <span class="d-none d-sm-block">Finalizza determina</span>
                </button>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>
</div>


<div class="card">
    <div class="card-header">
        <div class="card-toolbar">
            <?php if ($soloVariazioni === "off" && $soloProblematici === "off" && $soloRecuperi === "off"): ?>
                <div class="row">
                    <div class="divider">
                        <div class="divider-text">Dettagli per distretto</div>
                    </div>
                    <div class="col-6 col-sm-12 col-md-4" style="margin-bottom: 5px">
                        <div style="text-align:center"><h6>LEGGENDA</h6>
                            <span class="badge bg-success badge-pill badge-round ms-2">Minore 25k €</span>
                            <span class="badge bg-warning badge-pill badge-round ms-2">Maggiore 25k €</span>
                            <span class="badge bg-secondary badge-pill badge-round ms-2">Totali</span>
                        </div>
                        <p style="text-align:center">Clicca sul distretto per vedere i dettagli</p>
                        <div class="list-group" role="tablist">
                            <?php foreach ($distretti as $di): ?>
                                <a class="list-group-item list-group-item-action d-flex justify-content-between"
                                   id="<?= "dettagli_" . $di->id . "_list" ?>" data-bs-toggle="list"
                                   href="#<?= "dettagli_" . $di->id ?>" role="tab">
                                    <?= $di->nome ?>
                                    <div style="">
                                    <span class="badge bg-success badge-pill badge-round ms-2"
                                          style="width: 50px"><?= $stats['numeriTotali'][$di->id][IseeType::MINORE_25K] . '</span>' ?>
                                    <span class="badge bg-warning badge-pill badge-round ms-2"
                                          style="width: 50px"><?= $stats['numeriTotali'][$di->id][IseeType::MAGGIORE_25K] . '</span>' ?>
                                    <span class="badge bg-secondary badge-pill badge-round ms-2"
                                          style="width: 50px"><?= $stats['numeriTotali'][$di->id][IseeType::MAGGIORE_25K] + $stats['numeriTotali'][$di->id][IseeType::MINORE_25K] . '</span>' ?>
                                    </div>
                                </a>

                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-6 col-sm-12 col-md-8 mt-1">
                        <div class="tab-content text-justify" id="nav-tabContent">
                            <?php foreach ($distretti as $di2): ?>
                                <div class="tab-pane show" id="<?= "dettagli_" . $di2->id ?>" style="text-align:center"
                                     role="tabpanel"
                                     aria-labelledby="<?= "dettagli_" . $di2->id . "_list" ?>">
                                    <?php
                                    echo "<div class='row'><div class='col-md-12'><h2>Dettaglio distretto di " . $di2->nome . '</h2></div>';
                                    echo '<div class="col-md-4" style="text-align:center"><span class="badge bg-success" style="margin-bottom:5px">' . Html::encode("< MINORE 25K €") . '</span><br />';

                                    echo '<button type="button" class="btn btn-success">
                                        ' . $formatter->asCurrency($stats['importiTotali'][$di2->id][IseeType::MINORE_25K]) . '<span class="badge bg-transparent">' . $stats['numeriTotali'][$di2->id][IseeType::MINORE_25K] . '</span>
                                    </button></div>';
                                    echo '<div class="col-md-4" style="text-align:center"><span class="badge bg-warning"  style="margin-bottom:5px">' . Html::encode("> MAGGIORE 25K €") . '</span><br />';

                                    echo '<button type="button" class="btn btn-warning">
                                        ' . $formatter->asCurrency($stats['importiTotali'][$di2->id][IseeType::MAGGIORE_25K]) . '<span class="badge bg-transparent">' . $stats['numeriTotali'][$di2->id][IseeType::MAGGIORE_25K] . '</span>
                                    </button></div>';

                                    echo '<div class="col-md-4" style="text-align:center"><span class="badge bg-secondary"  style="margin-bottom:5px">IMPORTO TOTALE</span><br />';
                                    echo '<button type="button" class="btn btn-secondary">
                                        ' . $formatter->asCurrency($stats['importiTotali'][$di2->id][IseeType::MAGGIORE_25K] + $stats['importiTotali'][$di2->id][IseeType::MINORE_25K]) . ' € <span class="badge bg-transparent">' . ($stats['numeriTotali'][$di2->id][IseeType::MAGGIORE_25K] + $stats['numeriTotali'][$di2->id][IseeType::MINORE_25K]) . '</span>
                                    </button></div></div>';

                                    ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <!-- add select box for distretto -->

        <div class="row">
            <?php if ($soloVariazioni === "off" && $soloProblematici === "off" && $soloRecuperi === "off"): ?>
                <div class="divider">
                    <div class="divider-text">Totali globali di <?= count($distretti) ?>
                        distrett<?= count($distretti) === 1 ? "o" : "i" ?></div>
                </div>
                <?php
                $out = "";
                $totaleImporti = 0;
                $numeriTotali = 0;
                $importiPerTipo = [IseeType::MAGGIORE_25K => 0, IseeType::MINORE_25K => 0];
                $numeriPerTipo = [IseeType::MAGGIORE_25K => 0, IseeType::MINORE_25K => 0];
                foreach ($stats['importiTotali'] as $distretto => $numeri) {
                    $totaleImporti += $numeri[IseeType::MAGGIORE_25K] + $numeri[IseeType::MINORE_25K];
                    $numeriTotali += $stats['numeriTotali'][$distretto][IseeType::MAGGIORE_25K] + $stats['numeriTotali'][$distretto][IseeType::MINORE_25K];
                    $importiPerTipo[IseeType::MAGGIORE_25K] += $numeri[IseeType::MAGGIORE_25K];
                    $importiPerTipo[IseeType::MINORE_25K] += $numeri[IseeType::MINORE_25K];
                    $numeriPerTipo[IseeType::MAGGIORE_25K] += $stats['numeriTotali'][$distretto][IseeType::MAGGIORE_25K];
                    $numeriPerTipo[IseeType::MINORE_25K] += $stats['numeriTotali'][$distretto][IseeType::MINORE_25K];
                }
                echo '<div class="col-md-4" style="text-align:center"><span class="badge bg-success" style="margin-bottom:5px">' . Html::encode("< MINORE 25K €") . '</span><br />';

                echo '<button type="button" class="btn btn-success">
                                ' . $formatter->asCurrency($importiPerTipo[IseeType::MINORE_25K]) . '<span class="badge bg-transparent">' . $numeriPerTipo[IseeType::MINORE_25K] . '</span>
                            </button></div>';
                echo '<div class="col-md-4" style="text-align:center"><span class="badge bg-warning"  style="margin-bottom:5px">' . Html::encode("> MAGGIORE 25K €") . '</span><br />';

                echo '<button type="button" class="btn btn-warning">
                                ' . $formatter->asCurrency($importiPerTipo[IseeType::MAGGIORE_25K]) . '<span class="badge bg-transparent">' . $numeriPerTipo[IseeType::MAGGIORE_25K] . '</span>
                            </button></div>';
                echo '<div class="col-md-4" style="text-align:center"><span class="badge bg-secondary"  style="margin-bottom:5px">IMPORTO TOTALE</span><br />';
                echo '<button type="button" class="btn btn-secondary">
                                ' . $formatter->asCurrency($totaleImporti) . '<span class="badge bg-transparent">' . $numeriTotali . '</span>
                            </button></div>';
                ?>
            <?php endif; ?>
            <div class="divider">
                <div class="divider-text">Filtri</div>
            </div>
            <div class="col-md-6">
                <?= Html::beginForm(['/determina'], 'get') ?>
                <?= Select2::widget([
                    'name' => 'distrettiPost',
                    'data' => ArrayHelper::map(Distretto::find()->all(), 'id', 'nome'),
                    'value' => ArrayHelper::getColumn($distretti, 'id'),
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
                       for="solo-problematici">Solo ist. con Errori (ALERT)</label><br/>
                <input class="form-check-input" type="checkbox" role="switch" name="soloVariazioni"
                       id="soloVariazioni" <?= $soloVariazioni == "on" ? "checked" : "" ?>>
                <label class="form-check-label text-danger bold"
                       for="soloVariazioni">Solo ist. con Variazioni</label><br/>
                <input class="form-check-input" type="checkbox" role="switch" name="soloRecuperi"
                       id="soloRecuperi" <?= $soloRecuperi == "on" ? "checked" : "" ?>>
                <label class="form-check-label text-danger bold"
                       for="soloRecuperi">Solo ist. con Recuperi in corso</label>
            </div>
            <div class="col-md-12" style="text-align:center">
                <button type="submit" class="btn btn-primary">Filtra</button>
            </div>
            <div class="divider">
                <div class="divider-text">Operazioni</div>
            </div>
        </div>
        <?= Html::endForm() ?>
        <div class="col-md-8"><?= ExportWidget::widget([
                'models' => $istanzeArray,
                'columns' => ['cf','cognome','nome','distretto','isee','gruppo','importo','operazione'],
            ]) ?></div>
        <div class="col-md-4">
            <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                    data-bs-target="#concludi-determina">
                Finalizza determina
            </button>
        </div>
        <div class="divider">
            <div class="divider-text">Elenco</div>
        </div>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => "<div class='dataTable-top'>
                           </div><div class='table-container'>{items}</div>
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
    <?php JSRegister::begin([
        'key' => 'manage',
        'position' => \yii\web\View::POS_READY
    ]); ?>
    <script>
        $(document).ready(function () {
            const soloProblematici = document.getElementById('soloProblematici');
            const soloVariazioni = document.getElementById('soloVariazioni');
            const soloRecuperi = document.getElementById('soloRecuperi');

            soloProblematici.addEventListener('change', function () {
                if (soloProblematici.checked) {
                    soloVariazioni.checked = false;
                    soloRecuperi.checked = false;
                }
            });

            soloVariazioni.addEventListener('change', function () {
                if (soloVariazioni.checked) {
                    soloProblematici.checked = false;
                    soloRecuperi.checked = false;
                }
            });

            soloRecuperi.addEventListener('change', function () {
                if (soloRecuperi.checked) {
                    soloProblematici.checked = false;
                    soloVariazioni.checked = false;
                }
            });

        });
    </script>
    <?php JSRegister::end(); ?>
</div>