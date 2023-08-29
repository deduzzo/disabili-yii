<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Conto $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="conto-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'iban')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'note')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'attivo')->textInput() ?>

    <?= $form->field($model, 'id_istanza')->textInput() ?>

    <?= $form->field($model, 'data_disattivazione')->textInput() ?>

    <?= $form->field($model, 'data_creazione')->textInput() ?>

    <?= $form->field($model, 'data_modifica')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
