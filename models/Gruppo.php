<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "gruppo".
 *
 * @property int $id
 * @property int|null $data_termine_istanze
 * @property int|null $data_inizio_beneficio
 * @property string|null $descrizione_gruppo
 * @property string|null $descrizione_gruppo_old
 *
 * @property Istanza[] $istanzas
 */
class Gruppo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'gruppo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data_termine_istanze', 'data_inizio_beneficio'], 'integer'],
            [['descrizione_gruppo', 'descrizione_gruppo_old'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'data_termine_istanze' => 'Data Termine Istanze',
            'data_inizio_beneficio' => 'Data Inizio Beneficio',
            'descrizione_gruppo' => 'Descrizione Gruppo',
            'descrizione_gruppo_old' => 'Descrizione Gruppo Old',
        ];
    }

    /**
     * Gets query for [[Istanzas]].
     *
     * @return \yii\db\ActiveQuery|IstanzaQuery
     */
    public function getIstanzas()
    {
        return $this->hasMany(Istanza::class, ['id_gruppo' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return GruppoQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new GruppoQuery(get_called_class());
    }
}
