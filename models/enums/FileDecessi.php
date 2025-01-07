<?php

namespace app\models\enums;

use yii2mod\enum\helpers\BaseEnum;

class FileDecessi extends BaseEnum
{
    const CF = "cf";
    const COGNOME = "cognome";
    const NOME = "nome";
    const SESSO = "sesso";
    const DATA_NASCITA = "dataNascita";
    const COMUNE_NASCITA = "comuneNascita";
    const COD_COMUNE_NASCITA = "codComuneNascita";
    const COD_ISTAT_COMUNE_NASCITA = "codIstatComuneNascita";
    const PROVINCIA_NASCITA = "provinciaNascita";
    const INDIRIZZO = "indirizzoResidenza";
    const CAP = "capResidenza";
    const COMUNE_RESIDENZA = "comuneResidenza";
    const COD_COMUNE_RESIDENZA = "codComuneResidenza";
    const COD_ISTAT_COMUNE_RESIDENZA = "codIstatComuneResidenza";
    const ASP = "asp";
    const SSN_TIPO_ASSITITO = "ssnTipoAssistito";
    const SSN_ASSISTITO_RESIDENZA = "ssnInizioAssistenza";
    const SSN_FINE_ASSISTENZA = "ssnFineAssistenza";
    const SSN_MOTIVAZIONE_FINE_ASSISTENZA = "ssnMotivazioneFineAssistenza";
    const SSN_NUMERO_TESSERA = "ssnNumeroTessera";
    const MMG_ULTIMA_OPERAZIONE = "MMGUltimaOperazione";
    const MMG_ULTIMO_STATO = "MMGUltimoStato";
    const MMG_TIPO = "MMGTipo";
    const MMG_COD_REG = "MMGCodReg";
    const MMG_NOME = "MMGNome";
    const MMG_COGNOME = "MMGCognome";
    const MMG_CF = "MMGCf";
    const MMG_DATA_SCELTA = "MMGDataScelta";
    const MMG_DATA_REVOCA = "MMGDataRevoca";
    const DATA_DECESSO = "dataDecesso";
    const IN_VITA = "inVita";
    const ETA = "eta";

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
        self::CF => 'CF',
        self::COGNOME => 'Cognome',
        self::NOME => 'Nome',
        self::SESSO => 'Sesso',
        self::DATA_NASCITA => 'Data di nascita',
        self::COMUNE_NASCITA => 'Comune di nascita',
        self::COD_COMUNE_NASCITA => 'Codice comune di nascita',
        self::COD_ISTAT_COMUNE_NASCITA => 'Codice ISTAT comune di nascita',
        self::PROVINCIA_NASCITA => 'Provincia di nascita',
        self::INDIRIZZO => 'Indirizzo di residenza',
        self::CAP => 'CAP di residenza',
        self::COMUNE_RESIDENZA => 'Comune di residenza',
        self::COD_COMUNE_RESIDENZA => 'Codice comune di residenza',
        self::COD_ISTAT_COMUNE_RESIDENZA => 'Codice ISTAT comune di residenza',
        self::ASP => 'ASP',
        self::SSN_TIPO_ASSITITO => 'Tipo assistito SSN',
        self::SSN_ASSISTITO_RESIDENZA => 'Inizio assistenza SSN',
        self::SSN_FINE_ASSISTENZA => 'Fine assistenza SSN',
        self::SSN_MOTIVAZIONE_FINE_ASSISTENZA => 'Motivazione fine assistenza SSN',
        self::SSN_NUMERO_TESSERA => 'Numero tessera SSN',
        self::MMG_ULTIMA_OPERAZIONE => 'Ultima operazione MMG',
        self::MMG_ULTIMO_STATO => 'Ultimo stato MMG',
        self::MMG_TIPO => 'Tipo MMG',
        self::MMG_COD_REG => 'Codice regionale MMG',
        self::MMG_NOME => 'Nome MMG',
        self::MMG_COGNOME => 'Cognome MMG',
        self::MMG_CF => 'CF MMG',
        self::MMG_DATA_SCELTA => 'Data scelta MMG',
        self::MMG_DATA_REVOCA => 'Data revoca MMG',
        self::DATA_DECESSO => 'Data decesso',
        self::IN_VITA => 'In vita',
        self::ETA => 'Età',
    ];
}