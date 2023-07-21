<?php

namespace app\helpers;


use DateTime;

class Utils
{
    static function convertiDataInTimestamp($data, $formato = "d/m/Y") {
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
}