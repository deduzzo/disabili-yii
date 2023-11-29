<?php

namespace app\controllers;

use app\helpers\Utils;
use app\models\Determina;
use app\models\Distretto;
use app\models\enums\IseeType;
use app\models\Gruppo;
use app\models\Isee;
use app\models\Istanza;
use app\models\Movimento;
use app\models\SimulazioneDeterminaSearch;
use Carbon\Carbon;
use Monolog\Handler\Curl\Util;
use Yii;
use yii\bootstrap5\Html;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii2tech\spreadsheet\Spreadsheet;

class DeterminaController extends \yii\web\Controller
{
    public function actionIndex($export = false, $idDeterminaFinalizzare = null)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        Utils::verificaChiusuraAutomaticaIstanze();
        $searchModel = new SimulazioneDeterminaSearch();
        $getVars = $idDeterminaFinalizzare === null ? $this->request->post() : [];
        $distretti = $getVars['distrettiPost'] ?? Distretto::getAllIds();
        $distretti = Distretto::find()->where(['id' => $distretti])->all();
        $gruppi = $getVars['gruppiPost'] ?? Gruppo::getAllIds();
        $gruppi = Gruppo::find()->where(['id' => $gruppi])->all();
        $soloProblematici = isset($getVars['soloProblematici']) ? $getVars['soloProblematici'] : 'off';
        $soloVariazioni = isset($getVars['soloVariazioni']) ? $getVars['soloVariazioni'] : 'off';
        $soloRecuperi = isset($getVars['soloRecuperi']) ? $getVars['soloRecuperi'] : 'off';
        $allIstanzeAttive = (new Query())->select('id')->from('istanza')->where(['attivo' => true])->andWhere(['chiuso' => false]);
        //new rawquery
        $ultimaData = Movimento::getDataUltimoPagamento();
        $allPagamentiPrecedenti = (new Query())->select('c.id_istanza, i.id_distretto')->from('movimento m, conto c, istanza i')->where("m.id_conto = c.id")->andWhere('c.id_istanza = i.id')->andWhere('is_movimento_bancario = true')->andWhere(['data' => $ultimaData])
            ->andWhere(['i.id_distretto' => ArrayHelper::getColumn($distretti, 'id')])
            ->andWhere(['i.id_gruppo' => ArrayHelper::getColumn($gruppi, 'id')])
            ->all();
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
        $allIstanzeAttive->andWhere(['id_gruppo' => ArrayHelper::getColumn($gruppi, 'id')]);
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
                        'gruppo' => $istanza->gruppo->descrizione_gruppo,
                        'importoPrecedente' => $differenza['importoPrecedente'],
                        'importo' => $istanza->getProssimoImporto(),
                        'opArray' => $differenza,
                        'operazione' => $soloRecuperi === "off" ? $differenza['op'] : $istanza->getStatoRecupero(),
                    ];
                    if ($differenza['alert'] === true)
                        $alert[$istanza->distretto->id] = $istVal;
                    else {
                        if ($istanza->getProssimoImporto() > 0)
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
            if ($idDeterminaFinalizzare !== null)
                $istanza->finalizzaMensilita($idDeterminaFinalizzare);
        }
        $nonPagati = [];
        foreach ($distretti as $disPag) {
            $nonPagati = array_merge($nonPagati, $pagamentiPrecedentiPerDistretti[$disPag->id] ?? []);
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
                if ($istanza->getProssimoImporto() > 0)
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
        if (!$idDeterminaFinalizzare)
            return $this->render('simulazione', [
                'istanzeArray' => $istanzeArray,
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'allIdPagati' => $allIdPagatiMeseScorso,
                'soloProblematici' => $soloProblematici,
                'soloVariazioni' => $soloVariazioni,
                'soloRecuperi' => $soloRecuperi,
                'distretti' => $distretti,
                'gruppi' => $gruppi,
                'title' => "Simulazione prossima determina",
                'stats' => [
                    'importiTotali' => $importiTotali,
                    'numeriTotali' => $numeriTotali,
                    'alert' => $alert,
                ]
            ]);
        else {
            Yii::$app->session->setFlash('success', 'Determina finalizzata correttamente!');
            return $this->redirect(['istanza/index']);
        }
    }

    public function actionVisualizza($export = false)
    {
        if ($this->request->isGet && isset($this->request->get()['anno']) && isset($this->request->get()['mese'])) {
            $mese = $this->request->get()['mese'];
            $anno = $this->request->get()['anno'];
        } else {
            $ultimoPagamento = Movimento::getDataUltimoPagamento();
            $mese = Carbon::createFromFormat('Y-m-d', $ultimoPagamento)->month;
            $anno = Carbon::createFromFormat('Y-m-d', $ultimoPagamento)->year;
        }
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $searchModel = new SimulazioneDeterminaSearch();
        $getVars = $this->request->get();
        $distretti = $getVars['distrettiPost'] ?? Distretto::getAllIds();
        $distretti = Distretto::find()->where(['id' => $distretti])->all();
        $gruppi = $getVars['gruppiPost'] ?? Gruppo::getAllIds();
        $gruppi = Gruppo::find()->where(['id' => $gruppi])->all();
        //new rawquery
        $ultimaData = Carbon::createFromFormat('Y-m-d', $anno . '-' . $mese . "-01");
        $allPagamenti = (new Query())->select('c.id_istanza, i.id_distretto,m.importo')->from('movimento m, conto c, istanza i')->where("m.id_conto = c.id")->andWhere('c.id_istanza = i.id')->andWhere('is_movimento_bancario = true')
            ->andwhere(['>=', 'data', $ultimaData->startOfMonth()->format('Y-m-d')])->andWhere(['<=', 'data', $ultimaData->endOfMonth()->format('Y-m-d')])
            ->andWhere(['i.id_gruppo' => ArrayHelper::getColumn($gruppi, 'id')])
            ->andWhere(['i.id_distretto' => ArrayHelper::getColumn($distretti, 'id')])->all();
        $importiTotali = [];
        $numeriTotali = [];
        foreach (Distretto::find()->all() as $item) {
            $importiTotali[$item->id] = [IseeType::MAGGIORE_25K => 0, IseeType::MINORE_25K => 0, IseeType::NO_ISEE => 0];
            $numeriTotali[$item->id] = [IseeType::MAGGIORE_25K => 0, IseeType::MINORE_25K => 0, IseeType::NO_ISEE => 0];
        }
        $istanzeArray = [];
        // id, cf, cognome, nome distretto, isee, eta, gruppo, importo
        foreach ($allPagamenti as $istanzaRaw) {
            /* @var $istanza Istanza */
            $istanza = Istanza::findOne($istanzaRaw['id_istanza']);
            $istVal = [
                'id' => $istanza->id,
                'cf' => $istanza->anagraficaDisabile->codice_fiscale,
                'cognomeNome' => $istanza->getNominativoDisabile(),
                'dataNascita' => $istanza->anagraficaDisabile->data_nascita,
                'dataDecesso' => $istanza->data_decesso,
                'distretto' => $istanza->distretto->nome,
                'isee' => $istanza->getIseeTypeInDate($ultimaData->endOfMonth()),
                'eta' => $istanza->anagraficaDisabile->getEta($ultimaData),
                'gruppo' => $istanza->gruppo->descrizione_gruppo_old . " [" . $istanza->gruppo->descrizione_gruppo . "]",
                'gruppi' => $gruppi,
                //'importoPrecedente' => $differenza['importoPrecedente'],
                'importo' => Yii::$app->formatter->asCurrency($istanzaRaw['importo']),
                //'opArray' => $differenza,
                //'operazione' => $soloRecuperi === "off" ? $differenza['op'] : $istanza->getStatoRecupero(),
            ];
            $istanzeArray[] = $istVal;

            $numeriTotali[$istanza->distretto->id][$istanza->getIseeTypeInDate($ultimaData)] += 1;
            $importiTotali[$istanza->distretto->id][$istanza->getIseeTypeInDate($ultimaData)] += $istanzaRaw['importo'];
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $istanzeArray);
        return $this->render('simulazione', [
            'istanzeArray' => $istanzeArray,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'distretti' => $distretti,
            'stats' => [
                'importiTotali' => $importiTotali,
                'numeriTotali' => $numeriTotali,
            ],
            'anno' => $anno,
            'mese' => $mese,
            'title' => "Storico pagamenti",
        ]);
    }

    public
    function actionFinalizza()
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);
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


    //select DISTINCT i.id from istanza i, movimento m, conto c where m.id_conto = c.id AND c.id_istanza = i.id AND i.attivo = true AND i.id not in (SELECT distinct c2.id_istanza from movimento m2, conto c2 where m2.escludi_contabilita = true AND c2.id = m2.id_conto AND m2.data >= "2023-10-01");
    public
    function actionPagamenti()
    {
        $result = null;
        $vars = $this->request->get();
        $ultimoPagamento = Movimento::getDataUltimoPagamento();
        $mese = Carbon::createFromFormat('Y-m-d', $ultimoPagamento)->month;
        $anno = Carbon::createFromFormat('Y-m-d', $ultimoPagamento)->year;
        if (isset($vars['mese']) && isset($vars['anno']) && isset($vars['submit'])) {
            $mese = $vars['mese'];
            $anno = $vars['anno'];
            $result = "<div class='row'>";
            //$ultimoPagamento = Movimento::getDataUltimoPagamento();
            $mesePagamento = Carbon::createFromFormat('Y-m-d', $vars['anno'] . '-' . $vars['mese'] . "-01");
            $istanzePagate = (new Query())->select('i.id')->distinct()->from('istanza i, conto c, movimento m')->where('m.id_conto = c.id')->andWhere('c.id_istanza = i.id')->andWhere(['m.is_movimento_bancario' => true]);
            $istanzePagate = $istanzePagate->andWhere(['>=', 'm.data', $mesePagamento->startOfMonth()->format('Y-m-d')])->andWhere(['<=', 'm.data', $mesePagamento->endOfMonth()->format('Y-m-d')])->all();
            //select DISTINCT i.id from istanza i, movimento m, conto c where m.id_conto = c.id AND c.id_istanza = i.id AND i.attivo = true AND i.id not in (SELECT distinct c2.id_istanza from movimento m2, conto c2 where m2.escludi_contabilita = true AND c2.id = m2.id_conto AND m2.data >= "2023-10-01");
            $istanzeAttiveArrayId = (new Query())->select('i.id')->distinct()->from('istanza i, movimento m, conto c')->where('m.id_conto = c.id')->andWhere('c.id_istanza = i.id')->andWhere(['i.attivo' => true])->andWhere(['not in', 'i.id', $istanzePagate])->andWhere(['not in', 'i.id', (new Query())->select('c2.id_istanza')->distinct()->from('movimento m2, conto c2')->where('m2.escludi_contabilita = true')->andWhere('c2.id = m2.id_conto')->andWhere(['>=', 'm2.data', $mesePagamento->startOfMonth()->format('Y-m-d')])->all()])->all();
            $allIstanze = array_merge($istanzePagate, $istanzeAttiveArrayId);
            $errori = false;
            foreach ($allIstanze as $istanza) {
                $istanza = Istanza::findOne($istanza['id']);
                $tempResult = $istanza->verificaContabilitaMese(intval($vars['mese']), intval($vars['anno']));
                if ($tempResult != 0.0) {
                    $errori = true;
                    $result .= "<div class='col-md-1'>❌ #" . $istanza->id . "</div><div class='col-md-1'>" . Html::a('<i class="fa fa-solid fa-eye" style="color: #ffffff;"></i>', Url::toRoute(['istanza/scheda', 'id' => $istanza->id]), [
                            'title' => Yii::t('yii', 'Vai alla scheda'),
                            'class' => 'btn btn-icon btn-sm btn-primary',
                            'target' => '_blank',
                        ])
                        . "</div><div class='col-md-3'>" . $istanza->anagraficaDisabile->cognome
                        . " " . $istanza->anagraficaDisabile->nome
                        . "</div><div class='col-md-1'>" . $istanza->distretto->nome
                        . "</div><div class='col-md-1'><span style='margin-left:20px' class='badge " . ($tempResult > 0 ? 'bg-success' : 'bg-danger')
                        . "'>" . ($tempResult > 0 ? ("+" . $tempResult) : $tempResult)
                        . "</span></div><div class='col-md-5'></div>";
                }
            }
            if (!$errori)
                $result .= "<div class='col-md-12'>🆗Tutto ok! ✔️</div>";
            $result .= "</div>";
        }
        return $this->render('pagamenti', [
            "mese" => $mese,
            "anno" => $anno,
            "result" => $result !== null ? ($result === "" ? "🆗Tutto ok! ✔️" : $result) : "",
        ]);
    }

}
