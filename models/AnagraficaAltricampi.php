<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "anagrafica_altricampi".
 *
 * @property int $id
 * @property int|null $id_anagrafica
 * @property string|null $tipologia
 * @property string|null $valore
 * @property string|null $data_inserimento
 * @property int|null $valido
 *
 * @property Anagrafica $anagrafica
 */
class AnagraficaAltricampi extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'anagrafica_altricampi';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_anagrafica', 'valido'], 'integer'],
            [['valore'], 'string'],
            [['data_inserimento'], 'safe'],
            [['tipologia'], 'string', 'max' => 100],
            [['id_anagrafica'], 'exist', 'skipOnError' => true, 'targetClass' => Anagrafica::class, 'targetAttribute' => ['id_anagrafica' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_anagrafica' => 'Id Anagrafica',
            'tipologia' => 'Tipologia',
            'valore' => 'Valore',
            'data_inserimento' => 'Data Inserimento',
            'valido' => 'Valido',
        ];
    }

    /**
     * Gets query for [[Anagrafica]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAnagrafica()
    {
        return $this->hasOne(Anagrafica::class, ['id' => 'id_anagrafica']);
    }
}
