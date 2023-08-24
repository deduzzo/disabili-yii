<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "gruppo_pagamento".
 *
 * @property int $id
 * @property string|null $data
 * @property string|null $descrizione
 * @property int|null $progressivo
 *
 * @property DeterminaGruppoPagamento[] $determinaGruppoPagamentos
 * @property Determina[] $determinas
 * @property Movimento[] $movimentos
 */
class GruppoPagamento extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'gruppo_pagamento';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data'], 'safe'],
            [['descrizione'], 'string'],
            [['progressivo'], 'integer'],
            [['progressivo'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'data' => 'Data',
            'descrizione' => 'Descrizione',
            'progressivo' => 'Progressivo',
        ];
    }

    /**
     * Gets query for [[DeterminaGruppoPagamentos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeterminaGruppoPagamentos()
    {
        return $this->hasMany(DeterminaGruppoPagamento::class, ['id_gruppo' => 'id']);
    }

    /**
     * Gets query for [[Determinas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeterminas()
    {
        return $this->hasMany(Determina::class, ['id' => 'id_determina'])->viaTable('determina_gruppo_pagamento', ['id_gruppo' => 'id']);
    }

    /**
     * Gets query for [[Movimentos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMovimentos()
    {
        return $this->hasMany(Movimento::class, ['id_gruppo_pagamento' => 'id']);
    }
}
