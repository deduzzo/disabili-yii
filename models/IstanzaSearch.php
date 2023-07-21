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
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'data_inserimento', 'riconosciuto', 'data_riconoscimento', 'patto_di_cura', 'data_firma_patto', 'attivo', 'data_decesso', 'liquidazione_decesso_completata', 'data_liquidazione_decesso', 'chiuso', 'data_chiusura', 'id_anagrafica_disabile', 'id_distretto', 'id_gruppo'], 'integer'],
            [['classe_disabilita', 'nota_chiusura', 'note'], 'safe'],
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
        $query = Istanza::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
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
        ]);

        $query->andFilterWhere(['like', 'classe_disabilita', $this->classe_disabilita])
            ->andFilterWhere(['like', 'nota_chiusura', $this->nota_chiusura])
            ->andFilterWhere(['like', 'note', $this->note]);

        return $dataProvider;
    }
}
