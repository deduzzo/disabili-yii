<?php

namespace app\models\enums;

use yii2mod\enum\helpers\BaseEnum;

class TipologiaDatiCategoria extends BaseEnum
{
    const ISTANZA = 'istanza';
    const ANAGRAFICA = "anagrafica";

    const SERVIZI_ESTERNI = 'servizi-esterni';

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
        self::ISTANZA => 'Istanza',
        self::ANAGRAFICA => 'Anagrafica',
        self::SERVIZI_ESTERNI => 'Servizi Esterni',
    ];
}