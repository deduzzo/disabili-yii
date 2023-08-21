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
/** @var Istanza $istanza */
$this->title = 'Scheda '. $istanza->anagraficaDisabile->cognome_nome;
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
            <?php /*= $this->title */?>
        </h5>-->
        <!--begin::Card title-->
        <!--            <div class="card-toolbar">
                <?php /*= Html::a(Yii::t('app', 'Aggiungi'), ['create'], ['class' => 'btn btn-success fa fa-plus']) */ ?>
            </div>-->
    </div>
    <div class="card-body" id="card-content">
        <div class="card-columns custom-card-cols" style="column-count: 2">
            <div class="card bg-body">
                <div class="card-body">
                    <h5 class="card-title">Anagrafica Disabile</h5>
                    <?= $this->render('_anagrafica_view', [
                        'model' => $istanza->anagraficaDisabile,
                    ]) ?>
                </div>
            </div>
            <div class="card bg-body">
                <div class="card-body">
                    <h5 class="card-title">Anagrafica Caregiver</h5>

                    <?= $istanza->caregiver ?
                        $this->render('_anagrafica_view', [
                        'model' => $istanza->caregiver,
                    ]) : "Nessun Caregiver"; ?>
                </div>
            </div>
        </div>
    </div>
</div>

