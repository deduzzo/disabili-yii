<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Movimento $model */

$this->title = 'Update Movimento: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Movimentos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="movimento-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
