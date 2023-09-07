<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "recupero".
 *
 * @property int $id
 * @property float|null $importo
 * @property bool $chiuso
 * @property bool $annullato
 * @property string|null $data_annullamento
 * @property bool $rateizzato
 * @property int|null $num_rate
 * @property float|null $importo_rata
 * @property string|null $note
 * @property string|null $data_creazione
 * @property string|null $data_modifica
 * @property int|null $id_istanza
 * @property int|null $id_recupero_collegato
 *
 * @property Istanza $istanza
 * @property Movimento[] $movimentos
 * @property Recupero $recuperoCollegato
 * @property Recupero[] $recuperos
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
            [['chiuso', 'annullato', 'rateizzato'], 'boolean'],
            [['data_annullamento', 'data_creazione', 'data_modifica'], 'safe'],
            [['num_rate', 'id_istanza', 'id_recupero_collegato'], 'integer'],
            [['note'], 'string'],
            [['id_istanza'], 'exist', 'skipOnError' => true, 'targetClass' => Istanza::class, 'targetAttribute' => ['id_istanza' => 'id']],
            [['id_recupero_collegato'], 'exist', 'skipOnError' => true, 'targetClass' => Recupero::class, 'targetAttribute' => ['id_recupero_collegato' => 'id']],
        ];
    }


    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['data_creazione', 'data_modifica'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['data_modifica'],
                ],
                'value' => new Expression("CURDATE()")
            ],
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
            'chiuso' => 'Chiuso',
            'annullato' => 'Annullato',
            'data_annullamento' => 'Data Annullamento',
            'rateizzato' => 'Rateizzato',
            'num_rate' => 'Num Rate',
            'importo_rata' => 'Importo Rata',
            'note' => 'Note',
            'data_creazione' => 'Data Creazione',
            'data_modifica' => 'Data Modifica',
            'id_istanza' => 'Id Istanza',
            'id_recupero_collegato' => 'Id Recupero Collegato',
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

    /**
     * Gets query for [[RecuperoCollegato]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecuperoCollegato()
    {
        return $this->hasOne(Recupero::class, ['id' => 'id_recupero_collegato']);
    }

    /**
     * Gets query for [[Recuperos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecuperos()
    {
        return $this->hasMany(Recupero::class, ['id_recupero_collegato' => 'id']);
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
        if ($this->rateizzato && $this->num_rate > 0) {
            if (($this->num_rate * $this->importo_rata) % $this->importo!== 0) {
                $out = abs( ($this->num_rate * $this->importo_rata) % $this->importo);
            }
        }
        return $out;
    }

    public function getImportoSaldato() {
        $out = 0;
        foreach ($this->movimentos as $movimento) {
            $out += $movimento->importo;
        }
        return $out;
    }

    public function getImportoResiduo() {
        return abs($this->importo) - abs($this->getImportoSaldato());
    }
}
