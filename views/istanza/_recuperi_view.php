<?php

use kartik\editors\Summernote;
use richardfan\widget\JSRegister;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;


/** @var yii\web\View $this */
/** @var app\models\Istanza $istanza */

$searchModel = new \app\models\RecuperoSearch();
$searchModel->id_istanza = $istanza->id;
$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
\yii\widgets\Pjax::begin(['id' => 'lista-recuperi']);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'options' => [
        'tag' => 'div',
        'class' => 'grid-view small dataTable-wrapper dataTable-loading no-footer sortable searchable fixed-columns',
        'id' => 'datatable',
    ],
    'tableOptions' => [
        'class' => 'table table-striped dataTable-table',
        'id' => 'table1',
    ],
    'columns' => [
        'id',
        [
            'attribute' => 'importo',
            'label' => 'Importo',
            'contentOptions' => ['style' => 'font-weight: bold;'],
            'value' => function ($model) {
                return '<span class="badge bg-' . ($model->importo > 0 ? 'success' : 'danger') . '">' . Yii::$app->formatter->asCurrency($model->importo) . '</span>';
            },
            'format' => 'raw',
        ],
        [
            'attribute' => 'chiuso',
            'label' => 'Stato',
            'value' => function ($model) {
                return $model->annullato ?
                    '<span class="badge bg-warning">ANNULLATO</span>' :
                    ($model->chiuso ? '<span class="badge bg-secondary">CHIUSO</span>' : ('<span class="badge bg-info">Attivo</span>' . ($model->rateizzato == 1 ? ('<br /><span class="badge bg-success">RATEIZZATO<br />mancano ' . $model->getRateMancanti() . ' rate</span>') : ''))
                    );
            },
            'format' => 'raw',
            // center text
            'contentOptions' => ['class' => 'text-center'],
        ],
        // button edit
        [
            'label' => 'Descrizione',
            'value' => function ($model) {
                return $model->getDescrizioneRecupero(false);
            },
            'format' => 'raw',
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{editNote} {annulla} {delete}',
            'buttons' => [
                'delete' => function ($url, $model) {
                    return Html::a('<i class="bi bi-trash"></i>', '#', [
                        'class' => 'btn btn-sm btn-danger',
                        'onclick' => "confirmDeleteRecupero({$model->id}); return false;",
                        'style' => 'display: block; '.($model->getRateSaldate() >0 ? "disabled" : ""),
                        'title' => 'Elimina recupero',
                    ]);
                },
                'annulla' => function ($url, $model) {
                    if ($model->annullato == 0 && $model->chiuso == 0)
                        return Html::a('<i class="fas fa-undo"></i>', '#', [
                            'class' => 'btn btn-sm btn-warning',
                            'title' => 'Annulla recupero',
                            'style' => 'display: block',
                            'data-bs-toggle' => 'modal',
                            'data-bs-target' => '#warning',
                            'onclick' => '$("#id_recupero").val(' . $model->id . ');',
                        ]);
                    else
                        return "";
                },
                'editNote' => function ($url, $model) {
                    return Html::a('<i class="bi bi-card-text"></i>', '#', [
                        'class' => 'btn btn-sm btn-secondary',
                        'style' => 'margin-right: 3px; display: block',
                        'title' => $model->note . "<p>Clicca per modificare la nota</p>",
                        'data-bs-toggle' => 'tooltip',
                        'data-bs-placement' => 'bottom',
                        'data-bs-html' => 'true',
                        'onclick' => 'showNotaRecupero("' . $model->id . '","' . Html::encode($model->note) . '");',
                    ]);
                },

            ],
        ],
    ],
    'emptyText' => 'Nessun altro dato presente',

]);
\yii\widgets\Pjax::end();
?>

    <div class="modal fade text-left" id="warning" tabindex="-1" aria-labelledby="myModalLabel140"
         style="display: none;"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title white" id="myModalLabel140">
                        Annullamento recupero
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
                    <p style="margin-bottom:10px; font-weight: bold">Sei sicuro di voler annullare questo recupero?
                        Resteranno comunque visibili i movimenti di recupero già
                        effettuati.</p>
                    <?= Html::beginForm(['/recupero/annulla'], 'post', ['id' => 'annullamentoRecupero', 'class' => 'form-horizontal']) ?>
                    <?php
                    // show hidden field "id_recupero" of type text
                    echo Html::hiddenInput('id_recupero', null, ['class' => 'form-control', 'id' => 'id_recupero']);
                    echo Html::label('Data annullamento', 'data_annullamento', ['class' => 'control-label']);
                    echo Html::input('date', 'data_annullamento', date('Y-m-d'), ['class' => 'form-control', 'id' => 'data_annullamento']);
                    // show option field "restituisci_importo", possible values: "Restituisci intero importo", "Importo residuo da saldare in un unica soluzione (termina rateizzazione)","Chiudi contabile". Default "Selezionare scelta"
                    echo Html::label('Scelta', 'restituisci_importo', ['class' => 'control-label']);
                    echo Html::dropDownList('azione_chiusura', null, [
                        '' => 'Selezionare...',
                        'restituisci' => 'RESTITUISCI (se rateizzato, verrà generato un altro recupero negativo o positivo in base alla tipologia di recupero)',
                        'salda' => 'RESIDUO DA SALDARE in un unica soluzione (termina rateizzazione in anticipo)',
                        'chiudi' => 'CHIUDI contabile (nessun azione)',
                    ], ['class' => 'form-control', 'id' => 'restituisci_importo']);
                    // echo textarea "note" of type text
                    echo Html::label('Note chiusura', 'note', ['class' => 'control-label']);
                    echo Html::textarea('note', null, ['class' => 'form-control', 'id' => 'note']);
                    ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">Chiudi</span>
                    </button>

                    <button type="submit" class="btn btn-warning ms-1">
                        <i class="bx bx-check d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">Annulla Recupero</span>
                    </button>
                    <?= Html::endForm() ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="nuovo-recupero" data-bs-backdrop="static" data-bs-keyboard="false"
         tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" style="min-width: 700px">
            <div class="modal-content">
                <?= Html::beginForm(['recupero/create-by-istanza', 'id' => $istanza->id]); ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Aggiungi Recupero</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-12">Inserire i dati per il recupero per l'istanza di
                                    disabilità
                                    di <?= $istanza->getNominativoDisabile(); ?></div>
                                <div class="col-md-6 mt-4">
                                    <?= Html::label('Importo (€)', 'importo') ?>
                                    <?= Html::textInput('importo', null, ['class' => 'form-control', 'id' => 'importo', 'type' => 'number', 'oninput' => 'checkStatus()']) ?>
                                </div>
                                <div class="col-md-6 mt-4">
                                    <?= Html::label('Tipo', 'tipologia'); ?>
                                    <?= Html::radioList('tipologia', 'negativo', ['negativo' => 'Debito (-)', 'credito' => 'Credito (+)'], ['class' => 'form-control form-select', 'onchange' => 'checkStatus()', 'id' => 'tipologia', 'inline' => true]) ?>
                                </div>
                            </div>
                            <div class="row align-items-end">
                                <div class="col-md-12 form-check form-switch" style="margin-left:10px">
                                    <?= Html::checkbox('rateizzato', null, ['class' => 'form-control form-check-input', 'id' => 'rateizzato', 'onchange' => 'checkStatus()']) ?>
                                    <?= Html::label('Rateizzato', 'rateizzato', ['class' => 'form-check-label']); ?>
                                </div>
                                <div class="col-md-3 mt-2">
                                    <?= Html::label('N°Rate', 'numRate'); ?>
                                    <?= Html::textInput('numRate', null, ['class' => 'form-control', 'id' => 'numRate', 'type' => 'number', 'disabled' => true, 'oninput' => "checkStatus()"]) ?>
                                    <?= Html::hiddenInput('numRate_hidden', null, ['id' => "numRate_hidden"]) ?>
                                </div>
                                <div class="col-md-3 mt-2">
                                    <?= Html::label('Importo rata', 'importoRata'); ?>
                                    <?= Html::textInput('importoRata', null, ['class' => 'form-control', 'id' => 'importoRata', 'type' => 'number', 'disabled' => true, 'oninput' => "checkStatus()"]) ?>
                                    <?= Html::hiddenInput('importoRata_hidden', null, ['id' => "importoRata_hidden"]) ?>
                                </div>
                                <div class="col-md-3 mt-2">
                                    <?= Html::label('n° rate saldate', 'numRatePagate'); ?>
                                    <?= Html::textInput('numRatePagate', null, ['class' => 'form-control', 'id' => 'numRatePagate', 'type' => 'number', 'disabled' => true, 'oninput' => "checkStatus()"]) ?>
                                </div>
                                <div class="col-md-3 mt-2">
                                    <?= Html::label('n° mesi posticipo', 'numMesiPosticipo'); ?>
                                    <?= Html::textInput('numMesiPosticipo', null, ['class' => 'form-control', 'id' => 'numMesiPosticipo', 'type' => 'number', 'disabled' => true, 'oninput' => "checkStatus()"]) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 form-check form-switch d-flex justify-content-center align-items-center"
                                     style="margin-top: 3px;">
                                    <?= Html::checkbox('calcolo_automatico', false, ['class' => 'form-control form-check-input', 'id' => 'calcolo_automatico', 'onchange' => 'checkStatus()', 'disabled' => true]) ?>
                                    <?= Html::label('Calcola rata', 'calcolo_automatico', ['class' => 'form-check-label']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-white bg-primary mb-3" style="max-width: 18rem;">

                                <div class="card-body" style="min-height: 200px">
                                    <h5 class="card-title text-white">Riepilogo</h5>
                                    <p class="card-text text-white" id="riepilogoRateizzazione"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla
                    </button>
                    <button type="submit" class="btn btn-danger" id="btnSalvaRateizzazione" disabled="true">
                        Salva
                    </button>
                </div>
                <?= Html::endForm(); ?>
            </div>
        </div>
    </div>


    <div class="modal fade" id="modifica-nota-recupero" data-bs-backdrop="static" data-bs-keyboard="false"
         tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="min-width: 700px">
                <?= Html::beginForm(['recupero/aggiorna-nota']); ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="titoloNotaRecupero"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?= Html::hiddenInput('id_recupero', null, ['id' => 'id_recupero_nota']) ?>
                    <div class="row">
                        <div class="col-md-12">
                            <?= // using Summernote widget
                            Summernote::widget([
                                'name' => 'testo-nota',
                                'value' => null,
                                'id' => 'testo-nota',
                            ]); ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla
                        </button>
                        <button type="submit" class="btn btn-danger" id="salvaNota">
                            Salva nota
                        </button>
                    </div>
                    <?= Html::endForm(); ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDeleteRecupero(id) {
            if (confirm('Sei sicuro di voler eliminare questo recupero?')) {
                $.pjax({
                    url: '/recupero/delete?id=' + id,
                    container: '#lista-recuperi',
                    push: false,
                    replace: false,
                    scrollTo: false,
                    timeout: 10000
                }).done(function () {
                    //$.pjax.reload({container: '#lista-recuperi'});
                    location.reload();
                })
            }
        }

        function checkStatus() {
            if (document.getElementById('rateizzato').checked) {
                document.getElementById('numMesiPosticipo').disabled = false;
                document.getElementById('numRate').disabled = false;
                document.getElementById('numRatePagate').disabled = false;
                document.getElementById('calcolo_automatico').disabled = false;
                if (document.getElementById('calcolo_automatico').checked) {
                    if (!isNaN(parseFloat(document.getElementById('numRate').value)) && parseFloat(document.getElementById('numRate').value) > 0)
                        document.getElementById('importoRata').value = (parseFloat(document.getElementById('importo').value) / parseFloat(document.getElementById('numRate').value)).toFixed(2);
                } else {
                    if (!isNaN(parseFloat(document.getElementById('importoRata').value)) && parseFloat(document.getElementById('importoRata').value) > 0)
                        document.getElementById('numRate').value = Math.ceil(parseFloat(document.getElementById('importo').value) / parseFloat(document.getElementById('importoRata').value));
                }
            } else {
                document.getElementById('numRate').disabled = true;
                document.getElementById('numRate').value = '';
                document.getElementById('numRatePagate').disabled = true;
                document.getElementById('numRatePagate').value = '';
                document.getElementById('importoRata').value = '';
                document.getElementById('calcolo_automatico').disabled = true;
                document.getElementById('numMesiPosticipo').disabled = true;
                document.getElementById('numMesiPosticipo').value = '';
            }
            document.getElementById('importoRata').disabled = !document.getElementById('rateizzato').checked || document.getElementById('calcolo_automatico').checked;
            document.getElementById('numRate').disabled = !document.getElementById('rateizzato').checked || !document.getElementById('calcolo_automatico').checked;

            let riassunto = "Inserire tutti i valori richiesti";
            if (!isNaN(document.getElementById('importoRata').value) && document.getElementById('importo').value &&
                (!document.getElementById('rateizzato').checked || (document.getElementById('rateizzato').checked && !isNaN(document.getElementById('numRate').value) && document.getElementById('numRate').value > 0))) {
                riassunto = "Tipo: " + $("input[name='tipologia']:checked").next('label').text() + "<br />" +
                    "Importo: " + document.getElementById('importo').value + " €<br />" +
                    "Rateizzato: " + (document.getElementById('rateizzato').checked ? "SI" : "NO") + "<br />" +
                    (document.getElementById('rateizzato').checked && document.getElementById('numRate').value ?
                        ((!document.getElementById('calcolo_automatico').checked ?
                                ((parseFloat(document.getElementById('importo').value) % (parseInt(document.getElementById('numRate').value) * parseFloat(document.getElementById('importoRata').value)) === 0) ?
                                    document.getElementById('numRate').value :
                                    (parseInt(document.getElementById('numRate').value) - 1)) :
                                document.getElementById('numRate').value)
                            + " rate da " + document.getElementById('importoRata').value + " €<br />") : "") +
                    ((!document.getElementById('calcolo_automatico').checked && document.getElementById('rateizzato').checked && ((parseFloat(document.getElementById('importo').value) % (parseInt(document.getElementById('numRate').value) * parseFloat(document.getElementById('importoRata').value)) !== 0))) ?
                        ("+ ultima rata: " +
                            (Math.abs(parseFloat(document.getElementById('importo').value) - (parseFloat(document.getElementById('importoRata').value) * (parseInt(document.getElementById('numRate').value) - 1)))) + " €<br />")
                        : "") +
                    (document.getElementById('rateizzato').checked && document.getElementById('numRate').value && document.getElementById('numRatePagate').value && parseInt(document.getElementById('numRatePagate').value) > 0 ?
                        (document.getElementById('numRatePagate').value + " rate già pagate<br />") : "") +
                    "<p class='text-danger' style='margin-top: 10px'>RESIDUO: " + (document.getElementById('rateizzato').checked ? (document.getElementById('importo').value - (document.getElementById('importoRata').value * document.getElementById('numRatePagate').value)) : document.getElementById('importo').value) + " €</p>";
                document.getElementById('btnSalvaRateizzazione').disabled = false;
            } else
                document.getElementById('btnSalvaRateizzazione').disabled = true;

            document.getElementById('riepilogoRateizzazione').innerHTML = riassunto
            document.getElementById('numRate_hidden').value = document.getElementById('numRate').value;
            document.getElementById('importoRata_hidden').value = document.getElementById('importoRata').value;
        }

        function showNotaRecupero(idRecupero, testo) {
            console.log('coia');
            document.getElementById('id_recupero_nota').value = idRecupero;
            document.getElementById('titoloNotaRecupero').innerHTML = "Nota recupero #" + idRecupero;
            $('#testo-nota').summernote('code', $('<div/>').html(testo).text());
            //show the modal
            $('#modifica-nota-recupero').modal('show');
        }

    </script>

<?php JSRegister::begin([
    'key' => 'manage',
    'position' => \yii\web\View::POS_READY
]); ?>
    <script>
        $(document).ready(function () {
            checkStatus();
        });
    </script>
<?php JSRegister::end(); ?>