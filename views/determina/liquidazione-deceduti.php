<?php

use app\components\ExportWidget;
use app\models\enums\ImportoBase;
use app\models\enums\IseeType;
use app\models\IstanzaSearch;
use richardfan\widget\JSRegister;
use yii\bootstrap5\Html;
use yii\grid\CheckboxColumn;
use yii\grid\GridView;


/** @var yii\web\View $this */
/** @var string $result */
/** @var IstanzaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */


$this->title = 'Liquidazione Deceduti';
$this->params['breadcrumbs'][] = $this->title;
$formatter = \Yii::$app->formatter;


$cols = [
    'id',
    [
        'attribute' => 'distretto',
        'value' => function ($model) {
            return $model->distretto->nome;
        }
    ],
    [
        'label' => 'Nominativo',
        'attribute' => 'cognomeNome',
        'value' => function ($model) {
            return $model->getNominativoDisabile();
        }
    ],
    [
        'label' => 'Codice Fiscale',
        'attribute' => 'cf',
        'value' => function ($model) {
            return $model->anagraficaDisabile->codice_fiscale;
        }

    ],
    'data_decesso:date',
    [
        'attribute' => 'descrizione_gruppo',
        'value' => function ($model) {
            return $model->gruppo->descrizione_gruppo;
        }
    ],
    [
        'label' => 'Data Ultimo pagamento',
        'value' => function ($model) {
            $last = $model->getLastMovimentoBancario();
            if (!$last)
                return "-";
            return Yii::$app->formatter->asDate($last->data);
        }
    ],
    [
        'label' => 'Importo Ultimo pagamento',
        'value' => function ($model) {
            $last = $model->getLastMovimentoBancario();
            if (!$last)
                return "-";
            return Yii::$app->formatter->asCurrency($last->importo);
        }
    ],
    [
        'label' => 'Pagamenti Tornati indietro?',
        'value' => function ($model) {
            $last = $model->getLastMovimentoBancario();
            $tornatiIndietro = $model->getPagamentiTornatiIndietro(!$last ? null : $last->data);
            return (count($tornatiIndietro) === 0) ? "NO" : "SI";
        }
    ],
    [
        'label' => 'Giorni dovuti',
        'value' => function ($model) {
            return $model->getGiorniResiduoDecesso();
        }
    ],
    [
        'label' => 'Isee',
        'value' => function ($model) {
            return $model->getLastIseeType();
        }
    ],
];

$checkboxColumn = [
    [
        'class' => CheckboxColumn::class,
        'checkboxOptions' => function ($model) {
            // Modifichiamo questa parte per usare il codice fiscale come valore
            return [
                'value' => $model->anagraficaDisabile->codice_fiscale,
                'data-id' => $model->id // Manteniamo l'ID come attributo data per il form
            ];
        },
    ]
];

?>


<div class="modal fade text-left" id="finalizza-determina" tabindex="-1" aria-labelledby="label-modifica"
     style="display: none;"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document"
         style="min-width: 800px">
        <div class="modal-content">
            <?= Html::beginForm(['/determina/finalizza-liquidazione-deceduti'], 'post', ['id' => 'finalizza-deceduti', 'class' => 'form-horizontal']) ?>
            <input type="hidden" name="ids" id="ids">
            <div class="modal-header bg-primary">
                <h5 class="modal-title white" id="label-modifica">
                    Determina liquidazione
                </h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="feather feather-x">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="cols-12">
                        <p id="info_istanze" class="mb-2"></p>
                    </div>
                    <!-- checkbox per indicare "considera iban da colonna "iban" -->
                    <div class="col-md-12">
                        <div class="form-check form-switch form-check-primary">
                            <input type="checkbox" class="form-check-input" id="considera-iban" name="considera-iban">
                            <label class="form-check" for="considera-iban">Considera iban da colonna "iban"</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <?= // echo dropDownList with Determina::getAllDetermineMap() and 'prompt' => 'Selezionare la determina..'
                            Html::dropDownList('idDetermina', null, \app\models\Determina::getAllDetermineMap(), ['prompt' => 'Selezionare la determina..', 'class' => 'form-control'])
                        ?>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">Annulla</span>
                    </button>

                    <button type="submit" class="btn btn-warning ms-1">
                        <i class="bx bx-check d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">Finalizza determina di liquidazione</span>
                    </button>

                </div>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>
