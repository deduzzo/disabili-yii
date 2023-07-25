<?php

namespace app\models\enums;

use yii2mod\enum\helpers\BaseEnum;

class PagamentiConElenchi extends BaseEnum
{
    const MESE = "Mese";
    const PROGRESSIVO = "Progressivo ";
    const MATRICOLA = "Matricola";
    const COGNOME_NOME = "Cognome e Nome";
    const NATO_IL = "Nato il";
    const FASCIA = "Fascia di Reddito";
    const DESCRIZIONE = "Descrizione Pagamento";
    const IMPORTO = "Importo";

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
        self::MESE => 'Mese',
        self::PROGRESSIVO => 'Progressivo ',
        self::MATRICOLA => 'Matricola',
        self::COGNOME_NOME => 'Cognome e Nome',
        self::NATO_IL => 'Nato il',
        self::FASCIA => 'Fascia di Reddito',
        self::DESCRIZIONE => 'Descrizione Pagamento',
        self::IMPORTO => 'Importo'
    ];
}