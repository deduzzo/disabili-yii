<?php

use app\models\AnagraficaAltricampi;
use app\models\enums\IseeType;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Istanza $model */


\yii\widgets\Pjax::begin(['id' => 'lista-isee']);

echo GridView::widget([
    'dataProvider' => new ArrayDataProvider([
        'allModels' => $model->isees,
        'pagination' => false,
    ]),
    'options' => ['class' => 'grid-view small'],
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
            'attribute' => 'valido',
            'label' => 'Stato',
            'value' => function ($model) {
                return $model->valido ?
                    '<span class="badge bg-success">ATTIVO</span>' : '<span class="badge bg-danger">SCADUTO</span>';
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
                // data_presentazione today
                echo Html::beginTag('div', ['class' => 'form-group']);
                echo Html::label('Data presentazione', 'data_presentazione', ['class' => 'control-label']);
                echo Html::textInput('data_presentazione', date('d/m/Y'), ['class' => 'form-control', 'id' => 'data_presentazione']);
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
