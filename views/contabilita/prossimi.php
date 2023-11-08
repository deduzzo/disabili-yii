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
/** @var array $nomiGruppi */
/** @var string $result */


$this->title = 'Prossimi disabili (simulazione)';
$this->params['breadcrumbs'][] = $this->title;
$formatter = \Yii::$app->formatter;
?>
    <div class="card">
    <div class="card-header">
        <div class="card-toolbar">
            <!-- list of all new group names -->
            <?= Html::beginForm(['contabilita/prossimi'], 'get', ['class' => 'form-inline']) ?>
            <div class="row">
                <div class="col-md-8">
                    <label for="nomeGruppo" class="form-label">Seleziona Gruppo</label>
                    <select class="form-select" id="nomeGruppo" name="nomeGruppo">
                        <option selected>Scegli...</option>
                        <?php foreach ($nomiGruppi as $groupName): ?>
                            <option value="<?= $groupName ?>" <?= isset($_GET['nomeGruppo']) && $_GET['nomeGruppo'] === $groupName ? "selected" : "" ?>><?= $groupName ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <!-- button submit -->
                    <?= Html::submitButton('Verifica', ['class' => 'btn btn-primary', 'style' => 'margin-top: 30px', 'name' => "submit"]) ?>
                </div>
                <?= Html::endForm() ?>
                <div class="col-md-3" style="margin-top: 30px">
                    <?php if ($result !== null): ?>
                        <?= ExportWidget::widget([
                            'models' => $result['cfs'],
                            'columns' => ['distretto', 'cf'],
                        ]) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if ($result !== null): ?>
                <?= $result['out'] ?>
                <?php foreach ($result['errors'] as $error) {
                    echo $error . "<br />";
                } ?>
            <?php endif; ?>
        </div>
    </div>
    <?php JSRegister::begin([
        'key' => 'manage',
        'position' => \yii\web\View::POS_READY
    ]); ?>
    <script>

    </script>
<?php JSRegister::end(); ?>