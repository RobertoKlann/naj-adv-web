<?php

namespace App\Http\Controllers\NajWeb;

use App\Models\AtendimentoModel;
use App\Http\Controllers\NajController;

/**
 * Controller do atendimento.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      08/07/2020
 */
class AtendimentoController extends NajController {

    public function onLoad() {
        $this->setModel(new AtendimentoModel);
    }

    protected function resolveWebContext($usuarios, $code) {}

    /**
     * Busca todas as mensagens da advocacia.
     */
    public function allMessages() {
        return response()->json($this->getModel()->allMessages());
    }

    public function allMessagesChat($id) {
        return response()->json(['data' => $this->getModel()->allMessagesChat($id)]);
    }

    public function newMessagesFromChat($id) {
        return response()->json(['data' => $this->getModel()->newMessagesFromChat($id)]);
    }

    public function allMessagesFinish() {
        return response()->json($this->getModel()->allMessagesFinish());
    }

    public function quantidadeClienteByCard($parameters) {
        return response()->json($this->getModel()->quantidadeClienteByCard(json_decode(base64_decode($parameters))));
    }

    public function getQuantidadeClientePessoaGrupoByCard($parameters) {
        return response()->json($this->getModel()->getQuantidadeClientePessoaGrupoByCard(json_decode(base64_decode($parameters))));
    }

    public function getPessoasConsultaAvancada($parameters) {
        return response()->json($this->getModel()->getPessoasConsultaAvancada(json_decode(base64_decode($parameters))));
    }

    public function getPessoasGrupoConsultaAvancada($parameters) {
        return response()->json($this->getModel()->getPessoasGrupoConsultaAvancada(json_decode(base64_decode($parameters))));
    }

    public function getPessoasAniversarianteConsultaAvancada($parameters) {
        return response()->json($this->getModel()->getPessoasAniversarianteConsultaAvancada(json_decode(base64_decode($parameters))));
    }
    
}