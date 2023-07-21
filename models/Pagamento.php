<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pagamento".
 *
 * @property int $id
 * @property float $importo
 * @property int|null $data
 * @property int|null $is_recupero
 * @property string|null $note
 * @property int $tornato_indietro
 * @property int|null $data_invio_notifica
 * @property int|null $data_incasso
 * @property int|null $id_determina
 * @property int|null $id_conto
 *
 * @property Determina $determina
 */
class Pagamento extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pagamento';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['importo'], 'required'],
            [['importo'], 'number'],
            [['data', 'is_recupero', 'tornato_indietro', 'data_invio_notifica', 'data_incasso', 'id_determina', 'id_conto'], 'integer'],
            [['note'], 'string'],
            [['id_determina'], 'exist', 'skipOnError' => true, 'targetClass' => Determina::class, 'targetAttribute' => ['id_determina' => 'id']],
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
            'is_recupero' => 'Is Recupero',
            'note' => 'Note',
            'tornato_indietro' => 'Tornato Indietro',
            'data_invio_notifica' => 'Data Invio Notifica',
            'data_incasso' => 'Data Incasso',
            'id_determina' => 'Id Determina',
            'id_conto' => 'Id Conto',
        ];
    }

    /**
     * Gets query for [[Determina]].
     *
     * @return \yii\db\ActiveQuery|DeterminaQuery
     */
    public function getDetermina()
    {
        return $this->hasOne(Determina::class, ['id' => 'id_determina']);
    }

    /**
     * {@inheritdoc}
     * @return PagamentoQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PagamentoQuery(get_called_class());
    }
}
