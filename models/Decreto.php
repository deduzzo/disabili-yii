<?php

namespace app\models;

use app\helpers\DateManagerBehavior;

/**
 * This is the model class for table "decreto".
 *
 * @property int $id
 * @property string|null $descrizione_atto
 * @property string|null $data
 * @property string|null $dal
 * @property string|null $al
 * @property int|null $inclusi_minorenni
 * @property int|null $inclusi_maggiorenni
 * @property string|null $note
 *
 * @property DecretoGruppi[] $decretoGruppis
 * @property Gruppo[] $gruppos
 */
class Decreto extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'decreto';
    }

    public function behaviors()
    {
        return [
            [
                'class' => DateManagerBehavior::class,
                'attributes' => ['data', 'dal', 'al']
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data', 'dal', 'al'], 'safe'],
            [['inclusi_minorenni', 'inclusi_maggiorenni'], 'integer'],
            [['note'], 'string'],
            [['descrizione_atto'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'descrizione_atto' => 'Descrizione Atto',
            'data' => 'Data decreto',
            'dal' => 'Pagamenti dal',
            'al' => 'Pagamenti al',
            'inclusi_minorenni' => 'Minorenni inclusi',
            'inclusi_maggiorenni' => 'Maggiorenni inclusi',
            'note' => 'Note',
        ];
    }

    /**
     * Gets query for [[DecretoGruppis]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDecretoGruppis()
    {
        return $this->hasMany(DecretoGruppi::class, ['id_decreto' => 'id']);
    }

    /**
     * Gets query for [[Gruppos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGruppos()
    {
        return $this->hasMany(Gruppo::class, ['id' => 'id_gruppo'])->viaTable('decreto_gruppi', ['id_decreto' => 'id']);
    }
}
