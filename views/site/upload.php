<?php

/**
 * @var \yii\web\View $this
 * @var \app\models\UploadForm $files
 */

use app\components\ExportWidget;
use app\models\enums\TipologiaDatiCategoria;
use app\modules\export\Exporter;
use kartik\file\FileInput;
use yii\bootstrap5\ActiveForm;

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card">
    <div class="card-header border-0 pt-6">
        <h1 class="card-title">Upload file</h1>
    </div>
    <div class="card-body pt-0">
        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>
        <?php
        // echo option group with values "ricoveri" or "pagamenti"
        echo $form->field($files, 'tipo')->dropDownList([
            TipologiaDatiCategoria::RICOVERI => 'Ricoveri',
            TipologiaDatiCategoria::MOVIMENTI_CON_IBAN => 'Pagamenti con IBAN',
            TipologiaDatiCategoria::MOVIMENTI_CON_ELENCHI => 'Pagamenti con Elenchi',
            TipologiaDatiCategoria::TRACCIATO_SEPA => 'Tracciato SEPA',
        ], ['prompt' => 'Selezionare il tipo di file da importare..']);
        echo $form->field($files, 'simulazione')->checkbox();
        echo $form->field($files, 'files[]')->widget(FileInput::class, [
            'options' => [
                'multiple' => true,
                //'disabled' => ImportProcessi::processoInCorso()
            ],
            'pluginOptions' => [
                'initialCaption' => "Selezionare i files da importare..",
                'dropZoneTitle' =>  "Trascinare qui i files da importare.."
            ],
        ]);
        ?>
        <?php ActiveForm::end(); ?>
    </div>
</div>
