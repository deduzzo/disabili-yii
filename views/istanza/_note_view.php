<?php

use app\models\AnagraficaAltricampi;
use app\models\enums\IseeType;
use kartik\editors\Summernote;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Istanza $istanza */

?>
<button class="btn btn-<?=trim(strip_tags($istanza->note)) === "" ? "primary" : "danger" ?> collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#note-panel"
        aria-expanded="false" aria-controls="collapseExample" >
    <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= trim(strip_tags($istanza->note)) === "" ? "Nessuna nota presente" : htmlspecialchars($istanza->note) ?>"  data-bs-html="true" class="badge <?=trim(strip_tags($istanza->note)) === "" ? "bg-primary" : "bg-danger" ?>">Note <i class="bi bi-info-circle"></i></span>
</button>
<div class="collapse" id="note-panel" style="">
    <?php
    $form = ActiveForm::begin([
        'id' => 'my-form-id', // opzionale: id del tuo form
        'action' => ['/istanza/update','id'=>$istanza->id,'fromScheda' => true], // sostituisci 'controller/action' con il percorso desiderato
        'method' => 'post', // opzionale: il metodo di invio, di default è 'post'
    ]);
    ?>


    <?= $form->field($istanza, 'note')->widget(Summernote::class, [
        'useKrajeePresets' => true,
        // other widget settings
    ]); ?>

    <div class="form-group">
        <?= Html::submitButton('Aggiorna nota', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
