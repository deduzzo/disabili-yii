<?php

use app\components\ExportWidget;
use app\models\Distretto;
use app\models\enums\IseeType;
use kartik\select2\Select2;
use richardfan\widget\JSRegister;
use yii\bootstrap5\Html;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $result */


$this->title = 'Verifica formale codice IBAN';
$this->params['breadcrumbs'][] = $this->title;
$formatter = \Yii::$app->formatter;
?>
    <style>
        /* Centra tutto il contenuto della colonna */
        .col-md-12 {
            text-align: center; /* Centra il testo */
            font-size: larger; /* Aumenta la dimensione del font per tutto il contenuto */
        }

        /* Stili specifici per l'input dell'IBAN */
        .iban-input {
            text-transform: uppercase; /* Trasforma il testo in maiuscolo */
            font-weight: bold; /* Rende il testo grassetto */
            width: 500px; /* Imposta la larghezza del campo di input */
            margin: 0 auto; /* Centra l'input se la larghezza del div è maggiore */
            display: block; /* Rende l'input un blocco per permettere la centratura con margin auto */
            font-size: larger; /* Aumenta la dimensione del testo dell'input */
        }

        /* Stili per la label */
        .form-label {
            display: block; /* Fa sì che la label occupi la sua riga */
            margin-bottom: .5rem; /* Aggiunge un po' di spazio sotto la label */
            font-size: larger; /* Aumenta la dimensione del font della label */
        }
    </style>
    <div class="card">
    <div class="card-header">
        <div class="card-toolbar">
            <!-- list of all new group names -->
            <?= Html::beginForm(['contabilita/verifica-iban'], 'get', ['class' => 'form-inline']) ?>
            <div class="row">
                <div class="col-md-12">
                    <input type="text" class="form-control iban-input" id="iban" name="iban" placeholder="Inserisci IBAN">
                </div>
                <div class="col-md-12 text-center">
                    <!-- button submit -->
                    <?= Html::submitButton('Verifica', ['class' => 'btn btn-primary', 'style' => 'margin-top: 30px', 'name' => "submit"]) ?>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>
        <div class="card-body text-center">
            <?= $result ?>
        </div>
    </div>
    <?php JSRegister::begin([
        'key' => 'manage',
        'position' => \yii\web\View::POS_READY
    ]); ?>
    <script>

    </script>
<?php JSRegister::end(); ?>