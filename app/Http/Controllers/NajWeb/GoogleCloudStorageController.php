<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\GoogleCloudStorageModel;
use Google\Cloud\Storage\StorageClient;

/**
 * Controllador do Google Cloud Storage.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      04/08/2020
 */
class GoogleCloudStorageController extends NajController {

    /**
     * Instância do storage GCS.
     */
    protected $Storage;

    /**
     * Instância do storage Bucket.
     */
    protected $Bucket;


    public function __construct($pathKeyFile, $nameBucket) {
        $this->Storage = new StorageClient([
            'keyFilePath' => $pathKeyFile
        ]);

        $this->Bucket = $this->Storage->bucket($nameBucket);
    }

    public function onLoad() {
        $this->setModel(new GoogleCloudStorageModel);
    }

    public function storeFile($file, $nameFile) {
        return $this->Bucket->upload($file, ['name' => $nameFile]);
    }

    public function downloadFile($id) {
        $file = $this->Bucket->object($id);

        if($file->exists()) {
            return $file->downloadAsString();
        }

        return null;
    }

    public function copyFile($originalName, $nameFile) {
        $file = $this->Bucket->object($originalName);

        return $file->copy($this->Bucket, ['name' => $nameFile]);
    }

    public function getSizeFile($id) {
        $file = $this->Bucket->object($id);

        if($file->exists()) {
            $info = $file->info();

            return $info['size'];
        }

        return false;
    }

}