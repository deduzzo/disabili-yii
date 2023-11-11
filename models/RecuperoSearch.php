<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Recupero;

/**
 * RecuperoSearch represents the model behind the search form of `app\models\Recupero`.
 */
class RecuperoSearch extends Recupero
{
    public $id_istanza;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'recuperato', 'rateizzato', 'num_rate', 'id_istanza'], 'integer'],
            [['importo', 'importo_rata'], 'number'],
            [['note'], 'safe'],
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
        $query = Recupero::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]]
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
            'importo' => $this->importo,
            'chiuso' => $this->chiuso,
            'annullato' => $this->annullato,
            'data_annullamento' => $this->data_annullamento,
            'rateizzato' => $this->rateizzato,
            'num_rate' => $this->num_rate,
            'importo_rata' => $this->importo_rata,
            'id_istanza' => $this->id_istanza,
            'id_recupero_collegato' => $this->id_recupero_collegato,
        ]);


        if ($this->id_istanza)
            $query->andWhere(['id_istanza' => $this->id_istanza]);

        $query->andFilterWhere(['like', 'note', $this->note]);

        return $dataProvider;
    }
}
