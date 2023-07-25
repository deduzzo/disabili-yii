<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Anagrafica $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="anagrafica-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'cognome_nome')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'nome')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'codice_fiscale')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'data_nascita')->textInput() ?>

    <?= $form->field($model, 'comune_nascita')->textInput(['maxlength' => true]) ?>


    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
