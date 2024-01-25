<?php

namespace App\Models;

use App\Models\NajModel;

/**
 * Modelo do status da mensagem.
 *
 * @package    Models
 * @author     Roberto Oswaldo Klann
 * @since      13/07/2020
 */
class ChatMensagemStatusModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('chat_mensagem_status');
        
        $this->addColumn('id', true);
        $this->addColumn('id_mensagem');
        $this->addColumn('status');
        $this->addColumn('status_data_hora');
    }
    
}