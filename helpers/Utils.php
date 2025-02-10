<?php

namespace app\helpers;


use app\models\Istanza;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\XLSX\Sheet;
use Carbon\Carbon;
use CodiceFiscale\InverseCalculator;
use CodiceFiscale\Validator;
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
        if (!is_object($date) || get_class($date) != "DateTime")
            return null;
        else return $date->format($destFormat);
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

    static function dumpDb($importToGdrive = true)
    {
        try {
            $dump = new Mysqldump(Yii::$app->db->dsn, Yii::$app->db->username, Yii::$app->db->password);
            //create a temp folder in system temp directory
            $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dump_' . Carbon::now()->timestamp;
            mkdir($tempDir);
            // filename var is backup_YYYY-MM-DD_HH-MM-SS.sql
            $filename = 'backup_' . Carbon::now()->format('Y-m-d_H-i-s');
            $dump->start($tempDir . '/' . $filename . '.sql');
            //create a zip file
            $zip = new ZipArchive();
            $zip->open($tempDir . '/' . $filename . '.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
            $zip->addFile($tempDir . '/' . $filename . '.sql', $filename . '.sql');
            $zip->close();
            if ($importToGdrive) {
                $gdrive = new GdriveHelper();
                $gdrive->uploadFileInFolder($tempDir . '/' . $filename . '.zip', $gdrive->backupFolderId, $filename . '.zip');
                // remove temp folder and all files
                array_map('unlink', glob("$tempDir/*.*"));
            }
        } catch (\Exception $e) {
            echo 'mysqldump-php error: ' . $e->getMessage();
        }
    }

    public static function verificaIban($iban)
    {
        return verify_iban($iban);
    }

    public static function verificaChiusuraAutomaticaIstanze()
    {
        // SELECT * FROM `istanza` WHERE chiuso = false AND data_chiusura is not null;
        $istanze = Istanza::find()->where(['chiuso' => false])->andWhere(['not', ['data_chiusura' => null]])->all();
        foreach ($istanze as $istanza) {
            /* @var $istanza Istanza */
            if (Carbon::createFromFormat('Y-m-d', $istanza->data_chiusura)->isBefore(Carbon::now())) {
                $istanza->chiuso = true;
                $istanza->attivo = false;
                $istanza->save();
            }

        }
    }

    public static function getEtaFromCf($cf, $dataRiferimento = null)
    {
        $inverseCalculator = new InverseCalculator($cf);
        if ((new Validator($cf))->isFormallyValid()) {
            $birthDate = $inverseCalculator->getSubject()->getBirthDate();
            $referenceDate = $dataRiferimento ? Carbon::parse($dataRiferimento) : Carbon::now();
            return Carbon::parse($birthDate)->diffInYears($referenceDate);
        } else return null;
    }

    public static function getDataNascitaFromCf($cf)
    {
        $inverseCalculator = new InverseCalculator($cf);
        if ((new Validator($cf))->isFormallyValid()) {
            $birthDate = $inverseCalculator->getSubject()->getBirthDate();
            return Carbon::parse($birthDate)->format('d/m/Y');
        } else return null;
    }

    public static function getNumGiorni($da, $a = null, $consideraUltimoGiorno = false): ?array
    {
        $out = ['giorni' => 0, 'mesi' => 0];
        $da = Carbon::createFromFormat('Y-m-d', $da);
        list($daAnno, $daMese, $daGiorno) = explode('-', $da);
        if (!$a) {
            $a = Carbon::now();
            list($aAnno, $aMese, $aGiorno) = explode('-', $a->toDateString());
        } else if (!$da) return null;
        else {
            $a = Carbon::createFromFormat('Y-m-d', $a);
            // id $da and $a are in different months
            list($aAnno, $aMese, $aGiorno) = explode('-', $a);
        }
        if ($consideraUltimoGiorno)
            $a->addDay();
        if (!checkdate(intval($daMese), intval($daGiorno), intval($daAnno)) || !checkdate(intval($aMese), intval($aGiorno), intval($aAnno)) || !$da->lessThanOrEqualTo($a)) {
            return null;
        } else {
            // if year is differente, add 12 months for each year
            if ($da->year !== $a->year)
                $out['mesi'] += ($a->year - $da->year) * 12;
            if ($da->month !== $a->month)
                $out['mesi'] += $a->diffInMonths($da);
            if ($a->day !== $da->day) {
                $quantiGiorni = $da->daysInMonth >30 ? 30 : ($da->daysInMonth  <30 ? 30 : $da->daysInMonth);
                if ($da->day < $a->day)
                    $out['giorni'] += $a->day - $da->day;
                else
                    $out['giorni'] += $quantiGiorni - $da->day + $a->day;
            }
        }
        return $out;
    }

    public static function getObjectFromFileExcel($path)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $reader = ReaderEntityFactory::createReaderFromFile($path);
        $reader->open($path);
        $header = [];
        $rowIndex = 0;
        $out = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            /* @var Sheet $sheet */
            $out[$sheet->getName()] = [];
            foreach ($sheet->getRowIterator() as $row) {
                $newRow = [];
                foreach ($row->getCells() as $idxcel => $cel) {
                    $newRow[$idxcel] = $cel->getValue();
                }
                if ($rowIndex === 0) {
                    foreach ($newRow as $idx => $cell)
                        $header[$idx] = $cell;
                } else {
                    $out[$sheet->getName()][$rowIndex] = [];
                    foreach ($newRow as $idxCol => $col) {
                        $out[$sheet->getName()][$rowIndex][$header[$idxCol]] = $col;
                    }
                }
                $rowIndex++;
            }
        }
        return ['header' => $header, 'data' => $out];
    }

}
