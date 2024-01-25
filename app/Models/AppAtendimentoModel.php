<?php

namespace App\Models;

use App\Models\NajModel;

/**
 *
 */
class AppAtendimentoModel extends NajModel {

    public $incrementing = true;

    protected function loadTable() {
        $this->setTable('chat_atendimento');
        $this->addColumn('id', true);
        $this->addColumn('id_chat')->addJoin('chat');
        $this->addColumn('id_usuario');
        $this->addColumn('data_hora_inicio');
        $this->addColumn('data_hora_termino');
        $this->addColumn('status');

        $this->addColumnFrom('chat', 'nome');

        $this->setOrder('chat_atendimento.id', 'desc');

        $this->primaryKey = 'id';
    }

}
