<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "codice_esterno".
 *
 * @property int $id
 * @property int|null $id_servizio_esterno
 * @property int|null $id_tipologia
 * @property int|null $id_chiave_esterna
 * @property string|null $valore
 * @property int|null $attivo
 * @property string|null $legame_json
 */
class CodiceEsterno extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'codice_esterno';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_servizio_esterno', 'id_tipologia', 'id_chiave_esterna', 'attivo'], 'integer'],
            [['legame_json'], 'string'],
            [['valore'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_servizio_esterno' => 'Id Servizio Esterno',
            'id_tipologia' => 'Id Tipologia',
            'id_chiave_esterna' => 'Id Chiave Esterna',
            'valore' => 'Valore',
            'attivo' => 'Attivo',
            'legame_json' => 'Legame Json',
        ];

    }
}
