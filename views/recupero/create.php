<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Recupero $model */

$this->title = 'Create Recupero';
$this->params['breadcrumbs'][] = ['label' => 'Recuperos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="recupero-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
