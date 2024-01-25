<?php

namespace App\Models;

use App\Models\NajModel;

/**
 *
 */
class AppChatMensagemStatusModel extends NajModel {

    public $incrementing = true;

    protected function loadTable() {
        $this->setTable('chat_mensagem_status');
        $this->addColumn('id', true);
        $this->addColumn('id_mensagem')->addJoin('chat_mensagem');
        $this->addColumn('status'); // 0=Enviada, 1=Entregue, 2=Lida
        $this->addColumn('status_data_hora');

        $this->primaryKey = 'id';
    }

}
