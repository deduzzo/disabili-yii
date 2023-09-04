<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Recupero $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="recupero-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'importo')->textInput() ?>

    <?= $form->field($model, 'recuperato')->textInput() ?>

    <?= $form->field($model, 'rateizzato')->textInput() ?>

    <?= $form->field($model, 'num_rate')->textInput() ?>

    <?= $form->field($model, 'importo_rata')->textInput() ?>

    <?= $form->field($model, 'note')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'id_istanza')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
