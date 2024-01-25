<?php

namespace App\Models;

use App\Models\NajModel;

/**
 * Modelo de relacionamento da mensagem e atendimento.
 *
 * @package    Models
 * @author     Roberto Oswaldo Klann
 * @since      14/07/2020
 */
class ChatAtendimentoRelacionamentoMensagemModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('chat_atendimento_rel_mensagem');
        
        $this->addColumn('id', true);
        $this->addColumn('id_mensagem');
        $this->addColumn('id_atendimento');
    }
    
}