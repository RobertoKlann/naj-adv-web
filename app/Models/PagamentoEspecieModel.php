<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo de Pagamento Especie.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      11/02/2020
 */
class PagamentoEspecieModel extends NajModel {
    
    /**
     * Carrega Tabela
     */
    protected function loadTable() {
        $this->setTable('pagamento_especie');
        
        $this->addColumn('codigo', true);
        $this->addColumn('especie');
    }
    
    /**
     * Retorna os pagamentos especies e as unidades finaceira vinculadas aos mesmos
     * @return JSON
     */
    public function pagamentoEspecieUnidadeFinaceira(){
        //No mysql não é possivel usar a cláusula 'FULL OUTER JOIN'
        //Sendo assim para definir essa condição no mysql usamos o 'UNION'
        $result = DB::select("  SELECT pes.CODIGO, pes.ESPECIE, ufi.DESCRICAO
                                FROM pagamento_especie pes
                                left JOIN unidade_financeira ufi
                                ON (pes.CODIGO_UNIDADE_FINANCEIRA = ufi.CODIGO)
                                WHERE pes.ATIVO = 'S'
                                union
                                SELECT pes.CODIGO, pes.ESPECIE, ufi.DESCRICAO
                                FROM pagamento_especie pes
                                JOIN unidade_financeira ufi
                                ON (pes.CODIGO_UNIDADE_FINANCEIRA = ufi.CODIGO)
                                WHERE pes.ATIVO = 'S'
                                ORDER BY 2;");
        
        return response()->json($result);
    }
    
}
