<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\MonitoraProcessoMovimentacaoModel;

/**
 * Controller do Monitora Processo Movimentacao.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      25/11/2020
 */
class MonitoraProcessoMovimentacaoController extends NajController {

    public function onLoad() {
        $this->setModel(new MonitoraProcessoMovimentacaoModel);
    }
    
    /**
     * Desvincula uma atividade de uma movimentação
     * 
     * @param int $id_movimentacao
     * @return JSON
     */
    public function desvincularAtividade($id_movimentacao){
        $result = $this->getModel()->desvincularAtividade($id_movimentacao);
        return response()->json($result)->content();        
    }
}
