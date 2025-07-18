<?php

use app\models\AnagraficaAltricampi;
use app\models\enums\IseeType;
use app\models\IseeSearch;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Istanza $model */

$searchModel = new IseeSearch();
$dataProvider = $searchModel->search(Yii::$app->request->queryParams, $model);

\yii\widgets\Pjax::begin(['id' => 'lista-isee']);

if ($model->anagraficaDisabile->isMinorenne())
    // echo badge "Isee non necessario in quanto <18"
    echo '<span class="badge bg-info">Isee non necessario in quanto minorenne</span>';

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layout' => "<div class='table-container'>{items}</div>
                            <div class='dataTable-bottom'>
                                  <nav class='dataTable-pagination'>
                                        {pager}
                                  </nav>
                            </div>",
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
        'data_presentazione:date',
        [
            'attribute' => 'maggiore_25mila',
            'label' => 'Stato',
            'value' => function ($model) {
                return $model->maggiore_25mila ?
                    '<span class="badge '.IseeType::MAGGIORE_25K_COLOR.'">&#62; 25.000 €</span>' : '<span class="badge '.IseeType::MINORE_25K_COLOR.'">&#60; 25.000 €</span>';
            },
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'attribute' => 'anno_riferimento',
            'label' => 'Anno rif.',
            'value' => function ($model) {
                return $model->anno_riferimento ?? 'N/D';
            },
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'attribute' => 'valido',
            'label' => 'Stato',
            'value' => function ($model) {
                $tooltipText = $model->valido_fino_a ? \Carbon\Carbon::parse($model->valido_fino_a)->format('d/m/Y') : 'N/D';
                return !$model->verificato ? '<span class="badge bg-warning" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Da validare">DA VALIDARE</span>'  :
                    ($model->valido ?
                    '<span class="badge bg-success" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Valido fino al: ' . $tooltipText . '">ATTIVO</span>' :
                    '<span class="badge bg-danger" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Valido fino al: ' . $tooltipText . '">SCADUTO</span>');
            },
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{delete}',
            'buttons' => [
                'delete' => function ($url, $model) {
                    return Html::a('<i class="bi bi-trash"></i>', '#', [
                        'class' => 'btn btn-sm btn-danger',
                        'onclick' => "confirmDeleteIsee({$model->id}); return false;",
                        'title' => 'Elimina Isee',
                    ]);
                },
            ],
        ],
    ],
    'emptyText' => 'Nessun altro dato presente',

]);
\yii\widgets\Pjax::end();
?>

<div class="modal fade text-left" id="nuovo-isee" tabindex="-1" aria-labelledby="myModalLabel140" style="display: none;"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title white" id="myModalLabel140">
                    Nuovo Isee
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
                <p style="margin-bottom:10px; font-weight: bold">Inserire i dati:</p>
                <?= Html::beginForm(['/isee/aggiungi-da-istanza'], 'post', ['id' => 'aggiungi-isee', 'class' => 'form-horizontal']) ?>
                <?php
                // show hidden field "id_recupero" of type text
                echo Html::hiddenInput('id_istanza', $model->id, ['class' => 'form-control', 'id' => 'id_istanza']);
                // html multi select box for "maggiore" or "minore"
                echo Html::beginTag('div', ['class' => 'form-group']);
                echo Html::label('Isee', 'tipologia', ['class' => 'control-label']);
                echo Html::dropDownList('tipologia', null, ['minore' => "<MINORE 25.000",'maggiore'=> '>MAGGIORE 25.000'], ['class' => 'form-control', 'id' => 'id_isee', 'prompt' => 'Seleziona un valore..']);
                echo Html::endTag('div');
                // data_riferimento select box last 4 years, default current year
                echo Html::beginTag('div', ['class' => 'form-group']);
                echo Html::label('Anno di riferimento', 'anno_riferimento', ['class' => 'control-label']);
                echo Html::dropDownList('anno_riferimento', date('Y'), array_combine(range(date('Y') - 4, date('Y')), range(date('Y') - 4, date('Y'))), ['class' => 'form-control', 'id' => 'anno_riferimento']);
                echo Html::endTag('div');
                // data_presentazione today, type date
                echo Html::beginTag('div', ['class' => 'form-group']);
                echo Html::label('Data presentazione', 'data_presentazione', ['class' => 'control-label']);
                echo Html::input('date', 'data_presentazione', date('Y-m-d'), ['class' => 'form-control', 'id' => 'data_presentazione']);
                echo Html::endTag('div');
                // verificato checkbox default checked
                echo Html::beginTag('div', ['class' => 'form-check']);
                echo Html::checkbox('verificato', true, ['class' => 'form-check-input', 'id' => 'verificato']);
                echo Html::label('Verificato', 'verificato', ['class' => 'form-check-label']);
                echo Html::endTag('div');
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x d-block d-sm-none"></i>
                    <span class="d-none d-sm-block">Annulla</span>
                </button>

                <button type="submit" class="btn btn-warning ms-1">
                    <i class="bx bx-check d-block d-sm-none"></i>
                    <span class="d-none d-sm-block">Aggiungi isee</span>
                </button>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDeleteIsee(id) {
        if (confirm('Sei sicuro di voler eliminare questo isee?')) {
            $.pjax({
                url: '/isee/delete?id=' + id,
                container: '#lista-isee',
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
</script>
