<?php

namespace app\helpers;


/**
 * Helper per la visualizzazione dei dati nelle Views dei vari modelli
 *
 */
class ModelViewHelper
{

    public static function formatDate($originalDate, $originalFormat,$destinationFormat, $defaultOnNull = false, $emptyOnDefault = true, $default='1000-01-01'): string
    {
        if ($defaultOnNull)
            $ciao = "";
        if ($defaultOnNull && !$originalDate)
            return $default;
        else if ($originalDate === $default && $emptyOnDefault)
            return "";
        return date_format(date_create_from_format($originalFormat, $originalDate), $destinationFormat);
    }

}