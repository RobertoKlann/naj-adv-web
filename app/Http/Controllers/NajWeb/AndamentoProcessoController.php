<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\AndamentoProcessoModel;

/**
 * Controllador do andamento do processo.
 *
 * @since 2020-12-23
 */
class AndamentoProcessoController extends NajController {

    public function onLoad() {
        $this->setModel(new AndamentoProcessoModel);
    }

}