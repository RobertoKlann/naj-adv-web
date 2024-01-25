<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo de divisão do cliente.
 *
 * @package    Models
 * @author     Roberto Oswaldo Klann
 * @since      20/04/2020
 */
class DivisaoModel extends NajModel {

    protected function loadTable() {
        $this->setTable('divisao');

        $this->addColumn('CODIGO', true);
        $this->addColumn('DIVISAO');
    }
    
    /**
     * Obtêm todos os registros de Divisão
     * 
     * @return JSON
     */
    public function getAllDivisao() {
        $sql = "SELECT CODIGO, DIVISAO FROM divisao;";
        return DB::select($sql);
    }
    
}