<?php

namespace App\Models;

use App\Models\NajModel;

/**
 * Modelo dos tipos de compromisso da agenda.
 *
 * @since 2020-10-06
 */
class AgendaTipoCompromissoModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('agenda_tipo_compromisso');

        $this->addColumn('CODIGO', true);
        $this->addColumn('DESCRICAO');
        
        $this->setOrder('CODIGO');

        $this->primaryKey = 'CODIGO';
    }
    
}