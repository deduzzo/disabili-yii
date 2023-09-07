<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\IseeSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="isee-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'maggiore_25mila') ?>

    <?= $form->field($model, 'data_presentazione') ?>

    <?= $form->field($model, 'data_scadenza') ?>

    <?= $form->field($model, 'valido') ?>

    <?php // echo $form->field($model, 'id_istanza') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
