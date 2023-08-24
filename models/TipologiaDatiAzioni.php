<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tipologia_dati_azioni".
 *
 * @property int $id
 * @property string $tipo
 * @property string|null $categoria
 * @property string|null $tipologia tipologia
 * @property string|null $descrizione
 *
 * @property AnagraficaAltricampi[] $anagraficaAltricampis
 * @property Documento[] $documentos
 * @property Log[] $logs
 * @property Log[] $logs0
 */
class TipologiaDatiAzioni extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tipologia_dati_azioni';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tipo'], 'required'],
            [['descrizione'], 'string'],
            [['tipo'], 'string', 'max' => 50],
            [['categoria', 'tipologia'], 'string', 'max' => 100],
            [['tipo'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tipo' => 'Tipo',
            'categoria' => 'Categoria',
            'tipologia' => 'Tipologia',
            'descrizione' => 'Descrizione',
        ];
    }

    /**
     * Gets query for [[AnagraficaAltricampis]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAnagraficaAltricampis()
    {
        return $this->hasMany(AnagraficaAltricampi::class, ['id_tipologia' => 'id']);
    }

    /**
     * Gets query for [[Documentos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentos()
    {
        return $this->hasMany(Documento::class, ['id_tipologia' => 'id']);
    }

    /**
     * Gets query for [[Logs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLogs()
    {
        return $this->hasMany(Log::class, ['id_tipo_azione' => 'id']);
    }

    /**
     * Gets query for [[Logs0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLogs0()
    {
        return $this->hasMany(Log::class, ['id_tipologia_record' => 'id']);
    }
}
