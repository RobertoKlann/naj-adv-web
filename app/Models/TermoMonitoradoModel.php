<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Model dos Termos Monitorados.
 *
 * @package    Models
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      26/05/2020
 */
class TermoMonitoradoModel extends NajModel {

    protected function loadTable() {
        $this->setTable('monitora_termo_diario');

        $this->addColumn('id', true);
        $this->addColumn('id_monitoramento');
        $this->addColumn('termo_pesquisa');
        $this->addColumn('variacoes');
        $this->addColumn('contem');
        $this->addColumn('nao_contem');
        $this->addColumn('data_inclusao');
        $this->addColumn('status');
        
        $this->primaryKey = 'id';
    }
 
    /**
     * Busca o Id do termo cadastrado no BD em "monitora_termo_diario"
     * 
     * @param string $filter Id do Tremo na Escavador
     * @return int
     */
    public function buscaIdTermo($filter){
        $result = DB::select(
            "SELECT id
            FROM monitora_termo_diario
            WHERE id_monitoramento = '{$filter}'
            LIMIT 1;"
        );
        if(count($result) > 0 ){
            return $result[0]->id;
        }
        return null;
    }
    
    /**
     * Busca o nome de todos os termos do BD
     * 
     * @return array
     */
    public function buscaNomeDosTermos(){
        $result = DB::select(
            "SELECT id, termo_pesquisa FROM monitora_termo_diario;"
        );
        $retorno = array();
        if(count($result) > 0 ){
            $retorno = $result;
        }
        return $retorno;
    }
}
