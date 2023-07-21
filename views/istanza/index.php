<?php

use app\models\Istanza;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\IstanzaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Istanzas';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="istanza-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Istanza', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'data_inserimento',
            'riconosciuto',
            'classe_disabilita',
            'data_riconoscimento',
            //'patto_di_cura',
            //'data_firma_patto',
            //'attivo',
            //'data_decesso',
            //'liquidazione_decesso_completata',
            //'data_liquidazione_decesso',
            //'chiuso',
            //'data_chiusura',
            //'nota_chiusura:ntext',
            //'note:ntext',
            //'id_anagrafica_disabile',
            //'id_distretto',
            //'id_gruppo',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Istanza $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
