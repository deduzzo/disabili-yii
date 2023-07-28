<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\IstanzaSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="istanza-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'data_inserimento') ?>

    <?= $form->field($model, 'riconosciuto') ?>

    <?= $form->field($model, 'classe_disabilita') ?>

    <?= $form->field($model, 'data_riconoscimento') ?>

    <?php // echo $form->field($model, 'patto_di_cura') ?>

    <?php // echo $form->field($model, 'data_firma_patto') ?>

    <?php // echo $form->field($model, 'attivo') ?>

    <?php // echo $form->field($model, 'data_decesso') ?>

    <?php // echo $form->field($model, 'liquidazione_decesso_completata') ?>

    <?php // echo $form->field($model, 'data_liquidazione_decesso') ?>

    <?php // echo $form->field($model, 'chiuso') ?>

    <?php // echo $form->field($model, 'data_chiusura') ?>

    <?php // echo $form->field($model, 'nota_chiusura') ?>

    <?php // echo $form->field($model, 'note') ?>

    <?php // echo $form->field($model, 'id_anagrafica_disabile') ?>

    <?php // echo $form->field($model, 'id_distretto') ?>

    <?php // echo $form->field($model, 'id_gruppo') ?>

    <?php // echo $form->field($model, 'id_caregiver') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
