<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\NajController;
use App\Models\AppMonitoracaoModel;

class AppMonitoracaoController extends NajController {

    public function onLoad() {
        $this->setModel(new AppMonitoracaoModel);
    }

}
