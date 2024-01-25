<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\NajController;
use App\Models\AppChatMensagemStatusModel;

/**
 *
 */
class AppChatMensagemStatusController extends NajController {

    public function onLoad() {
        $this->setModel(new AppChatMensagemStatusModel);
    }

}
