<?php

use app\helpers\Utils;
use app\models\Distretto;
use app\models\enums\AnagraficaType;
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
$this->title = $istanza->anagraficaDisabile->cognome_nome;
$this->params['breadcrumbs'][] = ['label' => 'Istanze', 'url' => ['istanze/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="modal fade text-left" id="modifica-istanza" tabindex="-1" aria-labelledby="label-modifica"
     style="display: none;"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document" style="min-width: 800px">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title white" id="label-modifica">
                    Modifica Istanza
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
                <?= Html::beginForm(['/istanza/modifica'], 'post', ['id' => 'aggiungi-isee', 'class' => 'form-horizontal']) ?>
                <input type="hidden" name="id-istanza" value="<?= $istanza->id ?>">
                <div class="divider">
                    <div class="divider-text">Patto di cura e rinuncia</div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="patto-di-cura"
                                   name="patto-di-cura" <?= $istanza->patto_di_cura ? "checked" : "" ?>
                                   onchange="pattoDiCuraCheck()">
                            <label class="form-check-label" for="patto-di-cura">Patto di cura</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="data-patto-cura">Data firma patto:</label>
                        <input type="date" class="form-control" name="data-patto-cura" id="data-patto-cura"
                               value="<?= $istanza->data_firma_patto ?>">
                    </div>
                    <div class="col-md-3">
                        <div class="custom-control custom-checkbox">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="rinuncia"
                                       id="rinuncia" <?= $istanza->rinuncia ? "checked" : "" ?>
                                       onchange="attivoCheck()">
                                <label class="form-check-label text-danger bold"
                                       for="rinuncia">Rinuncia</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="data-chiusura">Data chiusura:</label>
                        <input type="date" class="form-control" name="data-chiusura" id="data-chiusura"
                               value="<?= $istanza->data_chiusura ?>"
                               disabled="<?= $istanza->data_chiusura === null ? "true" : "false" ?>">
                    </div>
                    <div class="divider">
                        <div class="divider-text">Dati Istanza</div>
                    </div>
                    <div class="col-md-6">
                        <input type="radio" class="btn-check" name="stato" id="stato-attivo" autocomplete="off"
                            <?= $istanza->attivo ? 'checked' : "" ?> value="attivo" onclick="attivoCheck()">
                        <label class="btn btn-outline-success" for="stato-attivo">Attivo</label>

                        <input type="radio" class="btn-check" name="stato" id="stato-nonattivo" autocomplete="off"
                               value="non-attivo" <?= !$istanza->attivo ? 'checked' : "" ?> onclick="attivoCheck()">
                        <label class="btn btn-outline-warning" for="stato-nonattivo">NON ATTIVO</label>
                        <p style="margin-top: 20px; padding: 10px; text-align: justify">Solo le istanze con stato
                            "Attivo" verranno considerate nel
                            pagamento mensile. Un istanza non può essere attiva se manca il patto di cura o è
                            chiusa.</p>
                    </div>
                    <div class="col-md-6">
                        <input type="radio" class="btn-check" name="aperto-chiuso" id="stato-aperto" autocomplete="off"
                            <?= !$istanza->chiuso ? 'checked' : "" ?> value="aperto" onclick="attivoCheck()">
                        <label class="btn btn-outline-success" for="stato-aperto">Aperto</label>
                        <input type="radio" class="btn-check" name="aperto-chiuso" id="stato-chiuso" autocomplete="off"
                               value="chiuso" <?= $istanza->chiuso ? 'checked' : "" ?> onclick="attivoCheck()">
                        <label class="btn btn-outline-danger" for="stato-chiuso">CHIUSO</label>
                        <p style="margin-top: 20px; text-align: justify; padding: 10px;"><b>Chiudere SOLO le istanze che
                                sono state completamente liquidate
                                e
                                che
                                non
                                necessitano di ulteriori azioni</b> (recuperi decessi, recuperi ecc)</p>
                    </div>
                    <div class="divider">
                        <div class="divider-text">Decesso</div>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-control custom-checkbox">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="deceduto"
                                       id="deceduto" <?= $istanza->data_decesso !== null ? "checked" : "" ?>
                                       onchange="decedutoCheck()">
                                <label class="form-check-label text-danger bold"
                                       for="deceduto">Deceduto</label>
                            </div>
                        </div>
                        <label for="data-decesso">Data decesso:</label>
                        <input type="date" class="form-control" name="data-decesso" id="data-decesso"
                               value="<?= $istanza->data_decesso ?>" disabled="<?= $istanza->data_decesso === null ?>">
                    </div>
                    <div class="col-md-4">
                        <div class="custom-control custom-checkbox" style="margin-top: 20px">
                            <input type="checkbox" class="form-check-input form-check-primary form-check-glow"
                                   name="liquidazione-decesso-completata"
                                   id="liquidazione-decesso-completata" <?= $istanza->liquidazione_decesso_completata ? 'checked' : "" ?>
                                   onchange="decedutoCheck()" disabled="<?= $istanza->data_decesso === null ?>">
                            <label class="form-check-label" for="liquidazione-decesso-completata">Deceduto
                                liquidato</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="data-liquidazione">Data liquidazione decesso</label>
                        <input type="date" class="form-control" name="data-liquidazione" id="data-liquidazione"
                               value="<?= $istanza->data_liquidazione_decesso ?>"
                               disabled="<?= $istanza->liquidazione_decesso_completata !== null ?>">
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
                    <span class="d-none d-sm-block">Modifica Istanza</span>
                </button>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>
</div>


<div class="card">
    <div class="card-header"  style="display: flex; align-items: center; justify-content: center; flex-wrap: wrap;">
        <?php if ($istanza->isInAlert()): ?>
            <span class="badge bg-danger" style="margin-right: 10px">ALERT!</span>
            <span class="badge bg-warning"><?= $istanza->isInAlert() ?></span>
        <?php endif; ?>
    </div>
    <div class="card-body" id="card-content">
        <div class="row p-1">
            <div class="col-md-2 d-flex flex-column align-items-center justify-content-center">
                <?php if (!$istanza->rinuncia): ?>
                    <span class="badge rounded-pill bg-<?= $istanza->data_decesso === null ? "success" : "danger" ?> text-small"><?= $istanza->data_decesso ? ("DECEDUTO " . Yii::$app->formatter->asDate($istanza->data_decesso)) : "IN VITA" ?></span>
                    <span class="badge rounded-pill bg-<?= ($istanza->data_decesso === null && $istanza->patto_di_cura && !$istanza->chiuso && $istanza->attivo) ? "success" : "danger" ?> text-small"><?=
                        $istanza->data_decesso !== null ? (($istanza->liquidazione_decesso_completata && $istanza->chiuso) ? "CHIUSO LIQUIDATO" : ($istanza->attivo ? "DECEDUTO ANCORA ATTIVO" : ($istanza->liquidazione_decesso_completata ? "LIQUIDATO NON ATTIVO <br />APERTO (CHIUDERE)" : ($istanza->attivo ? "ATTIVO DA LIQUIDARE" : "NON ATTIVO DA LIQUIDARE")))) : ($istanza->attivo ? "ATTIVO" : (!$istanza->patto_di_cura ? "" : ($istanza->rinuncia ? "RINUNCIA" : "NON ATTIVO, ATTENZIONARE"))) ?></span>
                <?php endif; ?>
                <?php if ($istanza->rinuncia): ?>
                    <span class="badge rounded-pill bg-danger text-large">RINUNCIA</span>
                <?php elseif (!$istanza->patto_di_cura): ?>
                    <span class="badge rounded-pill bg-warning text-large">MANCA PATTO DI CURA</span>
                <?php endif; ?>
            </div>
            <div class="col-md-2 h6 d-flex flex-column align-items-center justify-content-center">
                <div class="text-sm">Distretto</div>
                <div class="text-success"><?= $istanza->distretto->nome ?></div>
            </div>
            <div class="col-md-2 h6 d-flex flex-column align-items-center justify-content-center">
                <div class="text-sm">GRUPPO</div>
                <div class="text-success"><?= '<div style="display: flex; align-items: center; justify-content: center;"><h5 style="margin-right: 10px;"><span class="badge bg-primary">' . $istanza->gruppo->descrizione_gruppo_old . '</span></h5><h6><span class="badge bg-primary">' . $istanza->gruppo->descrizione_gruppo . '</span></h6></div>' ?></div>
            </div>
            <div class="col-md-2 h6 d-flex flex-column align-items-center justify-content-center">
                <?php $ultimo = $istanza->getLastIseeType(); ?>
                <div class="text-sm">Ultimo ISEE</div>
                <span class='badge <?= $ultimo === IseeType::NO_ISEE ? IseeType::NO_ISEE_COLOR : (($ultimo !== IseeType::MAGGIORE_25K) ? IseeType::MINORE_25K_COLOR : IseeType::MAGGIORE_25K_COLOR) ?>'><?= ($ultimo !== IseeType::NO_ISEE) ? Html::encode($ultimo) : "Nessun ISEE presente" ?></span>
            </div>
            <div class="col-md-1 h6 d-flex flex-column align-items-center justify-content-center">
                <div>Contabilità</div>
                <?= $istanza->getStatoRecupero() ?>
            </div>
            <div class="col-md-1 h6 d-flex flex-column align-items-center justify-content-center">
                <div class="badge text-sm text-<?= $istanza->anagraficaDisabile->isMinorenne() ? AnagraficaType::MINORE_18_COLOR : AnagraficaType::MAGGIORE_18_COLOR ?>"><?= $istanza->anagraficaDisabile->isMinorenne() ? "<18" : ">18" ?></div>
                <div class="badge text-<?= $istanza->anagraficaDisabile->isMinorenne() ? AnagraficaType::MINORE_18_COLOR : AnagraficaType::MAGGIORE_18_COLOR ?>"><?= $istanza->anagraficaDisabile->getEta() . ' anni' ?></div>
            </div>
            <div class="col-md-2 h6 d-flex flex-column align-items-center justify-content-center">
                <div>Importo</div>
                <div>prossimo mese:</div>
                <span class="badge bg-primary"><?= Yii::$app->formatter->asCurrency($istanza->getProssimoImporto()) ?></span>
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
                                <div class="row">
                                    <div class="col-md-2 h5 d-flex align-items-center ">
                                        <div class="h5 card-title" style="margin-right:10px">Recuperi</div>&nbsp;
                                        <?= $istanza->haRecuperiInCorso() ? "<span class='badge bg-warning text-dark h6'>Da recuperare</span>" : "" ?>
                                    </div>
                                    <!-- Pulsante -->
                                    <div class="col-md-10 d-flex justify-content-end align-items-center">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#nuovo-recupero">
                                            <i class="fas fa-solid fa-plus"></i> Recupero
                                        </button>
                                    </div>
                                </div>
                                <?=
                                $this->render('_recuperi_view', [
                                    'istanza' => $istanza,
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
                                <h5 class="card-title mb-0" style="margin-right:10px">Isee</h5>
                                <?= !$istanza->getLastIseeType() ? "<span class='badge bg-warning text-dark h6'>ISEE MANCANTE</span>" : "" ?>
                            </div>

                            <!-- Pulsante -->
                            <div class="col-md-6 d-flex justify-content-end align-items-center">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#nuovo-isee">
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
                        <?php
                         $totaleDovuto = $istanza->getTotaleAnnuoDovutoCorretto();
                         $totaleEffettivo = $istanza->getTotaleEffettivoAnnuo();
                        ?>
                        <h5 class="card-title">Movimenti<i class="bi bi-info-circle" style="margin-left: 5px" data-bs-toggle="tooltip" data-bs-html="true" data-bs-original-title="<?= "Totale dovuto dell'anno: <b>".Yii::$app->formatter->asCurrency($totaleDovuto) ."</b><br />Totale effettivo: <b>".Yii::$app->formatter->asCurrency($totaleEffettivo) ."</b><br />Differenza: <b>".Yii::$app->formatter->asCurrency($totaleDovuto - $totaleEffettivo)."</b>" ?>"</i></h5>
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
                            <div class="col-md-2">
                                <a class="btn btn-primary" data-bs-toggle="collapse" href="#rowdata"
                                   role="button" aria-expanded="false" aria-controls="multiCollapseExample1">Mostra dati
                                    originali</a>
                            </div>
                            <div class="col-md-2">
                                <a class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modifica-istanza"
                                   onclick="check()">Modifica
                                    Istanza</a>
                            </div>
                            <div class="col-md-8"></div>
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
    function attivoCheck() {
        console.log("attivo")
        if (!document.getElementById("patto-di-cura").checked) {
            document.getElementById("stato-attivo").checked = false;
            document.getElementById("stato-nonattivo").checked = true;
        }
        if (document.getElementById("deceduto").checked) {
            document.getElementById("stato-nonattivo").checked = true;
        }
        if (document.getElementById("rinuncia").checked) {
            document.getElementById("stato-nonattivo").checked = true;
            document.getElementById("stato-chiuso").checked = true;
        }
        if (document.getElementById("stato-chiuso").checked)
            document.getElementById("stato-nonattivo").checked = true;
        document.getElementById("data-chiusura").disabled = !document.getElementById("rinuncia").checked && !document.getElementById("stato-chiuso").checked;
    }

    function decedutoCheck() {
        console.log("deceduto")
        document.getElementById("data-decesso").disabled = !document.getElementById("deceduto").checked;
        document.getElementById("liquidazione-decesso-completata").disabled = !document.getElementById("deceduto").checked;
        document.getElementById("data-liquidazione").disabled = !document.getElementById("liquidazione-decesso-completata").checked || !document.getElementById("deceduto").checked;
        if (!document.getElementById("deceduto").checked)
            document.getElementById("liquidazione-decesso-completata").checked = document.getElementById("deceduto").checked;
        attivoCheck();
    }

    function pattoDiCuraCheck() {
        console.log("patto di cura")
        document.getElementById("data-patto-cura").disabled = !document.getElementById("patto-di-cura").checked;
        if (document.getElementById("data-patto-cura").disabled)
            document.getElementById("stato-nonattivo").checked = document.getElementById("data-patto-cura").disabled;
        attivoCheck();
    }

    function check() {
        attivoCheck();
        pattoDiCuraCheck();
        decedutoCheck();
    }
</script>

<?php JSRegister::begin([
    'key' => 'manage',
    'position' => \yii\web\View::POS_READY
]); ?>
<script>


</script>
<?php JSRegister::end(); ?>

