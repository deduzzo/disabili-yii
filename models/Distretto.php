<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "distretto".
 *
 * @property int $id
 * @property string|null $nome
 *
 * @property Istanza[] $istanzas
 */
class Distretto extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'distretto';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nome'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nome' => 'Distretto',
        ];
    }

    /**
     * Gets query for [[Istanzas]].
     *
     * @return \yii\db\ActiveQuery|IstanzaQuery
     */
    public function getIstanzas()
    {
        return $this->hasMany(Istanza::class, ['id_distretto' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return DistrettoQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new DistrettoQuery(get_called_class());
    }
}
