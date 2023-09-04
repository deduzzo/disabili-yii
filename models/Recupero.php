<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "recupero".
 *
 * @property int $id
 * @property float|null $importo
 * @property int $recuperato
 * @property int $rateizzato
 * @property int|null $num_rate
 * @property float|null $importo_rata
 * @property string|null $note
 * @property int|null $id_istanza
 *
 * @property Istanza $istanza
 * @property Movimento[] $movimentos
 * @property Ricovero[] $ricoveros
 */
class Recupero extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'recupero';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['importo', 'importo_rata'], 'number'],
            [['recuperato', 'rateizzato', 'num_rate', 'id_istanza'], 'integer'],
            [['note'], 'string'],
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
            'importo' => 'Importo',
            'recuperato' => 'Recuperato',
            'rateizzato' => 'Rateizzato',
            'num_rate' => 'Num Rate',
            'importo_rata' => 'Importo Rata',
            'note' => 'Note',
            'id_istanza' => 'Id Istanza',
        ];
    }

    /**
     * Gets query for [[Istanza]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIstanza()
    {
        return $this->hasOne(Istanza::class, ['id' => 'id_istanza']);
    }

    /**
     * Gets query for [[Movimentos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMovimentos()
    {
        return $this->hasMany(Movimento::class, ['id_recupero' => 'id']);
    }

    /**
     * Gets query for [[Ricoveros]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRicoveros()
    {
        return $this->hasMany(Ricovero::class, ['id_recupero' => 'id']);
    }

    public function getRateMancanti()
    {
        if ($this->rateizzato == 1 && $this->num_rate > 0)
            return $this->num_rate - count($this->movimentos);
        else
            return 0;
    }

    public function getUltimaRataSeDiversa() {
        $out = null;
        if ($this->rateizzato == 1 && $this->num_rate > 0) {
            if (($this->num_rate * $this->importo_rata) % $this->importo!== 0) {
                $out = abs( ($this->num_rate * $this->importo_rata) % $this->importo);
            }
        }
        return $out;
    }
}
