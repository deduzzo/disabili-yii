<?php

namespace app\models;

use app\helpers\Utils;
use app\models\enums\DatiTipologia;
use app\models\enums\ImportoBase;
use app\models\enums\IseeType;
use Carbon\Carbon;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "istanza".
 *
 * @property int $id
 * @property string|null $data_inserimento
 * @property bool $riconosciuto
 * @property string|null $classe_disabilita
 * @property string|null $data_riconoscimento
 * @property bool $patto_di_cura
 * @property string|null $data_firma_patto
 * @property bool $attivo
 * @property string|null $data_decesso
 * @property bool|null $liquidazione_decesso_completata
 * @property string|null $data_liquidazione_decesso
 * @property bool $chiuso
 * @property bool $rinuncia
 * @property string|null $data_chiusura
 * @property string|null $nota_chiusura
 * @property string|null $rawdata_json
 * @property string|null $note
 * @property int $id_anagrafica_disabile
 * @property int $id_distretto
 * @property int $id_gruppo
 * @property int|null $id_caregiver
 *
 * @property Anagrafica $anagraficaDisabile
 * @property Anagrafica $caregiver
 * @property Conto[] $contos
 * @property Distretto $distretto
 * @property Documento[] $documentos
 * @property Gruppo $gruppo
 * @property Isee[] $isees
 * @property Recupero[] $recuperos
 * @property Ricovero[] $ricoveros
 */
