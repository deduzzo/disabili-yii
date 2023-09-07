<?php

namespace app\models;

use app\models\Movimento;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * MovimentoSearch represents the model behind the search form of `app\models\Movimento`.
 */
class MovimentoSearch extends Movimento
{
    public $gruppoPagamentoDescrizione;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'data_invio_notifica', 'data_incasso', 'id_recupero', 'num_rata', 'id_gruppo_pagamento', 'id_conto'], 'integer'],
            [['is_movimento_bancario', 'tornato_indietro', 'contabilizzare'], 'boolean'],
            [['importo'], 'number'],
            [['data', 'periodo_da', 'periodo_a', 'note'], 'safe'],
            [['gruppoPagamentoDescrizione'], 'safe'],
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
    public function search($params, $istanza = null)
    {
        $query = Movimento::find()->innerJoin('conto c', 'movimento.id_conto = c.id')->
        leftJoin('gruppo_pagamento', 'movimento.id_gruppo_pagamento = gruppo_pagamento.id');
        if ($istanza)
            $query = $query->where(['c.id_istanza' => $istanza->id]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => isset($params['pageSize']) ? $params['pageSize'] : 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'data' => SORT_DESC, // or SORT_ASC for ascending
                ],
                'attributes' => [
                    'periodo_da',
                    'importo',
                    'is_movimento_bancario',
                    'data',
                    'periodo_da',
                    'periodo_a',
                    'tornato_indietro',
                    'data_invio_notifica',
                    'data_incasso',
                    'id_recupero',
                    'num_rata',
                    'contabilizzare',
                    'id_gruppo_pagamento',
                    'id_conto',
                    'note',
                    'gruppoPagamentoDescrizione' => [
                        'asc' => ['gruppo_pagamento.descrizione' => SORT_ASC],
                        'desc' => ['gruppo_pagamento.descrizione' => SORT_DESC],
                    ],
                ],
            ],
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
            'data' => $this->data,
            'periodo_da' => $this->periodo_da,
            'periodo_a' => $this->periodo_a,
            'tornato_indietro' => $this->tornato_indietro,
            'data_invio_notifica' => $this->data_invio_notifica,
            'data_incasso' => $this->data_incasso,
            'id_recupero' => $this->id_recupero,
            'num_rata' => $this->num_rata,
            'contabilizzare' => $this->contabilizzare,
            'id_gruppo_pagamento' => $this->id_gruppo_pagamento,
            'id_conto' => $this->id_conto,
            'is_movimento_bancario' => $this->is_movimento_bancario,
        ]);

        $query->andFilterWhere(['like', 'note', $this->note]);
        if ($this->gruppoPagamentoDescrizione) {
            $query->andFilterWhere(['like', 'gruppo_pagamento.descrizione', $this->gruppoPagamentoDescrizione]);
        }

        return $dataProvider;
    }
}
