<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Movimento $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="movimento-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'importo')->textInput() ?>

    <?= $form->field($model, 'data')->textInput() ?>

    <?= $form->field($model, 'periodo_da')->textInput() ?>

    <?= $form->field($model, 'periodo_a')->textInput() ?>

    <?= $form->field($model, 'tornato_indietro')->textInput() ?>

    <?= $form->field($model, 'data_invio_notifica')->textInput() ?>

    <?= $form->field($model, 'data_incasso')->textInput() ?>

    <?= $form->field($model, 'id_recupero')->textInput() ?>

    <?= $form->field($model, 'num_rata')->textInput() ?>

    <?= $form->field($model, 'contabilizzare')->textInput() ?>

    <?= $form->field($model, 'id_gruppo_pagamento')->textInput() ?>

    <?= $form->field($model, 'id_conto')->textInput() ?>

    <?= $form->field($model, 'note')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
