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
    public $models;
    public $columns;
    public $query;

    public function init()
    {
        parent::init();
        if ($this->models === null) {
            // call the exception
            throw new InvalidConfigException('Specificare il modello da esportare.');
        }
        if ($this->columns === null) {
            // retrive the columns from the model
            $this->columns = array_keys($this->models[0]);
        }
    }

    public function run()
    {
        if (Yii::$app->request->isPost && isset(Yii::$app->request->post()['exportWDG']) && Yii::$app->request->post()['exportWDG'] == 'true') {
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
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => $this->models
                ]),
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
        return Html::beginForm('', 'POST', ['id' => "formExport", 'class' => 'd-flex align-items-center']) .
            Html::hiddenInput('exportWDG', 'true') .
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
                    </div>
                </div>' .
            '</div>' .
            Html::endForm();
    }
}