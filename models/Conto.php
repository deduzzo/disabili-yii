<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "conto".
 *
 * @property int $id
 * @property string $iban
 * @property string $intestatario
 * @property string|null $note
 * @property bool $attivo
 * @property bool $validato
 * @property string|null $data_validazione
 * @property int|null $id_istanza
 * @property string|null $data_disattivazione
 * @property string|null $data_creazione
 * @property string|null $data_modifica
 * @property int|null $id_utente_creazione
 * @property int|null $id_utente_modifica
 *
 * @property ContoCessionario[] $contoCessionarios
 * @property Istanza $istanza
 * @property Movimento[] $movimentos
 */
class Conto extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'conto';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['iban'], 'required'],
            [['note','intestatario'], 'string'],
            [['attivo', 'validato'], 'boolean'],
            [['data_validazione', 'data_disattivazione', 'data_creazione', 'data_modifica'], 'safe'],
            [['id_istanza'], 'integer'],
            [['iban'], 'string', 'max' => 40],
            [['iban', 'id_istanza'], 'unique', 'targetAttribute' => ['iban', 'id_istanza']],
            [['id_istanza'], 'exist', 'skipOnError' => true, 'targetClass' => Istanza::class, 'targetAttribute' => ['id_istanza' => 'id']],
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
                'value' => new Expression("NOW()")
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'id_utente_creazione',
                'updatedByAttribute' => 'id_utente_modifica',
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
            'iban' => 'Iban',
            'note' => 'Note',
            'attivo' => 'Attivo',
            'id_istanza' => 'Id Istanza',
            'data_disattivazione' => 'Data Disattivazione',
            'data_creazione' => 'Data Creazione',
            'data_modifica' => 'Data Modifica',
        ];
    }

    /**
     * Gets query for [[ContoCessionarios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContoCessionarios()
    {
        return $this->hasMany(ContoCessionario::class, ['id_conto' => 'id']);
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
        return $this->hasMany(Movimento::class, ['id_conto' => 'id']);
    }

    public static function checkIban($iban) {
        return verify_iban($iban);
    }
}
