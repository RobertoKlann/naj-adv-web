<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo das Permissões do Usuário.
 *
 * @package    Models
 * @author     Roberto Oswaldo Klann
 * @since      16/01/2020
 */
class UsuarioPermissaoModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('usuario_permissao');
        
        $this->addColumn('id', true);
        $this->addColumn('codigo_pessoa');
        $this->addColumn('codigo_divisao')->addJoin('divisao', 'codigo');
        $this->addColumn('modulo');
        $this->addColumn('aplicacao');
        $this->addColumn('acessar');
        $this->addColumn('pesquisar');
        $this->addColumn('alterar');
        $this->addColumn('incluir');
        $this->addColumn('excluir');

        $this->addColumnFrom('divisao', 'divisao', 'divisao_nome');
        
        $this->setOrder('usuario_permissao.id');
    }

    public function executaStore($permissao) {
        $proximo = DB::select("
            SELECT MAX(ID) proximo
              FROM usuario_permissao
        ");
        
        DB::insert(
            'INSERT INTO usuario_permissao (id, codigo_pessoa, codigo_divisao, modulo, aplicacao, acessar, pesquisar, alterar, incluir, excluir) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                ($proximo[0]->proximo + 1),
                $permissao['codigo_pessoa'],
                $permissao['codigo_divisao'],
                $permissao['modulo'],
                'U',
                ($permissao['acessar'] == 'S') ? 'S' : 'N',
                ($permissao['pesquisar'] == 'S') ? 'S' : 'N',
                ($permissao['alterar'] == 'S') ? 'S' : 'N',
                ($permissao['incluir'] == 'S') ? 'S' : 'N',
                ($permissao['excluir'] == 'S') ? 'S' : 'N'
            ]
        );
    }

    public function executaUpdate($permissao) {
        DB::update(
            'UPDATE usuario_permissao set acessar = ? , pesquisar = ? , alterar = ? , incluir = ? , excluir = ? where id = ?',
            [
                ($permissao['acessar'] == 'S') ? 'S' : 'N',
                ($permissao['pesquisar'] == 'S') ? 'S' : 'N',
                ($permissao['alterar'] == 'S') ? 'S' : 'N',
                ($permissao['incluir'] == 'S') ? 'S' : 'N',
                ($permissao['excluir'] == 'S') ? 'S' : 'N',
                $permissao['id']
            ]
        );
    }

    public function deleteAllPermissaoByUsuario($pessoa_codigo) {
        DB::table('usuario_permissao')->where('codigo_pessoa', $pessoa_codigo)->delete();
    }

    public function deletePermissaoGlobalByModulo($pessoa_codigo, $modulo) {
        DB::delete("
            DELETE
              FROM usuario_permissao
             WHERE TRUE
               AND codigo_pessoa = {$pessoa_codigo}
               AND modulo        = '{$modulo}'
        ");
    }

    public function getAllModulosByUsuario($pessoa_codigo) {
        return DB::select("
            SELECT *
              FROM usuario_permissao
             WHERE TRUE
               AND codigo_pessoa = {$pessoa_codigo}
        ");
    }

}