<?php

namespace app\models;

use app\models\enums\ImportoBase;
use app\models\enums\IseeType;
use Carbon\Carbon;
use DateTime;
use Yii;
use function PHPUnit\Framework\lessThanOrEqual;

/**
 * This is the model class for table "ricovero".
 *
 * @property int $id
 * @property string|null $da
 * @property string|null $a
 * @property string|null $cod_struttura
 * @property string|null $descr_struttura
 * @property int|null $contabilizzare
 * @property string|null $note
 * @property int|null $id_istanza
 * @property int|null $id_determina
 * @property int|null $id_recupero
 *
 * @property Determina $determina
 * @property Istanza $istanza
 * @property Recupero $recupero
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
            [['da', 'a'], 'safe'],
            [['contabilizzare', 'id_istanza', 'id_determina', 'id_recupero'], 'integer'],
            [['note'], 'string'],
            [['cod_struttura', 'descr_struttura'], 'string', 'max' => 100],
            [['id_determina'], 'exist', 'skipOnError' => true, 'targetClass' => Determina::class, 'targetAttribute' => ['id_determina' => 'id']],
            [['id_istanza'], 'exist', 'skipOnError' => true, 'targetClass' => Istanza::class, 'targetAttribute' => ['id_istanza' => 'id']],
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
            'da' => 'Da',
            'a' => 'A',
            'cod_struttura' => 'Cod Struttura',
            'descr_struttura' => 'Descr Struttura',
            'contabilizzare' => 'Contabilizzare',
            'note' => 'Note',
            'id_istanza' => 'Id Istanza',
            'id_determina' => 'Id Determina',
            'id_recupero' => 'Id Recupero',
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
     * Gets query for [[Istanza]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIstanza()
    {
        return $this->hasOne(Istanza::class, ['id' => 'id_istanza']);
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

    public function getNumGiorni(): ?array
    {
        $out = ['giorni' => 0, 'mesi' => 0];
        $da = Carbon::createFromFormat('Y-m-d', $this->da);
        list($daAnno, $daMese, $daGiorno) = explode('-', $this->da);
        if ($this->contabilizzare && !$this->a) {
            $a = Carbon::now();
            list($aAnno, $aMese, $aGiorno) = explode('-', $a->toDateString());
        }
        else if (!$this->da || !$this->a) return null;
        else {
            $a = Carbon::createFromFormat('Y-m-d', $this->a);
            // id $da and $a are in different months
            list($aAnno, $aMese, $aGiorno) = explode('-', $this->a);
        }
        if (!checkdate(intval($daMese), intval($daGiorno), intval($daAnno)) || !checkdate(intval($aMese), intval($aGiorno), intval($aAnno)) || !$da->lessThan($a)) {
            return null;
        } else {
            if ($da->month !== $a->month) {
                if ($da->day !== 1)
                    $out['giorni'] += $da->daysInMonth - $da->day + 1;
                $out['mesi'] += $a->diffInMonths($da);
                if ($a->day !== $a->daysInMonth)
                    $out['giorni'] += $a->day;
                else
                    $out['mesi'] += 1;
            } else {
                if ($da->day === 1 && $a->day === $a->daysInMonth)
                    $out['mesi'] += 1;
                else
                    $out['giorni'] += $a->diffInDays($da) + 1;
            }
            return $out;
        }
    }

    public function getImportoRicovero()
    {
        $valoreMese = $this->istanza->getLastIseeType() === IseeType::MAGGIORE_25K ? ImportoBase::MAGGIORE_25K_V1 : ImportoBase::MINORE_25K_V1;
        $ricovero = $this->getNumGiorni();
        if  (!$this->a) return null;
        if ($ricovero)
            return ($ricovero['giorni'] * ($valoreMese / 30)) + ($ricovero['mesi'] * $valoreMese);
        else
            return 0;
    }
}
