<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "documento_tipologia".
 *
 * @property int $id
 * @property string $tipo
 *
 * @property Documento[] $documentos
 */
class DocumentoTipologia extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'documento_tipologia';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tipo'], 'required'],
            [['tipo'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tipo' => 'Tipo',
        ];
    }

    /**
     * Gets query for [[Documentos]].
     *
     * @return \yii\db\ActiveQuery|DocumentoQuery
     */
    public function getDocumentos()
    {
        return $this->hasMany(Documento::class, ['id_tipologia' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return DocumentoTipologiaQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new DocumentoTipologiaQuery(get_called_class());
    }
}
