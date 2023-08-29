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
}