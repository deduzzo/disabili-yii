<?php

namespace app\helpers;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Yii;

class GdriveHelper
{
    const JSON_CONFIG_PATH = '../config/private/drive.json';
    public $folderId;
    private $service;
    private $client;

    public function __construct()
    {
        $this->folderId = Yii::$app->params['gdrive_folderId'];
        $this->client = new Google_Client();
        $this->client->setApplicationName('Disabili DRIVE');
        $this->client->setScopes([Google_Service_Drive::DRIVE]);
        $this->client->setAuthConfig(self::JSON_CONFIG_PATH);  // Sostituisci con il percorso al tuo file JSON scaricato
        $this->client->setSubject('disabili-service@disabiliyiidrive.iam.gserviceaccount.com'); // L'email associata al Google Drive privato
        $this->service = new Google_Service_Drive($this->client);
    }


    public function uploadFileInFolder($localFilePath, $remoteFolderId, $fileName)
    {
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($fileName);
        $file->setParents(array($remoteFolderId));
        // Carica il file
        $data = file_get_contents($localFilePath);
        $createdFile = $this->service->files->create($file, array(
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
        $folderName =  $this->pulisciNome($folderName);
        $folder = new Google_Service_Drive_DriveFile();
        $folder->setName($folderName);
        $folder->setMimeType('application/vnd.google-apps.folder');
        $folder->setParents(array($remoteFolderId));
        // Crea la cartella
        $createdFolder = $this->service->files->create($folder, array('supportsAllDrives' => true));
        return $createdFolder;
    }

    public function folderExist($folderName, $remoteFolderId)
    {
        $folderName =  $this->pulisciNome($folderName);
        $query = "name='$folderName' and mimeType='application/vnd.google-apps.folder' and '" . $remoteFolderId . "' in parents";

        // Esegui la ricerca
        $results = $this->service->files->listFiles(array(
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
        $folderName =  $this->pulisciNome($folderName);
        $folder = $this->folderExist($folderName, $remoteFolderId);
        if ($folder == null)
            $folder = $this->createFolder($folderName, $remoteFolderId);
        return $folder;
    }

    public function renameFolder($folderId, $newName)
    {
        $newName =  $this->pulisciNome($newName);
        $newFolderMeta = new Google_Service_Drive_DriveFile();
        $newFolderMeta->setName($newName);

        // Aggiorna la cartella
        $updatedFolder = $this->service->files->update($folderId, $newFolderMeta, array('supportsAllDrives' => true));

        return $updatedFolder->getName();
    }

    public function getAllFilesInFolder($folderId)
    {
        // OTTENERE FILES NELLA CARTELLA CON ID:

        $query = "'$folderId' in parents";

        // Esegui la ricerca
        $results = $this->service->files->listFiles(array(
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
        $file = $this->service->files->get($fileId, $params);

        // Imposta gli header HTTP appropriati
        header('Content-Type: ' . $file->getMimeType());
        header('Content-Disposition: attachment; filename="' . $file->getName() . '"');

        // Ottieni il contenuto del file e invialo come risposta
        $responseParams = array(
            'alt' => 'media',
            'supportsAllDrives' => true
        );
        $response = $this->service->files->get($fileId, $responseParams);
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

        $results = $this->service->files->listFiles($params);

        return count($results) >0 ? $results->getFiles()[0] : null;
    }

    private function pulisciNome($nome) {
        return preg_replace('[^a-zA-Z0-9#-\s]', '', $nome);
    }


}