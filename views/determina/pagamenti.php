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
/** @var string $mese */
/** @var string $anno */
/** @var string $result */


$this->title = 'Verifica pagamenti';
$this->params['breadcrumbs'][] = $this->title;
$formatter = \Yii::$app->formatter;
?>
    <div class="card">
        <div class="card-header">
            <div class="card-toolbar">
                <?= Html::beginForm(['determina/pagamenti'], 'get', ['class' => 'form-inline']) ?>
                <div class="row">
                    <div class="col-md-4">
                        <label for="mese" class="form-label">Seleziona Mese</label>
                        <select class="form-select" id="mese" name="mese">
                            <?php
                            $mesi = array(
                                1 => 'Gennaio',
                                2 => 'Febbraio',
                                3 => 'Marzo',
                                4 => 'Aprile',
                                5 => 'Maggio',
                                6 => 'Giugno',
                                7 => 'Luglio',
                                8 => 'Agosto',
                                9 => 'Settembre',
                                10 => 'Ottobre',
                                11 => 'Novembre',
                                12 => 'Dicembre'
                            );
                            ?>
                            <option selected value="">Scegli...</option>
                            <?php
                            foreach ($mesi as $numero => $nome)
                                echo "<option value='$numero' >" . $nome . "</option>";
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="anno" class="form-label">Seleziona Anno</label>
                        <select class="form-select" id="anno" name="anno">
                            <option selected>Scegli...</option>
                            <?php
                            for ($i = date('Y') - 5; $i <= date('Y'); $i++)
                                echo "<option value='$i' " . ($i == date('Y') ? "selected " : "") . ">$i</option>";
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <!-- button submit -->
                        <?= Html::submitButton('Verifica', ['class' => 'btn btn-primary', 'style' => 'margin-top: 30px']) ?>
                    </div>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <?= $result ?>
            </div>
        </div>
    </div>
<?php JSRegister::begin([
    'key' => 'manage',
    'position' => \yii\web\View::POS_READY
]); ?>
    <script>

    </script>
<?php JSRegister::end(); ?>