<?php

namespace app\helpers;


use DateTime;

class Utils
{
    static function convertiDataInTimestamp($data, $formato = "d/m/Y")
    {
        try {
            if (is_string($data)) {
                $data = DateTime::createFromFormat($formato, $data);
            }
            if (get_class($data) != "DateTime") {
                return null;
            } else return $data->getTimestamp();
        } catch (\Exception $e) {
            return null;
        }
    }

    static function convertDateFromFormat($date, $originalFormat = "d/m/Y", $destFormat = "Y-m-d")
    {
        if (is_string($date)) {
            $date = DateTime::createFromFormat($originalFormat, $date);
        } else if (is_int($date)) {
            $date = DateTime::createFromFormat('dmY', $date);
        }
        if ($date === false || get_class($date) != "DateTime") {
            return null;
        } else return $date->format($destFormat);
    }

    static function jsonToHtml($jsonString)
    {
        // Sostituisci le chiavi
        $jsonString = preg_replace('/"([^"]+)"\s*:/', '<span class="json-key">"$1"</span>:', $jsonString);

        // Sostituisci le stringhe
        $jsonString = preg_replace('/:\s*"([^"]+)"/', ': <span class="json-string">"$1"</span>', $jsonString);

        // Sostituisci i numeri
        $jsonString = preg_replace('/:\s*([0-9]+)/', ': <span class="json-number">$1</span>', $jsonString);

        // Aggiungi classi per l'indentazione
        $jsonString = str_replace(["{\n", "[\n", "\n}", "\n]"], ["{<span class='json-indent'>", "[<span class='json-indent'>", "</span>}", "</span>]"], $jsonString);

        // Sostituisci le virgole e le nuove righe
        $jsonString = str_replace([',', "\n"], [',<br>', ''], $jsonString);

        return $jsonString;
    }

    static function is_assoc($var)
    {
        return is_array($var) && array_diff_key($var, array_keys(array_keys($var)));
    }
}