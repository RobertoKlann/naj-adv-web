<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\AgendaModel;

/**
 * Controllador da agenda.
 *
 * @since 2020-10-07
 */
class AgendaController extends NajController {

    public function onLoad() {
        $this->setModel(new AgendaModel);
    }

}