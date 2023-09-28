<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Ricovero;

/**
 * RicoveroSearch represents the model behind the search form of `app\models\Ricovero`.
 */
class RicoveroSearch extends Ricovero
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'contabilizzare', 'id_istanza', 'id_determina', 'id_recupero'], 'integer'],
            [['da', 'a', 'cod_struttura', 'descr_struttura', 'note'], 'safe'],
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
        $query = Ricovero::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['da' => SORT_DESC]],
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
            'da' => $this->da,
            'a' => $this->a,
            'contabilizzare' => $this->contabilizzare,
            'id_istanza' => $this->id_istanza,
            'id_determina' => $this->id_determina,
            'id_recupero' => $this->id_recupero,
        ]);

        $query->andFilterWhere(['like', 'cod_struttura', $this->cod_struttura])
            ->andFilterWhere(['like', 'descr_struttura', $this->descr_struttura])
            ->andFilterWhere(['like', 'note', $this->note]);

        $query->orderBy(['contabilizzare'=>SORT_DESC,'da'=>SORT_ASC]);

        return $dataProvider;
    }
}
