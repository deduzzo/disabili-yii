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
$this->title = $istanza->anagraficaDisabile->cognome_nome;
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
            <div class="col-md-2 d-flex flex-column align-items-center justify-content-center">
                <?php if (!$istanza->rinuncia): ?>
                <span class="badge rounded-pill bg-<?= $istanza->data_decesso === null ? "success": "danger" ?> text-small"><?= $istanza->data_decesso ? ("DECEDUTO ". Yii::$app->formatter->asDate($istanza->data_decesso)) : "IN VITA" ?></span>
                <span class="badge rounded-pill bg-<?= ($istanza->data_decesso === null && $istanza->patto_di_cura && !$istanza->chiuso) ? "success": "danger" ?> text-small"><?=
                    $istanza->data_decesso !== null ? (($istanza->liquidazione_decesso_completata && $istanza->chiuso) ? "CHIUSO LIQUIDATO" : "ATTIVO DA LIQUIDARE") : ($istanza->attivo ? "ATTIVO":  ($istanza->patto_di_cura ? "CHIUSO": "NON ATTIVO")) ?></span>
                <?php endif; ?>
                <?php if ($istanza->rinuncia): ?>
                    <span class="badge rounded-pill bg-danger text-large">RINUNCIA</span>
                <?php elseif(!$istanza->patto_di_cura): ?>
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
                <span class='badge <?= !$ultimo ? IseeType::NO_ISEE_COLOR : (($ultimo !== IseeType::MAGGIORE_25K) ? IseeType::MINORE_25K_COLOR : IseeType::MAGGIORE_25K_COLOR) ?>'><?= ($ultimo !== null) ? Html::encode($ultimo) : "Nessun ISEE presente" ?></span>
            </div>
            <div class="col-md-2 h6 d-flex flex-column align-items-center justify-content-center">
                <?= $istanza->getStatoRecupero() ?>
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

</script>

<?php JSRegister::begin([
    'key' => 'manage',
    'position' => \yii\web\View::POS_READY
]); ?>
<script>

</script>
<?php JSRegister::end(); ?>

