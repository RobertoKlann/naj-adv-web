<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\AgendaTipoCompromissoModel;

/**
 * Controllador tipo de compromisso da tarefa.
 *
 * @since 2020-10-06
 */
class AgendaTipoCompromissoController extends NajController {

    public function onLoad() {
        $this->setModel(new AgendaTipoCompromissoModel);
    }

}