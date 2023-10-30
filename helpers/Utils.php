<?php

namespace app\helpers;


use Carbon\Carbon;
use DateTime;
use Ifsnop\Mysqldump\Mysqldump;
use Yii;
use ZipArchive;

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

    static function dumpDb($importToGdrive = true) {
        try {
            $dump = new Mysqldump(Yii::$app->db->dsn, Yii::$app->db->username, Yii::$app->db->password);
            //create a temp folder in system temp directory
            $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dump_'.Carbon::now()->timestamp;
            mkdir($tempDir);
            // filename var is backup_YYYY-MM-DD_HH-MM-SS.sql
            $filename = 'backup_' . Carbon::now()->format('Y-m-d_H-i-s');
            $dump->start($tempDir.'/'.$filename.'.sql');
            //create a zip file
            $zip = new ZipArchive();
            $zip->open($tempDir.'/'.$filename.'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
            $zip->addFile($tempDir.'/'.$filename.'.sql', $filename.'.sql');
            $zip->close();
            if ($importToGdrive) {
                $gdrive = new GdriveHelper();
                $gdrive->uploadFileInFolder($tempDir.'/'.$filename.'.zip', $gdrive->backupFolderId,$filename.'.zip');
                // remove temp folder and all files
                array_map('unlink', glob("$tempDir/*.*"));
            }
        } catch (\Exception $e) {
            echo 'mysqldump-php error: ' . $e->getMessage();
        }
    }
}