<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "servizi_esterni".
 *
 * @property int $id
 * @property string|null $descrizione
 */
class ServiziEsterni extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'servizi_esterni';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['descrizione'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'descrizione' => 'Descrizione',
        ];
    }
}
