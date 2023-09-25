<?php

namespace app\controllers;

use app\models\Distretto;
use app\models\enums\IseeType;
use app\models\Isee;
use app\models\Istanza;
use app\models\Movimento;
use app\models\SimulazioneDeterminaSearch;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii2tech\spreadsheet\Spreadsheet;

class DeterminaController extends \yii\web\Controller
{
    public function actionIndex($export = false)
    {
        // unlimited memory_limit
        ini_set('memory_limit', '-1');
        $searchModel = new SimulazioneDeterminaSearch();
        $distretti = isset($this->request->get()['distrettiPost']) ? $this->request->get()['distrettiPost'] : Distretto::getAllIds();
        $distretti = Distretto::find()->where(['id' => $distretti])->all();
        $soloProblematici = isset($this->request->get()['soloProblematici']) ? $this->request->get()['soloProblematici'] : 'off';
        $soloVariazioni = isset($this->request->get()['soloVariazioni']) ? $this->request->get()['soloVariazioni'] : 'off';
        $soloRecuperi = isset($this->request->get()['soloRecuperi']) ? $this->request->get()['soloRecuperi'] : 'off';
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
            if (!$differenza['alert'] && in_array(strval($istanza->id), $allIdPagatiMeseScorso)) {
                if ($soloProblematici === "off" || ($soloProblematici == "on" && $differenza['op'] !== "")) {
                    $istVal = [
                        'id' => $istanza->id,
                        'cf' => $istanza->anagraficaDisabile->codice_fiscale,
                        'cognome' => $istanza->anagraficaDisabile->cognome,
                        'nome' => $istanza->anagraficaDisabile->nome,
                        'distretto' => $istanza->distretto->nome,
                        'isee' => $istanza->getLastIseeType(),
                        'eta' => $istanza->anagraficaDisabile->getEta(),
                        'gruppo' => $istanza->gruppo->descrizione_gruppo,
                        'importoPrecedente' => $differenza['importoPrecedente'],
                        'importo' => $istanza->getProssimoImporto(),
                        'opArray' => $differenza,
                        'operazione' => $soloRecuperi === "off" ? $differenza['op'] : $istanza->getStatoRecupero(),
                    ];
                    if ($differenza['alert'] === true)
                        $alert[$istanza->distretto->id] = $istVal;
                    else {
                        $importiTotali[$istanza->distretto->id][$istanza->getLastIseeType()] += $istanza->getProssimoImporto();
                        $numeriTotali[$istanza->distretto->id][$istanza->getLastIseeType()] += 1;
                        if ($differenza['recupero'] === true)
                            $recuperiPerDistretto[$istanza->distretto->id][] = $istVal;
                        if ($differenza['op'] !== "")
                            $differenzePerDistretto[$istanza->distretto->id][] = $istVal;
                    }
                    $istanzeArray[] = $istVal;
                }
            }
            $pagamentiPrecedentiPerDistretti[$istanza->distretto->id] = array_diff($pagamentiPrecedentiPerDistretti[$istanza->distretto->id], [$istanza->id]);
            $pagamentiAttualiPerDistretti[$istanza->distretto->id][] = $istanza->id;
            $allIdPagatiMeseScorso = array_diff($allIdPagatiMeseScorso, [$istanza->id]);
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
                'distretto' => $istanza->distretto->nome,
                'isee' => $istanza->getLastIseeType(),
                'eta' => $istanza->anagraficaDisabile->getEta(),
                'gruppo' => $istanza->gruppo->descrizione_gruppo,
                'importoPrecedente' => $differenza['importoPrecedente'],
                'importo' => $istanza->getProssimoImporto(),
                'opArray' => $differenza,
                'operazione' => $soloRecuperi === "off" ? $differenza['op'] : $istanza->getStatoRecupero(),
            ];
            if ($differenza['alert'] === true)
                $alert[$istanza->distretto->id][] = $istVal;
            else {
                $importiTotali[$istanza->distretto->id][$istanza->getLastIseeType()] += $istanza->getProssimoImporto();
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
            $alertGlobal = array_merge($alertGlobal, $alert[$disPag->id]);
            $recuperiTotali = array_merge($recuperiTotali, $recuperiPerDistretto[$disPag->id]);
            $differenzeTotali = array_merge($differenzeTotali, $differenzePerDistretto[$disPag->id]);
        }
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,
            $soloRecuperi === "on" ? $recuperiTotali :
                ($soloVariazioni === "on" ? $differenzeTotali :
                    ($soloProblematici === "on" ? $alertGlobal : $istanzeArray)));
        if (!$export) {
            return $this->render('simulazione', [
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
        else {
            $exporter = new Spreadsheet([
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => $istanzeArray
                ]),
                'columns' => ['id','cf','cognome'],
                //'headerColumnUnions' => $initArray['headerColumnUnions']
            ]);
            $exporter->render();
            $exporter->send('out.xlsx');
        }
    }

}
