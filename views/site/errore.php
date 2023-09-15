<?php

/**
 * @var \yii\web\View $this
 * @var \yii\base\Exception $exception
 */

use app\assets\MainAsset;
use yii\bootstrap5\Html;

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;
$themeMazer = MainAsset::register($this);
?>


<div class="error-page container">
    <div class="col-md-8 col-12 offset-md-2">
        <div class="text-center">
            <img
                    class="img-error"
                    src="<?= "{$themeMazer->baseUrl}/assets/original_assets/compiled/svg/error-".$exception->statusCode.".svg" ?>"
                    alt="Not Found"
            />
            <h1 class="error-title">Errore</h1>
            <p class="fs-5 text-gray-600">
                <?= $exception->getMessage() ?>
            </p>
            <a href="/" class="btn btn-lg btn-outline-primary mt-3">Torna alla home</a>
        </div>
    </div>
</div>