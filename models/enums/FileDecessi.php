<?php

namespace app\models\enums;

use yii2mod\enum\helpers\BaseEnum;

class FileDecessi extends BaseEnum
{
    const COGNOME = "cognome";
    const NOME = "nome";
    const SESSO = "sesso";
    const DATA_NASCITA = "data_nascita";
    const COMUNE_NASCITA = "comune_nascita";
    const PROVINCIA_NASCITA = "provincia_nascita";
    const INDIRIZZO = "indirizzo";
    const TIPO_MMG = "tipoMMG";
    const CODICE_REGIONALE_MMG = "codiceRegionaleMMG";
    const NOMINATIVO_MMG = "nominativoMMG";
    const STATO_MMG = "statoMMG";
    const TIPO_OP_MMG = "tipoOpMMG";
    const DATA_SCELTA_MMG = "dataSceltaMMG";
    const DATA_REVOCA_MMG = "dataRevocaMMG";
    const CF = "cf";
    const DATA_DECESSO = "data_decesso";



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
        self::COGNOME => 'cognome',
        self::NOME => 'nome',
        self::SESSO => 'sesso',
        self::DATA_NASCITA => 'data_nascita',
        self::COMUNE_NASCITA => 'comune_nascita',
        self::PROVINCIA_NASCITA => 'provincia_nascita',
        self::INDIRIZZO => 'indirizzo',
        self::TIPO_MMG => 'tipoMMG',
        self::CODICE_REGIONALE_MMG => 'codiceRegionaleMMG',
        self::NOMINATIVO_MMG => 'nominativoMMG',
        self::STATO_MMG => 'statoMMG',
        self::TIPO_OP_MMG => 'tipoOpMMG',
        self::DATA_SCELTA_MMG => 'dataSceltaMMG',
        self::DATA_REVOCA_MMG => 'dataRevocaMMG',
        self::CF => 'cf',
        self::DATA_DECESSO => 'data_decesso',
    ];
}