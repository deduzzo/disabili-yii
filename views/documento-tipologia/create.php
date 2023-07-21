<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\DocumentoTipologia $model */

$this->title = 'Create Documento Tipologia';
$this->params['breadcrumbs'][] = ['label' => 'Documento Tipologias', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="documento-tipologia-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
