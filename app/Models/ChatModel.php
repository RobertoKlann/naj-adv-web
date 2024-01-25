<?php

namespace App\Models;

use App\Models\NajModel;

/**
 * Modelo de boletos.
 *
 * @package    Models
 * @author     Roberto Oswaldo Klann
 * @since      13/07/2020
 */
class ChatModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('chat');

        $this->addColumn('id', true);
        $this->addColumn('data_inclusao');
        $this->addColumn('tipo');
        $this->addColumn('nome');
    }
    
}