<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Distretto $model */

$this->title = 'Update Distretto: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Distrettos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="distretto-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
