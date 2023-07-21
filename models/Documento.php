<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "documento".
 *
 * @property int $id
 * @property int $id_tipologia
 * @property int|null $data
 * @property int $id_istanza
 *
 * @property Istanza $istanza
 * @property DocumentoTipologia $tipologia
 */
class Documento extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'documento';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_tipologia', 'id_istanza'], 'required'],
            [['id_tipologia', 'data', 'id_istanza'], 'integer'],
            [['id_tipologia'], 'exist', 'skipOnError' => true, 'targetClass' => DocumentoTipologia::class, 'targetAttribute' => ['id_tipologia' => 'id']],
            [['id_istanza'], 'exist', 'skipOnError' => true, 'targetClass' => Istanza::class, 'targetAttribute' => ['id_istanza' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_tipologia' => 'Id Tipologia',
            'data' => 'Data',
            'id_istanza' => 'Id Istanza',
        ];
    }

    /**
     * Gets query for [[Istanza]].
     *
     * @return \yii\db\ActiveQuery|IstanzaQuery
     */
    public function getIstanza()
    {
        return $this->hasOne(Istanza::class, ['id' => 'id_istanza']);
    }

    /**
     * Gets query for [[Tipologia]].
     *
     * @return \yii\db\ActiveQuery|DocumentoTipologiaQuery
     */
    public function getTipologia()
    {
        return $this->hasOne(DocumentoTipologia::class, ['id' => 'id_tipologia']);
    }

    /**
     * {@inheritdoc}
     * @return DocumentoQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new DocumentoQuery(get_called_class());
    }
}
