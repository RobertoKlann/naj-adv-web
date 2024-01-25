<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo dos Relacionamentos do Usuários.
 *
 * @package    Models
 * @author     Roberto Oswaldo Klann
 * @since      16/01/2020
 */
class UsuarioRelacionamentoModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('pessoa_rel_clientes');
        
        $this->addColumn('usuario_id'   , true);
        $this->addColumn('pessoa_codigo', true)->addJoin('pessoa', 'CODIGO');
        $this->addColumn('contas_pagar');
        $this->addColumn('contas_receber');
        $this->addColumn('atividades');
        $this->addColumn('processos');
        $this->addColumn('agenda');

        $this->addColumnFrom('pessoa', 'nome'  , 'nome');
        $this->addColumnFrom('pessoa', 'cpf'   , 'cpf');
        $this->addColumnFrom('pessoa', 'cnpj'  , 'cnpj');
        $this->addColumnFrom('pessoa', 'cidade', 'cidade');
        
        $this->setOrder('pessoa_rel_clientes.usuario_id');
        
        $this->primaryKey = ['usuario_id', 'pessoa_codigo'];
    }

    public function hasRelacionamentoToUser($usuario) {
        return DB::select("
            SELECT *
              FROM pessoa_rel_clientes
             WHERE TRUE
               AND usuario_id = {$usuario}
        ");
    }

}