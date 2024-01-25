<?php

namespace App\Http\Controllers\NajWeb;

use App\Models\SysConfigModel;
use App\Http\Controllers\NajController;

/**
 * Controller dos relacionamentos da Advocacia x Usuário.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @author     William Goebel
 * @since      16/03/2020
 */
class SysConfigController extends NajController {

    public function onLoad() {
        $this->setModel(new SysConfigModel);
    }

    protected function resolveWebContext($usuarios, $code) {}

    /**
     * Verifica se Empresa existe
     * 
     * @param array $data
     * @return StdClass
     */
    public function existsEmpresa($data){
        return $this->getModel()->existsEmpresa($data);
    } 

    /**
     * Deleta registro no Sys_Config
     * 
     * @param string $secao
     * @param string $chave
     * @return int
     */
    public function destroySysConfig($secao, $chave) {
        return $this->getModel()->destroySysConfig($secao, $chave);
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
        $result = $this->getModel()->createSysConfig($secao, $chave, $valor);
        return response()->json($result)->content();
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
        $result = $this->getModel()->updateSysConfig($secao, $chave, $valor);
        return response()->json($result)->content();
    }
    
    /**
     * Busca o valor do campo valor do registro no Sys_Config
     * 
     * @param string $secao
     * @param string $chave
     * @return StdClass
     */
    public function searchSysConfig($secao, $chave) {
        $result = $this->getModel()->searchSysConfig($secao, $chave);
        return response()->json($result)->content();
    }
    
    /**
     * Busca todos os campos do registro no Sys_Config
     * 
     * @param string $secao
     * @param string $chave
     * @return StdClass
     */
    public function searchSysConfigAll($secao, $chave) {
        $result = $this->getModel()->searchSysConfigAll($secao, $chave);
        return response()->json($result)->content();
    }

    public function configuracaoPadrao() {
        return response()->json($this->getModel()->configuracaoPadrao());
    }
}