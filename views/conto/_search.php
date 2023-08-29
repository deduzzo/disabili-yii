<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\ContoSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="conto-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'iban') ?>

    <?= $form->field($model, 'note') ?>

    <?= $form->field($model, 'attivo') ?>

    <?= $form->field($model, 'id_istanza') ?>

    <?php // echo $form->field($model, 'data_disattivazione') ?>

    <?php // echo $form->field($model, 'data_creazione') ?>

    <?php // echo $form->field($model, 'data_modifica') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
