<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Recupero $model */

$this->title = 'Update Recupero: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Recuperos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="recupero-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
