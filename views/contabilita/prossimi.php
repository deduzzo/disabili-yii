<?php

use app\components\ExportWidget;
use app\models\Distretto;
use app\models\enums\IseeType;
use kartik\select2\Select2;
use richardfan\widget\JSRegister;
use yii\bootstrap5\Html;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $anno */
/** @var array $allNewGroupNames */
/** @var string $result */


$this->title = 'Prossimi disabili (simulazione)';
$this->params['breadcrumbs'][] = $this->title;
$formatter = \Yii::$app->formatter;
?>
    <div class="card">
    <div class="card-header">
        <div class="card-toolbar">
            <!-- list of all new group names -->
            <div class="row">
                <div class="col-md-8">
                    <label for="anno" class="form-label">Seleziona Nome Gruppo</label>
                    <select class="form-select" id="anno" name="anno">
                        <option selected>Scegli...</option>
                        <?php foreach ($allNewGroupNames as $groupName): ?>
                            <option value="<?= $groupName ?>"><?= $groupName ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <!-- button submit -->
                    <?= Html::submitButton('Verifica', ['class' => 'btn btn-primary', 'style' => 'margin-top: 30px', 'name' => "submit"]) ?>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?= $result ?>
        </div>
    </div>
    <?php JSRegister::begin([
        'key' => 'manage',
        'position' => \yii\web\View::POS_READY
    ]); ?>
    <script>

    </script>
<?php JSRegister::end(); ?>