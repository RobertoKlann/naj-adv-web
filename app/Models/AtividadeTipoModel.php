<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo de Atividade Tipo.
 *
 * @author William Goebel
 * @since 05/01/2021
 */
class AtividadeTipoModel extends NajModel {

    protected function loadTable() {
        $this->setTable('atividade_tipo');
        
        $this->addColumn('ID', true);
        $this->addColumn('ATIVIDADE');
        $this->addColumn('HISTORICO');
        
        $this->primaryKey = 'ID';
        
    }
    
    /**
     * Obtêm todos os registros de Atividade Tipo
     * 
     * @return JSON
     */
    public function getAllAtividadesTipos() {
        $sql = "SELECT ID, ATIVIDADE FROM atividade_tipo";
        return DB::select($sql);
    }
    
}