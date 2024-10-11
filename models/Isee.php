<?php

namespace app\models;

use Yii;

/*select insta1.id,an1.codice_fiscale, an1.nome, an1.cognome, dis.nome as distretto, isee.maggiore_25mila from istanza insta1, anagrafica an1, distretto dis, isee where insta1.id_distretto = dis.id AND insta1.chiuso = false AND insta1.attivo = true AND insta1.id_anagrafica_disabile = an1.id AND isee.id_istanza = insta1.id AND isee.valido = true AND insta1.id not in (
    select distinct ista.id from anagrafica ana, istanza ista, isee i
where ana.id = ista.id_anagrafica_disabile and ista.id = i.id_istanza and i.data_presentazione >= '2024-01-01' AND ista.chiuso = false AND i.valido = true)*/

/**
 * This is the model class for table "isee".
 *
 * @property int $id
 * @property float|null $importo
 * @property bool $maggiore_25mila
 * @property string|null $data_presentazione
 * @property int|null $anno_riferimento
 * @property string|null $data_scadenza
 * @property bool $valido
 * @property bool $verificato
 * @property string|null $valido_fino_a
 * @property int|null $id_istanza
 *
 * @property Istanza $istanza
 */
class Isee extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'isee';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['importo'], 'number'],
            [['maggiore_25mila', 'valido','verificato'], 'boolean'],
            [['data_presentazione', 'data_scadenza','valido_fino_a'], 'safe'],
            [['id_istanza','anno_riferimento'], 'integer'],
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
            'importo' => 'Importo',
            'maggiore_25mila' => 'Maggiore 25mila',
            'data_presentazione' => 'Data Presentazione',
            'anno_riferimento' => 'Anno Riferimento',
            'data_scadenza' => 'Data Scadenza',
            'valido' => 'Valido',
            'verificato' => 'Verificato',
            'valido_fino_a' => 'Valido Fino A',
            'id_istanza' => 'Id Istanza',
        ];
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
}
