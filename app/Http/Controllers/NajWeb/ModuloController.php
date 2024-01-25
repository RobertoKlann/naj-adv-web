<?php

namespace App\Http\Controllers\NajWeb;

use App\Models\ModuloModel;
use App\Http\Controllers\NajController;

/**
 * Controller dos módulos do Sistema.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      14/04/2020
 */
class ModuloController extends NajController {

    public function onLoad() {
        $this->setModel(new ModuloModel);
    }

    protected function resolveWebContext($usuarios, $code) {}

    public function allGrupos($parametros) {
        $grupos = $this->getModel()->getAllGrupos($parametros);

        return response()->json($grupos);
    }

    public function allModulos($parametros) {
        $grupos = $this->getModel()->getAllModulosByGrupo($parametros);

        return response()->json($grupos);
    }

}