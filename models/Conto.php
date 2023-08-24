<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "conto".
 *
 * @property int $id
 * @property string|null $iban
 * @property string|null $note
 * @property int $attivo
 * @property int|null $id_istanza
 * @property int|null $data_disattivazione
 * @property int|null $data_creazione
 * @property int|null $data_modifica
 *
 * @property ContoCessionario[] $contoCessionarios
 * @property Istanza $istanza
 * @property Movimento[] $movimentos
 */
class Conto extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'conto';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['note'], 'string'],
            [['attivo', 'id_istanza', 'data_disattivazione', 'data_creazione', 'data_modifica'], 'integer'],
            [['iban'], 'string', 'max' => 40],
            [['iban'], 'unique'],
            [['id_istanza'], 'exist', 'skipOnError' => true, 'targetClass' => Istanza::class, 'targetAttribute' => ['id_istanza' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'iban' => 'Iban',
            'note' => 'Note',
            'attivo' => 'Attivo',
            'id_istanza' => 'Id Istanza',
            'data_disattivazione' => 'Data Disattivazione',
            'data_creazione' => 'Data Creazione',
            'data_modifica' => 'Data Modifica',
        ];
    }

    /**
     * Gets query for [[ContoCessionarios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContoCessionarios()
    {
        return $this->hasMany(ContoCessionario::class, ['id_conto' => 'id']);
    }

    /**
     * Gets query for [[Istanza]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIstanza()
    {
        return $this->hasOne(Istanza::class, ['id' => 'id_istanza']);
    }

    /**
     * Gets query for [[Movimentos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMovimentos()
    {
        return $this->hasMany(Movimento::class, ['id_conto' => 'id']);
    }
}
