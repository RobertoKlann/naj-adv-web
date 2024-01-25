<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\MonitoraProcessoTribunalModel;
use App\Models\MonitoramentoTribunalModel;
use App\Models\SysConfigModel;
use Illuminate\Http\Request;

/**
 * Controller de Monitora Processo Tribunal.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      22/10/2020
 */
class MonitoraProcessoTribunalController extends NajController {

    public function onLoad() {
        $this->setModel(new MonitoraProcessoTribunalModel);
    }

    /**
     * Retorna o max(id) no BD 
     * 
     * @return integer
     */
    public function proximo() {
        $proximo = $this->getModel()->max('id');
        if(!$proximo){
            $proximo = 0;
        }
        return response()->json($proximo)->content();
    }
    
    /**
     * Insere registro em monitora_processo_tribunal e em monitora_processo_tribunal_rel_prc
     * 
     * @param Request $request
     * @return JSON
     */
    public function insere(Request $request){
        $dados  = (object) $request->input();
        
        $MonitoramentoTribunalModel = new MonitoramentoTribunalModel();
        $result1                    = $MonitoramentoTribunalModel->totalDeMonitoramentosAtivosNoSistema();
        $SysConfigModel             = new SysConfigModel();
        $result2                    = $SysConfigModel->searchSysConfig('PROCESSOS','MONITORAMENTO_TRIBUNAL_QUOTA');
        if($result1 >= $result2){
            $result = "Você já atingiu a quantidade máxima de monitoramentos cadastrados no sistema, contate o seu supervisor!";
        }else{
            $result = $this->model->verificaSeMonitoramentoJaExiste($dados->codigo_processo);
            if($result === false){
                $result = $this->model->verificaSeProcessoExiste($dados->codigo_processo);
                if($result === true){
                    $result = $this->model->insere($dados);
                }
            }
        }
        return response()->json($result)->content();
    }
    
    /**
     * Atualiza registro em monitora_processo_tribunal e em monitora_processo_tribunal_rel_prc
     * 
     * @param Request $request
     * @return JSON
     */
    public function atualiza(Request $request){
        $dados  = (object) $request->input();
        $result = $this->model->atualiza($dados);
        return response()->json($result)->content();
    }
    
    /**
     * Atualiza o campo frequencia de todos os registro em monitora_processo_tribunal
     * 
     * @param Request $request
     * @return JSON
     */
    public function atualizaFrequencia(Request $request){
        $frequencia  = $request->input('frequencia');
        $result = $this->model->atualizaFrequencia($frequencia);
        return response()->json($result)->content();
    }
    
}