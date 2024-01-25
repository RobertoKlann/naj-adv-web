<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

class PessoaRelacionamentoUsuarioModel extends NajModel {

    protected function loadTable() {
        $this->setTable('pessoa_rel_usuarios');

        $this->addColumn('pessoa_codigo', true);
        $this->addColumn('usuario_id'   , true);
    }

    public function getRelationsUserApp($userId) {
        $sql = "
            SELECT pessoa_codigo,
                   usuario_id,
                   'S' AS contas_pagar,
                   'S' AS contas_receber,
                   'S' AS atividades,
                   'S' AS processos
              FROM pessoa_rel_usuarios
             WHERE usuario_id = ?

             UNION

            SELECT pessoa_codigo,
                   usuario_id,
                   contas_pagar,
                   contas_receber,
                   atividades,
                   processos
              FROM pessoa_rel_clientes
             WHERE usuario_id = ?
        ";

        $relations = DB::select($sql, [$userId, $userId]);

        return $relations;
    }

    public function getRelacionamentosUsuario($codigo) {
        $pessoa_usuario = DB::select("
            SELECT *
              FROM pessoa_rel_usuarios
             WHERE TRUE
               AND usuario_id = {$codigo}
        ");

        if($pessoa_usuario) {
            return $pessoa_usuario;
        }

        return DB::select("
            SELECT *
              FROM pessoa_rel_clientes
             WHERE TRUE
               AND usuario_id = {$codigo}
        ");
    }
    
    /**
     * Verifica se pessoa existe em "pessoa_rel_usuarios"
     * 
     * @param int $pessoa_codigo
     * @return boolean
     */
    public function verificaSePessoaExisteEmPessoaRelUsuario($pessoa_codigo){
        $sql = "
            SELECT pessoa_codigo
              FROM pessoa_rel_usuarios
             WHERE pessoa_codigo = $pessoa_codigo
             LIMIT 1
        ";

        $result = DB::select($sql);

        if(count($result) == 1)
            return true;

        return false;
    }

    /**
     * Verifica se pessoa existe em "pessoa_rel_usuarios"
     * 
     * @param int $pessoa_codigo
     * @return boolean
     */
    public function verificaSePessoaExisteEmPessoaRelCliente($pessoa_codigo) {
        $sql = "
            SELECT pessoa_codigo
              FROM pessoa_rel_clientes
             WHERE pessoa_codigo = $pessoa_codigo
             LIMIT 1
        ";

        $result = DB::select($sql);

        if(count($result) == 1)
            return true;

        return false;
    }

    public function hasRelacionamentoToUser($usuario) {
        return DB::select("
            SELECT *
              FROM pessoa_rel_usuarios
             WHERE TRUE
               AND usuario_id = {$usuario}
        ");
    }

}
