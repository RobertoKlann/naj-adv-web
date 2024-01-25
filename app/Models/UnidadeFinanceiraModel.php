<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo de Unidade Financeira.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      11/02/2020
 */
class UnidadeFinanceiraModel extends NajModel {
    
    /**
     * Carrega Tabela
     */
    protected function loadTable() {
        $this->setTable('unidade_financeira');
        
        $this->addColumn('codigo', true);
        $this->addColumn('descricao');
    }
    
    /**
     * Retorna as unidades finaceiras ativas
     * @return JSON
     */
    public function unidades(){
        //Seleciona as unidades_finaceiras que estão relacionadas com unidade finaceira_extrato
        $sql = "SELECT unf.CODIGO, unf.DESCRICAO FROM unidade_financeira unf
                WHERE unf.ATIVO = 'S'
                ORDER BY 2;";
        $unidades_finaceiras = DB::select($sql); 
        
        return $unidades_finaceiras;
    }
    
}
