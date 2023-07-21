<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Gruppo $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="gruppo-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'data_termine_istanze')->textInput() ?>

    <?= $form->field($model, 'data_inizio_beneficio')->textInput() ?>

    <?= $form->field($model, 'descrizione_gruppo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'descrizione_gruppo_old')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
