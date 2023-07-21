<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Istanza $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="istanza-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'data_inserimento')->textInput() ?>

    <?= $form->field($model, 'riconosciuto')->textInput() ?>

    <?= $form->field($model, 'classe_disabilita')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'data_riconoscimento')->textInput() ?>

    <?= $form->field($model, 'patto_di_cura')->textInput() ?>

    <?= $form->field($model, 'data_firma_patto')->textInput() ?>

    <?= $form->field($model, 'attivo')->textInput() ?>

    <?= $form->field($model, 'data_decesso')->textInput() ?>

    <?= $form->field($model, 'liquidazione_decesso_completata')->textInput() ?>

    <?= $form->field($model, 'data_liquidazione_decesso')->textInput() ?>

    <?= $form->field($model, 'chiuso')->textInput() ?>

    <?= $form->field($model, 'data_chiusura')->textInput() ?>

    <?= $form->field($model, 'nota_chiusura')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'note')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'id_anagrafica_disabile')->textInput() ?>

    <?= $form->field($model, 'id_distretto')->textInput() ?>

    <?= $form->field($model, 'id_gruppo')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
