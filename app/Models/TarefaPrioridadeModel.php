<?php

namespace App\Models;

use App\Models\NajModel;

/**
 * Modelo das prioridades de tarefas.
 *
 * @since 2020-08-12
 */
class TarefaPrioridadeModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('tarefas_prioridade');

        $this->addColumn('ID', true);
        $this->addColumn('PRIORIDADE');
        
        $this->setOrder('ID');
    }
    
}