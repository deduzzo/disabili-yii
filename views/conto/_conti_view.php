<?php


use app\models\Anagrafica;
use app\models\ContoSearch;
use app\models\Istanza;
use app\models\MovimentoSearch;
use kartik\select2\Select2;
use richardfan\widget\JSRegister;
use yii\bootstrap5\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var Istanza $istanza */

$searchModel = new ContoSearch();
$dataProvider = $searchModel->search(Yii::$app->request->queryParams, $istanza);
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layout' => "<div class='table-container'>{items}</div>
                            <div class='dataTable-bottom'>
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
        'class' => 'grid-view small dataTable-wrapper dataTable-loading no-footer sortable searchable fixed-columns',
        'id' => 'datatable',
    ],
    'tableOptions' => [
        'class' => 'table table-striped dataTable-table',
        'id' => 'table1',
    ],
    'columns' => [
        'id',
        'iban',
        [
            'label' => "Stato",
            'value' => function ($model) {
                return ($model->attivo && $model->validato) ?
                    '<span class="badge bg-success">ATTIVO</span>' : (!$model->validato ? '<span class="badge bg-warning">DA VALIDARE</span>' : '<span class="badge bg-danger">DISATTIVO</span>');
            },
            'format' => 'raw',
        ],
        'note',
    ],
    'emptyText' => 'Nessun movimento presente',

]);


?>

<div class="modal fade text-left" id="nuovo-conto" tabindex="-1" aria-labelledby="myModalLabel140"
     style="display: none;"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <?= Html::beginForm(['conto/crea-nuovo'], 'post', ['id' => 'formNuovoConto']) ?>
        <?= Html::hiddenInput('idIstanza', $istanza->id) ?>
        <?= Html::hiddenInput('validato', "false") ?>
        <div class="modal-content" style="min-width: 500px">
            <div class="modal-header bg-primary">
                <h5 class="modal-title white" id="myModalLabel140">
                    Nuovo Conto
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

                <!-- iban -->
                <div class="mb-1">
                    <?= Html::label('Iban', 'newiban', ['class' => 'form-label']) ?>
                    <?= Html::textInput('newIban', '', ['class' => 'form-control', 'id' => 'newIban']) ?>
                </div>
                <!-- intestatario, kartik select2 with data all the anagrafica nome and cognome, filtering enabled -->
                <div class="mb-1">
                    <?= Html::label('Intestatario:', 'intestatario', ['class' => 'form-label']) ?>

                    <div class="mb-1">
                        <?php $newVersion = false;
                        if ($newVersion): ?>
                            <div class="d-flex align-items-center"> <!-- Contenitore Flexbox -->
                                <div class="flex-grow-1">
                                    <!-- Questo div farà in modo che Select2 cresca per riempire lo spazio disponibile -->
                                    <?= Select2::widget([
                                        'name' => 'intestatario',
                                        'data' => Anagrafica::getAnagraficheList(),
                                        'options' => [
                                            'placeholder' => 'Seleziona un intestatario...',
                                            'multiple' => false,
                                            'class' => 'form-control',
                                            'id' => 'intestatario',
                                        ],
                                        'pluginOptions' => [
                                            'minimumInputLength' => 3,
                                            'dropdownParent' => '#nuovo-conto'
                                        ]
                                    ]); ?>
                                </div>

                                <!-- pulsante di aggiungi anagrafica -->
                                <button id="aggiungiAnagrafica" class="btn btn-warning ms-1">
                                    <i class="bx bx-check d-block d-sm-none"></i>
                                    <span class="d-none d-sm-block">+</span>
                                </button>
                            </div>
                        <?php else: ?>
                        <div class="flex-grow-1">
                            <!-- input text of intestatario -->
                            <?= Html::textInput('intestatario', '', ['class' => 'form-control', 'id' => 'intestatario']) ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- note -->
                    <div class="mb-1">
                        <?= Html::label('Note', 'note', ['class' => 'form-label']) ?>
                        <?= Html::textarea('note', '', ['class' => 'form-control', 'id' => 'note']) ?>
                    </div>
                </div>
                <div class="modal-footer d-flex">
                    <div class="form-label mr-auto" id="checkStatus"></div>

                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">Annulla</span>
                    </button>

                    <button type="submit" id="aggiungiConto" class="btn btn-warning ms-1" disabled>
                        <i class="bx bx-check d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">Aggiungi conto</span>
                    </button>
                </div>
            </div>
        </div>
        <?= Html::endForm() ?>
    </div>
</div>


    <script>
        function checkIban() {
            const iban = $('#newIban').val();  // usiamo $(this) per brevità ed esattezza
            console.log(iban);
            $.ajax({
                url: '/conto/check-iban',
                type: 'POST',
                data: {newIban: iban},
                success: function (data) {
                    console.log(data);
                    if (data === true) {
                        // set text of div #checkStatus to 'IBAN VALIDO'
                        $('#checkStatus').text('IBAN VALIDO');
                        // set class of div #checkStatus to 'text-success'
                        $('#checkStatus').removeClass('text-danger');
                        $('#checkStatus').addClass('text-success');
                        $('#aggiungiConto').prop('disabled', false);
                    } else {
                        // set text of div #checkStatus to 'IBAN NON VALIDO'
                        $('#checkStatus').text('IBAN NON VALIDO');
                        // set class of div #checkStatus to 'text-danger'
                        $('#checkStatus').removeClass('text-success');
                        $('#checkStatus').addClass('text-danger');
                        // aggiungiConto disable
                        $('#aggiungiConto').prop('disabled', true);
                    }
                }
            });
        }
    </script>
