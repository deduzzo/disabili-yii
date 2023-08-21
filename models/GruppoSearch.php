<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Gruppo;

/**
 * GruppoSearch represents the model behind the search form of `app\models\Gruppo`.
 */
class GruppoSearch extends Gruppo
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'data_termine_istanze', 'data_inizio_beneficio'], 'integer'],
            [['descrizione_gruppo', 'descrizione_gruppo_old'], 'safe'],
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
        $query = Gruppo::find();

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
            'data_termine_istanze' => $this->data_termine_istanze,
            'data_inizio_beneficio' => $this->data_inizio_beneficio,
            'descrizione_gruppo' => $this->descrizione_gruppo,
            'descrizione_gruppo_old' => $this->descrizione_gruppo_old,
        ]);

        $query->andFilterWhere(['like', 'descrizione_gruppo', $this->descrizione_gruppo])
            ->andFilterWhere(['like', 'descrizione_gruppo_old', $this->descrizione_gruppo_old])->
                andFilterWhere(['like', 'data_termine_istanze', $this->data_termine_istanze])->
                andFilterWhere(['like', 'data_inizio_beneficio', $this->data_inizio_beneficio]);

        return $dataProvider;
    }
}
