<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ricovero".
 *
 * @property int $id
 * @property int|null $da
 * @property int|null $a
 * @property string|null $cod_struttura
 * @property string|null $descr_struttura
 * @property string|null $note
 * @property int|null $id_istanza
 * @property int|null $id_determina
 *
 * @property Determina $determina
 * @property Istanza $istanza
 */
class Ricovero extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ricovero';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['da', 'a', 'id_istanza', 'id_determina'], 'integer'],
            [['note'], 'string'],
            [['cod_struttura', 'descr_struttura'], 'string', 'max' => 100],
            [['id_determina'], 'exist', 'skipOnError' => true, 'targetClass' => Determina::class, 'targetAttribute' => ['id_determina' => 'id']],
            [['id_istanza'], 'exist', 'skipOnError' => true, 'targetClass' => Istanza::class, 'targetAttribute' => ['id_istanza' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'da' => 'Da',
            'a' => 'A',
            'cod_struttura' => 'Cod Struttura',
            'descr_struttura' => 'Descr Struttura',
            'note' => 'Note',
            'id_istanza' => 'Id Istanza',
            'id_determina' => 'Id Determina',
        ];
    }

    /**
     * Gets query for [[Determina]].
     *
     * @return \yii\db\ActiveQuery|DeterminaQuery
     */
    public function getDetermina()
    {
        return $this->hasOne(Determina::class, ['id' => 'id_determina']);
    }

    /**
     * Gets query for [[Istanza]].
     *
     * @return \yii\db\ActiveQuery|IstanzaQuery
     */
    public function getIstanza()
    {
        return $this->hasOne(Istanza::class, ['id' => 'id_istanza']);
    }

    /**
     * {@inheritdoc}
     * @return RicoveroQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new RicoveroQuery(get_called_class());
    }
}
