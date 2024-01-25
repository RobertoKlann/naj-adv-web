<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\ContaVirtualModel;

/**
 * Controller da Conta Virtual.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      10/01/2020
 */
class ContaVirtualController extends NajController {

    /**
     * Seta o model de Conta Virtual ao carregar o controller
     */
    public function onLoad() {
        $this->setModel(new ContaVirtualModel);
    }

    /**
     * Index da rota de Conta Virtual
     * @return view
     */
    public function index() {
        return view('najWeb.consulta.ContaVirtualConsultaView')->with('is_conta_virtual', true);
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
     * Verifica se a as naturezas de taxas foram definidas e se são consistentes
     * @return type
     */
    public function verificaNaturezaFinanceira(){
        $return = $this->getModel()->verificaNaturezaFinanceira();
        $response = new \stdClass();
        $response->response = [new \stdClass()]; 
        $response->response[0]->status_code    = 200; 
        $response->response[0]->status_message = 'success'; 
        if(!empty($return)){
            $response->response[0]->status_code    = 400; 
            $response->response[0]->status_message = $return; 
        }
        return response()->json($response);
    }

}
