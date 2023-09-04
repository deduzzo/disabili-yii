<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\RecuperoSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="recupero-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'importo') ?>

    <?= $form->field($model, 'recuperato') ?>

    <?= $form->field($model, 'rateizzato') ?>

    <?= $form->field($model, 'num_rate') ?>

    <?php // echo $form->field($model, 'importo_rata') ?>

    <?php // echo $form->field($model, 'note') ?>

    <?php // echo $form->field($model, 'id_istanza') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
