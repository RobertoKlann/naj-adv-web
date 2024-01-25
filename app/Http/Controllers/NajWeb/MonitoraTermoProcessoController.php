<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\MonitoraTermoProcessoModel;
use Illuminate\Http\Request;

/**
 * Controller da Conta Virtual.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      29/10/2020
 */
class MonitoraTermoProcessoController extends NajController {

    /**
     * Seta o model de Conta Virtual ao carregar o controller
     */
    public function onLoad() {
        $this->setModel(new MonitoraTermoProcessoModel);
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
     * Desvincula Processo
     */
    public function desvinculaProcesso($id){
        $id = json_decode(base64_decode($id));
        $id = $id->id;
        return $this->getModel()->desvinculaProcesso($id);
    } 
    
}
