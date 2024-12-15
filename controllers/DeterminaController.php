<?php

namespace app\controllers;

use app\helpers\Utils;
use app\models\Conto;
use app\models\Determina;
use app\models\DeterminaGruppoPagamento;
use app\models\Distretto;
use app\models\enums\IseeType;
use app\models\Gruppo;
use app\models\GruppoPagamento;
use app\models\Isee;
use app\models\Istanza;
use app\models\IstanzaSearch;
use app\models\Movimento;
use app\models\SimulazioneDeterminaSearch;
use Carbon\Carbon;
use Monolog\Handler\Curl\Util;
use Yii;
use yii\bootstrap5\Html;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii2tech\spreadsheet\Spreadsheet;

class DeterminaController extends \yii\web\Controller
{
    public function actionIndex($export = false, $idDeterminaFinalizzare = null, $escludiNuovoMese = null, $distretti = null, $gruppi = null, $singoleIstanze = null)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        Utils::verificaChiusuraAutomaticaIstanze();
        $searchModel = new SimulazioneDeterminaSearch();
        $getVars = $this->request->post();
        if ($distretti === null)
            $distretti = $getVars['distrettiPost'] ?? Distretto::getAllIds();
        $distretti = Distretto::find()->where(['id' => $distretti])->all();
        if ($gruppi === null)
            $gruppi = $getVars['gruppiPost'] ?? Gruppo::getAllIds();
        $gruppi = Gruppo::find()->where(['id' => $gruppi])->all();
        if ($singoleIstanze === null)
            $singoleIstanze = $getVars['singoleIstanze'] ?? [];
        $singoleIstanze = Istanza::find()->where(['id' => $singoleIstanze])->all();

