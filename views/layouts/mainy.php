<?php


use app\assets\MainAsset;
use kartik\icons\Icon;
use richardfan\widget\JSRegister;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var string $content
 */

$themeMazer = MainAsset::register($this);
Icon::map($this);
?>

<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css"/>
        <link rel="stylesheet" href="/style.css"/>
    </head>

    <body>
    <?php $this->beginBody() ?>
    <div id="app">
        <?= $this->render('/layouts/mainy_sidebar') ?>

        <div id="main" class='layout-navbar navbar-fixed'>
            <?= $this->render('/layouts/mainy_header') ?>

            <div id="main-content">
                <?= $this->render('/layouts/mainy_content', compact('content')) ?>

                <?= $this->render('/layouts/mainy_footer') ?>
            </div>
        </div>
    </div>

    <?php JSRegister::begin([
        'key' => 'globalManage',
        'position' => \yii\web\View::POS_READY
    ]); ?>
    <script>
        $(function () {
            $('[data-bs-toggle="tooltip"]').tooltip()
        })
    </script>

    <?php JSRegister::end(); ?>
    <?php $this->endBody() ?>
    <?php
    $this->registerJsFile("{$themeMazer->baseUrl}/assets/original_assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js", ['depends' => [\yii\web\JqueryAsset::class]]); ?>
    </body>

    </html>
<?php $this->endPage() ?>