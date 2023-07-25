<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "servizi_esterni_tipologia".
 *
 * @property int $id
 * @property string|null $chiave
 * @property string|null $descrizione_tipologia
 */
class ServiziEsterniTipologia extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'servizi_esterni_tipologia';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chiave', 'descrizione_tipologia'], 'string', 'max' => 100],
            [['chiave'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'chiave' => 'Chiave',
            'descrizione_tipologia' => 'Descrizione Tipologia',
        ];
    }
}
