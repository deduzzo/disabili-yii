<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Ricovero $model */

$this->title = 'Create Ricovero';
$this->params['breadcrumbs'][] = ['label' => 'Ricoveros', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ricovero-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
