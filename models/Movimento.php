<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "movimento".
 *
 * @property int $id
 * @property float $importo
 * @property string|null $data
 * @property string|null $periodo_da
 * @property string|null $periodo_a
 * @property int $tornato_indietro
 * @property int|null $data_invio_notifica
 * @property int|null $data_incasso
 * @property int|null $id_recupero
 * @property int|null $num_rata
 * @property int|null $contabilizzare
 * @property int|null $id_determina
 * @property int|null $id_conto
 * @property string|null $note
 *
 * @property Conto $conto
 * @property Determina $determina
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

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['importo'], 'required'],
            [['importo'], 'number'],
            [['data', 'periodo_da', 'periodo_a'], 'safe'],
            [['tornato_indietro', 'data_invio_notifica', 'data_incasso', 'id_recupero', 'num_rata', 'contabilizzare', 'id_determina', 'id_conto'], 'integer'],
            [['note'], 'string'],
            [['id_conto'], 'exist', 'skipOnError' => true, 'targetClass' => Conto::class, 'targetAttribute' => ['id_conto' => 'id']],
            [['id_determina'], 'exist', 'skipOnError' => true, 'targetClass' => Determina::class, 'targetAttribute' => ['id_determina' => 'id']],
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
            'data' => 'Data',
            'periodo_da' => 'Periodo Da',
            'periodo_a' => 'Periodo A',
            'tornato_indietro' => 'Tornato Indietro',
            'data_invio_notifica' => 'Data Invio Notifica',
            'data_incasso' => 'Data Incasso',
            'id_recupero' => 'Id Recupero',
            'num_rata' => 'Num Rata',
            'contabilizzare' => 'Contabilizzare',
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
     * Gets query for [[Recupero]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecupero()
    {
        return $this->hasOne(Recupero::class, ['id' => 'id_recupero']);
    }
}
