<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\UnidadeFinanceiraModel;

/**
 * Controller de Unidade Finaceira.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      11/02/2020
 */
class UnidadeFinanceiraController extends NajController {

    /**
     * Seta o model de Unidade Financeira ao carregar o controller
     */
    public function onLoad() {
        $this->setModel(new UnidadeFinanceiraModel);
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
    
    /**
     * Retorna as unidades finaceiras ativas
     * @return JSON
     */
    public function unidades(){
        $unidades_finaceiras = $this->getModel()->unidades();
        return response()->json($unidades_finaceiras)->content();
    }
    
}