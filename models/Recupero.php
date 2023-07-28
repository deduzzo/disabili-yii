<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "recupero".
 *
 * @property int $id
 * @property float|null $importo
 * @property int $recuperato
 * @property int $rateizzato
 * @property int|null $num_rate
 * @property int|null $id_istanza
 *
 * @property Istanza $istanza
 * @property Movimento[] $movimentos
 */
class Recupero extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'recupero';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['importo'], 'number'],
            [['recuperato', 'rateizzato', 'num_rate', 'id_istanza'], 'integer'],
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
            'importo' => 'Importo',
            'recuperato' => 'Recuperato',
            'rateizzato' => 'Rateizzato',
            'num_rate' => 'Num Rate',
            'id_istanza' => 'Id Istanza',
        ];
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
        return $this->hasMany(Movimento::class, ['id_recupero' => 'id']);
    }
}
