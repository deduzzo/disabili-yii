<?php

namespace app\models\enums;

use yii2mod\enum\helpers\BaseEnum;

class AnagraficaType extends BaseEnum
{
    const MINORE_18_COLOR = 'bg-warning';
    const MAGGIORE_18_COLOR = 'bg-info';



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
        self::MINORE_18_COLOR => 'Minore di 18 anni',
        self::MAGGIORE_18_COLOR => 'Maggiore di 18 anni',
    ];
}