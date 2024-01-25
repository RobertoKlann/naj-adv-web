<?php

namespace App\Models;

use App\Models\NajModel;

/**
 * Modelo da situação de tarefas.
 *
 * @since 2020-08-12
 */
class TarefaSituacaoModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('tarefas_situacao');

        $this->addColumn('id', true);
        $this->addColumn('situacao');
        $this->addColumn('grupo');
        $this->addColumn('ativa');
        
        $this->setOrder('id');
    }
    
}