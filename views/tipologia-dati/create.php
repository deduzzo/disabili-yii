<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\TipologiaDati $model */

$this->title = 'Create Tipologia Dati';
$this->params['breadcrumbs'][] = ['label' => 'Tipologia Datis', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tipologia-dati-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
