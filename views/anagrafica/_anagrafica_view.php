<?php


use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Anagrafica $model */

if ($model)
echo DetailView::widget([
    'model' => $model,
    'options' => ['class' => 'table table-striped table-bordered detail-view small'],
    'attributes' => [
        'cognome_nome',
        'codice_fiscale',
        'data_nascita:date',
    ],
]);
else
    echo '<p class="small">Non è stata trovata nessuna anagrafica</p>';