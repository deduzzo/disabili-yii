<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Conto $model */

$this->title = 'Create Conto';
$this->params['breadcrumbs'][] = ['label' => 'Contos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="conto-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
