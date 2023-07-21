<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Gruppo $model */

$this->title = 'Update Gruppo: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Gruppos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="gruppo-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
