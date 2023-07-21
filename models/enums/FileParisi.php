<?php

namespace app\models\enums;

use yii2mod\enum\helpers\BaseEnum;

class FileParisi extends BaseEnum
{
    const ALERT = "ALERT";
    const CHIUSO = "CHIUSO";
    const CLASSE_DISABILITA = "CLASSE DISABILITA'";
    const CF_DISABILE = "C. F.  Disabile";
    const CF_CESSIONARIO = "Codice Fiscale Cess";
    const CODICE_FORNITORE = "Codice Fornitore";
    const DISABILE_NOME_COGNOME = "DISABILE";
    const CESSIONARIO_NOME_COGNOME = "CESSIONARIO";
    const DISABILE_COMUNE = "COMUNE Dis";
    const DARE_AVERE = "DARE AVERE";
    const DISABILE_DATA_NASCITA = "Data di nascita";
    const CESSIONARIO_DATA_NASCITA = "Data nascita  Cess";
    const DISABILE_DECEDUTO = "DECEDUTO";
    const DISABILE_DATA_DECESSO = "Decesso";
    const DISTRETTO = "Distretto";
    const ESCLUDI = "ESCLUDI";
    const GRUPPO = "Gruppo";
    const IBAN = "IBAN";
    const DISABILE_IBAN = "IBAN disabile";
    const IMPORTO = "IMPORTO";
    const DISABILE_INDIRIZZO = "Indirizzo Disabile";
    const RINUNZIA = "RINUNZIA";
    const RINUNZIA_DATA = "RINUNZIA DATA";
    const ATTIVO = "ATTIVO";
    const NOTE = "Note";
    const NOTE_ESCLUSIONE = "Note Esclusione";
    const NOTA_ALLERT = "Nota Allert";
    const NOTA_CHIUSO = "NOTA CHIUSO";


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
        self::ALERT => 'ALERT',
        self::CHIUSO => 'CHIUSO',
        self::CLASSE_DISABILITA => 'CLASSE DISABILITA',
        self::CF_DISABILE => 'C. F.  Disabile',
        self::CF_CESSIONARIO => 'Codice Fiscale Cess',
        self::CODICE_FORNITORE => 'Codice Fornitore',
        self::DISABILE_NOME_COGNOME => 'DISABILE',
        self::CESSIONARIO_NOME_COGNOME => 'CESSIONARIO',
        self::DISABILE_COMUNE => 'COMUNE Dis',
        self::DARE_AVERE => 'DARE AVERE',
        self::DISABILE_DATA_NASCITA => 'Data di nascita',
        self::CESSIONARIO_DATA_NASCITA => 'Data nascita  Cess',
        self::DISABILE_DECEDUTO => 'DECEDUTO',
        self::DISABILE_DATA_DECESSO => 'Decesso',
        self::DISTRETTO => 'Distretto',
        self::ESCLUDI => 'ESCLUDI',
        self::GRUPPO => 'Gruppo',
        self::IBAN => 'IBAN',
        self::DISABILE_IBAN => 'IBAN disabile',
        self::IMPORTO => 'IMPORTO',
        self::DISABILE_INDIRIZZO => 'Indirizzo Disabile',
        self::RINUNZIA => 'RINUNZIA',
        self::RINUNZIA_DATA => 'RINUNZIA DATA',
        self::ATTIVO => 'ATTIVO',
        self::NOTE => 'Note',
        self::NOTE_ESCLUSIONE => 'Note Esclusione',
        self::NOTA_ALLERT => 'Nota Allert',
        self::NOTA_CHIUSO => 'NOTA CHIUSO',
    ];
}