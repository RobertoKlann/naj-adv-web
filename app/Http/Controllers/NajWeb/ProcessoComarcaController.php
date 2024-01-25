<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\ProcessoComarcaModel;

/**
 * Controller de Processo Comarca.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      15/09/2020
 */
class ProcessoComarcaController extends NajController {

    public function onLoad() {
        $this->setModel(new ProcessoComarcaModel);
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
     * Obtêm registros da tabela "prc_comarca" que contenham o conteúdo do filtro 
     * 
     * @param string $filter
     * @return JSON
     */
    public function getProcessoComarcaInFilter($filter) {
        $response = $this->getModel()->getProcessoComarcaInFilter($filter);

        return response()->json([
            'data' => $response
        ]);
    }
    
    /**
     * Obtêm comarca pelo nome
     * 
     * @param string $filter
     * @return JSON
     */
    public function getProcessoComarcaByName($filter){
        $response = $this->getModel()->getProcessoComarcaByName($filter);
        return response()->json($response);
    }

    public function comarcaFromChat() {
        return response()->json($this->getModel()->comarcaFromChat());
    }
    
}