<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "isee".
 *
 * @property int $id
 * @property float|null $importo
 * @property bool $maggiore_25mila
 * @property string|null $data_presentazione
 * @property string|null $data_scadenza
 * @property bool $valido
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
            [['importo'], 'number'],
            [['maggiore_25mila', 'valido'], 'boolean'],
            [['data_presentazione', 'data_scadenza'], 'safe'],
            [['id_istanza'], 'integer'],
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
