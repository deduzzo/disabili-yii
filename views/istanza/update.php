<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Istanza $model */

$this->title = 'Update Istanza: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Istanzas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="istanza-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
