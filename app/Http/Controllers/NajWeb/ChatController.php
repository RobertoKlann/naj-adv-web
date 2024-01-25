<?php

namespace App\Http\Controllers\NajWeb;

use App\Models\ChatModel;
use App\Http\Controllers\NajController;
use App\Http\Controllers\NajWeb\ChatRelacionamentoUsuarioController;

/**
 * Controller do chat.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      13/07/2020
 */
class ChatController extends NajController {

    public function onLoad() {
        $this->setModel(new ChatModel);
    }

    public function handleItems($model = null) {
        $action = $this->getCurrentAction();
        
        if ($action === NajController::DESTROY_ACTION) {
            $this->destroyItems($model);
            
            return;
        }
        
        $this->{"{$action}Items"}($model);
    }

    public function storeItems($model) {
        $ChatRelacionamentoUsuarioController = new ChatRelacionamentoUsuarioController();
        $ChatRelacionamentoUsuarioController->store([
            'id_chat'    => $this->getModel()->max('id'),
            'id_usuario' => request()->get('id_usuario')
        ]);
    }

    public function updateItems($model) {}

    protected function resolveWebContext($usuarios, $code) {}

}