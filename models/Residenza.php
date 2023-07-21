<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "residenza".
 *
 * @property int $id
 * @property string $indirizzo
 * @property string|null $comune_residenza
 * @property int|null $cap
 * @property int $attivo
 * @property int $id_anagrafica
 * @property int $data_inserimento
 * @property int|null $data_disattivazione
 *
 * @property Anagrafica $anagrafica
 */
class Residenza extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'residenza';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['indirizzo', 'id_anagrafica', 'data_inserimento'], 'required'],
            [['cap', 'attivo', 'id_anagrafica', 'data_inserimento', 'data_disattivazione'], 'integer'],
            [['indirizzo'], 'string', 'max' => 200],
            [['comune_residenza'], 'string', 'max' => 100],
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
            'indirizzo' => 'Indirizzo',
            'comune_residenza' => 'Comune Residenza',
            'cap' => 'Cap',
            'attivo' => 'Attivo',
            'id_anagrafica' => 'Id Anagrafica',
            'data_inserimento' => 'Data Inserimento',
            'data_disattivazione' => 'Data Disattivazione',
        ];
    }

    /**
     * Gets query for [[Anagrafica]].
     *
     * @return \yii\db\ActiveQuery|AnagraficaQuery
     */
    public function getAnagrafica()
    {
        return $this->hasOne(Anagrafica::class, ['id' => 'id_anagrafica']);
    }

    /**
     * {@inheritdoc}
     * @return ResidenzaQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ResidenzaQuery(get_called_class());
    }
}
