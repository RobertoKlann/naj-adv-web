<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo de Grupo de Pessoas.
 *
 * @package    Models
 * @author     Roberto Oswaldo Klann
 * @since      23/01/2020
 */
class GrupoPessoaModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('pessoa_grupo');
        
        $this->addColumn('codigo', true);
        $this->addColumn('grupo');
        $this->addColumn('principal');
    }
    
    /**
     * Obtêm todos os registros de Grupo Pessoa onde grupo seja igual a principal
     * 
     * @return JSON
     */
    public function getAllGrupoPessoa() {
        $sql = "SELECT CODIGO, GRUPO FROM pessoa_grupo
                WHERE PRINCIPAL = 'S';";
        return DB::select($sql);
    }
    
}