</div>


<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-12">
                <div class="card-toolbar d-flex align-items-center">
                    <a class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#finalizza-determina"
                       onclick="check()">Determina Liquidazione</a>

                    <!-- Textbox per incollare i valori -->
                    <textarea id="valuesTextbox" rows="2" cols="10" class="form-control me-2"
                              placeholder="Inserisci valori separati da invio"></textarea>

                    <!-- Bottone Seleziona Checkbox -->
                    <button id="selezionaCheckboxBtn" class="btn btn-secondary">Seleziona Checkbox</button>
                </div>
            </div>
            <div class="col-12" style="margin-top:10px">
                <?= ExportWidget::widget([
                    //'models' => $dataProvider->getModels(),
                    'dataProvider' => $dataProvider,
                    'columns' => array_merge($cols, [[
                        'label' => 'Importo a conguaglio',
                        'value' => function ($model) {
                            $dati = $model->getDatiLiquidazioneDecesso();
                            if ($dati['ok'] === true)
                                return $dati['valore'];
                            else
                                return $dati['descrizione'];
                        }
                    ]])
                ]) ?>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?= GridView::widget([
            'id' => 'elenco-disabili',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' =>
                "<div class='table-container'>{items}</div>
                        <div class='dataTable-bottom'>
                              <div class='dataTable-info'>{summary}</div>
                              <nav class='dataTable-pagination'>
                                    {pager}
                              </nav>
                        </div>",
            'pager' => [
                'class' => 'yii\bootstrap5\LinkPager',
                'firstPageLabel' => 'PRIMA',
                'lastPageLabel' => 'ULTIMA',
                'nextPageLabel' => '>>',
                'prevPageLabel' => '<<',
                'linkOptions' => ['class' => 'page-link'],
            ],
            'options' => [
                'tag' => 'div',
                'class' => 'dataTable-wrapper dataTable-loading no-footer sortable searchable fixed-columns',
            ],
            'tableOptions' => [
                'class' => 'table table-striped dataTable-table',
            ],
            'columns' => array_merge($checkboxColumn, $cols, [
                [
                    'label' => 'Importo a conguaglio',
                    'value' => function ($model) {
                            $dati = $model->getDatiLiquidazioneDecesso();
                            return $dati['descrizione'];
                    }
                ]
            ])
        ]); ?>
    </div>
</div>
<script>
    function check() {
        // Ottieni tutte le checkbox selezionate
        const checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');

        // Raccogli gli ID dalle checkbox selezionate usando l'attributo data-id
        const selectedIds = Array.from(checkboxes)
            .map(checkbox => checkbox.getAttribute('data-id'))
            .filter(id => id); // Filtra eventuali valori null/undefined

        document.getElementById('info_istanze').innerHTML = selectedIds.length + " Istanze selezionate";

        // Inserisci gli ID nell'input nascosto
        document.getElementById('ids').value = selectedIds.join(',');
    }

    document.getElementById("selezionaCheckboxBtn").addEventListener("click", function (event) {
        event.preventDefault();
        console.log("Bottone premuto");

        // Ottieni i codici fiscali dalla textbox
        const textbox = document.getElementById("valuesTextbox");
        const codiciFiscali = textbox.value.split('\n')
            .map(val => val.trim().toUpperCase()) // Normalizziamo i codici fiscali in maiuscolo
            .filter(val => val); // Rimuove le righe vuote

        console.log("Codici fiscali da cercare:", codiciFiscali);

        // Seleziona tutte le checkbox
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');

        // Loop sulle checkbox e seleziona quelle che corrispondono ai codici fiscali
        checkboxes.forEach(checkbox => {
            const value = checkbox.value.trim().toUpperCase();
            if (codiciFiscali.includes(value)) {
                checkbox.checked = true;
                console.log("Trovata corrispondenza per:", value);
            } else {
                checkbox.checked = false;
            }
        });
    });
</script>