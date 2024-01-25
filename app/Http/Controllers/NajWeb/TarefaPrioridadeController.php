<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\TarefaPrioridadeModel;

/**
 * Controllador das prioridades da tarefa.
 *
 * @since 2020-08-12
 */
class TarefaPrioridadeController extends NajController {

    public function onLoad() {
        $this->setModel(new TarefaPrioridadeModel);
    }

}