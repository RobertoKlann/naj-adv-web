<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\TarefaRelAgendaModel;

/**
 * Controllador do tipos de tarefas.
 *
 * @since 2020-08-12
 */
class TarefaRelAgendaController extends NajController {

    public function onLoad() {
        $this->setModel(new TarefaRelAgendaModel);
    }

}