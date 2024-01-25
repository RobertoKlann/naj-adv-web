<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\ProcessoModel;
use App\Models\ProcessosParadoModel;

/**
 * Controller de processos.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Klann
 * @author     William Goebel
 * @since      20/07/2020
 */
class ProcessoController extends NajController {

    public function onLoad() {
        $this->setModel(new ProcessoModel);
    }

    /**
     * Create da rota de Processo
     * @return view
     */
    public function create() {
        return view('najWeb.manutencao.ProcessoManutencaoView');
    }
    
    /**
     * Retorna o max(id) no BD 
     * @return integer
     */
    public function proximo() {
        $proximo = $this->getModel()->max('codigo');
        if(!$proximo){
            $proximo = 0;
        }
        return response()->json($proximo)->content();
    }
    
    public function anexos($key) {
        return response()->json($this->getModel()->anexos($key));
    }

    public function getPartes($key) {
        return response()->json($this->getModel()->getPartes($key));
    }  

    public function getParteCliente($key) {
        return response()->json($this->getModel()->getParteCliente($key));
    }

    public function getParteAdversaria($key) {
        return response()->json($this->getModel()->getParteAdversaria($key));
    }

    /**
     * Obtêm os registros de "prc_qualificacao"
     * 
     * @return JSON
     */
    public function getPrcQualificacao(){
        $result = $this->getModel()->getPrcQualificacao();
        return response()->json($result)->content();
    }
    
    /**
     * Obtêm os registros de "prc_orgao"
     * 
     * @return JSON
     */
    public function getPrcOrgao(){
        $result = $this->getModel()->getPrcOrgao();
        return response()->json($result)->content();
    }
    
    /**
     * Obtêm os registros de "prc_situacao"
     * 
     * @return JSON
     */
    public function getPrcSituacao(){
        $result = $this->getModel()->getPrcSituacao();
        return response()->json($result)->content();
    }
    
    public function getIdAreaJuridica($codigo_processo){
        $result = $this->getModel()->getIdAreaJuridica($codigo_processo);
        return response()->json($result)->content();
    }

    public function paginate() {
        //verificando se precisa registrar o monitoramento
        if($this->getMonitoramentoController() && !$this->isSkipsRoute()) {
            $this->getMonitoramentoController()->storeMonitoramento(self::PAGINATE_ACTION);
        }

        return $this->processPaginationAfter(
            $this->getModel()->makePagination()
        );
    }

}