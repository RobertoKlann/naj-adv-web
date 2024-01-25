<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\PrcMovimentoModel;

/**
 * Controller de Prc_Movimento.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      15/12/2020
 */
class PrcMovimentoController extends NajController {

    public function onLoad() {
        $this->setModel(new PrcMovimentoModel);
    }

    /**
     * Retorna o max(id) no BD 
     * @return integer
     */
    public function proximo() {
        $proximo = $this->getModel()->max('ID');
        if(!$proximo){
            $proximo = 0;
        }
        return response()->json($proximo)->content();
    }
    
}