        $soloProblematici = (isset($getVars['soloProblematici']) && !$idDeterminaFinalizzare) ? $getVars['soloProblematici'] : 'off';
        $soloVariazioni = (isset($getVars['soloVariazioni']) && $idDeterminaFinalizzare) ? $getVars['soloVariazioni'] : 'off';
        $soloRecuperi = (isset($getVars['soloRecuperi']) && $idDeterminaFinalizzare) ? $getVars['soloRecuperi'] : 'off';
        if ($escludiNuovoMese === null)
            $escludiNuovoMese = isset($getVars['escludiNuovoMese']) ? $getVars['escludiNuovoMese'] : 'off';
        $allIstanzeAttive = (new Query())->select('id')->from('istanza')->where(['attivo' => true])->andWhere(['chiuso' => false]);
        //new rawquery
        $ultimaData = Movimento::getDataUltimoPagamento();
        $allPagamentiPrecedenti = (new Query())->select('c.id_istanza, i.id_distretto')->from('movimento m, conto c, istanza i')->where("m.id_conto = c.id")->andWhere('c.id_istanza = i.id')->andWhere('is_movimento_bancario = true')->andWhere(['data' => $ultimaData])
            ->andWhere(['i.id_distretto' => ArrayHelper::getColumn($distretti, 'id')])
            ->andWhere(['i.id_gruppo' => ArrayHelper::getColumn($gruppi, 'id')])
            ->andWhere(['i.liquidazione_decesso_completata' => false])
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
        // get all id only of $singoleIstanze
        $singoleIstanzeIds = array_map(function ($el) {
            return ['id' => $el->id];
        }, $singoleIstanze);
        $allIstanzeAttive = array_merge($allIstanzeAttive, $singoleIstanzeIds);
        $istanzeArray = [];
        // id, cf, cognome, nome distretto, isee, eta, gruppo, importo
        foreach ($allIstanzeAttive as $istanza) {
            /* @var $istanza Istanza */
            $istanza = Istanza::findOne($istanza['id']);
            $differenza = $istanza->getDifferenzaUltimoImportoArray($escludiNuovoMese !== "on");
            $prossimoImporto = $istanza->getProssimoImporto($escludiNuovoMese !== "on");
            if (!$differenza['alert'] && $prossimoImporto > 0) {
                if ($soloProblematici === "off" || ($soloProblematici == "on" && $differenza['op'] !== "")) {
                    $istVal = [
                        'id' => $istanza->id,
                        'cf' => $istanza->anagraficaDisabile->codice_fiscale,
                        'cognome' => $istanza->anagraficaDisabile->cognome,
                        'nome' => $istanza->anagraficaDisabile->nome,
                        'dataNascita' => $istanza->anagraficaDisabile->data_nascita,
                        'stato' => $istanza->data_decesso !== null ? "Deceduto" : "In vita",
                        'distretto' => $istanza->distretto->nome,
                        'isee' => $istanza->getLastIseeType(),
                        'eta' => $istanza->anagraficaDisabile->getEta(),
                        'gruppo' => $istanza->gruppo->descrizione_gruppo,
                        'importoPrecedente' => $differenza['importoPrecedente'],
                        'importo' => $prossimoImporto,
                        'opArray' => $differenza,
                        'operazione' => $soloRecuperi === "off" ? $differenza['op'] : $istanza->getStatoRecupero(),
                    ];
                    if ($differenza['alert'] === true)
                        $alert[$istanza->distretto->id] = $istVal;
                    else {
                        $numeriTotali[$istanza->distretto->id][$istanza->getLastIseeType()] += 1;
                        $importiTotali[$istanza->distretto->id][$istanza->getLastIseeType()] += $prossimoImporto;
                        if ($differenza['recupero'] === true)
                            $recuperiPerDistretto[$istanza->distretto->id][] = $istVal;
                        if ($differenza['op'] !== "")
                            $differenzePerDistretto[$istanza->distretto->id][] = $istVal;
                    }
                    $istanzeArray[] = $istVal;
                }
                if (!array_key_exists($istanza->distretto->id, $pagamentiPrecedentiPerDistretti))
                    $pagamentiPrecedentiPerDistretti[$istanza->distretto->id] = [];
                $pagamentiPrecedentiPerDistretti[$istanza->distretto->id] = array_diff($pagamentiPrecedentiPerDistretti[$istanza->distretto->id], [$istanza->id]);
                $pagamentiAttualiPerDistretti[$istanza->distretto->id][] = $istanza->id;
                $allIdPagatiMeseScorso = array_diff($allIdPagatiMeseScorso, [$istanza->id]);
            }
            if ($idDeterminaFinalizzare !== null && ($escludiNuovoMese !== "on" || $prossimoImporto > 0))
                $istanza->finalizzaMensilita($idDeterminaFinalizzare, $escludiNuovoMese !== "on");
        }
        $nonPagati = [];
        if ($escludiNuovoMese !== "on") {
            foreach ($distretti as $disPag) {
                $nonPagati = array_merge($nonPagati, $pagamentiPrecedentiPerDistretti[$disPag->id] ?? []);
            }
        }
        foreach ($nonPagati as $idPagato) {
            $istanza = Istanza::findOne($idPagato);
            $differenza = $istanza->getDifferenzaUltimoImportoArray($escludiNuovoMese !== "on");
            $prossimoImporto = $istanza->getProssimoImporto($escludiNuovoMese !== "on");
            $istVal = [
                'id' => $istanza->id,
                'cf' => $istanza->anagraficaDisabile->codice_fiscale,
                'cognome' => $istanza->anagraficaDisabile->cognome,
                'nome' => $istanza->anagraficaDisabile->nome,
                'dataNascita' => $istanza->anagraficaDisabile->data_nascita,
                'stato' => $istanza->data_decesso !== null ? "Deceduto" : "In vita",
                'distretto' => $istanza->distretto->nome,
                'isee' => $istanza->getLastIseeType(),
                'eta' => $istanza->anagraficaDisabile->getEta(),
                'gruppo' => $istanza->gruppo->descrizione_gruppo,
                'importoPrecedente' => $differenza['importoPrecedente'],
                'importo' => $prossimoImporto,
                'opArray' => $differenza,
                'operazione' => $soloRecuperi === "off" ? $differenza['op'] : $istanza->getStatoRecupero(),
            ];
            if ($differenza['alert'] === true)
                $alert[$istanza->distretto->id][] = $istVal;
            else {
                $importiTotali[$istanza->distretto->id][$istanza->getLastIseeType()] += $prossimoImporto;
                if ($prossimoImporto > 0)
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
                'escludiNuovoMese' => $escludiNuovoMese,
                'distretti' => $distretti,
                'singoleIstanze' => $singoleIstanze ?? [],
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

    public function actionFinalizzaLiquidazioneDeceduti()
    {
        if ($this->request->isPost) {
            $vars = $this->request->post();
            // data_determina, descrizione_determina, ids (che sono separati da , e che vanno splittati)
            $idDetermina = $vars['idDetermina'];
            $ids = explode(",", $vars['ids']);
            if ($idDetermina && count($ids) > 0) {
                $determina = Determina::findOne($idDetermina);
                foreach ($ids as $id) {
                    $istanza = Istanza::findOne($id);
                    $valore = $istanza->getDatiLiquidazioneDecesso()['valore'];
                    // Creo prima il movimento contabile
                    $movimento = new Movimento();
                    $movimento->id_conto = $istanza->getContoValido(true)->id;
                    $movimento->data = $determina->data;
                    $movimento->importo = $valore;
                    $movimento->note = "Liquidazione decesso " . $istanza->getNominativoDisabile();
                    $movimento->is_movimento_bancario = false;
                    $movimento->id_determina = $determina->id;
                    $movimento->save();
                    // Creo il movimento bancario
                    $movimento = new Movimento();
                    $movimento->id_conto = $istanza->getContoValido(true)->id;
                    $movimento->data = $determina->data;
                    $movimento->importo = $valore;
                    $movimento->note = "Liquidazione decesso " . $istanza->getNominativoDisabile();
                    $movimento->is_movimento_bancario = true;
                    $movimento->escludi_contabilita = true;
                    $movimento->id_determina = $determina->id;
                    $movimento->save();

                    $istanza->chiuso = true;
                    $istanza->liquidazione_decesso_completata = true;
                    $istanza->data_liquidazione_decesso = $determina->data;
                    $istanza->save();
                }
                Yii::$app->session->setFlash('success', 'Determina finalizzata correttamente!');
                return $this->redirect(['istanza/index']);
            } else {
                Yii::$app->session->setFlash('error', 'Errore durante la creazione della determina');
                return $this->redirect(['contabilita/liquidazione-deceduti']);
            }
        }

    }

    public function actionLiquidazioneDeceduti()
    {
        $vars = [];
        if ($this->request->isPost) {
            $vars = $this->request->post();
        }
        $searchModel = new IstanzaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, array_key_exists('ExportWDG', $vars));

        // Impostiamo il numero di righe per pagina a 1000
        $dataProvider->pagination = [
            'pageSize' => 1000,
        ];

        $dataProvider->query->where(['chiuso' => false])
            ->andWhere(['not', ['data_decesso' => null]]);

        return $this->render('liquidazione-deceduti', [
            "dataProvider" => $dataProvider,
            "searchModel" => $searchModel
        ]);
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
        $determina = Determina::findOne($getVars['idDetermina'] ?? null) ?? Determina::find()->orderBy(['data' => SORT_DESC])->one();
        $gruppi = Gruppo::find()->where(['id' => $gruppi])->all();
        //new rawquery
        $ultimaData = Carbon::createFromFormat('Y-m-d', $anno . '-' . $mese . "-01");
        $allPagamenti = !$determina ? (new Query())->select('c.id_istanza, i.id_distretto,m.importo')->from('movimento m, conto c, istanza i')->where("m.id_conto = c.id")->andWhere('c.id_istanza = i.id')->andWhere('is_movimento_bancario = true')
            ->andwhere(['>=', 'data', $ultimaData->startOfMonth()->format('Y-m-d')])->andWhere(['<=', 'data', $ultimaData->endOfMonth()->format('Y-m-d')])
            ->andWhere(['i.id_gruppo' => ArrayHelper::getColumn($gruppi, 'id')])
            ->andWhere(['i.id_distretto' => ArrayHelper::getColumn($distretti, 'id')])->all() :
            (new Query())->select('c.id_istanza, i.id_distretto,m.importo')->from('movimento m, conto c, istanza i')
                ->where("m.id_conto = c.id")->andWhere('c.id_istanza = i.id')->andWhere('is_movimento_bancario = true')
                ->andWhere(['m.id_determina' => $determina->id])->all();
        $importiTotali = [];
        $numeriTotali = [];
        foreach (Distretto::find()->all() as $item) {
            $importiTotali[$item->id] = [IseeType::MAGGIORE_25K => 0, IseeType::MINORE_25K => 0, IseeType::NO_ISEE => 0];
            $numeriTotali[$item->id] = [IseeType::MAGGIORE_25K => 0, IseeType::MINORE_25K => 0, IseeType::NO_ISEE => 0];
        }
        // deceduti
        $importiTotali[-1] = [IseeType::MAGGIORE_25K => 0, IseeType::MINORE_25K => 0, IseeType::NO_ISEE => 0];
        $numeriTotali[-1] = [IseeType::MAGGIORE_25K => 0, IseeType::MINORE_25K => 0, IseeType::NO_ISEE => 0];

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
                'distretto' => !$istanza->liquidazione_decesso_completata ? $istanza->distretto->nome : "DECEDUTI",
                'isee' => $istanza->getIseeTypeInDate($ultimaData->endOfMonth()),
                'eta' => $istanza->anagraficaDisabile->getEta($ultimaData),
                'gruppo' => $istanza->gruppo->descrizione_gruppo_old . " [" . $istanza->gruppo->descrizione_gruppo . "]",
                //'importoPrecedente' => $differenza['importoPrecedente'],
                'importo' => Yii::$app->formatter->asCurrency($istanzaRaw['importo']),
                //'opArray' => $differenza,
                //'operazione' => $soloRecuperi === "off" ? $differenza['op'] : $istanza->getStatoRecupero(),
            ];
            $istanzeArray[] = $istVal;

            if (!$istanza->liquidazione_decesso_completata) {
                $numeriTotali[$istanza->distretto->id][$istanza->getIseeTypeInDate($ultimaData)] += 1;
                $importiTotali[$istanza->distretto->id][$istanza->getIseeTypeInDate($ultimaData)] += $istanzaRaw['importo'];
            } else {
                $numeriTotali[-1][$istanza->getIseeTypeInDate($ultimaData)] += 1;
                $importiTotali[-1][$istanza->getIseeTypeInDate($ultimaData)] += $istanzaRaw['importo'];
            }
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
            'gruppi' => $gruppi,
            'title' => "Storico pagamenti",
        ]);
    }

    public function actionFinalizza()
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $vars = $this->request->post();
        if (isset($vars['numero_determina'])) {
            $contiOk = Istanza::verificaContiMancantiIstanzeAttive();
            if ($contiOk === "") {
                $determina = new Determina();
                $determina->numero = $vars['numero_determina'];
                $determina->pagamenti_da = $vars['data_inizio'];
                $determina->pagamenti_a = $vars['data_fine'];
                $determina->data = $vars['data_determina'];
                $determina->non_ordinaria = isset($vars['escludiNuovoMese']);
                $determina->descrizione = "Pagamento mensilità da " . $vars['data_inizio'] . " a " . $vars['data_fine'] . " - " . $vars['descrizione'];
                $determina->save();
                $this->actionIndex(false, $determina->id, $vars['escludiNuovoMese'] ?? null, Json::decode($vars['distretti']) ?? null, Json::decode($vars['gruppi']) ?? null, Json::decode($vars['singoleIstanze']) ?? null);
            } else {
                Yii::$app->session->setFlash('error', 'Impossibile finalizzare: ci sono conti correnti non validi');
                return $this->redirect(['contabilita/conti-validi']);
            }
        } else {
            Yii::$app->session->setFlash('error', 'Errore durante la creazione della determina, manca numero');
            return $this->redirect(['istanza/index']);
        }
    }


    //select DISTINCT i.id from istanza i, movimento m, conto c where m.id_conto = c.id AND c.id_istanza = i.id AND i.attivo = true AND i.id not in (SELECT distinct c2.id_istanza from movimento m2, conto c2 where m2.escludi_contabilita = true AND c2.id = m2.id_conto AND m2.data >= "2023-10-01");
    public
    function actionPagamenti()
    {
        $result = null;
        $vars = $this->request->get();
        $ultimoPagamento = Movimento::getDataUltimoPagamento();
        $mese = null; // Carbon::createFromFormat('Y-m-d', $ultimoPagamento)->month;
        $anno = null; // Carbon::createFromFormat('Y-m-d', $ultimoPagamento)->year;
        $minGruppoPagato = 0;
        if (((isset($vars['mese']) && isset($vars['anno'])) || isset($vars['idDetermina'])) && isset($vars['submit'])) {
            $mese = $vars['mese'];
            $anno = $vars['anno'];
            $idDetermina = $vars['idDetermina'];
            if ($idDetermina === "")
                $idDetermina = null;
            $result = "<div class='row'>";
            //$ultimoPagamento = Movimento::getDataUltimoPagamento();
            if ($idDetermina)
                $idDetermina = Determina::findOne($idDetermina);
            if (!$idDetermina) {
                $mesePagamento = Carbon::createFromFormat('Y-m-d', $vars['anno'] . '-' . $vars['mese'] . "-01");
                $istanzePagate = (new Query())->select('i.id')->distinct()->from('istanza i, conto c, movimento m')->where('m.id_conto = c.id')->andWhere('c.id_istanza = i.id')->andWhere(['m.is_movimento_bancario' => true])
                    ->andWhere(['>=', 'm.data', $mesePagamento->startOfMonth()->format('Y-m-d')])->andWhere(['<=', 'm.data', $mesePagamento->endOfMonth()->format('Y-m-d')])->all();
                //select DISTINCT i.id from istanza i, movimento m, conto c where m.id_conto = c.id AND c.id_istanza = i.id AND i.attivo = true AND i.id not in (SELECT distinct c2.id_istanza from movimento m2, conto c2 where m2.escludi_contabilita = true AND c2.id = m2.id_conto AND m2.data >= "2023-10-01");
                $istanzeAttiveArrayId = (new Query())->select('i.id')->distinct()->from('istanza i, movimento m, conto c')
                    ->where('m.id_conto = c.id')->andWhere('c.id_istanza = i.id')
                    ->andWhere(['i.attivo' => true])->andWhere(['not in', 'i.id', $istanzePagate])
                    ->andWhere(['not in', 'i.id',
                        (new Query())->select('c2.id_istanza')->distinct()->from('movimento m2, conto c2')
                            ->where('m2.escludi_contabilita = true')->andWhere('c2.id = m2.id_conto')
                            ->andWhere(['>=', 'm2.data', $mesePagamento->startOfMonth()->format('Y-m-d')])->all()])
                    ->all();

                $allIstanze = array_merge($istanzePagate, $istanzeAttiveArrayId);
            } else {
                $istanzePagate = [];
                /*                $allGruppiPagati = DeterminaGruppoPagamento::find()->where(['id_determina' => $idDetermina->id])->all();
                                foreach ($allGruppiPagati as $gruppo) {
                                    if ($gruppo->id < $minGruppoPagato || $minGruppoPagato === 0)
                                        $minGruppoPagato = $gruppo->id;
                                    $allIstanzeGruppo = (new Query())->select('i.id')->distinct()->from('istanza i, conto c, movimento m')
                                        ->where('m.id_conto = c.id')->andWhere('c.id_istanza = i.id')
                                        ->andWhere(['m.is_movimento_bancario' => true])->andWhere(['m.id_gruppo_pagamento' => $gruppo->id_gruppo])->all();
                                    $istanzePagate = array_merge($istanzePagate, $allIstanzeGruppo);
                                }*/
                $istanzePagate = (new Query())->select('i.id')->distinct()->from('istanza i, conto c, movimento m')
                    ->where('m.id_conto = c.id')->andWhere('c.id_istanza = i.id')
                    ->andWhere(['m.is_movimento_bancario' => true])->andWhere(['m.id_determina' => $idDetermina->id])->all();

                $istanzeAttiveArrayId = (new Query())->select('i.id')->distinct()->from('istanza i, movimento m, conto c')
                    ->where('m.id_conto = c.id')->andWhere('c.id_istanza = i.id')
                    ->andWhere(['i.attivo' => true])->andWhere(['not in', 'i.id', $istanzePagate])
                    ->andWhere(['not in', 'i.id',
                        (new Query())->select('c2.id_istanza')->distinct()->from('movimento m2, conto c2')
                            ->where('m2.escludi_contabilita = true')->andWhere('c2.id = m2.id_conto')
                            ->andWhere(['>=', 'm2.id_gruppo_pagamento', $minGruppoPagato])->all()])
                    ->all();

                $allIstanze = array_merge($istanzePagate, $istanzeAttiveArrayId);
                // remove duplicates from $allIstanze
            }
            $allIstanzeAttiveContoDaValidare = (new Query())->select('i.id')->distinct()->from('istanza i, movimento m, conto c')
                ->where('m.id_conto = c.id')->andWhere('c.id_istanza = i.id')
                ->andWhere(['i.attivo' => true])->andWhere(['c.validato' => false, 'c.attivo' => true])->all();
            $allIstanze = array_merge($allIstanze, $allIstanzeAttiveContoDaValidare);

            $allIstanze = array_map("unserialize", array_unique(array_map("serialize", $allIstanze)));
            $errori = false;
            $warning = false;
            foreach ($allIstanze as $istanza) {
                $istanza = Istanza::findOne($istanza['id']);
                $tempResult = $istanza->verificaContabilitaMese(intval($vars['mese']), intval($vars['anno']), $idDetermina);
                if ($tempResult['tot'] != 0.0) {
                    $errori = true;
                    $result .= "<div class='col-md-1'>❌ #" . $istanza->id . "</div><div class='col-md-1'>" . Html::a('<i class="fa fa-solid fa-eye" style="color: #ffffff;"></i>', Url::toRoute(['istanza/scheda', 'id' => $istanza->id]), ['title' => Yii::t('yii', 'Vai alla scheda'),
                            'class' => 'btn btn-icon btn-sm btn-primary',
                            'target' => '_blank',])
                        . "</div><div class='col-md-3'>" . $istanza->anagraficaDisabile->cognome
                        . " " . $istanza->anagraficaDisabile->nome
                        . "</div><div class='col-md-1'>" . $istanza->distretto->nome
                        . "</div><div class='col-md-1'><span style='margin-left:20px' class='badge " . ($tempResult['tot'] > 0 ? 'bg-success' : 'bg-danger')
                        . "'>" . ($tempResult['tot'] > 0 ? ("+" . $tempResult['tot']) : $tempResult['tot'])
                        . "</span></div><div class='col-md-5'></div>";
                }
                if ($tempResult['contoOk'] === false) {
                    $warning = true;
                    $result .= "<div class='col-md-1'>❌ #" . $istanza->id . "</div><div class='col-md-1'>" . Html::a('<i class="fa fa-solid fa-eye" style="color: #ffffff;"></i>', Url::toRoute(['istanza/scheda', 'id' => $istanza->id]), ['title' => Yii::t('yii', 'Vai alla scheda'),
                            'class' => 'btn btn-icon btn-sm btn-primary',
                            'target' => '_blank',])
                        . "</div><div class='col-md-3'>" . $istanza->anagraficaDisabile->cognome
                        . " " . $istanza->anagraficaDisabile->nome
                        . "</div><div class='col-md-1'>" . $istanza->distretto->nome
                        . "</div><div class='col-md-1'><span style='margin-left:20px' class='badge bg-warning'>IBAN diverso</span>"
                        . "</span></div><div class='col-md-5'></div>";
                }
            }
            if (!$errori)
                $result .= "<div class='col-md-12'>🆗Tutto ok! ✔️</div>";
            if (!$warning)
                $result .= "<div class='col-md-12'>🆗Nessun warning! ✔️</div>";
            $result .= "</div>";
        }
        $ibanRipetuti = [];
        if (isset($vars['verifica-iban'])) {
            $istanze = Istanza::find()->where(['attivo' => true])->andWhere(['chiuso' => false])->all();
            $ibans = [];
            /* @var $istanza Istanza */
            foreach ($istanze as $istanza) {
                /* @var $contoValido Conto */
                $contoValido = $istanza->getContoValido();
                if ($contoValido) {
                    if (!array_key_exists($contoValido->iban, $ibans)) {
                        $ibans[$contoValido->iban] = [$istanza];
                    } else {
                        $ibans[$contoValido->iban][] = $istanza;
                        if (!array_key_exists($contoValido->iban, $ibanRipetuti))
                            $ibanRipetuti[] = $contoValido->iban;
                    }
                }
            }
            $out = [];
            foreach ($ibanRipetuti as $ibanRipetuto)
                $out[$ibanRipetuto] = $ibans[$ibanRipetuto];
            $ibanRipetuti = $out;
        }
        return $this->render('pagamenti', [
            "mese" => $mese,
            "anno" => $anno,
            "result" => $result !== null ? ($result === "" ? "🆗Tutto ok! ✔️" : $result) : "",
            "ibanRipetuti" => isset($vars['verifica-iban']) ? $ibanRipetuti : null,
        ]);
    }

}
