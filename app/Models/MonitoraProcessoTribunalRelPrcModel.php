<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo de Monitora Processo Tribunal Rel Prc.
 *
 * @author William Goebel
 * @since 19/11/2020
 */
class MonitoraProcessoTribunalRelPrcModel extends NajModel {

    protected function loadTable() {
        $this->setTable('monitora_processo_tribunal_rel_prc');
        
        $this->addColumn('id', true);
        $this->addColumn('codigo_processo');
        $this->addColumn('id_monitora_tribunal');
        
        $this->primaryKey = 'id';
        
    }
    
}