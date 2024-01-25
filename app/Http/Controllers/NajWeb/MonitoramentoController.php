<?php

namespace App\Http\Controllers\NajWeb;

use Illuminate\Http\Request;
use App\Estrutura;
use App\Http\Controllers\NajController;
use App\Http\Controllers\NajWeb\EscavadorController;

/**
 * Controller do Monitoramento.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      05/05/2020
 */
class MonitoramentoController extends NajController {

    /**
     * Busca callbacks na Escavador
     * 
     * @param int    $page           número da página
     * @param string $data_minima    data miníma para a pesquisa na Escavador, a data máxima será a data corrente
     * @param string $nomeArquivoLog nome do arquivod de log
     * @param bool   $print_r        define se ira imprimir o resultado em tela
     * @return object
     */
    public function buscaCallbacksEscavador($page = 1, $data_minima= null, $nomeArquivoLog = 'MonitoramentoController/buscaCallbacksEscavador', $print_r = true){
        //Cria uma requisição que será passada por parâmetro no método "retornarOsCallbacks"
        $request             = new Request();
        $request             = $request->create('','POST', ['data_maxima' => date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'). " +3 hours +1 second")), 'data_minima' => $data_minima, 'page' => $page]);
        $escavadorController = new EscavadorController();
        $content             = $escavadorController->retornarOsCallbacks($request, $nomeArquivoLog);
        $content             = json_decode($content);
        unset($escavadorController);
        if($print_r){
            return Estrutura::print_r($content);
        }
        return $content;
    }

}
