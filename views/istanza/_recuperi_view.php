<?php

use app\models\AnagraficaAltricampi;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Istanza $model */


\yii\widgets\Pjax::begin(['id' => 'lista-recuperi']);

echo GridView::widget([
    'dataProvider' => new ArrayDataProvider([
        'allModels' => $model->recuperos,
        'pagination' => false,
    ]),
    'options' => ['class' => 'grid-view small'],
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
                    ($model->chiuso ? '<span class="badge bg-secondary">CHIUSO</span>' : ('<span class="badge bg-info">Attivo</span>'.($model->rateizzato == 1 ? ('<br />mancano ' . $model->getRateMancanti() . ' rate') : ''))
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
                return ($model->rateizzato ? ((" <b>TOTALE: " . $model->num_rate . " rate</b><br />") . ($model->getUltimaRataSeDiversa() ? $model->num_rate - 1 : $model->num_rate) . ($model->importo_rata ? ' da ' . Yii::$app->formatter->asCurrency($model->importo_rata) .
                            ($model->getUltimaRataSeDiversa() ? ('<br />ultima: ' . Yii::$app->formatter->asCurrency($model->getUltimaRataSeDiversa())) : '')
                            : ' variabili')) : '<b>Unica Soluzione</b>') . '<br />' .
                    ($model->recuperoCollegato ? ('Collegato al recupero #' . $model->recuperoCollegato->id . '<br />') : '') .
                    ($model->getImportoSaldato() <> 0 ? 'Importo saldato: ' . Yii::$app->formatter->asCurrency($model->getImportoSaldato()) . '<br />' : '') . $model->note;
            },
            'format' => 'raw',
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{delete}{annulla}',
            'buttons' => [
                'delete' => function ($url, $model) {
                    return Html::a('<i class="bi bi-trash"></i>', '#', [
                        'class' => 'btn btn-sm btn-danger',
                        'onclick' => "confirmDeleteRecupero({$model->id}); return false;",
                        'title' => 'Elimina recupero',
                    ]);
                },
                'annulla' => function ($url, $model) {
                    if ($model->annullato == 0 && $model->chiuso == 0)
                        return Html::a('<i class="fas fa-undo"></i>', '#', [
                            'class' => 'btn btn-sm btn-warning',
                            'title' => 'Annulla recupero',
                            'data-bs-toggle' => 'modal',
                            'data-bs-target' => '#warning',
                            'onclick' => '$("#id_recupero").val(' . $model->id . ');',
                        ]);
                    else
                        return "";
                },
            ],
        ],
    ],
    'emptyText' => 'Nessun altro dato presente',

]);
\yii\widgets\Pjax::end();
?>

<div class="modal fade text-left" id="warning" tabindex="-1" aria-labelledby="myModalLabel140" style="display: none;"
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
</script>
