<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Isee $model */

$this->title = 'Update Isee: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Isees', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="isee-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
