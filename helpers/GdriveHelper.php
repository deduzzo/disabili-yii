<?php

namespace app\helpers;

use app\models\Anagrafica;
use app\models\AnagraficaAltricampi;
use app\models\Conto;
use app\models\ContoCessionario;
use app\models\Distretto;
use app\models\enums\FileGruppiGoogle;
use app\models\enums\FileParisi;
use app\models\Gruppo;
use app\models\Isee;
use app\models\Istanza;
use app\models\Movimento;
use app\models\Recupero;
use app\models\Ricovero;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\XLSX\Sheet;
use Carbon\Carbon;
use CodiceFiscale\Checker;
use CodiceFiscale\Validator;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Google_Service_Sheets;
use PHP_IBAN\IBAN;
use Yii;
use ZipStream\File;

class GdriveHelper
{
    const JSON_CONFIG_PATH = '../config/private/drive.json';
    public $folderId;
    public $backupFolderId;
    private $driveService;
    private $spreeadsheetService;
    private $client;

    public function __construct()
    {
        $this->folderId = Yii::$app->params['gdrive_folderId'];
        $this->backupFolderId = Yii::$app->params['gdrive_backupfolderId'];
        $this->client = new Google_Client();
        $this->client->setApplicationName('Disabili DRIVE');
        $this->client->addScope([Google_Service_Drive::DRIVE]);
        $this->client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);
        $this->client->setAuthConfig(self::JSON_CONFIG_PATH);  // Sostituisci con il percorso al tuo file JSON scaricato
        $this->client->setSubject('disabili-service@disabiliyiidrive.iam.gserviceaccount.com'); // L'email associata al Google Drive privato
        $this->driveService = new Google_Service_Drive($this->client);
        $this->spreeadsheetService = new Google_Service_Sheets($this->client);
    }


    public function uploadFileInFolder($localFilePath, $remoteFolderId, $fileName)
    {
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($fileName);
        $file->setParents(array($remoteFolderId));
        // Carica il file
        $data = file_get_contents($localFilePath);
        $createdFile = $this->driveService->files->create($file, array(
            'data' => $data,
            'mimeType' => 'application/octet-stream',
            'uploadType' => 'media',
            'supportsAllDrives' => true
        ));
        //echo "File ID: " . $createdFile->getId();
        return $createdFile;
    }

    public function createFolder($folderName, $remoteFolderId)
    {
        $folderName = $this->pulisciNome($folderName);
        $folder = new Google_Service_Drive_DriveFile();
        $folder->setName($folderName);
        $folder->setMimeType('application/vnd.google-apps.folder');
        $folder->setParents(array($remoteFolderId));
        // Crea la cartella
        $createdFolder = $this->driveService->files->create($folder, array('supportsAllDrives' => true));
        return $createdFolder;
    }

    public function folderExist($folderName, $remoteFolderId)
    {
        $folderName = $this->pulisciNome($folderName);
        $query = "name='$folderName' and mimeType='application/vnd.google-apps.folder' and '" . $remoteFolderId . "' in parents";

        // Esegui la ricerca
        $results = $this->driveService->files->listFiles(array(
            'q' => $query,
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true
        ));

        $files = $results->getFiles();

        if (count($files) > 0)
            return $files[0];
        else return null;
    }

    public function createFolderIfNotExist($folderName, $remoteFolderId)
    {
        $folderName = $this->pulisciNome($folderName);
        $folder = $this->folderExist($folderName, $remoteFolderId);
        if ($folder == null)
            $folder = $this->createFolder($folderName, $remoteFolderId);
        return $folder;
    }

    public function renameFolder($folderId, $newName)
    {
        $newName = $this->pulisciNome($newName);
        $newFolderMeta = new Google_Service_Drive_DriveFile();
        $newFolderMeta->setName($newName);

        // Aggiorna la cartella
        $updatedFolder = $this->driveService->files->update($folderId, $newFolderMeta, array('supportsAllDrives' => true));

        return $updatedFolder->getName();
    }

    public function getAllFilesInFolder($folderId)
    {
        // OTTENERE FILES NELLA CARTELLA CON ID:

        $query = "'$folderId' in parents";

        // Esegui la ricerca
        $results = $this->driveService->files->listFiles(array(
            'q' => $query,
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true
        ));

        return $results->getFiles();
    }

    public function serveFile($fileId)
    {

        $params = array(
            'supportsAllDrives' => true
        );
        $file = $this->driveService->files->get($fileId, $params);

        // Imposta gli header HTTP appropriati
        header('Content-Type: ' . $file->getMimeType());
        header('Content-Disposition: attachment; filename="' . $file->getName() . '"');

        // Ottieni il contenuto del file e invialo come risposta
        $responseParams = array(
            'alt' => 'media',
            'supportsAllDrives' => true
        );
        $response = $this->driveService->files->get($fileId, $responseParams);
        echo $response->getBody();
    }

    public function existFolderWithNameThatStartWith($searchString, $folderId)
    {
        // Creiamo la query
        $query = sprintf(
            "mimeType='application/vnd.google-apps.folder' and '%s' in parents and name starts with '%s'",
            $folderId,
            $this->pulisciNome($searchString)
        );

        // Parametri per la richiesta
        $params = array(
            'q' => $query,
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true
        );

        $results = $this->driveService->files->listFiles($params);

        return count($results) > 0 ? $results->getFiles()[0] : null;
    }

    public function verificaDatiNuoviDisabiliFiles($spreadsheetId)
    {
        $out = ['out' => "", 'cfs' => [], 'errors' => []];
        $response = $this->spreeadsheetService->spreadsheets->get($spreadsheetId);
        $sheets = $response->getSheets();
        $totaleMeseGlobale = 0;
        $countTotale = 0;
        foreach ($sheets as $sheet) {
            $sheetTitle = $sheet->getProperties()->getTitle();

            $range = $sheetTitle . '!A:AB';
            $values = $this->spreeadsheetService->spreadsheets_values->get($spreadsheetId, $range)->getValues();

            $count = 0;
            $totaleDistretto = 0;
            $inferiori = 0;
            $superiori = 0;
            foreach ($values as $index => $row) {
                if ($index > 1) {
                    if (isset($row[FileGruppiGoogle::CODICE_FISCALE]) && $row[FileGruppiGoogle::CODICE_FISCALE] !== "")
                        $out['cfs'][] = ["cf" => trim(strtoupper($row[FileGruppiGoogle::CODICE_FISCALE])), "distretto" => trim(strtoupper($row[FileGruppiGoogle::DISTRETTO]))];
                    if (isset($row[FileGruppiGoogle::DISTRETTO]) && $row[FileGruppiGoogle::DISTRETTO] !== "" && str_contains(strtoupper(trim($sheetTitle)), strtoupper(trim($row[FileGruppiGoogle::DISTRETTO]))) && (str_contains(strtolower(trim($row[FileGruppiGoogle::ESITO])), "positiv") || trim($row[FileGruppiGoogle::ESITO]) === "")) {
                        $count++;
                        if (isset($row[FileGruppiGoogle::CODICE_FISCALE]) && $row[FileGruppiGoogle::CODICE_FISCALE] !== "") {
                            $validator = new Validator(trim(strtoupper($row[FileGruppiGoogle::CODICE_FISCALE])));
                            if (!$validator->isFormallyValid())
                                $out['errors'][] = "CF non valido in riga: " . ($count + 1) . " nominativo: <b>" . $row[FileGruppiGoogle::COGNOME] . " " . $row[FileGruppiGoogle::NOME] . "</b> del foglio: " . $sheetTitle;
                        } else
                            $out['errors'][] = "CF non presente in riga: " . ($count + 1) . " nominativo:  <b>" . $row[FileGruppiGoogle::COGNOME] . " " . $row[FileGruppiGoogle::NOME] . "</b> del foglio: " . $sheetTitle;
                        if ((!isset($row[FileGruppiGoogle::IBAN_DISABILE]) || $row[FileGruppiGoogle::IBAN_DISABILE] === "") && (!isset($row[FileGruppiGoogle::IBAN_CESSIONARIO]) || $row[FileGruppiGoogle::IBAN_CESSIONARIO] === ""))
                            $out['errors'][] = "Iban non presente nella riga: " . ($count + 1) . " nominativo:  <b>" . $row[$row[FileGruppiGoogle::COGNOME]] . " " . $row[$row[FileGruppiGoogle::NOME]] . "</b> del foglio: " . $sheetTitle;
                        else {
                            $iban = (isset($row[FileGruppiGoogle::IBAN_DISABILE]) && $row[FileGruppiGoogle::IBAN_DISABILE] !== "") ? $row[FileGruppiGoogle::IBAN_DISABILE] : $row[FileGruppiGoogle::IBAN_CESSIONARIO];
                            if (!Utils::verificaIban(trim(strtoupper($iban))))
                                $out['errors'][] = "Iban non valido nella riga: " . ($count + 1) . " nominativo:  <b>" . $row[FileGruppiGoogle::COGNOME] . " " . $row[FileGruppiGoogle::NOME] . "</b> del foglio: " . $sheetTitle;
                        }
                        $tipoOk = str_contains(trim(strtolower($row[FileGruppiGoogle::ISEE])), "minore") || str_contains(trim(strtolower($row[FileGruppiGoogle::ISEE])), "inferiore") || str_contains(trim(strtolower($row[FileGruppiGoogle::ISEE])), "superiore");
                        $eta = Utils::getEtaFromCf($row[FileGruppiGoogle::CODICE_FISCALE]);
                        if ((!$tipoOk && ($eta && $eta>=18)) || ($eta && $eta>18 && str_contains(trim(strtolower($row[FileGruppiGoogle::ISEE])), "minore")))
                            $out['errors'][] = "Tipo ISEE non valido nella riga: " . ($count + 1) . " nominativo:  <b>" . $row[FileGruppiGoogle::COGNOME] . " " . $row[FileGruppiGoogle::NOME] . "</b> del foglio: " . $sheetTitle;
                        $eta = Utils::getEtaFromCf($row[FileGruppiGoogle::CODICE_FISCALE]);
                        $tipo = (!isset($row[FileGruppiGoogle::ISEE]) || $row[FileGruppiGoogle::ISEE] == "" || str_contains(trim(strtolower($row[FileGruppiGoogle::ISEE])), "inferiore") || str_contains(trim(strtolower($row[FileGruppiGoogle::ISEE])), "minore")) ? "inferiore" : "superiore";
                        $totaleDistretto += ($tipo === "inferiore") ? 1200 : 840;
                        if ($tipo === "inferiore") $inferiori++;
                        else $superiori++;
                    } else
                        if (isset($row[FileGruppiGoogle::DISTRETTO]) && $row[FileGruppiGoogle::DISTRETTO] !== "" &&
                            str_contains(strtoupper(trim($sheetTitle)), strtoupper(trim($row[FileGruppiGoogle::DISTRETTO]))) &&
                            str_contains(strtolower(trim($row[FileGruppiGoogle::ESITO])), "deced") && isset($row[FileGruppiGoogle::DATA_DECESSO]) &&
                            $row[FileGruppiGoogle::DATA_DECESSO] === "" && Utils::convertDateFromFormat($row[FileGruppiGoogle::DATA_DECESSO]) === null)
                            $out['errors'][] = "Data decesso non presente o non valida nella riga: " . ($count + 1) . " nominativo:  <b>" . $row[FileGruppiGoogle::COGNOME] . " " . $row[FileGruppiGoogle::NOME] . "</b> del foglio: " . $sheetTitle;
                }
            }
            $out['out'] .= $sheet->getProperties()->getTitle() . ": " . $count . "-> " . Yii::$app->formatter->asCurrency($totaleDistretto) . " [inferiori: " . $inferiori . ", superiori: " . $superiori . "]<br />";
            $totaleMeseGlobale += $totaleDistretto;
            $countTotale += $count;
        }
        $out['out'] .= "<br /> TOTALE NUOVI DISABILI: <b>" . $countTotale . "</b>";
        $out['out'] .= "<br /> TOTALE MENSILE STIMATO: <b>" . Yii::$app->formatter->asCurrency($totaleMeseGlobale) . "</b>";
        return $out;
    }

    public function importaNuovoGruppo($spreadsheetId, $gruppo, $cancellaTuttiDelGruppo = false)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $response = $this->spreeadsheetService->spreadsheets->get($spreadsheetId);
        $sheets = $response->getSheets();
        $errors = [];
        $cfs = [];
        //TODO
        if ($cancellaTuttiDelGruppo) {
            /*AnagraficaAltricampi::deleteAll();
            Ricovero::deleteAll();
            Isee::deleteAll();
            ContoCessionario::deleteAll();
            Conto::deleteAll();
            Movimento::deleteAll();
            Recupero::deleteAll();
            Istanza::deleteAll();
            Anagrafica::deleteAll();*/
            //Gruppo::deleteAll();
        }

        $transaction = Yii::$app->db->beginTransaction();
        foreach ($sheets as $sheet) {
            $sheetTitle = $sheet->getProperties()->getTitle();
            $range = $sheetTitle . '!A:AB';
            $distretto = Distretto::find()->where(['like', 'nome', '%' . strtoupper(substr($sheetTitle, 0, 4)) . '%', false])->one();
            $values = $this->spreeadsheetService->spreadsheets_values->get($spreadsheetId, $range)->getValues();
            foreach ($values as $index => $row) {
                if ($index > 1 && isset($row[FileGruppiGoogle::DISTRETTO]) && $row[FileGruppiGoogle::DISTRETTO] !== "") {
                    $istanza = Istanza::find()->innerJoin('anagrafica', 'anagrafica.id = istanza.id_anagrafica_disabile')->where(['codice_fiscale' => strtoupper(trim($row[FileGruppiGoogle::CODICE_FISCALE])), 'chiuso' => false])->one();
                    if (!$istanza && !in_array(strtoupper(trim($row[FileGruppiGoogle::CODICE_FISCALE])), $cfs)) {
                        $cfs[] = $row[FileGruppiGoogle::CODICE_FISCALE];
                        $disabile = Anagrafica::findOne(['codice_fiscale' => strtoupper($row[FileGruppiGoogle::CODICE_FISCALE])]);
                        if (!$disabile) {
                            $disabile = new Anagrafica();
                            $disabile->codice_fiscale = strtoupper(trim($row[FileGruppiGoogle::CODICE_FISCALE]));
                            $disabile->cognome = strtoupper(trim($row[FileGruppiGoogle::COGNOME]));
                            $disabile->nome = strtoupper(trim($row[FileGruppiGoogle::NOME]));
                            $disabile->data_nascita = Utils::convertDateFromFormat($row[FileGruppiGoogle::DATA_NASCITA_DISABILE]);
                            $disabile->indirizzo_residenza = strtoupper(trim($row[FileGruppiGoogle::INDIRIZZO_RESIDENZA_DISABILE]));
                            $disabile->save();
                            if ($disabile->errors)
                                $errors = array_merge($errors, ['disabile-' . $row[FileGruppiGoogle::CODICE_FISCALE] => $disabile->errors]);
                        }
                        if (isset($row[FileGruppiGoogle::CODICE_FISCALE_CESSIONARIO]) && strtoupper(trim($row[FileGruppiGoogle::CODICE_FISCALE_CESSIONARIO])) !== "") {
                            $cessionario = Anagrafica::findOne(['codice_fiscale' => strtoupper(trim($row[FileGruppiGoogle::CODICE_FISCALE_CESSIONARIO]))]);
                            if (!$cessionario) {
                                $cessionario = new Anagrafica();
                                $cessionario->codice_fiscale = strtoupper(trim($row[FileGruppiGoogle::CODICE_FISCALE_CESSIONARIO]));
                                $cessionario->cognome = strtoupper(trim($row[FileGruppiGoogle::COGNOME_CESSIONARIO]));
                                $cessionario->nome = strtoupper(trim($row[FileGruppiGoogle::NOME_CESSIONARIO]));
                                $cessionario->save();
                                if ($cessionario->errors)
                                    $errors = array_merge($errors, ['cessionario-' . $row[FileGruppiGoogle::CODICE_FISCALE_CESSIONARIO] => $cessionario->errors]);
                            }
                        } else $cessionario = null;
                        if ($disabile && $distretto && $gruppo) {
                            $istanza = new Istanza();
                            $istanza->id_distretto = $distretto->id;
                            $istanza->riconosciuto = strtoupper(trim($row[FileGruppiGoogle::ESITO] === "POSITIVO")) || (isset($row[FileGruppiGoogle::DATA_FIRMA_PATTO_CURA]) && strtoupper(trim($row[FileGruppiGoogle::DATA_FIRMA_PATTO_CURA])) !== "");
                            $istanza->id_gruppo = $gruppo->id;
                            $istanza->classe_disabilita = $row[FileGruppiGoogle::TIPOLOGIA_DISABILITA] ?? null;
                            $istanza->patto_di_cura = $istanza->riconosciuto;
                            $istanza->id_anagrafica_disabile = $disabile->id;
                            if ($cessionario)
                                $istanza->id_caregiver = $cessionario->id;
                            $istanza->attivo = false;
                            $istanza->data_decesso = isset($row[FileGruppiGoogle::DATA_DECESSO]) ? Utils::convertDateFromFormat($row[FileGruppiGoogle::DATA_DECESSO]) : null;
                            $istanza->chiuso = false;
                            $istanza->note = $row[FileGruppiGoogle::NOTE] ?? "";
                            $istanza->save();
                            if ($istanza->errors)
                                $errors = array_merge($errors, ['istanza-' . $row[FileGruppiGoogle::CODICE_FISCALE] => $istanza->errors]);
                            if (isset($row[FileGruppiGoogle::IBAN_DISABILE]) || isset($row[FileGruppiGoogle::IBAN_CESSIONARIO])) {
                                $contoString = strtoupper(trim($row[FileGruppiGoogle::IBAN_DISABILE])) !== "" ? strtoupper(trim($row[FileGruppiGoogle::IBAN_DISABILE])) : strtoupper(trim($row[FileGruppiGoogle::IBAN_CESSIONARIO]));
                                if ((new IBAN($contoString))->Verify()) {
                                    $conto = new Conto();
                                    $conto->id_istanza = $istanza->id;
                                    $conto->iban = $contoString;
                                    $conto->intestatario = strtoupper(trim($row[FileGruppiGoogle::IBAN_DISABILE])) !== "" ? ($disabile->cognome . " " . $disabile->nome) : ($row[FileGruppiGoogle::COGNOME_CESSIONARIO] . " " . $row[FileGruppiGoogle::NOME_CESSIONARIO]);
                                    $conto->save();
                                    if ($conto->errors)
                                        $errors = array_merge($errors, ['conto-' . $row[FileGruppiGoogle::CODICE_FISCALE] => $conto->errors]);
                                    if (strtoupper(trim($row[FileGruppiGoogle::IBAN_DISABILE])) == "" || $cessionario) {
                                        $contoCessionario = new ContoCessionario();
                                        $contoCessionario->id_conto = $conto->id;
                                        if ($cessionario)
                                            $contoCessionario->id_cessionario = $cessionario->id;
                                        else
                                            $contoCessionario->id_cessionario = $disabile->id;
                                        $contoCessionario->save();
                                        if ($contoCessionario->errors)
                                            $errors = array_merge($errors, ['contoCessionario-' . $row[FileGruppiGoogle::CODICE_FISCALE] => $contoCessionario->errors]);
                                    }
                                }
                            }
                            if (isset($row[FileGruppiGoogle::ISEE])) {
                                $isee = new Isee();
                                $isee->id_istanza = $istanza->id;
                                $isee->data_presentazione = Carbon::now()->format("Y-m-d");
                                $isee->maggiore_25mila = !((strtoupper(trim($row[FileGruppiGoogle::ISEE])) === "INFERIORE" || strtoupper(trim($row[FileGruppiGoogle::ISEE])) === "MINORENNE"));
                                $isee->valido = true;
                                $isee->save();
                                if ($isee->errors)
                                    $errors = array_merge($errors, ['isee-' . $row[FileGruppiGoogle::CODICE_FISCALE] => $isee->errors]);
                            }
                        }
                    } else
                        $errors = array_merge($errors, ['istanza-' . $row[FileGruppiGoogle::CODICE_FISCALE] => ['istanza già presente']]);
                }
            }
        }
        if (count($errors) === 0) {
            $transaction->commit();
            return true;
        } else {
            $transaction->rollBack();
            $date = date('Y-m-d_H-i-s');
            $folder = Yii::getAlias('@webroot') . '/' . Yii::$app->params['importPath'] . '/';
            $fp = fopen($folder . 'esito-importazione_' . $date . '.json', 'w');
            fwrite($fp, json_encode($errors));
            fclose($fp);
            Yii::$app->response->sendFile($folder . 'esito-importazione_' . $date . '.json');
            return false;
        }
    }

    private function pulisciNome($nome)
    {
        return preg_replace('/[^a-zA-Z0-9#\s-]/', '', $nome);
    }


}