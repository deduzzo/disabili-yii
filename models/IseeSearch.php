<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Isee;

/**
 * IseeSearch represents the model behind the search form of `app\models\Isee`.
 */
class IseeSearch extends Isee
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'data_presentazione', 'data_scadenza', 'id_istanza'], 'integer'],
            [['maggiore_25mila', 'valido'], 'boolean'],
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
        $query = Isee::find();

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
            'maggiore_25mila' => $this->maggiore_25mila,
            'data_presentazione' => $this->data_presentazione,
            'data_scadenza' => $this->data_scadenza,
            'valido' => $this->valido,
            'id_istanza' => $this->id_istanza,
        ]);

        return $dataProvider;
    }
}
