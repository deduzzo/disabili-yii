<?php

namespace app\components;

use app\helpers\Utils;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii2tech\spreadsheet\Spreadsheet;

class ExportWidget extends Widget
{
    public $dataProvider;
    public $columns;
    public $query;
    public $postVars;
    public $serverSide = false;

    public function init()
    {
        parent::init();
        if ($this->dataProvider === null) {
            // call the exception
            throw new InvalidConfigException('Specificare il data provider');
        }
        if ($this->columns === null) {
            // retrive the columns from the data provider
            $this->columns = $this->dataProvider->getModels();
        }
        if ($this->postVars === null) {
            // retrive the columns from the data provider
            $this->postVars = [];
        }
    }

    public function run()
    {
        // no timeout
        set_time_limit(0);
        // no memory limit
        ini_set('memory_limit', '-1');
        if ($this->serverSide || (Yii::$app->request->isPost && isset(Yii::$app->request->post()['exportWDG']) && Yii::$app->request->post()['exportWDG'] == 'true')) {
            $query = Yii::$app->request->post()['query'] ?? '';
            $columns = $this->columns;
            if ($query !== "") {
                if (Utils::is_assoc($columns))
                    $columns = $columns[$query];
            } else {
                if (Utils::is_assoc($columns))
                    $columns = $columns['default'];
            }
            $exporter = new Spreadsheet([
                'dataProvider' => $this->dataProvider/* ?? (new ArrayDataProvider([
                    'allModels' => $this->models
                ]))*/,
                'columns' => $columns,
                //'headerColumnUnions' => $initArray['headerColumnUnions']
            ]);

            $exporter->applyCellStyle('A1:XFD1',
                [
                    'font' => [
                        'name' => 'Arial',
                        'bold' => true,
                        'size' => 14,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => [
                            'rgb' => 'e2f0d9'
                        ]
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'rgb' => '000000'
                            ]
                        ]
                    ],
                ]
            );


            $exporter->render();
            $exporter->send('export.xlsx');
        }
        $postVarsHtml = "";
        foreach ($this->postVars as $key => $value) {
            if (is_array($value)) {
                // Gestisci il caso in cui $value è un array
                foreach ($value as $arrayKey => $arrayValue) {
                    // Utilizza la sintassi per gli array nei nomi degli input
                    // Ad esempio, il nome diventerà 'key[arrayKey]'
                    $postVarsHtml .= Html::hiddenInput($key . "[$arrayKey]", $arrayValue);
                }
            } else {
                // Il caso predefinito, dove $value non è un array
                $postVarsHtml .= Html::hiddenInput($key, $value);
            }
        }
        return Html::beginForm('', 'POST', ['id' => "formExport", 'class' => 'd-flex align-items-center']) .
            Html::hiddenInput('exportWDG', 'true') .
            $postVarsHtml.
            ($this->query !== null
                ? (
                    Html::dropDownList(
                        'query',
                        null,
                        array_merge(['' => 'Selezione il tipo di report..'], $this->query),
                        ['class' => 'form-control', 'id' => 'query','style' => 'width: 300px; margin-right: 10px;']
                    )
                )
                : ''
            ) .
            '<div class="col-auto">' .
            '<div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle me-1" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Esporta
                                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="position: absolute; inset: auto auto 0px 0px; margin: 0px; transform: translate(0px, -40px);" data-popper-placement="top-start">
                        <a class="dropdown-item" href="javascript:void(0);" onclick="event.preventDefault(); document.getElementById(' . "'formExport'" . ').submit();">Excel</a>
                    </div>
                </div>' .
            '</div>' .
            Html::endForm();
    }
}