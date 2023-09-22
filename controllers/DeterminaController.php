<?php

namespace app\controllers;

use app\models\Distretto;
use app\models\enums\IseeType;
use app\models\Isee;
use app\models\Istanza;
use app\models\SimulazioneDeterminaSearch;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class DeterminaController extends \yii\web\Controller
{
    public function actionIndex()
    {
        // unlimited memory_limit
        ini_set('memory_limit', '-1');
        $searchModel = new SimulazioneDeterminaSearch();
        $distretti = isset($this->request->post()['distrettiPost']) ? $this->request->post()['distrettiPost'] : Distretto::getAllIds();
        $distretti = Distretto::find()->where(['id' => $distretti])->all();
        $soloProblematici = isset($this->request->post()['soloProblematici']) ? $this->request->post()['soloProblematici'] : 'off';
        $soloErrori = isset($this->request->post()['soloErrori']) ? $this->request->post()['soloErrori'] : 'off';
        $allIstanzeAttive = (new Query())->select('id')->from('istanza')->where(['attivo' => true])->andWhere(['chiuso' => false]);
        //new rawquery
        $ultimaData = (new Query())->from('movimento')->select('max(data)')->where('is_movimento_bancario = true')->scalar();
        $allPagamentiPrecedenti = (new Query())->select('c.id_istanza, i.id_distretto')->from('movimento m, conto c, istanza i')->where("m.id_conto = c.id")->andWhere('c.id_istanza = i.id')->andWhere('is_movimento_bancario = true')->andWhere(['data' => $ultimaData])->all();
        $allIdPagatiMeseScorso = $allPagamentiPrecedenti ? array_column($allPagamentiPrecedenti, 'id_istanza') : [];
        $pagamentiPrecedentiPerDistretti = [];
        $pagamentiAttualiPerDistretti = [];
        $importiTotali = [];
        $numeriTotali = [];
        $alert = [];
        foreach (Distretto::find()->all() as $item) {
            $importiTotali[$item->id] = [IseeType::MAGGIORE_25K => 0, IseeType::MINORE_25K => 0];
            $numeriTotali[$item->id] = [IseeType::MAGGIORE_25K => 0, IseeType::MINORE_25K => 0];
            $alert[$item->id] = [];
        }
        foreach (Distretto::find()->all() as $dist) {
            $statistiche[$dist->id] = 0;
        }
        foreach ($allPagamentiPrecedenti as $pagamento) {
            $pagamentiPrecedentiPerDistretti[$pagamento['id_distretto']][] = $pagamento['id_istanza'];
        }
        if (count($distretti) > 0)
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
                        'operazione' => $differenza['op'],
                    ];
                    if ($differenza['alert'])
                        $alert[$istanza->distretto->id] = $istVal;
                    else {
                        $importiTotali[$istanza->distretto->id][$istanza->getLastIseeType()] += $istanza->getProssimoImporto();
                        $numeriTotali[$istanza->distretto->id][$istanza->getLastIseeType()] += 1;
                    }
                    $istanzeArray[] = $istVal;
                }
            }
            $pagamentiPrecedentiPerDistretti[$istanza->distretto->id] = array_diff($pagamentiPrecedentiPerDistretti[$istanza->distretto->id], [$istanza->id]);
            $pagamentiAttualiPerDistretti[$istanza->distretto->id][] = $istanza->id;
            $allIdPagatiMeseScorso = array_diff($allIdPagatiMeseScorso, [$istanza->id]);
        }
        $nonPagati = [];
        foreach ($distretti as $disPag)
            $nonPagati = array_merge($nonPagati, $pagamentiPrecedentiPerDistretti[$disPag->id]);
        foreach ($nonPagati as $idPagato) {
            $istanza = Istanza::findOne($idPagato);
            $differenza = $istanza->getDifferenzaUltimoImportoArray();
            $istVal[] = [
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
                'operazione' => $differenza['op'],
            ];
            if ($differenza['alert'])
                $alert[$istanza->distretto->id][] = $istVal;
            else {
                $importiTotali[$istanza->distretto->id][$istanza->getLastIseeType()] += $istanza->getProssimoImporto();
                $numeriTotali[$istanza->distretto->id][$istanza->getLastIseeType()] += 1;
            }
            //$istanzeArray[] = $istVal;
        }
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $istanzeArray);

        return $this->render('simulazione', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'allIdPagati' => $allIdPagatiMeseScorso,
            'soloProblematici' => $soloProblematici,
            'soloErrori' => $soloErrori,
            'distretti' => $distretti,
            'stats' => [
                'importiTotali' => $importiTotali,
                'numeriTotali' => $numeriTotali,
                'alert' => $alert]
        ]);
    }

}
