<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "gruppo".
 *
 * @property int $id
 * @property string|null $data_termine_istanze
 * @property string|null $data_inizio_beneficio
 * @property string|null $descrizione_gruppo
 * @property string|null $descrizione_gruppo_old
 *
 * @property DecretoGruppi[] $decretoGruppis
 * @property Decreto[] $decretos
 * @property Istanza[] $istanzas
 */
class Gruppo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'gruppo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data_termine_istanze', 'data_inizio_beneficio'], 'safe'],
            [['descrizione_gruppo', 'descrizione_gruppo_old'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'data_termine_istanze' => 'Data Termine Istanze',
            'data_inizio_beneficio' => 'Data Inizio Beneficio',
            'descrizione_gruppo' => 'Descrizione Gruppo',
            'descrizione_gruppo_old' => 'Descrizione Gruppo Old',
        ];
    }

    /**
     * Gets query for [[DecretoGruppis]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDecretoGruppis()
    {
        return $this->hasMany(DecretoGruppi::class, ['id_gruppo' => 'id']);
    }

    /**
     * Gets query for [[Decretos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDecretos()
    {
        return $this->hasMany(Decreto::class, ['id' => 'id_decreto'])->viaTable('decreto_gruppi', ['id_gruppo' => 'id']);
    }

    /**
     * Gets query for [[Istanzas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIstanzas()
    {
        return $this->hasMany(Istanza::class, ['id_gruppo' => 'id']);
    }

    public function getDescrizioneCompleta()
    {
        return $this->descrizione_gruppo_old . ' [' . $this->descrizione_gruppo . ']';
    }
}
