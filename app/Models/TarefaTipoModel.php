<?php

namespace App\Models;

use App\Models\NajModel;

/**
 * Modelo do tipos de tarefas.
 *
 * @since 2020-08-12
 */
class TarefaTipoModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('tarefas_tipo');

        $this->addColumn('ID', true);
        $this->addColumn('TIPO');
        $this->addColumn('CODIGO_TIPO_COMPROMISSO');
        
        $this->setOrder('ID');

        $this->primaryKey = 'ID';
    }
    
}