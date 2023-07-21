<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Distretto $model */

$this->title = 'Create Distretto';
$this->params['breadcrumbs'][] = ['label' => 'Distrettos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="distretto-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
