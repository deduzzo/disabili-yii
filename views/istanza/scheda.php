<?php

use app\helpers\Utils;
use app\models\Distretto;
use app\models\enums\IseeType;
use app\models\Gruppo;
use app\models\Istanza;
use richardfan\widget\JSRegister;
use yii\bootstrap5\Alert;
use yii\bootstrap5\Html;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var Istanza $istanza */
$this->title = 'Scheda ' . $istanza->anagraficaDisabile->cognome_nome;
$this->params['breadcrumbs'][] = ['label' => 'Istanze', 'url' => ['istanze/index']];
$this->params['breadcrumbs'][] = $this->title;
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
        <div class="row p-1">
            <?php if ($istanza->data_decesso === null): ?>
                <div class="col-md-2 h4 d-flex flex-column align-items-center justify-content-center">
                    <span class="badge rounded-pill bg-success">In vita</span>
                </div>
            <?php else: ?>
                <div class="col-md-2 d-flex flex-column align-items-center justify-content-center">
                    <span class="badge rounded-pill bg-danger text-xl">Deceduto</span>
                    <span class="badge rounded-pill bg-danger small">il <?= Yii::$app->formatter->asDate($istanza->data_decesso) ?></span>
                </div>
            <?php endif; ?>
            <div class="col-md-2 h6 d-flex flex-column align-items-center justify-content-center">
                <div class="text-sm">Distretto</div>
                <div class="text-success"><?= $istanza->distretto->nome ?></div>
            </div>
            <div class="col-md-2 h6 d-flex flex-column align-items-center justify-content-center">
                <?php $ultimo = $istanza->getLastIseeType(); ?>
                <div class="text-sm">Ultimo ISEE</div>
                <span class='badge <?= !$ultimo ? 'bg-secondary' :  (($ultimo !== IseeType::MAGGIORE_25K) ?  'bg-warning' : 'bg-primary') ?>'><?= ($ultimo !== null) ? Html::encode($ultimo) : "Nessun ISEE presente" ?></span>
            </div>
            <div class="col-md-2 h6 d-flex flex-column align-items-center justify-content-center">
                <?= $istanza->getStatoRecupero() ?>
            </div>
            <div class="col-md-2 h6 d-flex flex-column align-items-center justify-content-center">
                <!-- Button trigger modal -->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                    <i class="fas fa-solid fa-plus"></i> Recupero
                </button>

                <!-- Modal -->
                <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false"
                     tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog" style="min-width: 700px">
                        <div class="modal-content">
                            <?= Html::beginForm(['recupero/create-by-istanza', 'id' => $istanza->id]); ?>
                            <div class="modal-header">
                                <h5 class="modal-title" id="staticBackdropLabel">Aggiungi Recupero</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-md-12">Inserire i dati per il recupero per l'istanza di
                                                disabilità
                                                di <?= $istanza->getNominativoDisabile(); ?></div>
                                            <div class="col-md-6 mt-4">
                                                <?= Html::label('Importo (€)', 'importo') ?>
                                                <?= Html::textInput('importo', null, ['class' => 'form-control', 'id' => 'importo', 'type' => 'number', 'oninput' => 'checkStatus()']) ?>
                                            </div>
                                            <div class="col-md-6 mt-4">
                                                <?= Html::label('Tipo', 'tipologia'); ?>
                                                <?= Html::radioList('tipologia', 'negativo', ['negativo' => 'Debito (-)', 'credito' => 'Credito (+)'], ['class' => 'form-control form-select', 'onchange' => 'checkStatus()', 'id' => 'tipologia', 'inline' => true]) ?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 form-check form-switch" style="margin-left:10px">
                                                <?= Html::checkbox('rateizzato', null, ['class' => 'form-control form-check-input', 'id' => 'rateizzato', 'onchange' => 'checkStatus()']) ?>
                                                <?= Html::label('Rateizzato', 'rateizzato', ['class' => 'form-check-label']); ?>
                                            </div>
                                            <div class="col-md-4 mt-2">
                                                <?= Html::label('N°Rate', 'numRate'); ?>
                                                <?= Html::textInput('numRate', null, ['class' => 'form-control', 'id' => 'numRate', 'type' => 'number', 'disabled' => true, 'oninput' => "checkStatus()"]) ?>
                                                <?= Html::hiddenInput('numRate_hidden', null,['id' => "numRate_hidden"]) ?>
                                            </div>
                                            <div class="col-md-4 mt-2">
                                                <?= Html::label('Importo rata', 'importoRata'); ?>
                                                <?= Html::textInput('importoRata', null, ['class' => 'form-control', 'id' => 'importoRata', 'type' => 'number', 'disabled' => true, 'oninput' => "checkStatus()"]) ?>
                                                <?= Html::hiddenInput('importoRata_hidden', null,['id' => "importoRata_hidden"]) ?>
                                            </div>
                                            <div class="col-md-4 mt-2">
                                                <?= Html::label('n° rate saldate', 'numRatePagate'); ?>
                                                <?= Html::textInput('numRatePagate', null, ['class' => 'form-control', 'id' => 'numRatePagate', 'type' => 'number', 'disabled' => true, 'oninput' => "checkStatus()"]) ?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 form-check form-switch d-flex justify-content-center align-items-center"
                                                 style="margin-top: 3px;">
                                                <?= Html::checkbox('calcolo_automatico', true, ['class' => 'form-control form-check-input', 'id' => 'calcolo_automatico', 'onchange' => 'checkStatus()', 'disabled' => true]) ?>
                                                <?= Html::label('Calcolo automatico importo rata', 'calcolo_automatico', ['class' => 'form-check-label']); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card text-white bg-primary mb-3" style="max-width: 18rem;">

                                            <div class="card-body" style="min-height: 200px">
                                                <h5 class="card-title text-white">Riepilogo</h5>
                                                <p class="card-text text-white" id="riepilogoRateizzazione"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla
                                </button>
                                <button type="submit" class="btn btn-danger" id="btnSalvaRateizzazione" disabled="true">
                                    Salva
                                </button>
                            </div>
                            <?= Html::endForm(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card bg-body p-10">
                    <div class="card-body">
                        <h5 class="card-title">Anagrafica Disabile</h5>
                        <?= $this->render('../anagrafica/_anagrafica_view', [
                            'model' => $istanza->anagraficaDisabile,
                        ]) ?>
                        <h5>Altri dati</h5>
                        <?= $this->render('../anagrafica/_altricampi_view', [
                            'model' => $istanza->anagraficaDisabile,
                            'categoria' => 'anagrafica'
                        ]) ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-body p-10">
                    <div class="card-body">
                        <h5 class="card-title">Anagrafica Caregiver</h5>
                        <?= $this->render('../anagrafica/_anagrafica_view', [
                            'model' => $istanza->caregiver,
                        ]); ?>
                        <h5>Altri dati</h5>
                        <?= $this->render('../anagrafica/_altricampi_view', [
                            'model' => $istanza->caregiver,
                            'categoria' => 'anagrafica'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card bg-body p-10">
                    <div class="card-body">
                        <h5 class="card-title">Dettagli Istanza</h5>
                        <?=
                        $this->render('_istanza_view', [
                            'model' => $istanza,
                        ]) ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card bg-body p-10">
                            <div class="card-body">
                                <div class="col-md-2 h5 d-flex align-items-center ">
                                    <div class="h5 card-title">Ricoveri</div>&nbsp;
                                    <?= $istanza->haRicoveriDaRecuperare() ? "<span class='badge bg-warning text-dark h6'>Da recuperare</span>" : "" ?>
                                </div>
                                <?=
                                $this->render('_ricoveri_view', [
                                    'model' => $istanza,
                                ]) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card bg-body p-10">
                            <div class="card-body">
                                <div class="col-md-2 h5 d-flex align-items-center ">
                                    <div class="h5 card-title">Recuperi</div>&nbsp;
                                    <?= $istanza->haRecuperiInCorso() ? "<span class='badge bg-warning text-dark h6'>Da recuperare</span>" : "" ?>
                                </div>
                                <?=
                                $this->render('_recuperi_view', [
                                    'model' => $istanza,
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-body p-10">
                    <div class="card-body">
                        <div class="row">
                            <!-- Titolo -->
                            <div class="col-md-6 d-flex align-items-center">
                                <h5 class="card-title mb-0">Conti</h5>
                            </div>
                        </div>

                        <?=
                        $this->render('../conto/_conti_view', [
                            'istanza' => $istanza,
                        ]) ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-body p-10">
                    <div class="card-body">
                        <div class="row">
                            <!-- Titolo -->
                            <div class="col-md-6 d-flex align-items-center">
                                <h5 class="card-title mb-0">Isee</h5>
                            </div>

                            <!-- Pulsante -->
                            <div class="col-md-6 d-flex justify-content-end align-items-center">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuovo-isee">
                                    <i class="fas fa-solid fa-plus"></i> Isee
                                </button>
                            </div>
                        </div>

                        <?=
                        $this->render('../isee/_isee_view', [
                            'model' => $istanza,
                        ]) ?>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card bg-body p-10">
                    <div class="card-body">
                        <h5 class="card-title">Movimenti</h5>
                        <?=
                        $this->render('../movimento/_movimenti_view', [
                            'istanza' => $istanza,
                        ]) ?>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card bg-body p-10">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <a class="btn btn-primary" data-bs-toggle="collapse" href="#rowdata"
                                   role="button" aria-expanded="false" aria-controls="multiCollapseExample1">Mostra
                                    dati
                                    originali</a>
                            </div>
                            <div class="col-md-6"></div>
                            <div class="col-md-12">
                                <div class="collapse multi-collapse" id="rowdata">
                                    <div class="card card-body">
                                        <?php
                                        $json_pretty = json_decode($istanza->rawdata_json, true);
                                        echo "<pre>" . json_encode($json_pretty, JSON_PRETTY_PRINT) . "<pre/>";
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!--end::Card body-->
</div>
<script>
    function checkStatus() {
        document.getElementById('numRate_hidden').value = document.getElementById('numRate').value;
        document.getElementById('importoRata_hidden').value = document.getElementById('importoRata').value;
        if (document.getElementById('rateizzato').checked) {
            document.getElementById('numRate').disabled = false;
            document.getElementById('numRatePagate').disabled = false;
            document.getElementById('calcolo_automatico').disabled = false;
            if (document.getElementById('calcolo_automatico').checked) {
                if (!isNaN(parseFloat(document.getElementById('numRate').value)) && parseFloat(document.getElementById('numRate').value) > 0)
                    document.getElementById('importoRata').value = (parseFloat(document.getElementById('importo').value) / parseFloat(document.getElementById('numRate').value)).toFixed(2);
            } else {
                if (!isNaN(parseFloat(document.getElementById('importoRata').value)) && parseFloat(document.getElementById('importoRata').value) > 0)
                    document.getElementById('numRate').value = Math.ceil(parseFloat(document.getElementById('importo').value) / parseFloat(document.getElementById('importoRata').value));
            }
        } else {
            document.getElementById('numRate').disabled = true;
            document.getElementById('numRate').value = '';
            document.getElementById('numRatePagate').disabled = true;
            document.getElementById('numRatePagate').value = '';
            document.getElementById('importoRata').value = '';
            document.getElementById('calcolo_automatico').disabled = true;
        }
        document.getElementById('importoRata').disabled = !document.getElementById('rateizzato').checked || document.getElementById('calcolo_automatico').checked;
        document.getElementById('numRate').disabled = !document.getElementById('rateizzato').checked || !document.getElementById('calcolo_automatico').checked;

        let riassunto = "Inserire tutti i valori richiesti";
        if (!isNaN(document.getElementById('importoRata').value) && document.getElementById('importo').value &&
            (!document.getElementById('rateizzato').checked || (document.getElementById('rateizzato').checked && !isNaN(document.getElementById('numRate').value) && document.getElementById('numRate').value > 0))) {
            riassunto = "Tipo: " + $("input[name='tipologia']:checked").next('label').text() + "<br />" +
                "Importo: " + document.getElementById('importo').value + " €<br />" +
                "Rateizzato: " + (document.getElementById('rateizzato').checked ? "SI" : "NO") + "<br />" +
                (document.getElementById('rateizzato').checked && document.getElementById('numRate').value ?
                    ((!document.getElementById('calcolo_automatico').checked ?
                            ((parseFloat(document.getElementById('importo').value) % (parseInt(document.getElementById('numRate').value) * parseFloat(document.getElementById('importoRata').value)) === 0) ?
                                document.getElementById('numRate').value :
                                (parseInt(document.getElementById('numRate').value) - 1)) :
                            document.getElementById('numRate').value)
                        + " rate da " + document.getElementById('importoRata').value + " €<br />") : "") +
                ((!document.getElementById('calcolo_automatico').checked && ((parseFloat(document.getElementById('importo').value) % (parseInt(document.getElementById('numRate').value) * parseFloat(document.getElementById('importoRata').value)) !== 0))) ?
                    ("+ ultima rata: " +
                        (Math.abs(parseFloat(document.getElementById('importo').value) - (parseFloat(document.getElementById('importoRata').value) * (parseInt(document.getElementById('numRate').value) -1)))) + " €<br />")
                    : "") +
                (document.getElementById('rateizzato').checked && document.getElementById('numRate').value && document.getElementById('numRatePagate').value && parseInt(document.getElementById('numRatePagate').value) > 0 ?
                    (document.getElementById('numRatePagate').value + " rate già pagate<br />") : "") +
                "<p class='text-danger' style='margin-top: 10px'>RESIDUO: " + (document.getElementById('rateizzato').checked ? (document.getElementById('importo').value - (document.getElementById('importoRata').value * document.getElementById('numRatePagate').value)) : document.getElementById('importo').value) + " €</p>";
            document.getElementById('btnSalvaRateizzazione').disabled = false;
        } else
            document.getElementById('btnSalvaRateizzazione').disabled = true;

        document.getElementById('riepilogoRateizzazione').innerHTML = riassunto
    }
</script>

<?php JSRegister::begin([
    'key' => 'manage',
    'position' => \yii\web\View::POS_READY
]); ?>
<script>
    $(document).ready(function () {
        checkStatus();
    });
</script>
<?php JSRegister::end(); ?>

