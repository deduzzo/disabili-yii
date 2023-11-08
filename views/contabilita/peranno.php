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
/** @var string $anno */
/** @var array $importi */


$this->title = 'Contabilità per anno';
$this->params['breadcrumbs'][] = $this->title;
$formatter = \Yii::$app->formatter;
?>
    <!-- Aggiungi uno stile per rendere tutte le colonne uguali e per i colori del testo -->
    <style>
        .mese {
            width: calc(100% / 12); /* Dividi lo spazio equamente per i 12 mesi */
        }

        .positivo {
            color: blue;
        }

        .negativo {
            color: red;
        }

        .totali-bg {
            background-color: #f0f0f0; /* Un grigio chiaro */
        }
    </style>
    <div class="card">
        <div class="card-header">
            <div class="card-toolbar">
                <?= Html::beginForm(['contabilita/anno'], 'get', ['class' => 'form-inline']) ?>
                <div class="row">
                    <div class="col-md-8">
                        <label for="anno" class="form-label">Seleziona Anno</label>
                        <select class="form-select" id="anno" name="anno">
                            <option selected>Scegli...</option>
                            <?php
                            for ($i = 2017; $i <= date('Y'); $i++)
                                echo "<option value='$i' " . ($i == $anno ? "selected " : "") . ">$i</option>";
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <!-- button submit -->
                        <?= Html::submitButton('Verifica', ['class' => 'btn btn-primary', 'style' => 'margin-top: 30px', 'name' => "submit"]) ?>
                    </div>
                    <!-- Inizio della tabella -->
                    <div class="table-responsive" style="margin-top: 30px;">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center" style="font-size: 12px;"></th>
                                <!-- Colonna vuota per l'etichetta -->
                                <?php foreach (['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'] as $mese): ?>
                                    <th class="mese text-center" style="font-size: 12px;"><?= $mese ?></th>
                                <?php endforeach; ?>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td class="fw-bold text-center" style="font-size: 12px;">Fondi</td>
                                <?php for ($i = 0; $i < 12; $i++): ?>
                                    <?php if ($i === 0 || $importi['colspan'][$i - 1] === 1): ?>
                                        <td class="mese text-center" style="font-size: 12px;"
                                            colspan="<?= $importi['colspan'][$i] ?? 1 ?>"><?= $formatter->asCurrency($importi['incasso'][$i] ?? "") ?></td>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </tr>
                            <tr>
                                <td class="fw-bold text-center" style="font-size: 12px;">Pagamenti</td>
                                <?php for ($i = 0; $i < 12; $i++): ?>
                                    <td class="mese text-center"
                                        style="font-size: 12px;"><?= $formatter->asCurrency($importi['spesa'][$i]) ?></td>
                                <?php endfor; ?>
                            </tr>
                            <tr class="fw-bold totali-bg">
                                <td class="fw-bold text-center" style="font-size: 12px;">DIFFERENZE</td>
                                <?php
                                $totaleGlobale = 0;
                                for ($i = 0; $i < 12; $i++) {
                                    if (($i === 0 || $importi['colspan'][$i - 1] === 1)) {
                                        $spesa = 0;
                                        for ($k = $i; $k < $i + ($importi['colspan'][$i] ?? 0); $k++)
                                            $spesa += $importi['spesa'][$k] ?? 0;
                                        $valore = ($importi['incasso'][$i] ?? 0) - $spesa;
                                        $totaleGlobale += $valore;
                                        $classe = $valore >= 0 ? 'positivo' : 'negativo';
                                        echo "<td class='mese " . $classe . " text-center' style='font-size: 12px;'
                            colspan='" . $importi['colspan'][$i] . "'>" . $formatter->asCurrency($valore) . "</td>";
                                    }
                                } ?>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-12">
                        <?php foreach ($importi['determineStoriche'] as $determina): ?>
                        <?php $totaleGlobale -= $determina['importo'] ?>
                            <div class="row">
                                <div class="col-md-2">
                                    <h5><?= $determina['numero'] ?></h5>
                                </div>
                                <div class="col-md-4">
                                    <h6><?= Yii::$app->formatter->asDate($determina['data']) ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <h6><?= $determina['importo'] ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <h6><?= $determina['descrizione'] ?></h6>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- mostra totale globale -->
                    <div class="col-md-12" style="margin-top:20px">
                        <h4 class="text-center">Totale: <?= $formatter->asCurrency($totaleGlobale) ?></h4>
                    </div>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>
        <div class="card-body">
            <div class="row">

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