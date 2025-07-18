<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Decreto $model */

$this->title = 'Update Decreto: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Decretos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="decreto-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
