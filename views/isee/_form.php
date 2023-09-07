<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Isee $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="isee-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'maggiore_25mila')->textInput() ?>

    <?= $form->field($model, 'data_presentazione')->textInput() ?>

    <?= $form->field($model, 'data_scadenza')->textInput() ?>

    <?= $form->field($model, 'valido')->textInput() ?>

    <?= $form->field($model, 'id_istanza')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
