<?php

namespace App\Models;

use App\Models\NajModel;

/**
 *
 */
class AppChatAtendimentoRelUsuarioModel extends NajModel {

    public $incrementing = true;

    protected function loadTable() {
        $this->setTable('chat_atendimento_rel_mensagem');
        $this->addColumn('id', true);
        $this->addColumn('id_mensagem')->addJoin('chat_mensagem');
        $this->addColumn('id_atendimento')->addJoin('chat_atendimento');

        $this->addColumnFrom('chat', 'nome');

        $this->primaryKey = 'id';
    }

}
