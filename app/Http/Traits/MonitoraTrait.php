<?php

namespace App\Http\Traits;

use App\Http\Controllers\Api\AppMonitoracaoController;

trait MonitoraTrait {
    
    protected $monitora = false;
    
    /**
     * Insere a monitoração no banco
     */
    protected function monitora($nome, $acao) {
        $user = $this->getUserFromToken();
        
        $AppMonitoracaoController = new AppMonitoracaoController;
        $storeResult = $AppMonitoracaoController->store([
            'id_modulo'      => $AppMonitoracaoController->getModel()->getIdModulo($nome),
            'codigo_divisao' => 1,
            'codigo_usuario' => $AppMonitoracaoController->getModel()->getCodigoPessoa($user->cpf),
            'data_hora'      => date('Y-m-d H:i:s'),
            'acao'           => $acao,
        ]);
        
        return $storeResult;
    }
    
}
