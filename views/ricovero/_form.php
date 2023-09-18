<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Ricovero $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="ricovero-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'da')->textInput() ?>

    <?= $form->field($model, 'a')->textInput() ?>

    <?= $form->field($model, 'cod_struttura')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'descr_struttura')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'contabilizzare')->textInput() ?>

    <?= $form->field($model, 'note')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'id_istanza')->textInput() ?>

    <?= $form->field($model, 'id_determina')->textInput() ?>

    <?= $form->field($model, 'id_recupero')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
