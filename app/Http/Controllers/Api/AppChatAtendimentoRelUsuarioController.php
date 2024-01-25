<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\NajController;
use App\Models\AppChatAtendimentoRelUsuarioModel;

/**
 *
 */
class AppChatAtendimentoRelUsuarioController extends NajController {

    public function onLoad() {
        $this->setModel(new AppChatAtendimentoRelUsuarioModel);
    }

}
