<?php

use app\components\ExportWidget;
use app\models\Distretto;
use app\models\enums\IseeType;
use kartik\select2\Select2;
use richardfan\widget\JSRegister;
use yii\bootstrap5\Html;
use yii\data\ArrayDataProvider;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $anno */
/** @var array $nomiGruppi */
/** @var string $result */


$this->title = 'Prossimi disabili (simulazione)';
$this->params['breadcrumbs'][] = $this->title;
$formatter = \Yii::$app->formatter;
?>
    <div class="modal fade text-left" id="crea-gruppo" tabindex="-1" aria-labelledby="label-modifica"
         style="display: none;"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document"
             style="min-width: 800px">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title white" id="label-modifica">
                        Importazione dati
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
                <?= Html::beginForm(['contabilita/aggiungi-nuovo-gruppo'], 'post', ['id' => 'aggiungi-nuovo-gruppo-form', 'class' => 'form-horizontal']) ?>
                <?= Html::hiddenInput('nomeGruppoRaw', $_GET['nomeGruppo'] ?? ""); ?>
                <div class="modal-body">
                    <div class="row">
                        <?php if (isset($_GET['soloNuovi']) && $_GET['soloNuovi'] == "1"): ?>
                            <div class="col-md-12">
                                <b>Nome gruppo:</b>: da colonna AD
                                <?= Html::hiddenInput('nomeGruppo', "*") ?>
                            </div>
                        <?php else: ?>
                            <div class="col-md-12">
                                <?= Html::label('Nome gruppo', 'nomeGruppo', ['class' => 'form-label']) ?>
                                <?= Html::textInput('nomeGruppo', isset($_GET['nomeGruppo']) ? (explode('#', $_GET['nomeGruppo'])[0]) : "", ['class' => 'form-control', 'placeholder' => 'Nome gruppo', 'id' => 'nomeGruppo']) ?>
                            </div>
                        <?php endif; ?>
                        <div class="col-md-12" style="margin-top:10px">
                            <?= Html::label('Data inizio beneficio', 'dataInizioBeneficio', ['class' => 'form-label']) ?>
                            <?= Html::textInput('dataInizioBeneficio', null, ['class' => 'form-control', 'placeholder' => 'Data inizio beneficio', 'type' => 'date']) ?>
                        </div>
                        <div class="col-md-12" style="margin-top:10px">
                            <?= Html::label('Data termine istanze', 'dataTermineIstanze', ['class' => 'form-label']) ?>
                            <?= Html::textInput('dataTermineIstanze', null, ['class' => 'form-control', 'placeholder' => 'Data termine istanze', 'type' => 'date']) ?>
                        </div>
                        <!-- num mesi da caricare (input number) -->
                        <div class="col-md-3" style="margin-top:10px">
                            <?= Html::label('Mesi arretrati', 'numMesiDaCaricare', ['class' => 'form-label']) ?>
                            <?= Html::textInput('numMesiDaCaricare', null, ['class' => 'form-control', 'placeholder' => 'Numero mesi da caricare', 'type' => 'number']) ?>
                        </div>
                        <!-- nota recupero (text) -->
                        <div class="col-md-9" style="margin-top:10px">
                            <?= Html::label('Nota recupero', 'notaRecupero', ['class' => 'form-label']) ?>
                            <?= Html::textInput('notaRecupero', null, ['class' => 'form-control', 'placeholder' => 'Nota recupero']) ?>
                        </div>
                        <div class="col-md-6" style="margin-top:10px">
                            <?= Html::checkbox('cancellaDatiSePresenti', true, ['label' => 'Cancella dati se presenti']) ?>
                        </div>
                        <div class="col-md-6" style="margin-top:10px">
                            <?= Html::checkbox('soloNuovi', (isset($_GET['soloNuovi']) && $_GET['soloNuovi'] == "1"), ['label' => 'Solo Nuovi', 'disabled' => true]) ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                            <i class="bx bx-x d-block d-sm-none"></i>
                            <span class="d-none d-sm-block">Annulla</span>
                        </button>

                        <button type="submit" class="btn btn-warning ms-1">
                            <i class="bx bx-check d-block d-sm-none"></i>
                            <span class="d-none d-sm-block"><?= (isset($_GET['soloNuovi']) && $_GET['soloNuovi'] == "1") ? "Importa disabili selezionati" : "Crea nuovo gruppo e importa dati" ?></span>
                        </button>
                    </div>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-toolbar">
                <!-- list of all new group names -->
                <?= Html::beginForm(['contabilita/prossimi'], 'get', ['class' => 'form-inline']) ?>
                <div class="row">
                    <div class="col-md-8">
                        <label for="nomeGruppo" class="form-label">Seleziona Gruppo</label>
                        <select class="form-select" id="nomeGruppo" name="nomeGruppo">
                            <option selected>Scegli...</option>
                            <?php foreach ($nomiGruppi as $groupName): ?>
                                <option value="<?= $groupName ?>" <?= isset($_GET['nomeGruppo']) && $_GET['nomeGruppo'] === $groupName ? "selected" : "" ?>><?= $groupName ?></option>
                            <?php endforeach; ?>
                        </select>
                        <!-- add checkbox named "solo nuovi (colonna AC)" -->
                        <?= Html::checkbox('soloNuovi', isset($_GET['soloNuovi']) ? true : false, ['label' => 'Solo nuovi']) ?>
                    </div>
                    <div class="col-md-1">
                        <!-- button submit -->
                        <?= Html::submitButton('Verifica', ['class' => 'btn btn-primary', 'style' => 'margin-top: 30px', 'name' => "submit"]) ?>
                    </div>
                    <?= Html::endForm() ?>
                    <div class="col-md-3" style="margin-top: 30px">
                        <?php
                        // arraydata provider from $result['cfs']
                        $dataProvider = new ArrayDataProvider([
                            'allModels' => isset($result['cfs']) ? $result['cfs'] : [],
                            'pagination' => [
                                'pageSize' => 10,
                            ],
                            'sort' => [
                                'attributes' => ['distretto', 'cf'],
                            ],
                        ]);
                        if ($result !== null): ?>
                            <?= ExportWidget::widget([
                                'dataProvider' => isset($result['cfs']) ? $result['cfs'] : [],
                                'columns' => ['distretto', 'cf'],
                            ]) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <!-- result -->
                        <?php if ($result !== null): ?>
                            <?= $result['out'] ?>
                            <?php echo count($result['errors']) == 0 ? "<br /><b>Nessun errore riscontrato</b>" : "<br /></br /><h5>Errori riscontrati:</h5>";
                            foreach ($result['errors'] as $error) {
                                echo $error . "<br />";
                            } ?>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-12" style="margin-top: 20px;">
                        <?php if ($result !== null && count($result['errors']) == 0): ?>
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                                    data-bs-target="#crea-gruppo">
                                Importa disabili selezionati
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php JSRegister::begin([
    'key' => 'manage',
    'position' => \yii\web\View::POS_READY
]); ?>
    <script>

    </script>
<?php JSRegister::end(); ?>