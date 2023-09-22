<?php

namespace app\models;
use yii\base\Model;
use yii\data\ArrayDataProvider;


class SimulazioneDeterminaSearch extends Model
{
    public $id;
    public $cf;
    public $cognome;
    public $nome;
    public $gruppo;
    public $distretto;
    public $isee;
    public $eta;
    public $importo;
    public $importoPrecedente;
    public $operazione;

    public function rules()
    {
        return [
            [['id', 'isee', 'eta'], 'integer'],
            [['cf', 'cognome', 'nome', 'gruppo', 'distretto'], 'safe'],
            [['importo','importoPrecedente'], 'number'],
            [['operazione'], 'string'],
        ];
    }

    public function search($params, $istanzeArray)
    {
        $this->load($params);

        $filteredIstanzeArray = array_filter($istanzeArray, function ($item) {
            return (
                (!$this->id || $item['id'] == $this->id) &&
                (!$this->cf || strpos($item['cf'], $this->cf) !== false) &&
                (!$this->cognome || strpos(strtoupper($item['cognome']), $this->cognome) !== false) &&
                (!$this->nome || strpos($item['nome'], $this->nome) !== false) &&
                (!$this->gruppo || strpos($item['gruppo'], $this->gruppo) !== false) &&
                (!$this->distretto || strpos($item['distretto'], $this->distretto) !== false) &&
                (!$this->isee || $item['isee'] == $this->isee) &&
                (!$this->eta || $item['eta'] == $this->eta)
            );
        });

        return new ArrayDataProvider([
            'allModels' => $filteredIstanzeArray,
            'pagination' => [
                'pageSize' => 100,
            ],
            'sort' => [
                'attributes' => ['id', 'cf', 'cognome', 'nome', 'gruppo', 'distretto', 'isee', 'eta','importo','importoPrecedente','operazione'],
            ],
        ]);
    }
}