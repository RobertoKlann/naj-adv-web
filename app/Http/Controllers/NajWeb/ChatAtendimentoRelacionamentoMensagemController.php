<?php

namespace App\Http\Controllers\NajWeb;

use App\Models\ChatAtendimentoRelacionamentoMensagemModel;
use App\Http\Controllers\NajController;

/**
 * Controller do atendimento.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      08/07/2020
 */
class ChatAtendimentoRelacionamentoMensagemController extends NajController {

    public function onLoad() {
        $this->setModel(new ChatAtendimentoRelacionamentoMensagemModel);
    }

    protected function resolveWebContext($usuarios, $code) {}

}