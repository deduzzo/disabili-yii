<?php

namespace app\models\enums;

use yii2mod\enum\helpers\BaseEnum;

class PagamentiConIban extends BaseEnum
{
    const SERVIZIO = "Servizio";
    const ID_ELENCO = "ID Elenco";
    const MATRICOLA = "Matricola";
    const COGNOME_NOME = "Cognome e Nome";
    const CODICE_FISCALE = "Codice Fiscale";
    const DAL = "Dal Periodo ";
    const AL = "Al Periodo ";
    const FASCIA = "Fascia di Reddito";
    const IMPORTO = "Importo";
    const IBAN1 = "NA";
    const IBAN2 = "C2";
    const IBAN3 = "C";
    const IBAN4 = "Abi";
    const IBAN5 = "Cab";
    const IBAN6 = "C/C";


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
        self::SERVIZIO => 'Servizio',
        self::ID_ELENCO => 'ID Elenco',
        self::MATRICOLA => 'Matricola',
        self::COGNOME_NOME => 'Cognome e Nome',
        self::CODICE_FISCALE => 'Codice Fiscale',
        self::DAL => 'Dal Periodo ',
        self::AL => 'Al Periodo ',
        self::FASCIA => 'Fascia di Reddito',
        self::IMPORTO => 'Importo',
        self::IBAN1 => 'NA',
        self::IBAN2 => 'C2',
        self::IBAN3 => 'C',
        self::IBAN4 => 'Abi',
        self::IBAN5 => 'Cab',
        self::IBAN6 => 'C/C',
    ];
}
