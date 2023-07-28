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
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'riconosciuto', 'patto_di_cura', 'attivo', 'liquidazione_decesso_completata', 'chiuso', 'id_anagrafica_disabile', 'id_distretto', 'id_gruppo', 'id_caregiver'], 'integer'],
            [['data_inserimento', 'classe_disabilita', 'data_riconoscimento', 'data_firma_patto', 'data_decesso', 'data_liquidazione_decesso', 'data_chiusura', 'nota_chiusura', 'note'], 'safe'],
            [['descrizione_gruppo'], 'safe'],
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
        $query = Istanza::find()->innerJoin('gruppo', 'gruppo.id = istanza.id_gruppo')
            ->innerJoin('distretto', 'distretto.id = istanza.id_distretto')
            ->innerJoin('anagrafica anagraficaDisabile', 'anagraficaDisabile.id = istanza.id_anagrafica_disabile');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->sort->attributes['gruppo.descrizione_gruppo'] = [
            'asc' => ['gruppo.descrizione_gruppo' => SORT_ASC],
            'desc' => ['gruppo.descrizione_gruppo' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['anagraficaDisabile.cognome_nome'] =
        [
            'asc' => ['anagraficaDisabile.cognome_nome' => SORT_ASC],
            'desc' => ['anagraficaDisabile.cognome_nome' => SORT_DESC],
        ];

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
            'id_caregiver' => $this->id_caregiver,
        ]);

        $query->andFilterWhere(['like', 'classe_disabilita', $this->classe_disabilita])
            ->andFilterWhere(['like', 'nota_chiusura', $this->nota_chiusura])
            ->andFilterWhere(['like', 'note', $this->note])
            ->andFilterWhere(['like', 'gruppo.descrizione_gruppo', $this->descrizione_gruppo])
            ->andFilterWhere(['like', 'anagraficaDisabile.cognome_nome', $this->getAttribute('anagraficaDisabile.cognome_nome')]);

        return $dataProvider;
    }
}
