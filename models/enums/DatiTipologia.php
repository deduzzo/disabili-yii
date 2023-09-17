<?php

namespace app\models\enums;

use yii2mod\enum\helpers\BaseEnum;

class DatiTipologia extends BaseEnum
{
    const AZIONE = 'azione';
    const DATO = "dato";
    const LISTA_TOTALI_ATTIVI_NON_CHIUSI = "lista_totali_attivi_non_chiusi";
    const LISTA_MINORI18 = "lista_minorenni";
    const LISTA_MAGGIORI_18 = "lista_maggiori_18";
    const LISTA_NO_DATA_NASCITA = "lista_no_data_nascita";
    const LISTA_MAGGIORI_25K = "lista_maggiori_25k";
    const LISTA_MINORI_25K = "lista_minori_25k";
    const LISTA_NO_ISEE = "lista_no_isee";


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
        self::AZIONE => 'Azione',
        self::DATO => 'Dato',
        self::LISTA_TOTALI_ATTIVI_NON_CHIUSI => 'Lista totali attivi_non_chiusi',
        self::LISTA_MINORI18 => 'Lista minori 18',
        self::LISTA_MAGGIORI_25K => 'Lista maggiori 25k',
        self::LISTA_MINORI_25K => 'Lista minori 25k',
        self::LISTA_NO_ISEE => 'Lista no isee',
        self::LISTA_MAGGIORI_18 => 'Lista maggiori 18',
        self::LISTA_NO_DATA_NASCITA => 'Lista no data nascita',
    ];
}