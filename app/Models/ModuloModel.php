<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo de módulos do sistema.
 *
 * @package    Models
 * @author     Roberto Oswaldo Klann
 * @since      14/04/2020
 */
class ModuloModel extends NajModel {

    protected function loadTable() {
        $this->setTable('modulos');

        $this->addColumn('ID', true);
        $this->addColumn('MODULO');
        $this->addColumn('APELIDO');
        $this->addColumn('DESCRICAO');
        $this->addColumn('ID');
    }

    public function getAllGrupos($parameters) {
        $parametros = json_decode(base64_decode($parameters));
        $condition = '';

        if($parametros->divisao == 'N') {
            $condition = 'AND divisao = "N"';
        } else {
            $condition = 'AND divisao = "S"';
        }

        return DB::select("
              SELECT SUBSTRING_INDEX(APELIDO,' /',1) AS GRUPO
                FROM modulos
               WHERE APLICACAO = 'N'
               {$condition}
            GROUP BY GRUPO
            ORDER BY GRUPO ASC
        ");
    }

    public function getAllModulosByGrupo($values) {
        $parametros = json_decode(base64_decode($values));
        $grupo      = ($parametros->grupo == 'Mala_Direta') ? 'Mala Direta' : $parametros->grupo;
        $condition  = '';

        //Montando a condição do especial
        if($parametros->especial == 'N') {
            $condition = 'AND especial = "N"';
        } else if($parametros->especial == 'S') {
            $condition = 'AND especial = "S"';
        }

        //Montando a condição da divisão
        if($parametros->filterDivisao == 'S') {
            $condition .= ' AND divisao = "S"';
        } else if($parametros->filterDivisao == 'N') {
            $condition .= ' AND divisao = "N"';
        }

        $modulos = DB::select("
                SELECT id,
                       modulo,
                       apelido,
                       acessar,
                       pesquisar,
                       incluir,
                       alterar,
                       excluir,
                       especial,
                       'N' AS hasPermissao
                  FROM modulos
                 WHERE APLICACAO  = 'N'
                   AND SUBSTRING_INDEX(APELIDO,' /',1) like '%{$grupo}%'
                    {$condition}
              ORDER BY apelido
        ");

        foreach($modulos as $key => $modulo) {
            $moduloExistente = $this->hasPermissaoFromModulo($modulo->modulo, $parametros->divisao, $parametros->pessoa_codigo);
            if(is_array($moduloExistente) && count($moduloExistente) > 0) {
                $modulos[$key]->id           = $moduloExistente[0]->id;
                $modulos[$key]->pesquisar    = $moduloExistente[0]->pesquisar;
                $modulos[$key]->acessar      = $moduloExistente[0]->acessar;
                $modulos[$key]->incluir      = $moduloExistente[0]->incluir;
                $modulos[$key]->alterar      = $moduloExistente[0]->alterar;
                $modulos[$key]->excluir      = $moduloExistente[0]->excluir;
                $modulos[$key]->hasPermissao = 'S';
            } else {
                $modulos[$key]->pesquisar    = 'N';
                $modulos[$key]->acessar      = 'N';
                $modulos[$key]->incluir      = 'N';
                $modulos[$key]->alterar      = 'N';
                $modulos[$key]->excluir      = 'N';
                $modulos[$key]->hasPermissao = 'N';
            }
        }

        return $modulos;
    }

    public function hasPermissaoFromModulo($nomeModulo, $divisao, $pessoa) {
        //No dia 23/06/2020 foi alterado para IGUAL na condição do MODULO pois estava pegando vários modulos diferentes
        $modulo = DB::select("
              SELECT ID             as id,
                     CODIGO_PESSOA  as codigo_pessoa,
                     CODIGO_DIVISAO as codigo_divisao,
                     MODULO         as modulo,
                     APLICACAO      as aplicacao,
                     ACESSAR        as acessar,
                     PESQUISAR      as pesquisar,
                     ALTERAR        as alterar,
                     INCLUIR        as incluir,
                     EXCLUIR        as excluir
                FROM usuario_permissao
               WHERE modulo = '{$nomeModulo}'
                 AND codigo_pessoa = {$pessoa}
                 AND codigo_divisao = {$divisao};
        ");

        return $modulo;
    }

    public static function moduloIsGlobal($modulo) {
        $modulo = DB::select("
            SELECT *
              FROM modulos 
             WHERE TRUE
               AND divisao = 'N'
               AND modulo like '{$modulo}'
        ");

        return count($modulo) > 0;
    }

}