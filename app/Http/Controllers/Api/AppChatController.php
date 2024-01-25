<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\NajController;
use App\Models\AppChatModel;
use App\Http\Traits\MonitoraTrait;

/**
 * Controller de chat
 */
class AppChatController extends NajController {
    
    use MonitoraTrait;

    public function onLoad() {
        $this->setModel(new AppChatModel);
    }
    
    public function monitoracao() {
        return $this->resolveResponse(
            $this->monitora(
                'AcessoAreaClienteAPPMensagens',
                'Pesquisou por dados na rotina Chat'
            )
        );
    }

}
