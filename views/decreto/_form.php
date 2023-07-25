<?php

use kartik\date\DatePicker;
use yii\bootstrap5\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Decreto $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="decreto-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'descrizione_atto')->textInput(['maxlength' => true]) ?>

    <?=
    // $form->field($model, 'data') kartik datepicker
    $form->field($model, 'data')->widget(DatePicker::class, [
        'options' => ['placeholder' => 'Inserisci la data del decreto...'],
        'pluginOptions' => [
            'autoclose' => true,
            'format' => 'dd/mm/yyyy',
            'todayHighlight' => true,
        ],
    ])
    ?>

    <?=     $form->field($model, 'dal')->widget(DatePicker::class, [
        'options' => ['placeholder' => 'Pagamenti dal...'],
        'pluginOptions' => [
            'autoclose' => true,
            'format' => 'dd/mm/yyyy'
        ]
    ]) ?>

    <?=     $form->field($model, 'al')->widget(DatePicker::class, [
        'options' => ['placeholder' => 'Pagamenti al...'],
        'pluginOptions' => [
            'autoclose' => true,
            'format' => 'dd/mm/yyyy',
            'todayHighlight' => true,
        ]
    ]) ?>

    <?= $form->field($model, 'inclusi_minorenni')->checkbox() ?>

    <?= $form->field($model, 'inclusi_maggiorenni')->checkbox() ?>

    <?= $form->field($model, 'note')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
