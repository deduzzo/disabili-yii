<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "istanza".
 *
 * @property int $id
 * @property int|null $data_inserimento
 * @property int $riconosciuto
 * @property string|null $classe_disabilita
 * @property int|null $data_riconoscimento
 * @property int|null $patto_di_cura
 * @property int|null $data_firma_patto
 * @property int $attivo
 * @property int|null $data_decesso
 * @property int|null $liquidazione_decesso_completata
 * @property int|null $data_liquidazione_decesso
 * @property int|null $chiuso
 * @property int|null $data_chiusura
 * @property string|null $nota_chiusura
 * @property string|null $note
 * @property int $id_anagrafica_disabile
 * @property int $id_distretto
 * @property int $id_gruppo
 *
 * @property Anagrafica $anagraficaDisabile
 * @property Conto[] $contos
 * @property Distretto $distretto
 * @property Documento[] $documentos
 * @property Gruppo $gruppo
 * @property Isee[] $isees
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
            [['data_inserimento', 'riconosciuto', 'data_riconoscimento', 'patto_di_cura', 'data_firma_patto', 'attivo', 'data_decesso', 'liquidazione_decesso_completata', 'data_liquidazione_decesso', 'chiuso', 'data_chiusura', 'id_anagrafica_disabile', 'id_distretto', 'id_gruppo'], 'integer'],
            [['attivo', 'id_anagrafica_disabile', 'id_distretto', 'id_gruppo'], 'required'],
            [['nota_chiusura', 'note'], 'string'],
            [['classe_disabilita'], 'string', 'max' => 10],
            [['id_anagrafica_disabile'], 'exist', 'skipOnError' => true, 'targetClass' => Anagrafica::class, 'targetAttribute' => ['id_anagrafica_disabile' => 'id']],
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
     * Gets query for [[Ricoveros]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRicoveros()
    {
        return $this->hasMany(Ricovero::class, ['id_istanza' => 'id']);
    }
}
