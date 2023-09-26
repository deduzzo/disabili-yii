<?php

namespace app\components;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii2tech\spreadsheet\Spreadsheet;

class ExportWidget extends Widget
{
    public $models;
    public $columns;
    public function init()
    {
        parent::init();
        if ($this->models === null) {
            // call the exception
            throw new InvalidConfigException('Specificare il modello da esportare.');
        }
        if ($this->columns === null) {
            // retrive the columns from the model
            $this->columns = $this->models[0]->attributes();
        }
    }

    public function run()
    {
        if (Yii::$app->request->isPost && Yii::$app->request->post()['export'] == 'true')
        {
            $exporter = new Spreadsheet([
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => $this->models
                ]),
                'columns' => $this->columns,
                //'headerColumnUnions' => $initArray['headerColumnUnions']
            ]);
            $exporter->render();
            $exporter->send('out.xlsx');
        }
        return Html::beginForm('', 'POST').Html::hiddenInput('export', 'true').
            '<div class="btn-group dropdown me-1 mb-1">
                <button type="button" class="btn btn-warning">Esporta</button>
                <button type="button" class="btn btn-warning dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-reference="parent">
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu">
                    <h6 class="dropdown-header">Seleziona il formato</h6>
                    <a href="javascript:void(0);" class="dropdown-item active" onclick="event.preventDefault(); this.closest(\'form\').submit();">Pdf</a>
                </div>
            </div>'. Html::endForm();
    }
}