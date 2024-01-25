<?php

namespace App\Models;

use App\Models\NajModel;

/**
 * Modelo do relacionamento da tarefa e agenda.
 *
 * @since 2020-10-07
 */
class TarefaRelAgendaModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('tarefas_rel_agenda');

        $this->addColumn('ID_TAREFA', true);
        $this->addColumn('ID_COMPROMISSO_PRAZO_INTERNO');
        $this->addColumn('ID_COMPROMISSO_PRAZO_FATAL');

        $this->setOrder('ID_TAREFA');

        $this->primaryKey = 'ID_TAREFA';
    }
    
}