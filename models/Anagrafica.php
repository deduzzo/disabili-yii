<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "anagrafica".
 *
 * @property int $id
 * @property string $cognome_nome
 * @property string|null $nome
 * @property string|null $codice_fiscale
 * @property int|null $data_nascita
 * @property string|null $comune_nascita
 *
 * @property ContoCessionario[] $contoCessionarios
 * @property Istanza[] $istanzas
 * @property Residenza[] $residenzas
 */
class Anagrafica extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'anagrafica';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cognome_nome'], 'required'],
            [['data_nascita'], 'integer'],
            [['cognome_nome', 'nome', 'comune_nascita'], 'string', 'max' => 100],
            [['codice_fiscale'], 'string', 'max' => 20],
            [['codice_fiscale'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cognome_nome' => 'Cognome Nome',
            'nome' => 'Nome',
            'codice_fiscale' => 'Codice Fiscale',
            'data_nascita' => 'Data Nascita',
            'comune_nascita' => 'Comune Nascita',
        ];
    }

    /**
     * Gets query for [[ContoCessionarios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContoCessionarios()
    {
        return $this->hasMany(ContoCessionario::class, ['id_cessionario' => 'id']);
    }

    /**
     * Gets query for [[Istanzas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIstanzas()
    {
        return $this->hasMany(Istanza::class, ['id_anagrafica_disabile' => 'id']);
    }

    /**
     * Gets query for [[Residenzas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getResidenzas()
    {
        return $this->hasMany(Residenza::class, ['id_anagrafica' => 'id']);
    }
}
