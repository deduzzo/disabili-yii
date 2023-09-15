<?php

namespace app\models\enums;

use yii2mod\enum\helpers\BaseEnum;

class IseeType extends BaseEnum
{
    const MAGGIORE_25K = ">MAGGIORE 25k";
    const MINORE_25K = "<MINORE 25k";
    const MINORE_25K_COLOR = "bg-primary";
    const MAGGIORE_25K_COLOR = "bg-warning";
    const NO_ISEE = "N/D";
    const NO_ISEE_COLOR = "bg-secondary";
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
        self::MAGGIORE_25K => 'Maggiore di 25.000€',
        self::MINORE_25K => 'Minore di 25.000€',
        self::NO_ISEE => 'N/D',
    ];
}