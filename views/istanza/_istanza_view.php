<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Istanza $model */

?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'data_inserimento',
            'riconosciuto:boolean',
            'classe_disabilita',
            'attivo:boolean',
            'data_decesso',
            'liquidazione_decesso_completata:date',
            'chiuso:boolean',
        ],
    ]) ?>
