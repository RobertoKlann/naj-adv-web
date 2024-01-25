<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\PessoaClienteModel;

/**
 * Controllador de Pessoa x Usuário tipo cliente.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      23/01/2020
 */
class PessoaClienteController extends NajController {

    public function onLoad() {
        $this->setModel(new PessoaClienteModel);
    }

    protected function resolveWebContext($pessoas, $code) {
        return view('najWeb.pessoa');
    }

    public function handleItems($model = null) {
        $action = $this->getCurrentAction();
        
        if ($action === NajController::DESTROY_ACTION) {
            $this->destroyItems($model);
            
            return;
        }
        
        $this->{"{$action}Items"}($model);
    }

    public function isPessoaCliente($codigo_pessoa) {
        return response()->json($this->getModel()->isPessoaCliente($codigo_pessoa));
    }
    
    public function storeItems($model) {}

    public function updateItems($model) {}
    
    public function destroyItems($model) {}

}