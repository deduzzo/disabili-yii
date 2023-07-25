<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Decreto;

/**
 * DecretoSearch represents the model behind the search form of `app\models\Decreto`.
 */
class DecretoSearch extends Decreto
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'data', 'dal', 'al', 'inclusi_minorenni', 'inclusi_maggiorenni'], 'integer'],
            [['descrizione_atto', 'note'], 'safe'],
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
        $query = Decreto::find();

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
            'data' => $this->data,
            'dal' => $this->dal,
            'al' => $this->al,
            'inclusi_minorenni' => $this->inclusi_minorenni,
            'inclusi_maggiorenni' => $this->inclusi_maggiorenni,
        ]);

        $query->andFilterWhere(['like', 'descrizione_atto', $this->descrizione_atto])
            ->andFilterWhere(['like', 'note', $this->note]);

        return $dataProvider;
    }
}
