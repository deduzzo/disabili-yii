<?php

namespace app\controllers;

use app\models\Istanza;
use app\models\SimulazioneDeterminaSearch;
use Yii;
use yii\db\Query;

class DeterminaController extends \yii\web\Controller
{
    public function actionIndex($distretto = null, $soloProblematici = null)
    {
        // unlimited memory_limit
        ini_set('memory_limit', '-1');
        $searchModel = new SimulazioneDeterminaSearch();
        $allIstanze = (new Query())->select('id')->from('istanza')->where(['attivo' => true])->andWhere(['chiuso' => false]);
        //new rawquery
        $ultimaData = (new Query())->from('movimento')->select('max(data)')->where('is_movimento_bancario = true')->scalar();
        $allPagamenti = (new Query())->select('c.id_istanza, i.id_distretto')->from('movimento m, conto c, istanza i')->where("m.id_conto = c.id")->andWhere('c.id_istanza = i.id')->andWhere('is_movimento_bancario = true')->andWhere(['data' => $ultimaData])->all();
        $allIdPagati = $allPagamenti ? array_column($allPagamenti, 'id_istanza') : [];
        $perDistrettiMapPagamentoPrecedente = [];
        $perDistrettiMapAttuale = [];
        foreach ($allPagamenti as $pagamento) {
            $perDistrettiMapPagamentoPrecedente[$pagamento['id_distretto']][] = $pagamento['id_istanza'];
        }
        if ($distretto)
            $allIstanze->andWhere(['id_distretto' => $distretto]);
        $allIstanze = $allIstanze->all();
        $istanzeArray = [];
        // id, cf, cognome, nome distretto, isee, eta, gruppo, importo
        foreach ($allIstanze as $istanza) {
            /* @var $istanza Istanza */
            $istanza = Istanza::findOne($istanza['id']);
            $differenza = $istanza->getDifferenzaUltimoImportoArray();
            if (!$differenza['alert'] && in_array(strval($istanza->id), $allIdPagati)) {
                if ($soloProblematici === null || ($soloProblematici == "on" && $differenza['op'] !== "")) {
                    $istanzeArray[] = [
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
                }
            }
            if ($distretto) {
                $perDistrettiMapPagamentoPrecedente[$distretto] = array_diff($perDistrettiMapPagamentoPrecedente[$distretto], [$istanza->id]);
                $perDistrettiMapAttuale[$distretto][] = $istanza->id;
            }
            $allIdPagati = array_diff($allIdPagati, [$istanza->id]);
        }
        $nonPagati = $distretto ? $perDistrettiMapPagamentoPrecedente[$distretto] : $allIdPagati;
        foreach ($nonPagati as $idPagato) {
            $istanza = Istanza::findOne($idPagato);
            $differenza = $istanza->getDifferenzaUltimoImportoArray();
            $istanzeArray[] = [
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
        }
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $istanzeArray);

        return $this->render('simulazione', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'distretto' => $distretto,
            'allIdPagati' => $allIdPagati,
            'perDistrettiMapPagamentoPrecedente' => $perDistrettiMapPagamentoPrecedente,
            'perDistrettiMapAttuale' => $perDistrettiMapAttuale,
            'soloProblematici' => $soloProblematici,
        ]);
    }

}
