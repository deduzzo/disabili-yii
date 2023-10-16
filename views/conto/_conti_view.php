<?php


use app\models\ContoSearch;
use app\models\Istanza;
use app\models\MovimentoSearch;
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
                return ($model->attivo && $model->validato )?
                    '<span class="badge bg-success">ATTIVO</span>' : (!$model->validato ? '<span class="badge bg-warning">DA VALIDARE</span>' : '<span class="badge bg-danger">DISATTIVO</span>');
            },
            'format' => 'raw',
        ],
        'note',
    ],
    'emptyText' => 'Nessun movimento presente',

]);
?>

<div class="modal fade text-left" id="nuovo-conto" tabindex="-1" aria-labelledby="myModalLabel140" style="display: none;"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
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

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x d-block d-sm-none"></i>
                    <span class="d-none d-sm-block">Annulla</span>
                </button>

                <button type="submit" class="btn btn-warning ms-1">
                    <i class="bx bx-check d-block d-sm-none"></i>
                    <span class="d-none d-sm-block">Aggiungi conto</span>
                </button>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>
</div>