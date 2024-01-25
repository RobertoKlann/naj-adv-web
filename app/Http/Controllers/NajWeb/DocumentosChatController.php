<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajWeb\GoogleCloudStorageController;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\NajController;
use App\Models\DocumentosChatModel;

/**
 * Controlador dos documentos do Chat.
 *
 * @since 2020-08-11
 */
class DocumentosChatController extends NajController {

    private $laravelStorageDir;

    public function __construct() {
        $base = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
        @list($dir,) = explode('/public', $base);

        $this->laravelStorageDir = $dir . '/storage/app/pessoa_anexos/';

        parent::__construct();
    }

    public function onLoad() {
        $this->setModel(new DocumentosChatModel);
    }

    public function documentos($key) {
        return response()->json(['data' => $this->getModel()->documentos($key)]);
    }

    public function download($parameters) {
        $parametros   = json_decode(base64_decode($parameters));
        $originalName = $parametros->name;
        $pathStorage  = $this->getModel()->getPathStorage();

        $nameFile = $parametros->identificador . "/pessoa_anexos/" . $parametros->id;
        //Verificando se é para subir pro GCP
        if($this->getModel()->isSyncGoogleStorage()) {
            $GCSController = new GoogleCloudStorageController($this->getModel()->getKeyFileGoogleStorage(), $pathStorage);

            return $GCSController->downloadFile($nameFile);
        }

        return file_get_contents($pathStorage . "/" . $nameFile);
    }

}