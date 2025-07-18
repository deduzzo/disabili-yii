<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Anagrafica $model */

$this->title = 'Update Anagrafica: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Anagraficas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="anagrafica-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
