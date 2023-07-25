<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\DecretoSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="decreto-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'descrizione_atto') ?>

    <?= $form->field($model, 'data') ?>

    <?= $form->field($model, 'dal') ?>

    <?= $form->field($model, 'al') ?>

    <?php // echo $form->field($model, 'inclusi_minorenni') ?>

    <?php // echo $form->field($model, 'inclusi_maggiorenni') ?>

    <?php // echo $form->field($model, 'note') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