class Istanza extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'istanza';
    }

    public static function verificaContiMancantiIstanzeAttive($fix = false)
    {
        $istanzeAttive = Istanza::findAll(['attivo' => true]);
        $out = "";
        foreach ($istanzeAttive as $istanza) {
            $contoValido = $istanza->getContoValido();
            if (!$contoValido && !$istanza->haRicoveriInCorso()) {
                if ($fix)
                    $istanza->fixConto();
                $out .= "<div><b>#" . $istanza->id . " " . $istanza->getNominativoDisabile() . "</b> CON CONTO NON VALIDO".($fix ? "-> FIXATO OK" : " non fixato")."</div><br />";
            }
        }
        return $out;
    }

    public static function getAllIstanzeAttiveIdNominativo()
    {
        return Istanza::find()
            ->select([
                'istanza.id',
                'nominativo' => 'CONCAT(istanza.id, " - ", a.cognome, " ", a.nome)'
            ])
            ->innerJoin('anagrafica a', 'a.id = istanza.id_anagrafica_disabile')
            ->where(['istanza.attivo' => true])
            ->asArray()
            ->all();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data_inserimento', 'data_riconoscimento', 'data_firma_patto', 'data_decesso', 'data_liquidazione_decesso', 'data_chiusura'], 'safe'],
            [['riconosciuto', 'patto_di_cura', 'attivo', 'liquidazione_decesso_completata', 'chiuso', 'rinuncia'], 'boolean'],
            [['classe_disabilita', 'nota_chiusura', 'rawdata_json', 'note'], 'string'],
            [['id_anagrafica_disabile', 'id_distretto', 'id_gruppo'], 'required'],
            [['id_anagrafica_disabile', 'id_distretto', 'id_gruppo', 'id_caregiver'], 'integer'],
            [['id_anagrafica_disabile'], 'exist', 'skipOnError' => true, 'targetClass' => Anagrafica::class, 'targetAttribute' => ['id_anagrafica_disabile' => 'id']],
            [['id_caregiver'], 'exist', 'skipOnError' => true, 'targetClass' => Anagrafica::class, 'targetAttribute' => ['id_caregiver' => 'id']],
            [['id_distretto'], 'exist', 'skipOnError' => true, 'targetClass' => Distretto::class, 'targetAttribute' => ['id_distretto' => 'id']],
            [['id_gruppo'], 'exist', 'skipOnError' => true, 'targetClass' => Gruppo::class, 'targetAttribute' => ['id_gruppo' => 'id']],
            ['id_anagrafica_disabile', 'validateActiveAnagrafica'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'data_inserimento' => 'Data Inserimento',
            'riconosciuto' => 'Riconosciuto',
            'classe_disabilita' => 'Classe Disabilita',
            'data_riconoscimento' => 'Data Riconoscimento',
            'patto_di_cura' => 'Patto Di Cura',
            'data_firma_patto' => 'Data Firma Patto',
            'attivo' => 'Attivo',
            'data_decesso' => 'Data Decesso',
            'liquidazione_decesso_completata' => 'Liquidazione Decesso Completata',
            'data_liquidazione_decesso' => 'Data Liquidazione Decesso',
            'chiuso' => 'Chiuso',
            'rinuncia' => 'Rinuncia',
            'data_chiusura' => 'Data Chiusura',
            'nota_chiusura' => 'Nota Chiusura',
            'rawdata_json' => 'Rawdata Json',
            'note' => 'Note',
            'id_anagrafica_disabile' => 'Id Anagrafica Disabile',
            'id_distretto' => 'Id Distretto',
            'id_gruppo' => 'Id Gruppo',
            'id_caregiver' => 'Id Caregiver',
        ];
    }

    public function validateActiveAnagrafica($attribute, $params, $validator)
    {
        if ($this->attivo && static::find()->where([
                'id_anagrafica_disabile' => $this->id_anagrafica_disabile,
                'attivo' => true,
            ])->andWhere(['<>', 'id', $this->id])->exists()) {
            $this->addError($attribute, 'Non può esistere un\'altra istanza attiva con lo stesso id_anagrafica_disabile.');
        }
    }


    /**
     * Gets query for [[AnagraficaDisabile]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAnagraficaDisabile()
    {
        return $this->hasOne(Anagrafica::class, ['id' => 'id_anagrafica_disabile']);
    }

    /**
     * Gets query for [[Caregiver]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCaregiver()
    {
        return $this->hasOne(Anagrafica::class, ['id' => 'id_caregiver']);
    }

    /**
     * Gets query for [[Contos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContos()
    {
        return $this->hasMany(Conto::class, ['id_istanza' => 'id']);
    }

    /**
     * Gets query for [[Distretto]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDistretto()
    {
        return $this->hasOne(Distretto::class, ['id' => 'id_distretto']);
    }

    /**
     * Gets query for [[Documentos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentos()
    {
        return $this->hasMany(Documento::class, ['id_istanza' => 'id']);
    }

    /**
     * Gets query for [[Gruppo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGruppo()
    {
        return $this->hasOne(Gruppo::class, ['id' => 'id_gruppo']);
    }

    /**
     * Gets query for [[Isees]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIsees()
    {
        return $this->hasMany(Isee::class, ['id_istanza' => 'id']);
    }


    /**
     * Gets query for [[Recuperos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecuperos()
    {
        return $this->hasMany(Recupero::class, ['id_istanza' => 'id']);
    }

    /**
     * Gets query for [[Ricoveros]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRicoveros()
    {
        return $this->hasMany(Ricovero::class, ['id_istanza' => 'id']);
    }

    private function getLastIsee()
    {
        return Isee::find()->where(['id_istanza' => $this->id, 'valido' => 1,'verificato' => 1])->orderBy(['data_presentazione' => SORT_DESC])->one();
    }


    public static function getTotaliAttivi(string $tipoDato, $distretto = null, $gruppo = null)
    {
        $query = Istanza::find()->
        innerJoin('anagrafica a', 'istanza.id_anagrafica_disabile = a.id')->
        where(['istanza.attivo' => true, 'istanza.chiuso' => false]);
        switch ($tipoDato) {
            case DatiTipologia::LISTA_TOTALI_ATTIVI_NON_CHIUSI:
                break;
            case DatiTipologia::LISTA_MINORI18:
                $query->andWhere('a.data_nascita > DATE_SUB(CURDATE(), INTERVAL 18 YEAR)');
                break;
            case DatiTipologia::LISTA_MAGGIORI_18:
                $query->andWhere('a.data_nascita <= DATE_SUB(CURDATE(), INTERVAL 18 YEAR)');
                break;
            case DatiTipologia::LISTA_NO_DATA_NASCITA:
                $query->andWhere(['a.data_nascita' => null]);
                break;
            case DatiTipologia::LISTA_MAGGIORI_25K:
                $query->innerJoin(['isee is', '']);
                break;
        }
        return $query->count();
    }

    public function getTotaleAnnuoDovutoCorretto()
    {
        $lastIsee = $this->getLastIseeType();
        if ($lastIsee !== IseeType::NO_ISEE) {
            $meseUltimoPagamento = Carbon::createFromFormat('Y-m-d', Movimento::getDataUltimoPagamento())->month;
            $totaleTeorico = $meseUltimoPagamento * ($lastIsee === IseeType::MAGGIORE_25K ? ImportoBase::MAGGIORE_25K_V1 : ImportoBase::MINORE_25K_V1);
            //$totaleRecuperi = Movimento::find()->innerJoin('conto c', 'c.id = movimento.id_conto')->where(['c.id_istanza' => $this->id])->andWhere(["IS NOT", "id_recupero", null])->andWhere(['>=','movimento.data',Carbon::now()->startOfYear()->format('Y-m-d')])->sum('importo');
            //$ricoveriAttivi = $this->getImportoRicoveriDaContabilizzare();
            return $totaleTeorico;
        } else return null;
    }

    public function getTotaleEffettivoAnnuo()
    {
        return Movimento::find()->innerJoin('conto c', 'c.id = movimento.id_conto')->where(['c.id_istanza' => $this->id])->andWhere(["is_movimento_bancario" => true])->andWhere(['>=', 'movimento.data', Carbon::now()->startOfYear()->format('Y-m-d')])->sum('importo');
    }


    public function getLastIseeType()
    {
        if ($this->anagraficaDisabile->isMinorenne())
            return IseeType::MINORE_25K;
        else {
            $last = $this->getLastIsee();
            if ($last)
                return ($last->maggiore_25mila) ? IseeType::MAGGIORE_25K : IseeType::MINORE_25K;
            else
                return IseeType::NO_ISEE;
        }
    }

    public function getImportoRecuperi() {
        $importoRecuperi = 0;
        $recuperiInCorso = Recupero::find()->where(['id_istanza' => $this->id, 'chiuso' => 0])->all();
        $ricoveriDaRecuperare = Ricovero::find()->where(['id_istanza' => $this->id, 'id_recupero' => null, 'contabilizzare' => 1])->all();
        foreach ($recuperiInCorso as $recupero) {
            $importoRecuperi += $recupero->importo;
            $recuperato = Movimento::find()->where(['id_recupero' => $recupero->id, 'tornato_indietro' => 0])->all();
            foreach ($recuperato as $importo) {
                $importoRecuperi -= $importo->importo;
            }
        }
        foreach ($ricoveriDaRecuperare as $ricovero) {
            $importoRecuperi -= $ricovero->getImportoRicovero();
        }
        return $importoRecuperi;
    }

    public function getStatoRecupero()
    {
        $importoRecuperi = $this->getImportoRecuperi();
        return ($importoRecuperi !== 0) ? ("<div>Recuperare</div><span class='badge bg-warning text-dark h6'>" . Yii::$app->formatter->asCurrency($importoRecuperi) . "</span>") :
            "<span class='badge bg-success'>OK</span>";
    }

    public function haRicoveriDaRecuperare()
    {
        return Ricovero::find()->where(['id_istanza' => $this->id, 'id_recupero' => null, 'contabilizzare' => 1])->count() > 0;
    }

    public function haRecuperiInCorso()
    {
        return Recupero::find()->where(['id_istanza' => $this->id, 'chiuso' => false])->count() > 0;
    }

    public function getUltimoContoAttivoValidato() {
        return Conto::find()->where(['id_istanza' => $this->id, 'attivo' => true, 'validato' => true])->one();
    }

    public function getContoValido($lastIfEmpty = false, $fakeIfNull = false)
    {
        $conto = $this->getUltimoContoAttivoValidato();
        if (!$conto || $this->haCambioIbanInCorso())
            $conto = Conto::find()->where(['id_istanza' => $this->id, 'attivo' => true, 'validato' => false])->one();
        if (!$conto && $lastIfEmpty) {
            // get the last conto used in the last payment
            try {
                $conto = $this->getLastMovimentoBancario()->conto;
            } catch (\Exception $e) {
                if ($fakeIfNull) {
                    $conto = new Conto();
                    $conto->iban = "IBANXXXXXXXXXXXXXXXXXXXX";
                    $conto->intestatario = "Erede";
                    $conto->attivo = true;
                    $conto->validato = true;
                    $conto->id_istanza = $this->id;
                    $conto->save();
                }
            }
        }
        return $conto;
    }

    public function haContoValido()
    {
        return $this->getContoValido() !== null || $this->haCambioIbanInCorso();
    }

    public static function getNumIstanzeAttive()
    {
        return Istanza::find()->where(['attivo' => 1])->count();
    }

    public static function getNumDecedutiDaLiquidare()
    {
        return Istanza::find()->where(['chiuso' => false])->andWhere(['IS NOT', 'data_decesso', null])->andWhere(['liquidazione_decesso_completata' => false])->count();
    }

    /**
     * Returns the age distribution of active disabled people
     *
     * @return array An array with the count of disabled people in each age group
     */
    public static function getAgeDistribution()
    {
        $distribution = [
            'under18' => 0,
            'between18and65' => 0,
            'over65' => 0,
            'total' => 0
        ];

        $activeInstances = Istanza::find()
            ->where(['attivo' => 1])
            ->with('anagraficaDisabile')
            ->all();

        foreach ($activeInstances as $instance) {
            $age = $instance->anagraficaDisabile->getEta();

            if ($age === null) {
                continue; // Skip if age cannot be calculated
            }

            $distribution['total']++;

            if ($age < 18) {
                $distribution['under18']++;
            } elseif ($age >= 18 && $age <= 65) {
                $distribution['between18and65']++;
            } else {
                $distribution['over65']++;
            }
        }

        return $distribution;
    }

    /**
     * Returns the ISEE distribution of active disabled people
     *
     * @return array An array with the count of disabled people in each ISEE group
     */
    public static function getIseeDistribution()
    {
        $distribution = [
            'maggiore25k' => 0,
            'minore25k' => 0,
            'no_isee' => 0,
            'total' => 0
        ];

        $activeInstances = Istanza::find()
            ->where(['attivo' => 1])
            ->all();

        foreach ($activeInstances as $instance) {
            $iseeType = $instance->getLastIseeType();
            $distribution['total']++;

            if ($iseeType === IseeType::MAGGIORE_25K) {
                $distribution['maggiore25k']++;
            } elseif ($iseeType === IseeType::MINORE_25K) {
                $distribution['minore25k']++;
            } else {
                $distribution['no_isee']++;
            }
        }

        return $distribution;
    }

    /**
     * Returns the distribution of active disabled people by district
     *
     * @return array An array with district data including counts by age and ISEE
     */
    public static function getDistrictDistribution()
    {
        $districts = Distretto::find()->all();
        $distribution = [];

        foreach ($districts as $district) {
            $distribution[$district->id] = [
                'name' => $district->nome,
                'total' => 0,
                'under18' => 0,
                'adults' => 0,
                'maggiore25k' => 0,
                'minore25k' => 0
            ];
        }

        $activeInstances = Istanza::find()
            ->where(['attivo' => 1])
            ->with(['anagraficaDisabile', 'distretto'])
            ->all();

        foreach ($activeInstances as $instance) {
            $districtId = $instance->id_distretto;
            $age = $instance->anagraficaDisabile->getEta();
            $iseeType = $instance->getLastIseeType();

            if (!isset($distribution[$districtId])) {
                continue; // Skip if district not found
            }

            $distribution[$districtId]['total']++;

            if ($age !== null) {
                if ($age < 18) {
                    $distribution[$districtId]['under18']++;
                } else {
                    $distribution[$districtId]['adults']++;
                }
            } else {
                $distribution[$districtId]['adults']++; // Default to adult if age unknown
            }

            if ($iseeType === IseeType::MAGGIORE_25K) {
                $distribution[$districtId]['maggiore25k']++;
            } elseif ($iseeType === IseeType::MINORE_25K) {
                $distribution[$districtId]['minore25k']++;
            }
        }

        // Remove districts with no active instances
        foreach ($distribution as $id => $data) {
            if ($data['total'] === 0) {
                unset($distribution[$id]);
            }
        }

        return $distribution;
    }

    public function haRicoveriInCorso()
    {
        return Ricovero::find()->where(['contabilizzare' => 1, 'id_istanza' => $this->id])->andWhere(['IS', 'a', null])->count() > 0;
    }

    public function getNominativoDisabile()
    {
        if ($this->anagraficaDisabile->nome)
            return $this->anagraficaDisabile->cognome . ' ' . $this->anagraficaDisabile->nome;
        else
            return $this->anagraficaDisabile->cognome_nome;
    }

    public function getProblemiLiquidazioneDecesso()
    {
        $out = null;
        if (!$this->patto_di_cura)
            $out .= "MANCA PATTO - ";
        if ($this->getLastIseeType() == IseeType::NO_ISEE)
            $out .= "MANCA ISEE - ";
        if ($this->rinuncia)
            $out .= "RINUNCIA - ";
        if (!$this->haContoValido())
            $out .= "MANCA CONTO - ";
        return $out ? substr($out, 0, strlen($out) - 3) : null;
    }

    public function getGiorniResiduoDecesso()
    {
        $ultimoMovimentoBancario = $this->getLastMovimentoBancario();
        $ultimoMovimentoContabile = $this->getlastMovimentoContabile();
        $maggioredeiduemovimenti = $ultimoMovimentoBancario && $ultimoMovimentoContabile ? $ultimoMovimentoBancario->data > $ultimoMovimentoContabile->data ? $ultimoMovimentoBancario : $ultimoMovimentoContabile : ($ultimoMovimentoBancario ? $ultimoMovimentoBancario : $ultimoMovimentoContabile);
        $dataUltimoMovimentoBancario = $maggioredeiduemovimenti ? Carbon::createFromFormat('Y-m-d', $maggioredeiduemovimenti->data)->endOfMonth()->format('Y-m-d') : null;
        // add one day to $dataUltimoMovimentoBancario
        $giornoDopoDataUltimoPagamento = $dataUltimoMovimentoBancario ? Carbon::createFromFormat('Y-m-d', $dataUltimoMovimentoBancario)->addDay()->format('Y-m-d') : null;
        $dataInizioDovuta = $giornoDopoDataUltimoPagamento ?? $this->gruppo->data_inizio_beneficio;
        $movimentiTornatiIndietro = $this->getPagamentiTornatiIndietro($dataUltimoMovimentoBancario);
        $restituire = false;
        if (Carbon::createFromFormat('Y-m-d', $dataInizioDovuta)->isAfter(Carbon::createFromFormat('Y-m-d', $this->data_decesso)) && $dataUltimoMovimentoBancario)
            $restituire = true;
        // add une day to $this->data_decesso
        $totaleGiorniDovuti = Utils::getNumGiorni(!$restituire ? $dataInizioDovuta : Carbon::createFromFormat('Y-m-d', $this->data_decesso)->addDay()->format('Y-m-d'), !$restituire ? $this->data_decesso : $dataInizioDovuta, true);
        if ($totaleGiorniDovuti === null)
            return 0;
        $totale = ($totaleGiorniDovuti['mesi'] * 30 + $totaleGiorniDovuti['giorni']);
        return ($restituire ? -$totale : $totale);
    }

    public function getLastMovimentoBancario($data = null)
    {
        if (!$data)
            return Movimento::find()->innerJoin('conto c', 'movimento.id_conto = c.id')->where(['c.id_istanza' => $this->id, 'movimento.is_movimento_bancario' => true, 'tornato_indietro' => false])->orderBy(['periodo_a' => SORT_DESC])->one();
        else
            return Movimento::find()->innerJoin('conto c', 'movimento.id_conto = c.id')->where(['c.id_istanza' => $this->id, 'movimento.is_movimento_bancario' => true, 'tornato_indietro' => false])->andWhere(['=', 'data', $data])->one();
    }

    public function getPagamentiTornatiIndietro($afterDate = null)
    {
        if (!$afterDate)
            return Movimento::find()->innerJoin('conto c', 'movimento.id_conto = c.id')->where(['c.id_istanza' => $this->id, 'movimento.is_movimento_bancario' => true, 'tornato_indietro' => true])->orderBy(['periodo_a' => SORT_DESC])->all();
        else
            return Movimento::find()->innerJoin('conto c', 'movimento.id_conto = c.id')->where(['c.id_istanza' => $this->id, 'movimento.is_movimento_bancario' => true, 'tornato_indietro' => true])->andWhere(['>=', 'data', $afterDate])->all();
    }

    public function cancellaMovimentiCollegati()
    {
        $movimenti = Movimento::find()->innerJoin('conto c', 'movimento.id_conto = c.id')->where(['c.id_istanza' => $this->id])->all();
        foreach ($movimenti as $movimento) {
            $movimento->delete();
        }
    }

    public function getProssimoImporto($includiNuovoMese = true, $skipDeceduto = false)
    {
        if ($this->isInAlert($skipDeceduto))
            return null;
        else {
            /* @var $lastIsee Isee */
            $totale = $includiNuovoMese ? (($this->getLastIseeType() === IseeType::MAGGIORE_25K) ? ImportoBase::MAGGIORE_25K_V1 : ImportoBase::MINORE_25K_V1) : 0;
            foreach ($this->recuperos as $recupero) {
                if (!$recupero->chiuso && !$recupero->annullato) {
                    if ($recupero->importo > 0)
                        $totale += $recupero->getProssimaRata();
                    else
                        $totale -= $recupero->getProssimaRata();
                }
            }
            $totale -= $this->getImportoRicoveriDaContabilizzare();
        }
        return $totale > 0 ? $totale : 0;
    }

    public function getImportoRicoveriDaContabilizzare()
    {
        $totale = 0;
        foreach ($this->ricoveros as $ricovero) {
            if ($ricovero->contabilizzare) {
                $totale += $ricovero->getImportoRicovero();
            }
        }
        return $totale;
    }

    public function getDifferenzaUltimoImportoArray($includiNuovoMese = true)
    {
        $lastMovimento = $this->getLastMovimentoBancario(Movimento::getDataUltimoPagamento());
        $prossimoImporto = $this->getProssimoImporto($includiNuovoMese);
        $differenza = $prossimoImporto - ($lastMovimento ? $lastMovimento->importo : 0.0);
        $hacambioiban = $this->haCambioIbanInCorso();
        $alert = $this->isInAlert();
        $haOmonimi = $this->haOmonimi();
        $op = $alert ?? (($prossimoImporto <= 0.0 || !$this->attivo) ? 'ELIMINARE<br /> PROSSIMO IMPORTO 0'
            : ($differenza != 0.0 ? ($lastMovimento !== null ? "AGGIORNARE IMPORTO" : "AGGIUNGERE <br />AGGIORNARE IMPORTO") : "")) .
        ($hacambioiban ? ("<br />VERIFICARE CAMBIO IBAN finale " . $this->finaleContoDaValidare()) : "");
        if ($op !== null && $op !== "" && $haOmonimi)
            $op .= "<br />ATTENZIONE! OMONIMI NEL DISTRETTO";
        return [
            'alert' => $alert != null,
            'presenteScorsoMese' => $lastMovimento !== null,
            'importo' => ($prossimoImporto <= 0.0 || !$this->attivo) ? 0.0 : $prossimoImporto,
            'importoPrecedente' => ($lastMovimento ? $lastMovimento->importo : 0),
            'differenza' => $differenza,
            'op' => $op,
            'recupero' => $this->haRecuperiInCorso(),
            'color' => $op ? 'danger' : ($differenza != 0.0 ? 'warning' : 'success')
        ];
    }

    public function finaleContoDaValidare()
    {
        $conto = Conto::find()->where(['id_istanza' => $this->id, 'attivo' => true, 'validato' => false])->one();
        // if exist return the latest 6 digit of $conto->iban
        return $conto ? substr($conto->iban, -6) : null;
    }

    public function isInAlert($skipDeceduto = false)
    {
        $out = null;
        if ((!$this->attivo || $this->chiuso) && !$skipDeceduto)
            $out .= "NON ATTIVO - ";
        if ($this->data_decesso && !$skipDeceduto)
            $out .= "DECEDUTO - ";
        if (!$this->patto_di_cura)
            $out .= "MANCA PATTO - ";
        if ($this->getLastIseeType() == IseeType::NO_ISEE)
            $out .= "MANCA ISEE - ";
        if ($this->rinuncia)
            $out .= "RINUNCIA - ";
        if ($this->haRicoveriInCorso())
            $out .= "ATTUALMENTE RICOVERATO - ";
        if (!$this->haContoValido())
            $out .= "MANCA CONTO - ";
        return $out ? substr($out, 0, strlen($out) - 3) : null;
    }

    public function getRecuperi($tipologia)
    {
        if (!($tipologia === Recupero::POSITIVO || $tipologia === Recupero::NEGATIVO))
            throw new \Exception("Tipologia non valida");
        $out = [];
        foreach ($this->recuperos as $recupero) {
            if ($recupero->importo > 0 && $tipologia === Recupero::POSITIVO && !$recupero->chiuso)
                $out[] = $recupero;
            else if ($recupero->importo < 0 && $tipologia === Recupero::NEGATIVO && !$recupero->chiuso)
                $out[] = $recupero;
        }
        return $out;
    }

    public function getRecuperiNegativiRateizzati()
    {
        $out = [];
        foreach ($this->getRecuperi(Recupero::NEGATIVO) as $recupero) {
            if ($recupero->rateizzato)
                $out[] = $recupero;
        }
        return $out;
    }

    public function getRecuperiNegativiNonRateizzati()
    {
        $out = [];
        foreach ($this->getRecuperi(Recupero::NEGATIVO) as $recupero) {
            if (!$recupero->rateizzato)
                $out[] = $recupero;
        }
        return $out;
    }

    public function getRicoveriDaContabilizzare()
    {
        $out = [];
        foreach ($this->ricoveros as $ricovero) {
            if ($ricovero->contabilizzare && $ricovero->a !== null)
                $out[] = $ricovero;
        }
        return $out;
    }

    public function getTotaleConguaglioDecesso() {
        $giorniResiduo = $this->getGiorniResiduoDecesso();
        $totaleRimanente=  $this->getImportoRecuperi();
        if ($giorniResiduo === null)
            return "-";
        else
            return ($this->getGiorniResiduoDecesso() * ((($this->getLastIseeType() === IseeType::MAGGIORE_25K) ? ImportoBase::MAGGIORE_25K_V1 : ImportoBase::MINORE_25K_V1) / 30)) + $totaleRimanente;
    }

    public function getDatiLiquidazioneDecesso() {
        $prossimoImportoResiduo = $this->getProssimoImporto(false,true);
        $importoRecuperi = $this->getImportoRecuperi();
        if ($prossimoImportoResiduo === null) {
            $alert = $this->isInAlert(true);
            return ["ok" => false, "descrizione" => $alert, "valore" => 0.0];
        }
        else {
            $problemiLiquidazioneDecesso = $this->getProblemiLiquidazioneDecesso();
            $giorniResiduo = $this->getGiorniResiduoDecesso();
            if ($problemiLiquidazioneDecesso)
                return "ALERT: " . $problemiLiquidazioneDecesso;
            else if ($giorniResiduo === null)
                return ["ok" => false, "descrizione" => "Non è possibile calcolare il totale", "valore" => 0.0];
            else {
                $val = $importoRecuperi + ($this->getGiorniResiduoDecesso() * ((($this->getLastIseeType() === IseeType::MAGGIORE_25K) ? ImportoBase::MAGGIORE_25K_V1 : ImportoBase::MINORE_25K_V1) / 30));
                return ["ok" => true, "descrizione" => Yii::$app->formatter->asCurrency($val), "valore" => $val];
            }
        }
    }

    public function verificaContabilitaMese($mese, $anno, $determina = null)
    {
        $movimentiIstanzaMese = null;
        if (!$determina) {
            $inizioMese = Carbon::createFromDate($anno, $mese, 1)->format('Y-m-d');
            $fineMese = Carbon::create($inizioMese)->endOfMonth()->format('Y-m-d');
            $movimentiIstanzaMese = Movimento::find()->innerJoin('conto c', 'c.id = movimento.id_conto')->where(['c.id_istanza' => $this->id])->andWhere(['>=', 'movimento.data', $inizioMese])->andWhere(['<=', 'movimento.data', $fineMese])->all();
        } else {
            $movimentiIstanzaMese = Movimento::find()->innerJoin('conto c', 'c.id = movimento.id_conto')->where(['c.id_istanza' => $this->id, 'id_determina' => $determina->id])->all();
        }

        $logico = 0;
        $reale = 0;
        $ibanLogico = null;
        $ibanReale = null;
        $utimaDataLogica = null;
        $ultimaDataReale = null;
        if ($this->attivo && count($movimentiIstanzaMese) === 0 && !$this->haRicoveriInCorso() && !$determina->non_ordinaria)
            $logico = ($this->getLastIseeType() === IseeType::MAGGIORE_25K ? ImportoBase::MAGGIORE_25K_V1 : ImportoBase::MINORE_25K_V1);
        foreach ($movimentiIstanzaMese as $movimento) {
            if ($movimento->is_movimento_bancario && $movimento->escludi_contabilita) {
                if (!$movimento->tornato_indietro) {
                    if (!$ultimaDataReale)
                        $ultimaDataReale = $movimento->data;
                    if (Carbon::createFromFormat('Y-m-d', $movimento->data)->isAfter(Carbon::createFromFormat('Y-m-d', $ultimaDataReale))) {
                        $ultimaDataReale = $movimento->data;
                        $ibanReale = $movimento->conto->iban;
                    }
                    $reale += $movimento->importo;
                }
            } else {
                if (!$utimaDataLogica)
                    $utimaDataLogica = $movimento->data;
                if (Carbon::createFromFormat('Y-m-d', $movimento->data)->isAfter(Carbon::createFromFormat('Y-m-d', $utimaDataLogica))) {
                    $utimaDataLogica = $movimento->data;
                    $ibanLogico = $movimento->conto->iban;
                }
                $logico += $movimento->importo;
            }
        }
        $contoOk = true;
        if (isset($determina->non_ordinaria) && (($determina->non_ordinaria && ($reale - $logico != 0)) || !$determina->non_ordinaria))
            $contoOk = $ibanReale === $ibanLogico;

        return ['contoOk' =>  $contoOk, 'tot' => (($reale > 0 || $logico > $reale) ? ($reale - $logico) : 0)];
    }

    public function finalizzaMensilita($idDetermina, $pagaMeseCorrente = true, $chiusuraDeceduto = false)
    {
        $errors = [];
        if (!$this->haRicoveriInCorso()) {
            $contoValido = $this->getContoValido();
            $recuperiPositivi = $this->getRecuperi(Recupero::POSITIVO);
            $lastIseeType = $this->getLastIseeType();
            $determina = Determina::findOne($idDetermina);
            $importoSurplus = 0;
            if ($pagaMeseCorrente) {
                // caricamento importo base
                $movimento = new Movimento();
                $movimento->id_conto = $contoValido->id;
                $movimento->contabilizzare = false;
                $movimento->is_movimento_bancario = false;
                $movimento->data = $determina->data ?? Carbon::now()->format('Y-m-d');
                $movimento->periodo_da = Carbon::createFromDate(Carbon::parse($determina->pagamenti_a)->startOfMonth())->format('Y-m-d');
                $movimento->periodo_a = Carbon::createFromDate(Carbon::parse($determina->pagamenti_a)->endOfMonth())->format('Y-m-d');
                $movimento->id_determina = $idDetermina;
                $movimento->note = "Beneficio contabile di " . Carbon::parse($determina->pagamenti_a)->locale('it')->monthName . ' ' . Carbon::parse($determina->pagamenti_a)->year;
                $movimento->importo = ($lastIseeType === IseeType::MAGGIORE_25K ? ImportoBase::MAGGIORE_25K_V1 : ImportoBase::MINORE_25K_V1);
                $movimento->save();
                if ($movimento->errors)
                    $errors = array_merge($movimento->errors, $errors);
                $importoSurplus += ($lastIseeType === IseeType::MAGGIORE_25K ? ImportoBase::MAGGIORE_25K_V1 : ImportoBase::MINORE_25K_V1);
            }
            if ($chiusuraDeceduto) {
                $chiusura = $this->getDatiLiquidazioneDecesso();
                if ($chiusura["ok"] === true) {
                    $movimento = new Movimento();
                    $movimento->id_conto = $contoValido->id;
                    $movimento->contabilizzare = false;
                    $movimento->is_movimento_bancario = false;
                    $movimento->data = $determina->data ?? Carbon::now()->format('Y-m-d');
                    $movimento->periodo_da = $determina->pagamenti_da;
                    $movimento->periodo_a = $determina->pagamenti_a;
                    $movimento->id_determina = $idDetermina;
                    $movimento->note = "Chiusura decesso";
                    $movimento->importo = $chiusura["valore"];
                    $movimento->save();
                    if ($movimento->errors)
                        $errors = array_merge($movimento->errors, $errors);
                    $importoSurplus += $chiusura["valore"];
                }
            }
            foreach ($recuperiPositivi as $recuperPos) {
                $movimento = new Movimento();
                $movimento->id_conto = $contoValido->id;
                $movimento->contabilizzare = false;
                $movimento->id_recupero = $recuperPos->id;
                $movimento->is_movimento_bancario = false;
                $movimento->data = $determina->data ?? Carbon::now()->format('Y-m-d');
                $movimento->id_determina = $idDetermina;
                // put in $mese the month of the date $movimento-data (format 'Y-m-d')  in italian
                if ($recuperPos->rateizzato) {
                    $movimento->importo = -$recuperPos->getProssimaRata();
                    $movimento->num_rata = $recuperPos->getNumeroProssimaRata();
                    $importoSurplus += $movimento->importo;
                    if ($recuperPos->getRateMancanti() == 1) {
                        $recuperPos->chiuso = true;
                        $recuperPos->save();
                        if ($recuperPos->errors)
                            $errors = array_merge($recuperPos->errors, $errors);
                    }
                } else {
                    $movimento->importo = $recuperPos->importo;
                    $importoSurplus += $movimento->importo;
                    $recuperPos->chiuso = true;
                    $recuperPos->save();
                    if ($recuperPos->errors)
                        $errors = array_merge($recuperPos->errors, $errors);
                }
                $movimento->save();
                if ($movimento->errors)
                    $errors = array_merge($movimento->errors, $errors);
            }
            // priorità i recuperi negativi rateizzati
            $ricoveri = $this->getRicoveriDaContabilizzare();
            foreach ($ricoveri as $ricoveroDaCont) {
                $recupero = new Recupero();
                $recupero->id_istanza = $this->id;
                $recupero->importo = -$ricoveroDaCont->getImportoRicovero();
                $recupero->rateizzato = false;
                $recupero->save();
                if ($recupero->errors)
                    $errors = array_merge($recupero->errors, $errors);
                $ricoveroDaCont->id_recupero = $recupero->id;
                $ricoveroDaCont->contabilizzare = false;
                $ricoveroDaCont->save();
                if ($ricoveroDaCont->errors)
                    $errors = array_merge($ricoveroDaCont->errors, $errors);
            }
            if (count($ricoveri) > 0)
                $this->refresh();

            foreach ($this->getRecuperiNegativiRateizzati() as $recuperoNegRat) {
                if ($importoSurplus > 0) {
                    $importoOriginale = $recuperoNegRat->getProssimaRata();
                    $movimento = new Movimento();
                    $movimento->id_conto = $contoValido->id;
                    $movimento->contabilizzare = false;
                    $movimento->id_recupero = $recuperoNegRat->id;
                    $movimento->is_movimento_bancario = false;
                    $movimento->data = $determina->data ?? Carbon::now()->format('Y-m-d');
                    $movimento->id_determina = $idDetermina;
                    $movimento->importo = abs($recuperoNegRat->getProssimaRata()) < abs($importoSurplus) ? -abs($recuperoNegRat->getProssimaRata()) : -$importoSurplus;
                    $movimento->num_rata = $recuperoNegRat->getNumeroProssimaRata();
                    $importoSurplus -= abs($movimento->importo);
                    if (abs($movimento->importo) < $importoOriginale && $importoSurplus == 0)
                        $recuperoNegRat->num_rate += 1;
                    else if ($recuperoNegRat->getRateMancanti() == 1) {
                        $recuperoNegRat->chiuso = true;
                    }
                    $recuperoNegRat->save();
                    if ($recuperoNegRat->errors)
                        $errors = array_merge($recuperoNegRat->errors, $errors);
                    $movimento->save();
                    if ($movimento->errors)
                        $errors = array_merge($movimento->errors, $errors);
                }
            }
            foreach ($this->getRecuperiNegativiNonRateizzati() as $recuperoNegNonRateizzato) {
                if ($importoSurplus > 0) {
                    $movimento = new Movimento();
                    $movimento->id_conto = $contoValido->id;
                    $movimento->contabilizzare = false;
                    $movimento->id_recupero = $recuperoNegNonRateizzato->id;
                    $movimento->is_movimento_bancario = false;
                    $movimento->data = $determina->data ?? Carbon::now()->format('Y-m-d');
                    $movimento->id_determina = $idDetermina;
                    $movimento->importo = abs($recuperoNegNonRateizzato->getImportoResiduo()) < abs($importoSurplus) ? -abs($recuperoNegNonRateizzato->getImportoResiduo()) : -$importoSurplus;
                    $movimento->save();
                    if ($movimento->errors)
                        $errors = array_merge($movimento->errors, $errors);
                    $importoSurplus -= abs($movimento->importo);
                    $recuperoNegNonRateizzato->refresh();
                    if ($recuperoNegNonRateizzato->getImportoResiduo() > 0 && $importoSurplus == 0) {
                        $recuperoNegNonRateizzato->rateizzato = true;
                        $recuperoNegNonRateizzato->num_rate = 2;
                        $movimento->num_rata = 1;
                        $movimento->save();
                    } else
                        $recuperoNegNonRateizzato->chiuso = true;

                    $recuperoNegNonRateizzato->save();
                    if ($recuperoNegNonRateizzato->errors)
                        $errors = array_merge($recuperoNegNonRateizzato->errors, $errors);
                }
            }
            if (count($errors) > 0)
                print_r($errors);
        }
    }

    public function contoInFaseDiValidazione()
    {
        $conti = Conto::find()->where(['id_istanza' => $this->id, 'attivo' => false, 'validato' => false])->all();
        return count($conti) > 0;
    }

    public function haCambioIbanInCorso()
    {
        return Conto::find()->where(['id_istanza' => $this->id, 'attivo' => true, 'validato' => false])->count() > 0;
    }

    public function getIseeTypeInDate(Carbon $date)
    {
        $isee = Isee::find()->where(['id_istanza' => $this->id])->andWhere(['<=', 'data_presentazione', $date->format('Y-m-d')])->orderBy(['data_presentazione' => SORT_DESC])->one();
        if ($isee) {
            return $isee->maggiore_25mila ? IseeType::MAGGIORE_25K : IseeType::MINORE_25K;
        } else {
            $firstIsee = Isee::find()->where(['id_istanza' => $this->id])->orderBy(['data_presentazione' => SORT_ASC])->one();
            if ($firstIsee)
                return $firstIsee->maggiore_25mila ? IseeType::MAGGIORE_25K : IseeType::MINORE_25K;
            else
                if ($this->anagraficaDisabile->getEta() < 18)
                    return IseeType::MINORE_25K;
                else
                    return IseeType::NO_ISEE;
        }
    }

    public function haOmonimi(): bool
    {
        // SELECT ISTANZE WITH SAME NAME AND COGNOME AND DIFFERENT ID
        return (new Query())->select('id_anagrafica_disabile')
                ->from('istanza i, anagrafica a')->where('i.id_anagrafica_disabile = a.id')
                ->andWhere(['!=', 'i.id', $this->id])->andWhere(['i.attivo' => 1])
                ->andWhere(['a.cognome' => $this->anagraficaDisabile->cognome, 'a.nome' => $this->anagraficaDisabile->nome])
                ->andWhere(['i.id_distretto' => $this->id_distretto])->count() > 0;
    }

    public function inChiusura()
    {
        return (!$this->chiuso && $this->data_chiusura !== null);
    }

    public function getlastMovimentoContabile($data = null) {
        if (!$data)
            return Movimento::find()->innerJoin('conto c', 'movimento.id_conto = c.id')->where(['c.id_istanza' => $this->id, 'movimento.is_movimento_bancario' => false, 'tornato_indietro' => false])->orderBy(['periodo_a' => SORT_DESC])->one();
        else
            return Movimento::find()->innerJoin('conto c', 'movimento.id_conto = c.id')->where(['c.id_istanza' => $this->id, 'movimento.is_movimento_bancario' => false, 'tornato_indietro' => false])->andWhere(['=', 'data', $data])->one();
    }

    public function fixConto() {
        $ultimoMovimentoBancario = $this->getLastMovimentoBancario();
        $ultimoMovimentoLogico = $this->getlastMovimentoContabile();
        if ($ultimoMovimentoBancario && $ultimoMovimentoLogico) {
            $conto = $ultimoMovimentoBancario->conto;
            $ultimoMovimentoLogico->id_conto = $conto->id;
            $ultimoMovimentoLogico->save();
            $conto->attivo = true;
            $conto->save();
        }
    }
}
