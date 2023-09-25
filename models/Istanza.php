<?php

namespace app\models;

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
        return Isee::find()->where(['id_istanza' => $this->id, 'valido' => 1])->orderBy(['data_presentazione' => SORT_DESC])->one();
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

    public function getStatoRecupero()
    {
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
            $importoRecuperi += $ricovero->getImportoRicovero();
        }
        return ($importoRecuperi !== 0) ? ("<div>Recuperare</div><span class='badge bg-warning text-dark h6'>" . Yii::$app->formatter->asCurrency($importoRecuperi) . "</span>") :
            "<span class='badge bg-success'>OK</span>";
    }

    public function haRicoveriDaRecuperare()
    {
        return Ricovero::find()->where(['id_istanza' => $this->id, 'id_recupero' => null, 'contabilizzare' => 1])->count() > 0;
    }

    public function haRecuperiInCorso()
    {
        return Recupero::find()->where(['id_istanza' => $this->id, 'chiuso' => 0])->count() > 0;
    }

    public function getContoValido()
    {
        return Conto::find()->where(['id_istanza' => $this->id, 'attivo' => 1])->one();
    }

    public static function getNumIstanzeAttive()
    {
        return Istanza::find()->where(['attivo' => 1])->count();
    }

    public static function getNumDecedutiDaLiquidare()
    {
        return Istanza::find()->where(['chiuso' => false])->andWhere(['IS NOT', 'data_decesso', null])->andWhere(['liquidazione_decesso_completata' => false])->count();
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

    public function getLastMovimentoBancario($data = null)
    {
        if (!$data)
            return Movimento::find()->innerJoin('conto c', 'movimento.id_conto = c.id')->where(['c.id_istanza' => $this->id, 'movimento.is_movimento_bancario' => true])->orderBy(['periodo_a' => SORT_DESC])->one();
        else
            return Movimento::find()->innerJoin('conto c', 'movimento.id_conto = c.id')->where(['c.id_istanza' => $this->id, 'movimento.is_movimento_bancario' => true])->andWhere(['=', 'data', $data])->one();
    }

    public function cancellaMovimentiCollegati()
    {
        $movimenti = Movimento::find()->innerJoin('conto c', 'movimento.id_conto = c.id')->where(['c.id_istanza' => $this->id])->all();
        foreach ($movimenti as $movimento) {
            $movimento->delete();
        }
    }

    public function getProssimoImporto()
    {
        if ($this->isInAlert())
            return null;
        else {
            /* @var $lastIsee Isee */
            $totale = ($this->getLastIseeType() === IseeType::MAGGIORE_25K) ? ImportoBase::MAGGIORE_25K_V1 : ImportoBase::MINORE_25K_V1;
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

    public function getDifferenzaUltimoImportoArray()
    {
        $op = $this->isInAlert();
        $lastMovimento = $this->getLastMovimentoBancario(Movimento::getDataUltimoPagamento());
        $prossimoImporto = $this->getProssimoImporto();
        $differenza = $this->getProssimoImporto() - ($lastMovimento ? $lastMovimento->importo : 0.0);
        return [
            'alert' => $op != null,
            'presenteScorsoMese' => $lastMovimento !== null,
            'importo' => ($prossimoImporto <= 0.0 || !$this->attivo) ? 0.0 : $prossimoImporto,
            'importoPrecedente' => ($lastMovimento ? $lastMovimento->importo : 0),
            'differenza' => $differenza,
            'op' => $op ?? (($prossimoImporto <= 0.0 || !$this->attivo) ? 'ELIMINARE<br /> PROSSIMO IMPORTO 0'
                    : ($differenza != 0.0 ? ($lastMovimento !== null ? "AGGIORNARE IMPORTO": "AGGIUNGERE E AGGIORNARE IMPORTO") : "AGGIUNGERE")),
            'recupero' => $this->haRecuperiInCorso()
        ];
    }

    public function isInAlert()
    {
        $out = null;
        if (!$this->attivo || $this->chiuso)
            $out .= "NON ATTIVO - ";
        if ($this->data_decesso)
            $out .= "DECEDUTO - ";
        if (!$this->patto_di_cura)
            $out .= "MANCA PATTO - ";
        if ($this->getLastIseeType() == IseeType::NO_ISEE)
            $out .= "MANCA ISEE - ";
        if ($this->rinuncia)
            $out .= "RINUNCIA - ";
        if ($this->haRicoveriInCorso())
            $out .= "ATTUALMENTE RICOVERATO - ";
        return $out ? substr($out, 0, strlen($out) - 3) : null;
    }
}
