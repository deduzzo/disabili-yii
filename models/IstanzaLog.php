<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "istanza_log".
 *
 * @property int $id
 * @property int|null $id_istanza
 * @property string|null $tipologia
 * @property string|null $vecchio_valore
 * @property string|null $data_modifica
 *
 * @property Istanza $istanza
 */
class IstanzaLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'istanza_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_istanza'], 'integer'],
            [['vecchio_valore'], 'string'],
            [['data_modifica'], 'safe'],
            [['tipologia'], 'string', 'max' => 100],
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
            'id_istanza' => 'Id Istanza',
            'tipologia' => 'Tipologia',
            'vecchio_valore' => 'Vecchio Valore',
            'data_modifica' => 'Data Modifica',
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
}
