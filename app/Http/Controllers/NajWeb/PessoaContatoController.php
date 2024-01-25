<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\PessoaContatoModel;

/**
 * Controller de Pessoas.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      17/01/2020
 */
class PessoaContatoController extends NajController {

    public function onLoad() {
        $this->setModel(new PessoaContatoModel);
    }

    public function proximo() {
        $proximo = $this->getModel()->max('codigo');
        if(!$proximo){
            $proximo = 0;
        }
        return response()->json($proximo)->content();
    }
    
}