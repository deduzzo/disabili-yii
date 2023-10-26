<?php

namespace app\controllers;

use app\models\Determina;
use app\models\Distretto;
use app\models\enums\IseeType;
use app\models\Isee;
use app\models\Istanza;
use app\models\Movimento;
use app\models\SimulazioneDeterminaSearch;
use Carbon\Carbon;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii2tech\spreadsheet\Spreadsheet;

class DeterminaController extends \yii\web\Controller
{
    public function actionIndex($export = false, $idDeterminaFinalizzare = null)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $searchModel = new SimulazioneDeterminaSearch();
        $getVars = $idDeterminaFinalizzare === null ? $this->request->get() : [];
        $distretti = $getVars['distrettiPost'] ?? Distretto::getAllIds();
        $distretti = Distretto::find()->where(['id' => $distretti])->all();
        $soloProblematici = isset($getVars['soloProblematici']) ? $getVars['soloProblematici'] : 'off';
        $soloVariazioni = isset($getVars['soloVariazioni']) ? $getVars['soloVariazioni'] : 'off';
        $soloRecuperi = isset($getVars['soloRecuperi']) ? $getVars['soloRecuperi'] : 'off';
        $allIstanzeAttive = (new Query())->select('id')->from('istanza')->where(['attivo' => true])->andWhere(['chiuso' => false]);
        //new rawquery
        $ultimaData = Movimento::getDataUltimoPagamento();
        $allPagamentiPrecedenti = (new Query())->select('c.id_istanza, i.id_distretto')->from('movimento m, conto c, istanza i')->where("m.id_conto = c.id")->andWhere('c.id_istanza = i.id')->andWhere('is_movimento_bancario = true')->andWhere(['data' => $ultimaData])->all();
        $allIdPagatiMeseScorso = $allPagamentiPrecedenti ? array_column($allPagamentiPrecedenti, 'id_istanza') : [];
        $pagamentiPrecedentiPerDistretti = [];
        $pagamentiAttualiPerDistretti = [];
        $importiTotali = [];
        $numeriTotali = [];
        $recuperiPerDistretto = [];
        $recuperiTotali = [];
        $differenzePerDistretto = [];
        $differenzeTotali = [];
        $alert = [];
        foreach (Distretto::find()->all() as $item) {
            $importiTotali[$item->id] = [IseeType::MAGGIORE_25K => 0, IseeType::MINORE_25K => 0];
            $numeriTotali[$item->id] = [IseeType::MAGGIORE_25K => 0, IseeType::MINORE_25K => 0];
            $recuperiPerDistretto[$item->id] = [];
            $alert[$item->id] = [];
        }
        foreach ($allPagamentiPrecedenti as $pagamento) {
            $pagamentiPrecedentiPerDistretti[$pagamento['id_distretto']][] = $pagamento['id_istanza'];
        }
        $allIstanzeAttive->andWhere(['id_distretto' => ArrayHelper::getColumn($distretti, 'id')]);
        $allIstanzeAttive = $allIstanzeAttive->all();
        $istanzeArray = [];
        // id, cf, cognome, nome distretto, isee, eta, gruppo, importo
        foreach ($allIstanzeAttive as $istanza) {
            /* @var $istanza Istanza */
            $istanza = Istanza::findOne($istanza['id']);
            $differenza = $istanza->getDifferenzaUltimoImportoArray();
            if (!$differenza['alert'] && $istanza->getProssimoImporto() > 0) {
                if ($soloProblematici === "off" || ($soloProblematici == "on" && $differenza['op'] !== "")) {
                    $istVal = [
                        'id' => $istanza->id,
                        'cf' => $istanza->anagraficaDisabile->codice_fiscale,
                        'cognome' => $istanza->anagraficaDisabile->cognome,
                        'nome' => $istanza->anagraficaDisabile->nome,
                        'dataNascita' => $istanza->anagraficaDisabile->data_nascita,
                        'distretto' => $istanza->distretto->nome,
                        'isee' => $istanza->getLastIseeType(),
                        'eta' => $istanza->anagraficaDisabile->getEta(),
                        'gruppo' => $istanza->gruppo->descrizione_gruppo_old . " [" . $istanza->gruppo->descrizione_gruppo . "]",
                        'importoPrecedente' => $differenza['importoPrecedente'],
                        'importo' => $istanza->getProssimoImporto(),
                        'opArray' => $differenza,
                        'operazione' => $soloRecuperi === "off" ? $differenza['op'] : $istanza->getStatoRecupero(),
                    ];
                    if ($differenza['alert'] === true)
                        $alert[$istanza->distretto->id] = $istVal;
                    else {
                        if ($idDeterminaFinalizzare !== null)
                            $istanza->finalizzaMensilita($idDeterminaFinalizzare);
                        if ($istanza->getProssimoImporto() >0)
                            $numeriTotali[$istanza->distretto->id][$istanza->getLastIseeType()] += 1;
                        $importiTotali[$istanza->distretto->id][$istanza->getLastIseeType()] += $istanza->getProssimoImporto();
                        if ($differenza['recupero'] === true)
                            $recuperiPerDistretto[$istanza->distretto->id][] = $istVal;
                        if ($differenza['op'] !== "")
                            $differenzePerDistretto[$istanza->distretto->id][] = $istVal;
                    }
                    $istanzeArray[] = $istVal;
                }
                $pagamentiPrecedentiPerDistretti[$istanza->distretto->id] = array_diff($pagamentiPrecedentiPerDistretti[$istanza->distretto->id], [$istanza->id]);
                $pagamentiAttualiPerDistretti[$istanza->distretto->id][] = $istanza->id;
                $allIdPagatiMeseScorso = array_diff($allIdPagatiMeseScorso, [$istanza->id]);
            }
        }
        $nonPagati = [];
        foreach ($distretti as $disPag) {
            $nonPagati = array_merge($nonPagati, $pagamentiPrecedentiPerDistretti[$disPag->id]);
        }
        foreach ($nonPagati as $idPagato) {
            $istanza = Istanza::findOne($idPagato);
            $differenza = $istanza->getDifferenzaUltimoImportoArray();
            $istVal = [
                'id' => $istanza->id,
                'cf' => $istanza->anagraficaDisabile->codice_fiscale,
                'cognome' => $istanza->anagraficaDisabile->cognome,
                'nome' => $istanza->anagraficaDisabile->nome,
                'dataNascita' => $istanza->anagraficaDisabile->data_nascita,
                'distretto' => $istanza->distretto->nome,
                'isee' => $istanza->getLastIseeType(),
                'eta' => $istanza->anagraficaDisabile->getEta(),
                'gruppo' => $istanza->gruppo->descrizione_gruppo_old . " [" . $istanza->gruppo->descrizione_gruppo . "]",
                'importoPrecedente' => $differenza['importoPrecedente'],
                'importo' => $istanza->getProssimoImporto(),
                'opArray' => $differenza,
                'operazione' => $soloRecuperi === "off" ? $differenza['op'] : $istanza->getStatoRecupero(),
            ];
            if ($differenza['alert'] === true)
                $alert[$istanza->distretto->id][] = $istVal;
            else {
                $importiTotali[$istanza->distretto->id][$istanza->getLastIseeType()] += $istanza->getProssimoImporto();
                if ($istanza->getProssimoImporto() >0)
                    $numeriTotali[$istanza->distretto->id][$istanza->getLastIseeType()] += 1;
                if ($differenza['recupero'] === true)
                    $recuperiPerDistretto[$istanza->distretto->id][] = $istVal;
                if ($differenza['op'] !== "")
                    $differenzePerDistretto[$istanza->distretto->id][] = $istVal;
            }
            $istanzeArray[] = $istVal;
        }
        $alertGlobal = [];
        foreach ($distretti as $disPag) {
            $alertGlobal = array_merge($alertGlobal, $alert[$disPag->id] ?? []);
            $recuperiTotali = array_merge($recuperiTotali, $recuperiPerDistretto[$disPag->id] ?? []);
            $differenzeTotali = array_merge($differenzeTotali, $differenzePerDistretto[$disPag->id] ?? []);
        }
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,
            $soloRecuperi === "on" ? $recuperiTotali :
                ($soloVariazioni === "on" ? $differenzeTotali :
                    ($soloProblematici === "on" ? $alertGlobal : $istanzeArray)));
        return $this->render('simulazione', [
            'istanzeArray' => $istanzeArray,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'allIdPagati' => $allIdPagatiMeseScorso,
            'soloProblematici' => $soloProblematici,
            'soloVariazioni' => $soloVariazioni,
            'soloRecuperi' => $soloRecuperi,
            'distretti' => $distretti,
            'stats' => [
                'importiTotali' => $importiTotali,
                'numeriTotali' => $numeriTotali,
                'alert' => $alert,
            ]
        ]);
    }

    public function actionFinalizza()
    {
        $vars = $this->request->post();
        if (isset($vars['numero_determina'])) {
            $determina = new Determina();
            $determina->numero = $vars['numero_determina'];
            $determina->data = $vars['data_determina'];
            $determina->descrizione = "Pagamento mensilità da " . $vars['data_inizio'] . " a " . $vars['data_fine'];
            $determina->save();
            $this->actionIndex(false, $determina->id);
        }
    }

    public function actionPagamenti()
    {
        $result = null;
        $vars = $this->request->get();
        $ultimoPagamento = Movimento::getDataUltimoPagamento();
        if (isset($vars['mese']) && isset($vars['anno']) && isset($vars['submit'])) {
            $result = "";
            //$ultimoPagamento = Movimento::getDataUltimoPagamento();
            $mesePagamento = Carbon::createFromFormat('Y-m-d',$vars['anno'].'-'.$vars['mese']."-01");
            $istanzePagate = (new Query())->select('i.id')->distinct()->from('istanza i, conto c, movimento m')->where('m.id_conto = c.id')->andWhere('c.id_istanza = i.id');
            // and where m.data is between start and and of mesepagamento
            $istanzePagate = $istanzePagate->andWhere(['>=', 'm.data', $mesePagamento->startOfMonth()->format('Y-m-d')])->andWhere(['<=', 'm.data', $mesePagamento->endOfMonth()->format('Y-m-d')])->all();
            foreach ($istanzePagate as $istanza) {
                $istanza = Istanza::findOne($istanza['id']);
                $tempResult = $istanza->verificaContabilitaMese(intval($vars['mese']), intval($vars['anno']));
                if ($tempResult !== 0.0) {
                    $result .= "<div><p style='display:inline-block;'>❌ Istanza #" . $istanza->id
                        . " nominativo: " . $istanza->anagraficaDisabile->cognome
                        . " " . $istanza->anagraficaDisabile->nome
                        . " distretto: " . $istanza->distretto->nome
                        . ": "
                        . '</p><span style="margin-left:20px" class="badge ' . ($tempResult > 0 ? 'bg-success' : 'bg-danger')
                        . '">' . ($tempResult > 0 ? ("+".$tempResult) : $tempResult)
                        . '</span></div>';
                }
            }
        }
        return $this->render('pagamenti', [
            "mese" => Carbon::createFromFormat('Y-m-d', $ultimoPagamento)->month,
            "anno" => Carbon::createFromFormat('Y-m-d', $ultimoPagamento)->year,
            "result" => $result !== null ? ($result === "" ? "🆗Tutto ok! ✔️" : $result) : "",
        ]);
    }

}
