<?php

use app\models\Distretto;
use app\models\enums\IseeType;
use app\models\Gruppo;
use app\models\Istanza;
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

<!--    <div class="container-fluid">
        <?php /*echo $this->render('_search', ['model' => $searchModel]); */ ?>
    </div>-->

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
        <div class="row p-1">
            <?php if ($istanza->data_decesso === null): ?>
                <div class="col-md-2 h4 d-flex flex-column align-items-center justify-content-center">
                    <span class="badge rounded-pill bg-success">In vita</span>
                </div>
            <?php else: ?>
                <div class="col-md-2 d-flex flex-column align-items-center justify-content-center">
                    <span class="badge rounded-pill bg-danger text-xl">Deceduto</span>
                        <span class="badge rounded-pill bg-danger small">il <?= $formatter->asDate($istanza->data_decesso) ?></span>
                </div>
            <?php endif; ?>
            <div class="col-md-2 h6 d-flex flex-column align-items-center justify-content-center">
                <div class="text-sm">Distretto</div>
                <div class="text-success"><?= $istanza->distretto->nome ?></div>
            </div>
            <div class="col-md-2 h6 d-flex flex-column align-items-center justify-content-center">
                <?php $ultimo = $istanza->getLastIseeType(); ?>
                <div class="text-sm">Ultimo ISEE</div>
                <span class='badge <?= (!$ultimo || $ultimo === IseeType::MAGGIORE_25K) ? 'bg-secondary' : 'bg-primary' ?>'><?= ($ultimo !== null) ? Html::encode($ultimo) : "Nessun ISEE presente" ?></span>
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
                        <h5 class="card-title">Conti</h5>
                        <?=
                        $this->render('../conto/_conti_view', [
                            'istanza' => $istanza,
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
        </div>
    </div>
</div>

