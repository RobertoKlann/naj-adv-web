<?php

namespace App\Models;

use App\Models\NajModel;

/**
 *
 */
class AppChatModel extends NajModel {

    public $incrementing = true;

    protected function loadTable() {
        $this->setTable('chat');
        $this->addColumn('id', true);
        $this->addColumn('data_inclusao');
        $this->addColumn('tipo');
        $this->addColumn('nome');
        $this->addColumn('tag');

        $this->setOrder('id', 'desc');

        $this->primaryKey = 'id';
    }

}
