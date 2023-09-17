<?php

use app\models\AnagraficaAltricampi;
use app\models\enums\DatiTipologia;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Anagrafica $model */
/** @var string $categoria */

$anagraficaAltricampi = AnagraficaAltricampi::find()->innerJoin('tipologia_dati_azioni t','t.id = anagrafica_altricampi.id_tipologia')
    ->where(['id_anagrafica' => $model->id ?? null,'t.categoria' => $categoria,'t.tipologia' => DatiTipologia::DATO])->all();
echo GridView::widget([
    'dataProvider' => new \yii\data\ArrayDataProvider([
        'allModels' => $anagraficaAltricampi,
        'pagination' => false,
    ]),
    'options' => ['class' => 'grid-view small'],
    'columns' => [
        'tipologia.descrizione',
        'valore',
    ],
    'emptyText' => 'Nessun altro dato presente',
    'showHeader' => false,
    'showFooter'=> false,
    'summary' => '',
]);