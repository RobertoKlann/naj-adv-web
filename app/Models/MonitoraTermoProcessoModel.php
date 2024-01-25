<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Model do Monitora Termo Processo.
 *
 * @package    Models
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      21/07/2020
 */
class MonitoraTermoProcessoModel extends NajModel {

    protected function loadTable() {
        $this->setTable('monitora_termo_processo');

        $this->addColumn('id', true);
        $this->addColumn('codigo_processo');
        $this->addColumn('numero_antigo');
        $this->addColumn('numero_novo');
        $this->addColumn('status');
        $this->addColumn('data_inclussao');
        $this->addColumn('data_ultima_movimentacao');
        $this->addColumn('tipo');
        
        $this->primaryKey = 'id';
    }
    
    /**
     * Desvincula Processo
     */
    public function desvinculaProcesso($id_monitora_termo_processo){
        $sql = "UPDATE monitora_termo_processo SET codigo_processo = null WHERE id = $id_monitora_termo_processo";
        $result = DB::update($sql);
        if($result){
            return response()->json("Processo desvinculado com sucesso!",200)->content();
        }else{
            return response()->json("Não foi possível desvincular o processo, contate o suporte!",400)->content();
        }
    } 
    
}


