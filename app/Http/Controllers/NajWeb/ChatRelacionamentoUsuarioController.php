<?php

namespace App\Http\Controllers\NajWeb;

use App\Models\ChatRelacionamentoUsuarioModel;
use App\Http\Controllers\NajController;

/**
 * Controller do chat de relacionamento com usuários.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann.
 * @since      14/09/2020
 */
class ChatRelacionamentoUsuarioController extends NajController {

    public function onLoad() {
        $this->setModel(new ChatRelacionamentoUsuarioModel);
    }

    protected function resolveWebContext($usuarios, $code) {}

}