<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "isee".
 *
 * @property int $id
 * @property int $maggiore_25mila
 * @property int|null $data_presentazione
 * @property int|null $data_scadenza
 * @property int $valido
 * @property int|null $id_istanza
 *
 * @property Istanza $istanza
 */
class Isee extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'isee';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['maggiore_25mila'], 'required'],
            [['maggiore_25mila', 'data_presentazione', 'data_scadenza', 'valido', 'id_istanza'], 'integer'],
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
            'maggiore_25mila' => 'Maggiore 25mila',
            'data_presentazione' => 'Data Presentazione',
            'data_scadenza' => 'Data Scadenza',
            'valido' => 'Valido',
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
}
