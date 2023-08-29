<?php

namespace app\models\enums;

use yii2mod\enum\helpers\BaseEnum;

class FileRicoveri extends BaseEnum
{
    const COD_STRUTTURA = 4;
    const COGNOME = 5;
    const NOME = 6;
    const DATA_NASCITA = 7;
    const COD_FISCALE = 8;
    const DISTRETTO = 9;
    const DATA_RICOVERO = 10;
    const DATA_DIMISSIONE = 11;


    /**
     * @var string message category
     * You can set your own message category for translate the values in the $list property
     * Values in the $list property will be automatically translated in the function `listData()`
     */
    public static $messageCategory = 'app';

    /**
     * @var array
     */
    public static $list = [
        self::COD_STRUTTURA => 'Codice struttara',
        self::COGNOME => 'Cognome',
        self::NOME => 'Nome ',
        self::DATA_NASCITA => 'Data di nascita',
        self::COD_FISCALE => 'Codice sanitario individuale',
        self::DISTRETTO => 'Distretto',
        self::DATA_RICOVERO => 'Data di ricovero',
        self::DATA_DIMISSIONE => 'Data di dimissione',
    ];
}