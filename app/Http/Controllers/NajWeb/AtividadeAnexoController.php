<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajWeb\GoogleCloudStorageController;
use App\Http\Controllers\NajController;
use App\Models\ProcessoAnexoModel;

/**
 * Controller dos anexos das atividades.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Klann
 * @since      18/09/2021
 */
class AtividadeAnexoController extends NajController {

    public function __construct() {
        $base = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
        @list($dir,) = explode('/public', $base);

        $this->laravelStorageDir = $dir . '/storage/app/atividade_anexos/';

        parent::__construct();
    }

    public function onLoad() {
        $this->setModel(new ProcessoAnexoModel);
    }

    public function download($parameters) {
        $parametros   = json_decode(base64_decode($parameters));
        $pathStorage  = $this->getModel()->getPathStorage();

        $nameFile = $parametros->identificador . "/atividade_anexos/" . $parametros->id;
        //Verificando se é para subir pro GCP
        if($this->getModel()->isSyncGoogleStorage()) {
            $GCSController = new GoogleCloudStorageController($this->getModel()->getKeyFileGoogleStorage(), $pathStorage);

            return $GCSController->downloadFile($nameFile);
        }

        return file_get_contents($pathStorage . "/" . $nameFile);
    }
    
}