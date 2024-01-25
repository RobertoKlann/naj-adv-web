<?php

namespace App\Models;

use App\Models\NajModel;

/**
 * Modelo dos Dispositivos do Usuários.
 *
 * @package    Models
 * @author     Roberto Oswaldo Klann
 * @since      16/01/2020
 */
class UsuarioDispositivoModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('dispositivos');
        
        $this->addColumn('id', true);
        $this->addColumn('usuario_id');
        $this->addColumn('ativo');
        
        $this->primaryKey = 'id';
    }
    
}