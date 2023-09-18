<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\RicoveroSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="ricovero-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'da') ?>

    <?= $form->field($model, 'a') ?>

    <?= $form->field($model, 'cod_struttura') ?>

    <?= $form->field($model, 'descr_struttura') ?>

    <?php // echo $form->field($model, 'contabilizzare') ?>

    <?php // echo $form->field($model, 'note') ?>

    <?php // echo $form->field($model, 'id_istanza') ?>

    <?php // echo $form->field($model, 'id_determina') ?>

    <?php // echo $form->field($model, 'id_recupero') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
