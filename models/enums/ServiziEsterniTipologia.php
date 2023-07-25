<?php

namespace app\models\enums;

use yii2mod\enum\helpers\BaseEnum;

class ServiziEsterniTipologia extends BaseEnum
{
    const CONTO = 'CONTO';
    const ID_FORNITORE = "ID_FORNITORE";
    const ELENCO_PAGAMENTO = "ELENCO_PAGAMENTO";
    const ISTANZA = "ISTANZA";

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
        self::CONTO => 'Conto',
        self::ID_FORNITORE => 'Id Fornitore',
        self::ELENCO_PAGAMENTO => 'Elenco Pagamento',
        self::ISTANZA => 'Istanza',
    ];
}