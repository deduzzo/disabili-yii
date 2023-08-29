<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Conto;

/**
 * ContoSearch represents the model behind the search form of `app\models\Conto`.
 */
class ContoSearch extends Conto
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'attivo', 'id_istanza', 'data_disattivazione', 'data_creazione', 'data_modifica'], 'integer'],
            [['iban', 'note'], 'safe'],
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
    public function search($params,$istanza =null)
    {
        $query = Conto::find();
        if ($istanza)
            $query = $query->where(['id_istanza' => $istanza->id]);

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
            'attivo' => $this->attivo,
            'id_istanza' => $this->id_istanza,
            'data_disattivazione' => $this->data_disattivazione,
            'data_creazione' => $this->data_creazione,
            'data_modifica' => $this->data_modifica,
        ]);

        $query->andFilterWhere(['like', 'iban', $this->iban])
            ->andFilterWhere(['like', 'note', $this->note]);

        return $dataProvider;
    }
}
