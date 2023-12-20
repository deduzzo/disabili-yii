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
/** @var string $result */


$this->title = 'Verifica conti istanze attive';
$this->params['breadcrumbs'][] = $this->title;
$formatter = \Yii::$app->formatter;
?>
    <div class="card">
        <div class="card-header">
            <div class="card-toolbar">
                <!-- list of all new group names -->
                <?php
                $istanzeAttive = \app\models\Istanza::findAll(['attivo' => true]);
                $ok = true;
                foreach ($istanzeAttive as $istanza) {
                    $contoValido = $istanza->getContoValido();
                    if (!$contoValido && !$istanza->haRicoveriInCorso()) {
                        $ok = false;
                        echo "<div class='col-md-12'><b>#" . $istanza->id . " " . $istanza->getNominativoDisabile() . "</b> CON CONTO NON VALIDO</div>";
                    }
                }
                if ($ok)
                    echo "✔️Conti validi per tutte le istanze attive";
                ?>
            </div>
        </div>
    </div>
<?php JSRegister::begin([
    'key' => 'manage',
    'position' => \yii\web\View::POS_READY
]); ?>
    <script>

    </script>
<?php JSRegister::end(); ?>