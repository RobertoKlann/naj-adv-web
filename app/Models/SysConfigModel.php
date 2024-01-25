<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo de relacionamento da Advocacia x Usuário.
 *
 * @package    Models
 * @author     Roberto Oswaldo Klann
 * @author     William Goebel
 * @since      16/03/2020
 */
class SysConfigModel extends NajModel {

    protected function loadTable() {
        $this->setTable('sys_config');

        $this->addColumn('ID', true);
        $this->addColumn('SECAO');
        $this->addColumn('CHAVE');
        $this->addColumn('VALOR');
    }

    /**
     * Verifica se Empresa existe
     * 
     * @param array $data
     * @return StdClass
     */
    public function existsEmpresa($data) {
        return DB::select("
            SELECT *
              FROM sys_config
             WHERE secao = '{$data['SECAO']}'
               AND chave = '{$data['CHAVE']}'
        ");
    }

    /**
     * Deleta registro no Sys_Config
     * 
     * @param string $secao
     * @param string $chave
     * @return int
     */
    public function destroySysConfig($secao, $chave) {
        return DB::table('sys_config')->where(['SECAO' => $secao, 'CHAVE' => $chave])->delete();
    }
    
    /**
     * Create registro no Sys_Config
     * 
     * @param string $secao
     * @param string $chave
     * @param string $valor
     * @return int
     */
    public function createSysConfig($secao, $chave, $valor) {
        if(is_null($valor) || ($valor == 'null')){
            $sql1 = "INSERT INTO sys_config (SECAO, CHAVE, VALOR) VALUES ('$secao', '$chave', null)";
        } else {
            $sql1 = "INSERT INTO sys_config (SECAO, CHAVE, VALOR) VALUES ('$secao', '$chave', '$valor')";
        }

        $result = DB::update($sql1);

        if($result == 1)
            return true;
        
        return false;
    }
    
    /**
     * Update registro no Sys_Config
     * 
     * @param string $secao
     * @param string $chave
     * @param string $valor
     * @return int
     */
    public function updateSysConfig($secao, $chave, $valor) {
        if(is_null($valor) || ($valor == 'null')){
            $sql1 = "UPDATE sys_config SET VALOR = null WHERE SECAO = '$secao' AND CHAVE = '$chave'";
        } else {
            $sql1 = "UPDATE sys_config SET VALOR = '$valor' WHERE SECAO = '$secao' AND CHAVE = '$chave'";
        }

        $result = DB::update($sql1);

        if($result == 1)
            return true;

        return false;
    }
    
    /**
     * Busca o valor do campo valor do registro no Sys_Config
     * 
     * @param string $secao
     * @param string $chave
     * @return StdClass
     */
    public function searchSysConfig($secao, $chave) {
        $sql = "SELECT VALOR FROM sys_config WHERE SECAO = '$secao' AND CHAVE = '$chave'";
        $result = DB::select($sql);

        if(count($result) > 0)
            return $result[0]->VALOR;
    }
    
    /**
     * Busca todos os campos do registro no Sys_Config
     * 
     * @param string $secao
     * @param string $chave
     * @return StdClass
     */
    public function searchSysConfigAll($secao, $chave) {
        $sql = "SELECT * FROM sys_config WHERE SECAO = '$secao' AND CHAVE = '$chave'";
        $result = DB::select($sql);

        if(count($result) > 0)
            return $result[0];
    }

    /**
     * Verifica se existe uma determinada chave existe no registro
     * 
     * @param type $section
     * @param type $key
     * @return string
     */
    public function existsChaveSysConfig($section, $key) {
        $result = DB::select("
            SELECT chave
              FROM sys_config
             WHERE TRUE
               AND SECAO = '{$section}'
               AND CHAVE = '{$key}'
        ");

        if(count($result) > 0)
            return $result[0]->chave;
    }

    public function configuracaoPadrao() {
        $a = 1;
        $keys = [
            [
                'section' => 'PROCESSOS',
                'key' => 'PRC_PARADOS_PERIODO',
                'default' => '3'
            ],
            [
                'section' => 'PROCESSOS',
                'key' => 'PRC_PARADOS_INTIMACAO',
                'default' => 'SIM'
            ],
            [
                'section' => 'PROCESSOS',
                'key' => 'PRC_PARADOS_ATIVIDADES',
                'default' => 'SIM'
            ],
            [
                'section' => 'PROCESSOS',
                'key' => 'PRC_PARADOS_ANDAMENTOS',
                'default' => 'SIM'
            ]
        ];

        foreach ($keys as $key) {
            $hasConfig = $this->searchSysConfig($key['section'], $key['key']);

            if(!$hasConfig) {
                $this->createSysConfig($key['section'], $key['key'], $key['default']);

                $hasConfig = $this->searchSysConfig($key['section'], $key['key']);
            }

            $configuration[$key['section']][$key['key']] = $hasConfig;
        }

        return $configuration;
    }
    
}
