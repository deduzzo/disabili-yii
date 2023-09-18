<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Ricovero $model */

$this->title = 'Update Ricovero: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Ricoveros', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="ricovero-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
