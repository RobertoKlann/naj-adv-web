<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\ProcessoAreaJuridicaModel;

/**
 * Controller de Processo Area Juridica.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      15/09/2020
 */
class ProcessoAreaJuridicaController extends NajController {

    public function onLoad() {
        $this->setModel(new ProcessoAreajuridicaModel);
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
    
    /**
     * Obtêm registros da tabela "prc_area_juridica" que contenham o conteúdo do filtro 
     * 
     * @param string $filter
     * @return JSON
     */
    public function getProcessoAreaJuridicaInFilter($filter) {
        $response = $this->getModel()->getProcessoAreaJuridicaInFilter($filter);

        return response()->json([
            'data' => $response
        ]);
    }
    
    public function getProcessoAreaJuridica(){
        $result = $this->getModel()->getProcessoAreaJuridica();
        return response()->json($result)->content();
    }

    public function areasFromChat() {
        return response()->json($this->getModel()->areasFromChat());
    }

}