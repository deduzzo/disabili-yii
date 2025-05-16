<?php

use kartik\date\DatePicker;
use yii\bootstrap5\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Decreto $model */
/** @var yii\widgets\ActiveForm $form */

$formId = 'decreto-form-' . (isset($model->id) ? $model->id : 'new');
?>

<div class="decreto-form">

    <?php $form = ActiveForm::begin([
        'id' => $formId,
        'options' => ['data-pjax' => true],
        'action' => isset($model->id) ? ['decreto/update', 'id' => $model->id] : ['decreto/create'],
    ]); ?>

    <?= $form->field($model, 'descrizione_atto')->textInput(['maxlength' => true]) ?>

    <?=
    $form->field($model, 'data')->widget(DatePicker::class, [
        'options' => ['placeholder' => 'Inserisci la data del decreto...'],
        'pluginOptions' => [
            'autoclose' => true,
            'format' => 'dd/mm/yyyy',
            'todayHighlight' => true,
        ],
    ])
    ?>

    <?= $form->field($model, 'importo')->textInput(['type' => 'number', 'step' => '0.01']) ?>

    <?= $form->field($model, 'dal')->widget(DatePicker::class, [
        'options' => ['placeholder' => 'Pagamenti dal...'],
        'pluginOptions' => [
            'autoclose' => true,
            'format' => 'dd/mm/yyyy'
        ]
    ]) ?>

    <?= $form->field($model, 'al')->widget(DatePicker::class, [
        'options' => ['placeholder' => 'Pagamenti al...'],
        'pluginOptions' => [
            'autoclose' => true,
            'format' => 'dd/mm/yyyy',
            'todayHighlight' => true,
        ]
    ]) ?>

    <?= $form->field($model, 'inclusi_minorenni')->checkbox() ?>

    <?= $form->field($model, 'inclusi_maggiorenni')->checkbox() ?>

    <?= $form->field($model, 'note')->textarea(['rows' => 4]) ?>

    <div class="modal-footer">
        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
            <i class="bx bx-x d-block d-sm-none"></i>
            <span class="d-none d-sm-block">Annulla</span>
        </button>

        <?= Html::submitButton('Salva', [
            'class' => 'btn btn-primary ms-1',
            'id' => 'submit-' . $formId,
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<script>
    $('#<?= $formId ?>').on('beforeSubmit', function(e) {
        e.preventDefault();

        var form = $(this);
        var formData = form.serialize();

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            success: function(response) {
                $('#decreto-modal').modal('hide');
                $.pjax.reload({container: '#decreti-grid'});
            },
            error: function(xhr, status, error) {
                alert('Si è verificato un errore: ' + error);
            }
        });

        return false;
    });
</script>
