<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Conto $model */

$this->title = 'Update Conto: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Contos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="conto-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
