<?php

namespace app\models\enums;

use yii2mod\enum\helpers\BaseEnum;

class TipologiaDatiCategoria extends BaseEnum
{
    const ISTANZA = 'istanza';
    const ANAGRAFICA = "anagrafica";
    const SERVIZI_ESTERNI = 'servizi-esterni';
    const RICOVERI = 'ricoveri';
    const MOVIMENTI_CON_IBAN = 'movimenti-con-iban';
    const MOVIMENTI_CON_ELENCHI = 'movimenti-con-elenco';
    const TRACCIATO_SEPA = 'tracciato-sepa';
    const AGGIUNGI_DISTRETTO_GRUPPO = 'distretto-gruppo';
    const DECESSI = 'decessi';

    const LIQUIDAZIONE_EREDI_RAW = 'liquidazione-eredi-raw';

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
        self::RICOVERI => 'Ricoveri',
        self::MOVIMENTI_CON_IBAN => 'Movimenti con IBAN',
        self::MOVIMENTI_CON_ELENCHI => 'Movimenti con Elenco',
        self::TRACCIATO_SEPA => 'Tracciato SEPA',
        self::AGGIUNGI_DISTRETTO_GRUPPO => 'Aggiungi Distretto / Gruppo',
        self::DECESSI => 'Decessi',
        self::LIQUIDAZIONE_EREDI_RAW => 'Liquidazione Eredi da file',
    ];
}