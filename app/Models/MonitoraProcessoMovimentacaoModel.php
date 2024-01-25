<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Model de Monitora Processo Movimentacao.
 *
 * @package    Models
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      25/11/2020
 */
class MonitoraProcessoMovimentacaoModel extends NajModel {

    protected function loadTable() {
        $this->setTable('monitora_processo_movimentacao');

        $this->addColumn('id', true);
        $this->addColumn('id_monitora_processo');
        $this->addColumn('id_movimentacao');
        $this->addColumn('id_prc_movimento');
        $this->addColumn('conteudo');
        $this->addColumn('conteudo_json');
        $this->addColumn('data');
        $this->addColumn('data_distribuicao');
        $this->addColumn('lido');
        $this->addColumn('instancia');
        $this->addColumn('url_tj');
        $this->addColumn('sistema');
        $this->addColumn('assunto');
        $this->addColumn('classe');
        $this->addColumn('area');
        $this->addColumn('valor_causa');
        $this->addColumn('orgao_julgador');        
        $this->addColumn('data_hora_inclusao');
        $this->addColumn('data_ultima_atualizacao');
        $this->addColumn('data_hora_cadastro');
        #$this->addColumn('descartada');      
        #$this->addColumn('id_atividade');
        
        $this->primaryKey = 'id';
    }
    
    /**
     * Desvincula uma atividade de uma movimentação
     * 
     * @param int $id_movimentacao
     * @return bool
     */
    public function desvincularAtividade($id_movimentacao){
        $sql = "UPDATE monitora_processo_movimentacao SET id_atividade = null WHERE id = $id_movimentacao";
        $result = DB::update($sql);
        if($result == 1){
            return true;
        }else{
            return false;
        }
    }
    
}
