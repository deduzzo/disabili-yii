<?php

use app\models\Decreto;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\date\DatePicker;
/** @var yii\web\View $this */
/** @var app\models\DecretoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Decreti';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="decreto-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Aggiungi nuovo', '#', [
            'class' => 'btn btn-success',
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#decreto-modal',
            'data-mode' => 'create',
        ]) ?>
    </p>

    <?php Pjax::begin(['id' => 'decreti-grid']); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'descrizione_atto',
            [
                'attribute' => 'data',
                'format' => 'date',
                'value' => function ($model) {
                    return $model->data ? date('Y-m-d', strtotime(str_replace('/', '-', $model->data))) : null;
                }
            ],
            [
                'attribute' => 'dal',
                'format' => 'date',
                'value' => function ($model) {
                    return $model->dal ? date('Y-m-d', strtotime(str_replace('/', '-', $model->dal))) : null;
                }
            ],
            [
                'attribute' => 'al',
                'format' => 'date',
                'value' => function ($model) {
                    return $model->al ? date('Y-m-d', strtotime(str_replace('/', '-', $model->al))) : null;
                }
            ],
            'importo',
            //'inclusi_minorenni',
            //'inclusi_maggiorenni',
            //'note:ntext',
            [
                'class' => ActionColumn::className(),
                'template' => '{update} {delete}',
                'buttons' => [
                    'update' => function ($url, $model, $key) {
                        return Html::a('<i class="bi bi-pencil"></i>', '#', [
                            'class' => 'btn btn-sm btn-primary',
                            'data-bs-toggle' => 'modal',
                            'data-bs-target' => '#decreto-modal',
                            'data-mode' => 'update',
                            'data-id' => $model->id,
                            'title' => 'Modifica Decreto',
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<i class="bi bi-trash"></i>', '#', [
                            'class' => 'btn btn-sm btn-danger',
                            'onclick' => "confirmDeleteDecreto({$model->id}); return false;",
                            'title' => 'Elimina Decreto',
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

<!-- Modal for Create/Update -->
<div class="modal fade" id="decreto-modal" tabindex="-1" aria-labelledby="decretoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title white" id="decretoModalLabel">Decreto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="decreto-modal-content">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Modal for Delete Confirmation -->
<div class="modal fade" id="delete-decreto-modal" tabindex="-1" aria-labelledby="deleteDecretoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title white" id="deleteDecretoModalLabel">Conferma eliminazione</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Sei sicuro di voler eliminare questo decreto?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-btn">Elimina</button>
            </div>
        </div>
    </div>
</div>

<?php
$createUrl = Url::to(['decreto/create']);
$updateUrl = Url::to(['decreto/update']);
$deleteUrl = Url::to(['decreto/delete']);

$this->registerJs(<<<JS
    let decretoIdToDelete = null;

    $(document).ready(function() {
        // Handle modal open for create/update
        $('#decreto-modal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const mode = button.data('mode');
            const modal = $(this);

            if (mode === 'create') {
                modal.find('.modal-title').text('Nuovo Decreto');
                $.get('$createUrl', function(data) {
                    $('#decreto-modal-content').html(data);
                });
            } else if (mode === 'update') {
                const id = button.data('id');
                modal.find('.modal-title').text('Modifica Decreto');
                $.get('$updateUrl' + '?id=' + id, function(data) {
                    $('#decreto-modal-content').html(data);
                });
            }
        });

        // Clean up modal content when modal is hidden
        $('#decreto-modal').on('hidden.bs.modal', function () {
            $('#decreto-modal-content').html('');
        });

        // Clean up delete modal when hidden
        $('#delete-decreto-modal').on('hidden.bs.modal', function () {
            decretoIdToDelete = null;
        });
    });

    function confirmDeleteDecreto(id) {
        decretoIdToDelete = id;
        $('#delete-decreto-modal').modal('show');
    }

    $('#confirm-delete-btn').click(function() {
        if (decretoIdToDelete) {
            $.post('$deleteUrl' + '?id=' + decretoIdToDelete, function() {
                $('#delete-decreto-modal').modal('hide');
                $.pjax.reload({container: '#decreti-grid'});
            });
        }
    });
JS, \yii\web\View::POS_READY);

// Make confirmDeleteDecreto function globally available
$this->registerJs("window.confirmDeleteDecreto = function(id) {
    decretoIdToDelete = id;
    $('#delete-decreto-modal').modal('show');
};", \yii\web\View::POS_HEAD);
?>
