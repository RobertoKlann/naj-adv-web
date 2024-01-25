<?php

namespace App\Http\Controllers\NajWeb;

use App\Models\UsuarioModel;
use App\Http\Controllers\NajController;
use App\Http\Controllers\NajWeb\UsuarioController;

/**
 * Controller do install do sistema.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      10/03/2020
 */
class InstallController extends NajController {

    public function onLoad() {
        $this->setModel(new UsuarioModel);
    }

    protected function resolveWebContext($usuarios, $code) {}

    public function store($attrs = null) {
        $UsuarioController = new UsuarioController();
        $usuario = $this->getModel()->find(request()->get('id'));

        return $UsuarioController->storeUserByInstall($attrs);
    }

}