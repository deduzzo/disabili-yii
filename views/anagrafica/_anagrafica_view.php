<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Anagrafica $model */

if ($model)
echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'cognome_nome',
        'codice_fiscale',
        'data_nascita:date',
    ],
]);
else
    echo '<p>Non è stata trovata nessuna anagrafica</p>';