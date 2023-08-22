<?php

namespace app\models\enums;

use yii2mod\enum\helpers\BaseEnum;

class ImportoBase extends BaseEnum
{
    const MAGGIORE_25K_V1 = 840;
    const MINORE_25K_V1 = 1200;
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
        self::MAGGIORE_25K_V1 => 'Maggiore di 25k',
        self::MINORE_25K_V1 => 'Minore di 25k',
    ];
}