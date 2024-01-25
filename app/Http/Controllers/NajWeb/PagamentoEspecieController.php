<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\PagamentoEspecieModel;

/**
 * Controller de Pagamento Especie.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      11/02/2020
 */
class PagamentoEspecieController extends NajController {

    /**
     * Seta o model de pagamento especie ao carregar o controller
     */
    public function onLoad() {
        $this->setModel(new PagamentoEspecieModel);
    }

    /**
     * Retorna o max(id) no BD 
     * @return integer
     */
    public function proximo() {
        $proximo = $this->getModel()->max('codigo');

        return $proximo;
    }
    
    /**
     * Retorna os pagamentos especies 
     * @return JSON
     */
    public function paginate() {
        $result = parent::paginate();
        return response()->json($result['resultado'])->content();
    }
    
    /**
     * Retorna os pagamentos especies e as unidades finaceira vinculadas aos mesmos
     * @return JSON
     */
    public function pagamentoEspecieUnidadeFinaceira(){
        $result = $this->getModel()->pagamentoEspecieUnidadeFinaceira();
        return $result;
    }
    
}