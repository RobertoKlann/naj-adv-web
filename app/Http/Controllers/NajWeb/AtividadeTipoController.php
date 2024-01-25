<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\AtividadeTipoModel;

/**
 * Controller de Atividade Tipo.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      05/01/2020
 */
class AtividadeTipoController extends NajController {

    public function onLoad() {
        $this->setModel(new AtividadeTipoModel);
    }
    
    /**
     * Obtêm todos os registros de Atividade Tipo
     * 
     * @return JSON
     */
    public function getAllAtividadesTipos() {
        return response()->json($this->getModel()->getAllAtividadesTipos());
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