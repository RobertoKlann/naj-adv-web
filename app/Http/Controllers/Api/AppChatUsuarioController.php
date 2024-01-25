<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\NajController;
use App\Models\AppChatUsuarioModel;

/**
 *
 */
class AppChatUsuarioController extends NajController {

    public function onLoad() {
        $this->setModel(new AppChatUsuarioModel);
    }

}
