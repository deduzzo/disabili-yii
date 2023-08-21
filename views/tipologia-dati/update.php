<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\TipologiaDati $model */

$this->title = 'Update Tipologia Dati: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Tipologia Datis', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="tipologia-dati-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
