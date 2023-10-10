<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cambio_iban".
 *
 * @property int $id
 * @property int|null $id_istanza
 * @property int|null $id_anagrafica
 * @property int|null $id_movimento_verifica
 * @property string|null $iban
 * @property string|null $data_inserimento
 * @property bool|null $verificato
 * @property string|null $note
 *
 * @property Anagrafica $anagrafica
 * @property Istanza $istanza
 * @property Movimento $movimentoVerifica
 */
class CambioIban extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cambio_iban';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_istanza', 'id_anagrafica', 'id_movimento_verifica'], 'integer'],
            [['data_inserimento'], 'safe'],
            [['verificato'], 'boolean'],
            [['note'], 'string'],
            [['iban'], 'string', 'max' => 40],
            [['id_anagrafica'], 'exist', 'skipOnError' => true, 'targetClass' => Anagrafica::class, 'targetAttribute' => ['id_anagrafica' => 'id']],
            [['id_istanza'], 'exist', 'skipOnError' => true, 'targetClass' => Istanza::class, 'targetAttribute' => ['id_istanza' => 'id']],
            [['id_movimento_verifica'], 'exist', 'skipOnError' => true, 'targetClass' => Movimento::class, 'targetAttribute' => ['id_movimento_verifica' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_istanza' => 'Id Istanza',
            'id_anagrafica' => 'Id Anagrafica',
            'id_movimento_verifica' => 'Id Movimento Verifica',
            'iban' => 'Iban',
            'data_inserimento' => 'Data Inserimento',
            'verificato' => 'Verificato',
            'note' => 'Note',
        ];
    }

    /**
     * Gets query for [[Anagrafica]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAnagrafica()
    {
        return $this->hasOne(Anagrafica::class, ['id' => 'id_anagrafica']);
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
     * Gets query for [[MovimentoVerifica]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMovimentoVerifica()
    {
        return $this->hasOne(Movimento::class, ['id' => 'id_movimento_verifica']);
    }
}
