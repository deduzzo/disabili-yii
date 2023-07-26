<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "movimento".
 *
 * @property int $id
 * @property float $importo
 * @property string|null $data
 * @property int|null $is_recupero
 * @property int|null $num_rate_totali
 * @property int|null $num_rata
 * @property float|null $totale_rateizzato
 * @property int|null $rateizzazione_chiusa
 * @property int|null $periodo_da
 * @property int|null $periodo_a
 * @property int $tornato_indietro
 * @property int|null $data_invio_notifica
 * @property int|null $data_incasso
 * @property int|null $id_determina
 * @property int|null $id_conto
 * @property string|null $note
 *
 * @property Conto $conto
 * @property Determina $determina
 * @property Ricovero[] $ricoveros
 */
class Movimento extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'movimento';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['importo'], 'required'],
            [['importo', 'totale_rateizzato'], 'number'],
            [['data'], 'safe'],
            [['is_recupero', 'num_rate_totali', 'num_rata', 'rateizzazione_chiusa', 'periodo_da', 'periodo_a', 'tornato_indietro', 'data_invio_notifica', 'data_incasso', 'id_determina', 'id_conto'], 'integer'],
            [['note'], 'string'],
            [['id_conto'], 'exist', 'skipOnError' => true, 'targetClass' => Conto::class, 'targetAttribute' => ['id_conto' => 'id']],
            [['id_determina'], 'exist', 'skipOnError' => true, 'targetClass' => Determina::class, 'targetAttribute' => ['id_determina' => 'id']],
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
            'data' => 'Data',
            'is_recupero' => 'Is Recupero',
            'num_rate_totali' => 'Num Rate Totali',
            'num_rata' => 'Num Rata',
            'totale_rateizzato' => 'Totale Rateizzato',
            'rateizzazione_chiusa' => 'Rateizzazione Chiusa',
            'periodo_da' => 'Periodo Da',
            'periodo_a' => 'Periodo A',
            'tornato_indietro' => 'Tornato Indietro',
            'data_invio_notifica' => 'Data Invio Notifica',
            'data_incasso' => 'Data Incasso',
            'id_determina' => 'Id Determina',
            'id_conto' => 'Id Conto',
            'note' => 'Note',
        ];
    }

    /**
     * Gets query for [[Conto]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConto()
    {
        return $this->hasOne(Conto::class, ['id' => 'id_conto']);
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
     * Gets query for [[Ricoveros]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRicoveros()
    {
        return $this->hasMany(Ricovero::class, ['id_movimento_recupero' => 'id']);
    }
}
