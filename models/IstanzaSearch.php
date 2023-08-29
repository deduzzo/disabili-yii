<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Istanza;

/**
 * IstanzaSearch represents the model behind the search form of `app\models\Istanza`.
 */
class IstanzaSearch extends Istanza
{
    public $descrizione_gruppo;
    public $cognomeNome;
    public $cf;
    public $distretto;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'riconosciuto', 'patto_di_cura', 'attivo', 'liquidazione_decesso_completata', 'chiuso', 'id_anagrafica_disabile', 'id_distretto', 'id_gruppo', 'id_caregiver'], 'integer'],
            [['data_inserimento', 'classe_disabilita', 'data_riconoscimento', 'data_firma_patto', 'data_decesso', 'data_liquidazione_decesso', 'data_chiusura', 'nota_chiusura', 'note'], 'safe'],
            [['descrizione_gruppo','cognomeNome','cf','distretto'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $this->descrizione_gruppo = $params['IstanzaSearch']['descrizione_gruppo'] ?? null;
        $this->cognomeNome = $params['IstanzaSearch']['cognomeNome'] ?? null;
        $this->cf = $params['IstanzaSearch']['cf'] ?? null;
        $query = Istanza::find()->innerJoin('gruppo', 'gruppo.id = istanza.id_gruppo')
            ->innerJoin('distretto', 'distretto.id = istanza.id_distretto')
            ->innerJoin('anagrafica', 'anagrafica.id = istanza.id_anagrafica_disabile')
            ->innerJoin('gruppo g', 'g.id = istanza.id_gruppo');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => isset($params['pageSize']) ? $params['pageSize'] : 100, // Default to 10 if not set
            ],
            'sort' => [
                'defaultOrder' => [
                    'data_inserimento' => SORT_DESC,
                ],
            ],
        ]);

        $dataProvider->sort->attributes['descrizione_gruppo'] = [
            'asc' => ['gruppo.descrizione_gruppo' => SORT_ASC],
            'desc' => ['gruppo.descrizione_gruppo' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['cognomeNome'] =
            [
                'asc' => ['anagrafica.cognome_nome' => SORT_ASC],
                'desc' => ['anagrafica.cognome_nome' => SORT_DESC],
            ];
        $dataProvider->sort->attributes['cf'] =
            [
                'asc' => ['anagrafica.cognome_nome' => SORT_ASC],
                'desc' => ['anagrafica.cognome_nome' => SORT_DESC],
            ];
        $dataProvider->sort->attributes['distretto'] =
            [
                'asc' => ['distretto.nome' => SORT_ASC],
                'desc' => ['distretto.nome' => SORT_DESC],
            ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if (!isset($this->attivo)) {
            $this->attivo = 1;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'data_inserimento' => $this->data_inserimento,
            'riconosciuto' => $this->riconosciuto,
            'data_riconoscimento' => $this->data_riconoscimento,
            'patto_di_cura' => $this->patto_di_cura,
            'data_firma_patto' => $this->data_firma_patto,
            'attivo' => $this->attivo,
            'data_decesso' => $this->data_decesso,
            'liquidazione_decesso_completata' => $this->liquidazione_decesso_completata,
            'data_liquidazione_decesso' => $this->data_liquidazione_decesso,
            'chiuso' => $this->chiuso,
            'data_chiusura' => $this->data_chiusura,
            'id_anagrafica_disabile' => $this->id_anagrafica_disabile,
            'id_distretto' => $this->id_distretto,
            'id_gruppo' => $this->id_gruppo,
            'id_caregiver' => $this->id_caregiver,
        ]);

        $query->andFilterWhere(['like', 'classe_disabilita', $this->classe_disabilita])
            ->andFilterWhere(['like', 'nota_chiusura', $this->nota_chiusura])
            ->andFilterWhere(['like', 'note', $this->note])
            ->andFilterWhere(['like', 'g.descrizione_gruppo', $this->descrizione_gruppo])
            ->andFilterWhere(['like', 'anagrafica.cognome_nome', $this->cognomeNome]);
        if ($this->cf)
            $query->andFilterWhere(['like', 'anagrafica.codice_fiscale', $this->cf]);
        if ($this->distretto)
            $query->andFilterWhere(['like', 'distretto.nome', $this->distretto]);

        return $dataProvider;
    }
}
