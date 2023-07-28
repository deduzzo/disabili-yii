<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Istanza $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Istanzas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="istanza-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'data_inserimento',
            'riconosciuto',
            'classe_disabilita',
            'data_riconoscimento',
            'patto_di_cura',
            'data_firma_patto',
            'attivo',
            'data_decesso',
            'liquidazione_decesso_completata',
            'data_liquidazione_decesso',
            'chiuso',
            'data_chiusura',
            'nota_chiusura:ntext',
            'note:ntext',
            'id_anagrafica_disabile',
            'id_distretto',
            'id_gruppo',
            'id_caregiver',
        ],
    ]) ?>

</div>
