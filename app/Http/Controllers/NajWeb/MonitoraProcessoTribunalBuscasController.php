<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\MonitoraProcessoTribunalBuscasModel;

/**
 * Controller do Monitora Processo Tribunal Buscas.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      23/11/2020
 */
class MonitoraProcessoTribunalBuscasController extends NajController {

    /**
     * Seta o model de Monitora Processo Tribunal Buscas ao carregar o controller
     */
    public function onLoad() {
        $this->setModel(new MonitoraProcessoTribunalBuscasModel());
    }
    
    /**
     * Busca último registro em "monitora_processo_tribunal_buscas" com base no "id_monitora_tribunal"
     * 
     * @param int $id_monitora_tribunal
     * @return array
     */
    public function buscaMonitoraProcessoTribunalByIdMonitoraTribunal(){
        return $this->getModel()->buscaMonitoraProcessoTribunalByIdMonitoraTribunal();
    }
    
}
