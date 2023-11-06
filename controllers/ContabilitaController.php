<?php

namespace app\controllers;

use app\helpers\GdriveHelper;
use app\models\Decreto;
use app\models\DecretoSearch;
use app\models\Movimento;
use Carbon\Carbon;
use yii\db\Query;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * DecretoController implements the CRUD actions for Decreto model.
 */
class ContabilitaController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    public function actionProssimi()
    {
        $spid = "1ofNJ8KOG-mCMdnS5mum0V_mBmZ5alvKB62FvZKxzB3A";
        $gdrive = new GdriveHelper();
        $out = $gdrive->getSpreeadsheetData($spid);
        return $this->render('prossimi', [
            'result' => $out
        ]);
    }

    /**
     * Lists all Decreto models.
     *
     * @return string
     */
    public function actionAnno()
    {
        $anno = $_GET['anno'] ?? date('Y');
        $importi = ["spesa" => [], "incasso" => [], 'colspan' => []];
        foreach (range(0, 11) as $mese) {
            // spesa
            $inizioMese = Carbon::createfromformat('Y-m-d', $anno . '-' . ($mese +1). '-01');
            $fineMese = (clone $inizioMese)->endOfMonth();
            $dataUltimoPagamento = Movimento::getDataUltimoPagamento();
            if (Carbon::now()->isAfter($dataUltimoPagamento)) {
                //$spesa = Movimento::find()->where(['periodo_da' => $inizioMese->format('Y-m-d'), 'periodo_a' => $fineMese->format('Y-m-d'),'is_movimento_bancario' => true, 'tornato_indietro' => false])->sum('importo');
                $spesa = (new Query())
                    ->select('SUM(importo)')
                    ->from('movimento')
                    ->where(
                        ['and',
                            ['>=', 'periodo_da', $inizioMese->format('Y-m-d')],
                            ['<=', 'periodo_a', $fineMese->format('Y-m-d')]])->andWhere([
                        'is_movimento_bancario' => true,
                        'tornato_indietro' => false
                    ]);

                $spesa = $spesa->scalar();
                $importi["spesa"][$mese] = floatval($spesa);
                // fondi
                if (!isset($importi['colspan'][$mese -1]) || $importi['colspan'][$mese -1] === 1) {
                    $fondi = (new Query())
                        ->select('descrizione_atto,data,importo,dal,al')
                        ->from('decreto')
                        ->where(['<=', 'dal', $inizioMese->format('Y-m-d')])
                            ->andWhere(['>=', 'al', $fineMese->format('Y-m-d')])
                    ->orderBy('data ASC')->all();
                    if ($fondi) {
                        foreach ($fondi as $fondo) {
                            $importi["incasso"][$mese] = floatval($fondo['importo']);
                        }
                        $numMontsFromDalAndAl = Carbon::createfromformat('Y-m-d', $fondi[0]['dal'])->diffInMonths(Carbon::createfromformat('Y-m-d', $fondi[0]['al']), false);
                        $importi['colspan'][$mese] = $numMontsFromDalAndAl + 1;
                    } else
                        $importi['colspan'][$mese] = 1;
                } else
                    $importi['colspan'][$mese] = $importi['colspan'][$mese -1] - 1;
            }
        }
        return $this->render('peranno', [
            'anno' => $anno,
            'importi' => $importi
        ]);
    }
}
