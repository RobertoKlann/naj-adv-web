<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\ProcessoClasseModel;

/**
 * Controller de Processo Classe.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      15/09/2020
 */
class ProcessoClasseController extends NajController {

    public function onLoad() {
        $this->setModel(new ProcessoClasseModel);
    }

    /**
     * Retorna o max(id) no BD 
     * @return integer
     */
    public function proximo() {
        $proximo = $this->getModel()->max('CODIGO');
        if(!$proximo){
            $proximo = 0;
        }
        return response()->json($proximo)->content();
    }
    
    /**
     * Obtêm registros da tabela "prc_classe" que contenham o conteúdo do filtro 
     * 
     * @param string $filter
     * @return JSON
     */
    public function getProcessoClasseInFilter($filter) {
        $response = $this->getModel()->getProcessoClasseInFilter($filter);

        return response()->json([
            'data' => $response
        ]);
    }
    
    /**
     * Obtêm processo classe pelo nome
     * 
     * @param string $filter
     * @return JSON
     */
    public function getProcessoClasseByName($filter){
        $response = $this->getModel()->getProcessoClasseByName($filter);
        return response()->json($response);
    }

    public function classeFromChat() {
        return response()->json($this->getModel()->classeFromChat());
    }
}