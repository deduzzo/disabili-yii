<?php

namespace app\controllers;

use app\models\Istanza;
use app\models\SimulazioneDeterminaSearch;
use Yii;

class DeterminaController extends \yii\web\Controller
{
    public function actionIndex($distretto = null)
    {
        // unlimited memory_limit
        ini_set('memory_limit', '-1');
        $searchModel = new SimulazioneDeterminaSearch();
        $allIstanze = Istanza::find()->where(['attivo' => true, 'chiuso' => false]);
        if ($distretto)
            $allIstanze->andWhere(['id_distretto' => $distretto]);
        $allIstanze = $allIstanze->all();
        $istanzeArray = [];
        // id, cf, cognome, nome distretto, isee, eta, gruppo, importo
        foreach ($allIstanze as $istanza) {
            /* @var $istanza Istanza */
            $differenza =  $istanza->getDifferenzaUltimoImportoArray();
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
        ]);
    }

}
