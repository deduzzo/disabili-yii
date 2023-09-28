<?php

namespace app\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "movimento".
 *
 * @property int $id
 * @property float $importo
 * @property bool $is_movimento_bancario
 * @property string|null $data
 * @property string|null $periodo_da
 * @property string|null $periodo_a
 * @property bool $tornato_indietro
 * @property int|null $data_invio_notifica
 * @property int|null $data_incasso
 * @property int|null $id_recupero
 * @property int|null $num_rata
 * @property bool $contabilizzare
 * @property bool $escludi_contabilita
 * @property string|null $note
 * @property int|null $id_gruppo_pagamento
 * @property int|null $id_determina
 * @property int|null $id_conto
 *
 * @property Conto $conto
 * @property Determina $determina
 * @property GruppoPagamento $gruppoPagamento
 * @property Recupero $recupero
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

    public static function getDataUltimoPagamento()
    {
        return (new Query())->from('movimento')->select('max(data)')->where('is_movimento_bancario = true')->andWhere('escludi_contabilita = true')->scalar();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['importo'], 'required'],
            [['importo'], 'number'],
            [['is_movimento_bancario', 'tornato_indietro', 'contabilizzare'], 'boolean'],
            [['data', 'periodo_da', 'periodo_a'], 'safe'],
            [['data_invio_notifica', 'data_incasso', 'id_recupero', 'num_rata', 'id_gruppo_pagamento', 'id_determina', 'id_conto'], 'integer'],
            [['note'], 'string'],
            [['id_conto'], 'exist', 'skipOnError' => true, 'targetClass' => Conto::class, 'targetAttribute' => ['id_conto' => 'id']],
            [['id_determina'], 'exist', 'skipOnError' => true, 'targetClass' => Determina::class, 'targetAttribute' => ['id_determina' => 'id']],
            [['id_gruppo_pagamento'], 'exist', 'skipOnError' => true, 'targetClass' => GruppoPagamento::class, 'targetAttribute' => ['id_gruppo_pagamento' => 'id']],
            [['id_recupero'], 'exist', 'skipOnError' => true, 'targetClass' => Recupero::class, 'targetAttribute' => ['id_recupero' => 'id']],
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
            'is_movimento_bancario' => 'Is Movimento Bancario',
            'data' => 'Data',
            'periodo_da' => 'Periodo Da',
            'periodo_a' => 'Periodo A',
            'tornato_indietro' => 'Tornato Indietro',
            'data_invio_notifica' => 'Data Invio Notifica',
            'data_incasso' => 'Data Incasso',
            'id_recupero' => 'Id Recupero',
            'num_rata' => 'Num Rata',
            'contabilizzare' => 'Contabilizzare',
            'note' => 'Note',
            'id_gruppo_pagamento' => 'Id Gruppo Pagamento',
            'id_determina' => 'Id Determina',
            'id_conto' => 'Id Conto',
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
     * Gets query for [[GruppoPagamento]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGruppoPagamento()
    {
        return $this->hasOne(GruppoPagamento::class, ['id' => 'id_gruppo_pagamento']);
    }

    /**
     * Gets query for [[Recupero]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecupero()
    {
        return $this->hasOne(Recupero::class, ['id' => 'id_recupero']);
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
}
