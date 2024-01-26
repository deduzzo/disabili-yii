<?php

namespace app\models\enums;

use yii2mod\enum\helpers\BaseEnum;

class FileGruppiGoogle extends BaseEnum
{
    const ESITO = 0;
    const COD_FORNITORE = 1;
    const NOTE_FORNITORE = 2;
    const DISTRETTO = 3;
    const CODICE_FISCALE = 4;
    const COGNOME = 5;
    const NOME = 6;
    const DATA_NASCITA_DISABILE = 7;
    const DATA_DECESSO = 8;
    const COMUNE_NASCITA_DISABILE = 9;
    const COMUNE_RESIDENZA_DISABILE = 10;
    const INDIRIZZO_RESIDENZA_DISABILE = 11;
    const CAP_DISABILE = 12;
    const IBAN_DISABILE = 13;
    const NOME_CESSIONARIO = 14;
    const COGNOME_CESSIONARIO = 15;
    const LUOGO_NASCITA_CESSIONARIO = 16;
    const DATA_NASCITA_CESSIONARIO = 17;
    const CODICE_FISCALE_CESSIONARIO = 18;
    const INDIRIZZO_CESSIONARIO = 19;
    const IBAN_CESSIONARIO = 20;
    const CELLULARE = 21;
    const EMAIL = 22;
    const DATA_PRESENTAZIONE_ISTANZA = 23;
    const DATA_FIRMA_PATTO_CURA = 24;
    const TIPOLOGIA_DISABILITA = 25;
    const ISEE = 26;
    const NOTE = 27;
    const GRUPPO = 28;


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
        self::ESITO => "Esito",
        self::COD_FORNITORE => "Codice Fornitore",
        self::NOTE_FORNITORE => "Note Fornitore",
        self::DISTRETTO => "Distretto",
        self::CODICE_FISCALE => "Codice Fiscale",
        self::COGNOME => "Cognome",
        self::NOME => "Nome",
        self::DATA_NASCITA_DISABILE => "Data Nascita Disabile",
        self::DATA_DECESSO => "Data Decesso",
        self::COMUNE_NASCITA_DISABILE => "Comune Nascita Disabile",
        self::COMUNE_RESIDENZA_DISABILE => "Comune Residenza Disabile",
        self::INDIRIZZO_RESIDENZA_DISABILE => "Indirizzo Residenza Disabile",
        self::CAP_DISABILE => "Cap Disabile",
        self::IBAN_DISABILE => "Iban Disabile",
        self::NOME_CESSIONARIO => "Nome Cessionario",
        self::COGNOME_CESSIONARIO => "Cognome Cessionario",
        self::LUOGO_NASCITA_CESSIONARIO => "Luogo Nascita Cessionario",
        self::DATA_NASCITA_CESSIONARIO => "Data Nascita Cessionario",
        self::CODICE_FISCALE_CESSIONARIO => "Codice Fiscale Cessionario",
        self::INDIRIZZO_CESSIONARIO => "Indirizzo Cessionario",
        self::IBAN_CESSIONARIO => "Iban Cessionario",
        self::CELLULARE => "Cellulare",
        self::EMAIL => "Email",
        self::DATA_PRESENTAZIONE_ISTANZA => "Data Presentazione Istanza",
        self::DATA_FIRMA_PATTO_CURA => "Data Firma Patto Cura",
        self::TIPOLOGIA_DISABILITA => "Tipologia Disabilita",
        self::ISEE => "Isee",
        self::NOTE => "Note",
        self::GRUPPO => "Gruppo",
    ];
}