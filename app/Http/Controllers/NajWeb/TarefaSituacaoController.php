<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\TarefaSituacaoModel;

/**
 * Controllador da situação das tarefas.
 *
 * @since 2020-08-12
 */
class TarefaSituacaoController extends NajController {

    public function onLoad() {
        $this->setModel(new TarefaSituacaoModel);
    }

}