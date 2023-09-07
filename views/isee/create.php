<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Isee $model */

$this->title = 'Create Isee';
$this->params['breadcrumbs'][] = ['label' => 'Isees', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="isee-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
