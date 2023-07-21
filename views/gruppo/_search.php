<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\GruppoSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="gruppo-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'data_termine_istanze') ?>

    <?= $form->field($model, 'data_inizio_beneficio') ?>

    <?= $form->field($model, 'descrizione_gruppo') ?>

    <?= $form->field($model, 'descrizione_gruppo_old') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
