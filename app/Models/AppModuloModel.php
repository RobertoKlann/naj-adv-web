<?php

namespace App\Models;

use App\Models\NajModel;

class AppModuloModel extends NajModel {

    protected function loadTable() {
        $this->setTable('modulos');

        $this->addColumn('ID', true);
        $this->addColumn('MODULO');
        $this->addColumn('APELIDO');
        $this->addColumn('DESCRICAO');
        $this->addColumn('APLICACAO');
        $this->addColumn('ACESSAR');
        $this->addColumn('PESQUISAR');
        $this->addColumn('INCLUIR');
        $this->addColumn('ALTERAR');
        $this->addColumn('EXCLUIR');
        $this->addColumn('DIVISAO');
        $this->addColumn('ESPECIAL');
    }

}