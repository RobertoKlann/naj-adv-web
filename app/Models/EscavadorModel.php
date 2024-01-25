<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo de Escavador.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      12/11/2020
 */
class EscavadorModel extends NajModel {
     
    /**
     * Carrega Tabela
     */
    protected function loadTable() {
        $this->setTable('sys_config');
    }
    
    /**
     * Verifica se o token do Escavador foi informado no BD
     * @return boolean|string
     */
    public function verificaTokenEscavador() {
        $sql = "SELECT VALOR
                FROM sys_config
                WHERE SECAO = 'ESCAVADOR'
                AND CHAVE = 'TOKEN'
                LIMIT 1";
        $result = DB::select($sql);
        if(count($result) > 0){
            //Nelson: "o TOKEN diminuiu de tamanho, então se esta mensagem for uma consistência sua, pode remover."
//            if(strlen($result[0]->VALOR) != 1091){
//                return "O valor do token da Escavador no 'sys_config' não contêm exatamente 1091 caracteres";
//            }
            return true;
        }else{
            return "A seção 'ESCAVADOR' com chave 'TOKEN' não foi informada no sys_config";
        }
    }
    
}