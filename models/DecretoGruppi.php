<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "decreto_gruppi".
 *
 * @property int $id_gruppo
 * @property int $id_decreto
 *
 * @property Decreto $decreto
 * @property Gruppo $gruppo
 */
class DecretoGruppi extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'decreto_gruppi';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_gruppo', 'id_decreto'], 'required'],
            [['id_gruppo', 'id_decreto'], 'integer'],
            [['id_gruppo', 'id_decreto'], 'unique', 'targetAttribute' => ['id_gruppo', 'id_decreto']],
            [['id_decreto'], 'exist', 'skipOnError' => true, 'targetClass' => Decreto::class, 'targetAttribute' => ['id_decreto' => 'id']],
            [['id_gruppo'], 'exist', 'skipOnError' => true, 'targetClass' => Gruppo::class, 'targetAttribute' => ['id_gruppo' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_gruppo' => 'Id Gruppo',
            'id_decreto' => 'Id Decreto',
        ];
    }

    /**
     * Gets query for [[Decreto]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDecreto()
    {
        return $this->hasOne(Decreto::class, ['id' => 'id_decreto']);
    }

    /**
     * Gets query for [[Gruppo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGruppo()
    {
        return $this->hasOne(Gruppo::class, ['id' => 'id_gruppo']);
    }
}
