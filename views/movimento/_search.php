<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\MovimentoSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="movimento-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'importo') ?>

    <?= $form->field($model, 'data') ?>

    <?= $form->field($model, 'periodo_da') ?>

    <?= $form->field($model, 'periodo_a') ?>

    <?php // echo $form->field($model, 'tornato_indietro') ?>

    <?php // echo $form->field($model, 'data_invio_notifica') ?>

    <?php // echo $form->field($model, 'data_incasso') ?>

    <?php // echo $form->field($model, 'id_recupero') ?>

    <?php // echo $form->field($model, 'num_rata') ?>

    <?php // echo $form->field($model, 'contabilizzare') ?>

    <?php // echo $form->field($model, 'id_determina') ?>

    <?php // echo $form->field($model, 'id_conto') ?>

    <?php // echo $form->field($model, 'note') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
