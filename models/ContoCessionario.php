<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "conto_cessionario".
 *
 * @property int $id
 * @property int|null $id_conto
 * @property int|null $id_cessionario
 * @property int $attivo
 * @property int|null $data_disattivazione
 * @property int|null $note
 *
 * @property Anagrafica $cessionario
 * @property Conto $conto
 */
class ContoCessionario extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'conto_cessionario';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_conto', 'id_cessionario', 'attivo', 'data_disattivazione', 'note'], 'integer'],
            [['id_cessionario'], 'exist', 'skipOnError' => true, 'targetClass' => Anagrafica::class, 'targetAttribute' => ['id_cessionario' => 'id']],
            [['id_conto'], 'exist', 'skipOnError' => true, 'targetClass' => Conto::class, 'targetAttribute' => ['id_conto' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_conto' => 'Id Conto',
            'id_cessionario' => 'Id Cessionario',
            'attivo' => 'Attivo',
            'data_disattivazione' => 'Data Disattivazione',
            'note' => 'Note',
        ];
    }

    /**
     * Gets query for [[Cessionario]].
     *
     * @return \yii\db\ActiveQuery|AnagraficaQuery
     */
    public function getCessionario()
    {
        return $this->hasOne(Anagrafica::class, ['id' => 'id_cessionario']);
    }

    /**
     * Gets query for [[Conto]].
     *
     * @return \yii\db\ActiveQuery|ContoQuery
     */
    public function getConto()
    {
        return $this->hasOne(Conto::class, ['id' => 'id_conto']);
    }

    /**
     * {@inheritdoc}
     * @return ContoCessionarioQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ContoCessionarioQuery(get_called_class());
    }
}
