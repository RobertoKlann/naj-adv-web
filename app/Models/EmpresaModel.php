<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo de empresa.
 *
 * @package    Models
 * @author     Roberto Oswaldo Klann
 * @since      30/01/2020
 */
class EmpresaModel extends NajModel {

    protected function loadTable() {
        $this->setTable('empresa');

        $this->addColumn('codigo', true);
        $this->addColumn('codigo_divisao');
        $this->addColumn('nome');
        $this->addColumn('cnpj');
        $this->addColumn('cep');
        $this->addColumn('cidade');
        $this->addColumn('uf');
        $this->addColumn('bairro');
        $this->addColumn('complemento');
        $this->addColumn('numero');
        $this->addColumn('endereco');
    }

    public function getFirstEmpresa() {
        return DB::select("
            SELECT *
              FROM empresa
          ORDER BY codigo
             LIMIT 1
        ");
    }
    
}