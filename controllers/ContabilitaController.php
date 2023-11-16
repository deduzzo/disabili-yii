<?php

namespace app\controllers;

use app\helpers\GdriveHelper;
use app\helpers\Utils;
use app\models\Decreto;
use app\models\DecretoSearch;
use app\models\Determina;
use app\models\Movimento;
use Carbon\Carbon;
use Yii;
use yii\db\Query;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use function PHPUnit\Framework\stringStartsWith;

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
        $spid = Yii::$app->params['gdrive_prossimiGruppiFolderId'];
        $gdrive = new GdriveHelper();
        $out = null;
        $allNewGroupNames = $gdrive->getAllFilesInFolder($spid);
        // $allNewGroupMaps an array with key the name of file and value the id of file
        $allNewGroupMaps = [];
        foreach ($allNewGroupNames as $groupName) {
            $allNewGroupMaps[$groupName->getName()] = $groupName->getId();
        }
        if (isset($_GET['nomeGruppo'])) {
            $out = $gdrive->verificaDatiNuoviDisabiliFiles($allNewGroupMaps[$_GET['nomeGruppo']]);
        }
        return $this->render('prossimi', [
            'result' => $out,
            'nomiGruppi' => array_keys($allNewGroupMaps)
        ]);
    }

    public function actionAggiungiNuovoGruppo()
    {
        if ($this->request->isPost) {
            if (isset($_POST['nomeGruppo']) && isset($_POST['nomeGruppoRaw']) && isset($_POST['cancellaDatiSePresenti'])) {
                $spid = Yii::$app->params['gdrive_prossimiGruppiFolderId'];
                $gdrive = new GdriveHelper();
                $allNewGroupNames = $gdrive->getAllFilesInFolder($spid);
                $allNewGroupMaps = [];
                foreach ($allNewGroupNames as $groupName)
                    $allNewGroupMaps[$groupName->getName()] = $groupName->getId();
                $gdrive->importaNuoviDisabili($allNewGroupMaps[$_POST['nomeGruppoRaw']],$_POST['nomeGruppo'], boolval($_POST['cancellaDatiSePresenti']));
            }
        }
    }

    public function actionVerificaIban()
    {
        $out = "";
        if (isset($_GET['iban']) && $this->request->isGet) {
            $iban = $_GET['iban'];
            if (Utils::verificaIban($iban))
                $out = '<span class="badge bg-success">IBAN: ' . $iban . ' VALIDO</span>';
            else
                $out = '<span class="badge bg-danger">IBAN: ' . $iban . ' NON VALIDO</span>';
        }
        return $this->render('verifica-iban', [
            'result' => $out,
        ]);
    }

    /**
     * Lists all Decreto models.
     *
     * USCITE
     * select sum(res) FROM ( SELECT sum(importo) as res from movimento where movimento.is_movimento_bancario = true AND movimento.tornato_indietro = false UNION ALL SELECT sum(importo) as res from determina where determina.storico = true ) a;
     * ENTRATE
     * select sum(importo) from decreto;
     *
     * @return string
     */
    public
    function actionAnno()
    {
        $anno = $_GET['anno'] ?? date('Y');
        $importi = ["spesa" => [], "incasso" => [], 'colspan' => [], 'determineStoriche' => []];
        foreach (range(0, 11) as $mese) {
            // spesa
            $inizioMese = Carbon::createfromformat('Y-m-d', $anno . '-' . ($mese + 1) . '-01');
            $fineMese = (clone $inizioMese)->endOfMonth();
            $dataUltimoPagamento = Movimento::getDataUltimoPagamento();
            if (Carbon::now()->isAfter($dataUltimoPagamento)) {
                //$spesa = Movimento::find()->where(['periodo_da' => $inizioMese->format('Y-m-d'), 'periodo_a' => $fineMese->format('Y-m-d'),'is_movimento_bancario' => true, 'tornato_indietro' => false])->sum('importo');
                $spesa = (new Query())
                    ->select('SUM(importo)')
                    ->from('movimento')
                    ->where(
                        ['and',
                            ['>=', 'data', $inizioMese->format('Y-m-d')],
                            ['<=', 'data', $fineMese->format('Y-m-d')]
                        ]
                    )
                    ->andWhere([
                        'is_movimento_bancario' => true,
                        'tornato_indietro' => false
                    ]);

                $spesa = $spesa->scalar();
                $importi["spesa"][$mese] = floatval($spesa);
                // fondi
                if (!isset($importi['colspan'][$mese - 1]) || $importi['colspan'][$mese - 1] === 1) {
                    $fondi = (new Query())
                        ->select('descrizione_atto,data,importo,dal,al')
                        ->from('decreto')
                        ->where(['<=', 'dal', $inizioMese->format('Y-m-d')])
                        ->andWhere(['>=', 'al', $fineMese->format('Y-m-d')])
                        ->orderBy('data ASC')->all();
                    if ($fondi) {
                        $numMontsFromDalAndAl = Carbon::createfromformat('Y-m-d', $fondi[0]['dal'])->diffInMonths(Carbon::createfromformat('Y-m-d', $fondi[0]['al']), false);
                        $importi["incasso"][$mese] = 0;
                        foreach ($fondi as $fondo) {
                            $importi["incasso"][$mese] += floatval($fondo['importo']);
                        }

                        $importi['colspan'][$mese] = $numMontsFromDalAndAl + 1;
                    } else
                        $importi['colspan'][$mese] = 1;
                } else
                    $importi['colspan'][$mese] = $importi['colspan'][$mese - 1] - 1;
            }
        }
        $inizioAnno = Carbon::createfromformat('Y-m-d', $anno . '-01-01');
        $fineAnno = (clone $inizioAnno)->endOfYear();
        $importi['determineStoriche'] = Determina::find()->where(['storico' => true])->andWhere(['>=', 'data', $inizioAnno->format('Y-m-d')])->andWhere(['<=', 'data', $fineAnno->format('Y-m-d')])->all();
        return $this->render('peranno', [
            'anno' => $anno,
            'importi' => $importi
        ]);
    }
}
