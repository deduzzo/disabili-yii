<?php

namespace app\models;

use app\models\enums\IseeType;
use Yii;

/**
 * This is the model class for table "istanza".
 *
 * @property int $id
 * @property string|null $data_inserimento
 * @property int $riconosciuto
 * @property string|null $classe_disabilita
 * @property string|null $data_riconoscimento
 * @property int|null $patto_di_cura
 * @property string|null $data_firma_patto
 * @property int $attivo
 * @property string|null $data_decesso
 * @property int|null $liquidazione_decesso_completata
 * @property string|null $data_liquidazione_decesso
 * @property int|null $chiuso
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
 * @property IstanzaLog[] $istanzaLogs
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
            [['riconosciuto', 'patto_di_cura', 'attivo', 'liquidazione_decesso_completata', 'chiuso', 'id_anagrafica_disabile', 'id_distretto', 'id_gruppo', 'id_caregiver'], 'integer'],
            [['attivo', 'id_anagrafica_disabile', 'id_distretto', 'id_gruppo'], 'required'],
            [['nota_chiusura', 'note','rawdata_json','classe_disabilita'], 'string'],
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
            'data_chiusura' => 'Data Chiusura',
            'nota_chiusura' => 'Nota Chiusura',
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
     * Gets query for [[IstanzaLogs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIstanzaLogs()
    {
        return $this->hasMany(IstanzaLog::class, ['id_istanza' => 'id']);
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

    public function getLastIsee()
    {
        return Isee::find()->where(['id_istanza' => $this->id, 'valido' => 1])->orderBy(['data_presentazione' => SORT_DESC])->one();
    }

    public function getLastIseeType()
    {
        $last = Isee::find()->where(['id_istanza' => $this->id, 'valido' => 1])->orderBy(['data_presentazione' => SORT_DESC])->one();
        if ($last)
            return ($last->maggiore_25mila === 0) ? IseeType::MINORE_25K : IseeType::MAGGIORE_25K;
        else
            return null;
    }

    public function getStatoRecupero()
    {
        $importoRecuperi = 0;
        $recuperiInCorso = Recupero::find()->where(['id_istanza' => $this->id, 'recuperato' => 0])->all();
        $ricoveriDaRecuperare = Ricovero::find()->where(['id_istanza' => $this->id, 'id_recupero' => null,'contabilizzare' => 1])->all();
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
        return ($importoRecuperi !== 0) ? ("<div>Da recuperare:</div><span class='badge bg-warning text-dark h6'>" . Yii::$app->formatter->asCurrency($importoRecuperi) . "</span>") :
            "<span class='badge bg-success'>In regola</span>";
    }

    public function haRicoveriDaRecuperare()
    {
        return Ricovero::find()->where(['id_istanza' => $this->id, 'id_recupero' => null,'contabilizzare' => 1])->count() > 0;
    }

    public function haRecuperiInCorso()
    {
        return Recupero::find()->where(['id_istanza' => $this->id, 'recuperato' => 0])->count() > 0;
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
        return Istanza::find()->where(['attivo' => 1])->andWhere(['IS NOT','data_decesso', null])->andWhere(['OR',['liquidazione_decesso_completata' =>null],['liquidazione_decesso_completata' => 0]])->count();
    }

    public function getNominativoDisabile()
    {
        if ($this->anagraficaDisabile->nome)
            return $this->anagraficaDisabile->nome . ' ' . $this->anagraficaDisabile->cognome;
        else
            return $this->anagraficaDisabile->cognome_nome;
    }
}
