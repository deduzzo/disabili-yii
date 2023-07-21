<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Istanza $model */

$this->title = 'Create Istanza';
$this->params['breadcrumbs'][] = ['label' => 'Istanzas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="istanza-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
