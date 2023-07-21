<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "determina".
 *
 * @property int $id
 * @property string|null $numero
 * @property int|null $data
 * @property string|null $descrizione
 *
 * @property Pagamento[] $pagamentos
 * @property Ricovero[] $ricoveros
 */
class Determina extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'determina';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data'], 'integer'],
            [['descrizione'], 'string'],
            [['numero'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'numero' => 'Numero',
            'data' => 'Data',
            'descrizione' => 'Descrizione',
        ];
    }

    /**
     * Gets query for [[Pagamentos]].
     *
     * @return \yii\db\ActiveQuery|PagamentoQuery
     */
    public function getPagamentos()
    {
        return $this->hasMany(Pagamento::class, ['id_determina' => 'id']);
    }

    /**
     * Gets query for [[Ricoveros]].
     *
     * @return \yii\db\ActiveQuery|RicoveroQuery
     */
    public function getRicoveros()
    {
        return $this->hasMany(Ricovero::class, ['id_determina' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return DeterminaQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new DeterminaQuery(get_called_class());
    }
}
