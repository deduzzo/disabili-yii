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
/** @var array $ibanRipetuti */


$this->title = 'Verifica pagamenti';
$this->params['breadcrumbs'][] = $this->title;
$formatter = \Yii::$app->formatter;
?>
    <div class="card">
        <div class="card-header">
            <div class="card-toolbar">
                <?= Html::beginForm(['determina/pagamenti'], 'get', ['class' => 'form-inline']) ?>
                <!-- bootstrap separator with name "Pagamenti" -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="divider">
                            <div class="divider-text">Pagamenti Mensili</div>
                        </div>
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
                                    <option value="">Scegli...</option>
                                    <?php
                                    foreach ($mesi as $numero => $nome)
                                        echo "<option value='$numero' " . ($mese == $numero ? 'selected' : '') . " >" . $nome . "</option>";
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
                                <?= Html::submitButton('Verifica', ['class' => 'btn btn-primary', 'style' => 'margin-top: 30px', 'name' => "submit"]) ?>
                            </div>
                            <?= Html::endForm() ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="divider">
                            <div class="divider-text">Verifica Iban</div>
                        </div>
                        <?= Html::beginForm(['determina/pagamenti'], 'get', ['class' => 'form-inline']) ?>
                        <button type="submit" class="btn btn-primary" value="verifica-iban" name="verifica-iban">
                            Verifica Iban
                        </button>
                        <?php if ($ibanRipetuti !== null): ?>
                            <div class="alert alert-danger" role="alert">
                                <strong>Attenzione!</strong> Sono stati trovati <?= count($ibanRipetuti) ?> iban
                                ripetuti.<br /><br />
                                <?php foreach ($ibanRipetuti as $key => $iban) {
                                    echo $key ."<br />";
                                    foreach ($iban as $istanza) {
                                        /* @var \app\models\Istanza $istanza */
                                        echo "<a href='" . Url::to(['istanza/scheda', 'id' => $istanza->id]) . "' target='_blank'>" . $istanza->id . "-" . $istanza->getNominativoDisabile(). " - ".$istanza->getGruppo(). "</a><br />";
                                    }
                                    echo "<br />";
                                } ?>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!--<div class="accordion" id="accordionExample">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Accordion Item #1
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample" style="">
                            <div class="accordion-body">
                                <strong>This is the first item's accordion body.</strong> It is shown by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Accordion Item #2
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample" style="">
                            <div class="accordion-body">
                                <strong>This is the second item's accordion body.</strong> It is hidden by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Accordion Item #3
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample" style="">
                            <div class="accordion-body">
                                <strong>This is the third item's accordion body.</strong> It is hidden by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
                            </div>
                        </div>
                    </div>
                </div>-->
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