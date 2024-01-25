<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajWeb\GoogleCloudStorageController;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\NajController;
use App\Models\ProcessoAnexoModel;

/**
 * Controller dos anexos do processo.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Klann
 * @since      23/12/2020
 */
class ProcessoAnexoController extends NajController {

    private $laravelStorageDir;

    public function __construct() {
        $base = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
        @list($dir,) = explode('/public', $base);

        $this->laravelStorageDir = $dir . '/storage/app/prc_anexos/';

        parent::__construct();
    }

    public function onLoad() {
        $this->setModel(new ProcessoAnexoModel);
    }

    public function download($parameters) {
        $parametros   = json_decode(base64_decode($parameters));
        $pathStorage  = $this->getModel()->getPathStorage();

        $nameFile = $parametros->identificador . "/prc_anexos/" . $parametros->id;

        //VERIFICANDO SE É TEXTO VERSAO
        $codigoTextoVersao = $this->hasTextoVersao($parametros->id);

        if($codigoTextoVersao) {
            $versao   = $this->getLastVersaoTexto($codigoTextoVersao);
            $nameFile = $parametros->identificador . "/textos/txt" . $codigoTextoVersao . "_vs" . $versao . ".docx";
        }

        //Verificando se é para subir pro GCP
        if($this->getModel()->isSyncGoogleStorage()) {
            $GCSController = new GoogleCloudStorageController($this->getModel()->getKeyFileGoogleStorage(), $pathStorage);

            return $GCSController->downloadFile($nameFile);
        }

        return file_get_contents($pathStorage . "/" . $nameFile);
    }

    private function hasTextoVersao($codigo) {
        $ProcessoAnexoModel = new ProcessoAnexoModel();

        return $ProcessoAnexoModel->hasTextoVersao($codigo);
    }

    private function getLastVersaoTexto($codigo) {
        $versao = DB::select("
            SELECT VERSAO
              FROM texto_versao
             WHERE TRUE
               AND codigo_texto = {$codigo}
          ORDER BY versao DESC
             LIMIT 1
        ");

        return $versao[0]->VERSAO;
    }
    
}