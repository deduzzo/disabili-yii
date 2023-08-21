<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tipologia_dati".
 *
 * @property int $id
 * @property string $tipo
 * @property string|null $categoria
 * @property string|null $descrizione
 *
 * @property AnagraficaAltricampi[] $anagraficaAltricampis
 * @property Documento[] $documentos
 */
class TipologiaDati extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tipologia_dati';
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
            [['categoria'], 'string', 'max' => 20],
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
}
