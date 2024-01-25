<?php

namespace App\Http\Controllers\NajWeb;

use App\Models\UsuarioModel;
use App\Http\Controllers\NajController;
use App\Models\UsuarioRelacionamentoModel;
use App\Http\Controllers\NajWeb\UsuarioRelacionamentoMonitoramentoSistemaController;

/**
 * Controller dos Relacionamentos do Usuários.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      16/01/2020
 */
class UsuarioRelacionamentoController extends NajController {

    public function onLoad() {
        $this->setModel(new UsuarioRelacionamentoModel);
        $this->setMonitoramentoController(new UsuarioRelacionamentoMonitoramentoSistemaController);
    }

    protected function resolveWebContext($usuarios, $code) {
        return view('najWeb.usuario');
    }

    /**
     * Index da rota de usuários.
     */
    public function index() {
        return view('najWeb.consulta.UsuarioRelacionamentoConsultaView')->with('is_usuarios', true);
    }

    /**
     * Create da rota de usuários.
     */
    public function create() {
        return view('najWeb.manutencao.UsuarioRelacionamentoManutencaoView')->with('is_usuarios', true);
    }

    public function proximo() {
        $proximo = $this->getModel()->max('id');

        return response()->json($proximo);
    }

    public function handleItems($model = null) {
        $action = $this->getCurrentAction();
        
        if ($action === NajController::DESTROY_ACTION) {
            $this->destroyItems($model);
            
            return;
        }
        
        $this->{"{$action}Items"}($model);
    }
    
    public function storeItems($model) {}

    public function updateItems($model) {
        
    }
    
    public function destroyItems($model) {}

}