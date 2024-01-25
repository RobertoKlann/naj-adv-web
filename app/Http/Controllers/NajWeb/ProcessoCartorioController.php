<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\ProcessoCartorioModel;

/**
 * Controller de Processo Cartorio.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      15/09/2020
 */
class ProcessoCartorioController extends NajController {

    public function onLoad() {
        $this->setModel(new ProcessoCartorioModel);
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
     * Obtêm registros da tabela "prc_cartorio" que contenham o conteúdo do filtro 
     * 
     * @param string $filter
     * @return JSON
     */
    public function getProcessoCartorioInFilter($filter) {
        $response = $this->getModel()->getProcessoCartorioInFilter($filter);

        return response()->json([
            'data' => $response
        ]);
    }
    
    /**
     * Obtêm cartorio pelo nome
     * 
     * @param string $filter
     * @return JSON
     */
    public function getProcessoCartorioByName($filter){
        $response = $this->getModel()->getProcessoCartorioByName($filter);
        return response()->json($response);
    }
}