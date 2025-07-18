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
 * @property bool $liquidazione_decesso
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

    public static function getDataUltimoPagamento($escludiDecesso = true)
    {
        return (new Query())->from('movimento m, determina d')->select('max(m.data)')
            ->where('m.id_determina = d.id')->andWhere('d.deceduti' . ($escludiDecesso ? ' = false' : ' = true'))
            ->andWhere('is_movimento_bancario = true')->andWhere('m.escludi_contabilita = true')
        ->scalar();
    }

    public static function getNumPagatiUltimoPagamento()
    {
        $dataUltimoPagamento = self::getDataUltimoPagamento();
        if (!$dataUltimoPagamento) {
            return 0;
        }

        // Get the first day of the month of the last payment
        $dataInizio = date('Y-m-01', strtotime($dataUltimoPagamento));
        // Get the last day of the month of the last payment
        $dataFine = date('Y-m-t', strtotime($dataUltimoPagamento));

        $count = (new Query())->select('COUNT(DISTINCT c.id_istanza)')
            ->from('movimento m')
            ->innerJoin('conto c', 'm.id_conto = c.id')
            ->where(['m.is_movimento_bancario' => true])
            ->andWhere(['m.tornato_indietro' => false])
            ->andWhere(['>=', 'm.data', $dataInizio])
            ->andWhere(['<=', 'm.data', $dataFine])
            ->scalar();

        return $count ? $count : 0; // Return 0 if null
    }

    public static function getTotalePagatiUltimoPagamento()
    {
        $dataUltimoPagamento = self::getDataUltimoPagamento();
        if (!$dataUltimoPagamento) {
            return 0;
        }

        // Get the first day of the month of the last payment
        $dataInizio = date('Y-m-01', strtotime($dataUltimoPagamento));
        // Get the last day of the month of the last payment
        $dataFine = date('Y-m-t', strtotime($dataUltimoPagamento));

        $total = (new Query())->select('SUM(m.importo)')
            ->from('movimento m')
            ->where(['m.is_movimento_bancario' => true])
            ->andWhere(['m.tornato_indietro' => false])
            ->andWhere(['>=', 'm.data', $dataInizio])
            ->andWhere(['<=', 'm.data', $dataFine])
            ->scalar();

        return $total ? $total : 0; // Return 0 if null
    }

    public static function getNumPagatiLastMonth()
    {
        // For backward compatibility, now uses the last payment date
        return self::getNumPagatiUltimoPagamento();
    }

    public static function getTotalePagatiLastMonth()
    {
        // For backward compatibility, now uses the last payment date
        return self::getTotalePagatiUltimoPagamento();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['importo'], 'required'],
            [['importo'], 'number'],
            [['is_movimento_bancario', 'tornato_indietro', 'contabilizzare','escludi_contabilita','liquidazione_decesso'], 'boolean'],
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
            'escludi_contabilita' => 'Escludi Contabilita',
            'liquidazione_decesso' => 'Liquidazione Decesso',
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
