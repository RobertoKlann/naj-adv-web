<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\MonitoraProcessoTribunalRelPrcModel;

/**
 * Controller de Monitora Processo Tribunal Rel Prc.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      19/11/2020
 */
class MonitoraProcessoTribunalRelPrcController extends NajController {

    public function onLoad() {
        $this->setModel(new MonitoraProcessoTribunalRelPrcModel);
    }

    /**
     * Retorna o max(id) no BD 
     * @return integer
     */
    public function proximo() {
        $proximo = $this->getModel()->max('id');
        if(!$proximo){
            $proximo = 0;
        }
        return response()->json($proximo)->content();
    }
    
}