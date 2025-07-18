<?php

namespace app\models;

use Carbon\Carbon;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "anagrafica".
 *
 * @property int $id
 * @property string|null $cognome_nome
 * @property string|null $cognome
 * @property string|null $nome
 * @property string $codice_fiscale
 * @property string|null $data_nascita
 * @property string|null $comune_nascita
 * @property string|null $indirizzo_residenza
 * @property string|null $comune_residenza
 *
 * @property AnagraficaAltricampi[] $anagraficaAltricampis
 * @property ContoCessionario[] $contoCessionarios
 * @property Istanza[] $istanzas
 * @property Istanza[] $istanzas0
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

    public static function getAnagraficheList()
    {
       // return id, union cognome + nome, as map id => value
        return ArrayHelper::map(Anagrafica::find()->select(['id', 'CONCAT(cognome, " ", nome, " - ", DATE_FORMAT(data_nascita, "%d/%m/%Y")) as cognome_nome'])->
        where(['not', ['cognome' => null]])->andWhere(['not', ['nome' => null]])->orderBy('cognome_nome')->all(), 'id', 'cognome_nome');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['codice_fiscale'], 'required'],
            [['data_nascita'], 'safe'],
            [['cognome_nome', 'nome','cognome', 'comune_nascita', 'comune_residenza'], 'string', 'max' => 100],
            [['codice_fiscale'], 'string', 'max' => 20],
            [['indirizzo_residenza'], 'string', 'max' => 1000],
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
            'cognome_nome' => 'Nominativo',
            'nome' => 'Nome',
            'cognome' => 'Cognome',
            'codice_fiscale' => 'Codice Fiscale',
            'data_nascita' => 'Data Nascita',
            'comune_nascita' => 'Comune Nascita',
            'indirizzo_residenza' => 'Indirizzo Residenza',
            'comune_residenza' => 'Comune Residenza',
        ];
    }

    /**
     * Gets query for [[AnagraficaAltricampis]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAnagraficaAltricampis()
    {
        return $this->hasMany(AnagraficaAltricampi::class, ['id_anagrafica' => 'id']);
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
     * Gets query for [[Istanzas0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIstanzas0()
    {
        return $this->hasMany(Istanza::class, ['id_caregiver' => 'id']);
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

    public function getEta($dataRiferimento= null)
    {
        if (!$this->data_nascita)
            return null;
        $referenceDate = $dataRiferimento ? Carbon::parse($dataRiferimento) : Carbon::now();

        return Carbon::parse($this->data_nascita)->diffInYears($referenceDate);
    }


    public function isMinorenne() {
        return $this->getEta() < 18;
    }
}
