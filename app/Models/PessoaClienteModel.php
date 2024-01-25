<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Controllador de Pessoa x Usuário tipo cliente.
 *
 * @package    Models
 * @author     Roberto Oswaldo Klann
 * @since      23/01/2020
 */
class PessoaClienteModel extends NajModel {

    protected function loadTable() {
        $this->setTable('pessoa_rel_clientes');
        
        $this->addColumn('pessoa_codigo', true);
        $this->addColumn('usuario_id'   , true);

    }

    public function isPessoaCliente($codigo_pessoa) {
        return DB::select("
            SELECT *
              FROM pessoa_rel_clientes
             WHERE TRUE
               AND pessoa_codigo = {$codigo_pessoa}
        ");
    }

}