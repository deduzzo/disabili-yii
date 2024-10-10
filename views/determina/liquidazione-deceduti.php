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
            return (count($tornatiIndietro) === 0) ? "NO" :  "SI";
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
            return ['value' => $model->id];
        },
    ]
];

?>

<?= ExportWidget::widget([
    //'models' => $dataProvider->getModels(),
    'dataProvider' => $dataProvider,
    'columns' => array_merge($cols,    [[
        'label' => 'Importo a conguaglio',
        'value' => function ($model) {
            $problemiLiquidazioneDecesso = $model->getProblemiLiquidazioneDecesso();
            $giorniResiduo = $model->getGiorniResiduoDecesso();
            if ($problemiLiquidazioneDecesso)
                return "ALERT: ". $problemiLiquidazioneDecesso;
            else if ($giorniResiduo === null)
                return "-";
            else
                return Yii::$app->formatter->asCurrency($model->getGiorniResiduoDecesso() * ((($model->getLastIseeType() === IseeType::MAGGIORE_25K) ? ImportoBase::MAGGIORE_25K_V1 : ImportoBase::MINORE_25K_V1) / 30));
        }
    ]])
]) ?>

<?= Html::beginForm(['determina/liquidazione-deceduti'], 'post'); ?>
    <div class="card">
        <div class="card-header">
            <div class="card-toolbar">
                <!--begin::Button-->
                <?= Html::submitButton('Liquidazione Deceduti', ['class' => 'btn btn-primary me-3', 'disabled' => true]) ?>
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
                    'columns' => array_merge($checkboxColumn,$cols,    [[
                        'label' => 'Importo a conguaglio',
                        'value' => function ($model) {
                            $problemiLiquidazioneDecesso = $model->getProblemiLiquidazioneDecesso();
                            $giorniResiduo = $model->getGiorniResiduoDecesso();
                            if ($problemiLiquidazioneDecesso)
                                return "ALERT: ". $problemiLiquidazioneDecesso;
                            else if ($giorniResiduo === null)
                                return "-";
                            else
                                return Yii::$app->formatter->asCurrency($model->getGiorniResiduoDecesso() * ((($model->getLastIseeType() === IseeType::MAGGIORE_25K) ? ImportoBase::MAGGIORE_25K_V1 : ImportoBase::MINORE_25K_V1) / 30));
                        }
                    ]])
                ]); ?>
            </div>
        </div>
    </div>
<?= Html::endForm() ?>