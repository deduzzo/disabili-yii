<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\DocumentoTipologia $model */

$this->title = 'Update Documento Tipologia: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Documento Tipologias', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="documento-tipologia-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
