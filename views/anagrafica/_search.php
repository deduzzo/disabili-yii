<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\AnagraficaSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="anagrafica-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'cognome') ?>

    <?= $form->field($model, 'nome') ?>

    <?= $form->field($model, 'codice_fiscale') ?>

    <?= $form->field($model, 'data_nascita') ?>

    <?php // echo $form->field($model, 'comune_nascita') ?>

    <?php // echo $form->field($model, 'attivo') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
