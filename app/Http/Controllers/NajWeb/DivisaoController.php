<?php

namespace App\Http\Controllers\NajWeb;

use App\Models\DivisaoModel;
use App\Http\Controllers\NajController;

/**
 * Controller das divisões do cliente.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      20/04/2020
 */
class DivisaoController extends NajController {

    public function onLoad() {
        $this->setModel(new DivisaoModel);
    }

    protected function resolveWebContext($usuarios, $code) {}

}