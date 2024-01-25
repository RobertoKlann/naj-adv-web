<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

class AppMonitoracaoModel extends NajModel {

    protected function loadTable() {
        $this->setTable('monitora');
        
        $this->addColumn('id', true);
        $this->addColumn('id_modulo');
        $this->addColumn('codigo_divisao');
        $this->addColumn('codigo_usuario');
        $this->addColumn('data_hora');
        $this->addColumn('acao');
    }
    
    public function getIdModulo($nomeModulo) {
        $idModulo = DB::select("
            SELECT ID
              FROM modulos
             WHERE TRUE
               AND modulo = '{$nomeModulo}'
        ");

        if(is_array($idModulo) && count($idModulo) > 0) return $idModulo[0]->ID;
    }
    
    public function getCodigoPessoa($cpfUsuario) {
        $pessoaUsuario = DB::select("
            SELECT CODIGO
              FROM pessoa
             WHERE TRUE
               AND cpf = '{$cpfUsuario}'
        ");

        if(is_array($pessoaUsuario) && count($pessoaUsuario) > 0) return $pessoaUsuario[0]->CODIGO;
    }

}