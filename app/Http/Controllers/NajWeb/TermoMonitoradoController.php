<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\TermoMonitoradoModel;

/**
 * Controller dos Termos Monitorados.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      26/05/2020
 */
class TermoMonitoradoController extends NajController {

    /**
     * Seta o model dos Termos Monitorados ao carregar o controller
     */
    public function onLoad() {
        $this->setModel(new TermoMonitoradoModel);
    }

    /**
     * Retorna o max(id) no BD 
     * @return JSON
     */
    public function proximo() {
        $proximo = $this->getModel()->max('id');
        if(!$proximo){
            $proximo = 0;
        }
        return response()->json($proximo)->content();
    }

}
