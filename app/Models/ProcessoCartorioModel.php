<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo de Processos Cartorio.
 *
 * @author William Goebel
 * @since 2020-09-15
 */
class ProcessoCartorioModel extends NajModel {

    protected function loadTable() {
        $this->setTable('prc_cartorio');
        
        $this->addColumn('CODIGO', true);
        $this->addColumn('CARTORIO');
        
        $this->primaryKey = 'CODIGO';
        
    }
    
    /**
     * Obtêm registros da tabela "prc_cartorio" que contennham o conteúdo do filtro 
     * 
     * @param string $filter
     * @return array
     */
    public function getProcessoCartorioInFilter($filter) {
        return DB::select(
            "SELECT CODIGO,
                    CARTORIO
               FROM prc_cartorio
              WHERE TRUE
                AND CARTORIO LIKE'%{$filter}%'
            "
        );
    }

    /**
     * Obtêm cartorio pelo nome
     * 
     * @param string $filter
     * @return array
     */
    public function getProcessoCartorioByName($filter){
        $sql = "SELECT * FROM prc_cartorio WHERE CARTORIO = '{$filter}'";
        return DB::select($sql);
    }
    
}