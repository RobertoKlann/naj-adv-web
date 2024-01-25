<?php

namespace App\Models;

use App\Models\NajModel;

/**
 * Modelo do relacionamento do chat com usuário.
 *
 * @package    Models
 * @author     Roberto Oswaldo Klann
 * @since      14/09/2020
 */
class ChatRelacionamentoUsuarioModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('chat_rel_usuarios');

        $this->addColumn('id', true);
        $this->addColumn('id_usuario');
        $this->addColumn('id_chat');
    }

}