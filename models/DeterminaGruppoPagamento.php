<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "determina_gruppo_pagamento".
 *
 * @property int $id
 * @property int|null $id_determina
 * @property int|null $id_gruppo
 *
 * @property Determina $determina
 * @property GruppoPagamento $gruppo
 */
class DeterminaGruppoPagamento extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'determina_gruppo_pagamento';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_determina', 'id_gruppo'], 'integer'],
            [['id_gruppo', 'id_determina'], 'unique', 'targetAttribute' => ['id_gruppo', 'id_determina']],
            [['id_determina'], 'exist', 'skipOnError' => true, 'targetClass' => Determina::class, 'targetAttribute' => ['id_determina' => 'id']],
            [['id_gruppo'], 'exist', 'skipOnError' => true, 'targetClass' => GruppoPagamento::class, 'targetAttribute' => ['id_gruppo' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_determina' => 'Id Determina',
            'id_gruppo' => 'Id Gruppo',
        ];
    }

    /**
     * Gets query for [[Determina]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDetermina()
    {
        return $this->hasOne(Determina::class, ['id' => 'id_determina']);
    }

    /**
     * Gets query for [[Gruppo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGruppo()
    {
        return $this->hasOne(GruppoPagamento::class, ['id' => 'id_gruppo']);
    }
}
