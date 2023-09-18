<?php

namespace app\controllers;

use app\models\Istanza;
use app\models\SimulazioneDeterminaSearch;
use Yii;
use yii\data\ArrayDataProvider;
use yii\data\SqlDataProvider;
use yii\db\Query;

class DeterminaController extends \yii\web\Controller
{
    public function actionIndex()
    {

        $searchModel = new SimulazioneDeterminaSearch();
        $allIstanze = Istanza::find()->where(['attivo' => true, 'chiuso' => false,'id_distretto' => 4])->all();
        $istanzeArray = [];
        // id, cf, cognome, nome distretto, isee, eta, gruppo, importo
        foreach ($allIstanze as $istanza) {
            /* @var $istanza Istanza */
            $istanzeArray[] = [
                'id' => $istanza->id,
                'cf' => $istanza->anagraficaDisabile->codice_fiscale,
                'cognome' => $istanza->anagraficaDisabile->cognome,
                'nome' => $istanza->anagraficaDisabile->nome,
                'distretto' => $istanza->distretto->nome,
                'isee' => $istanza->getLastIseeType(),
                'eta' => $istanza->anagraficaDisabile->getEta(),
                'gruppo' => $istanza->gruppo->descrizione_gruppo,
                'importo' => $istanza->getProssimoImporto(),
            ];
        }
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $istanzeArray);

        return $this->render('simulazione', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

}
