<?php

namespace App\Models;

use App\Models\NajModel;

/**
 *
 */
class AppChatUsuarioModel extends NajModel {

    public $incrementing = true;

    protected function loadTable() {
        $this->setTable('chat_rel_usuarios');
        $this->addColumn('id', true);
        $this->addColumn('id_usuario');
        $this->addColumn('id_chat');

        $this->setOrder('id');

        $this->primaryKey = 'id';
    }

}
