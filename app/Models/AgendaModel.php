<?php

namespace App\Models;

use App\Models\NajModel;

/**
 * Modelo da agenda.
 *
 * @since 2020-10-07
 */
class AgendaModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('agenda');

        $this->addColumn('ID', true);
        $this->addColumn('CODIGO_DIVISAO');
        $this->addColumn('CODIGO_TIPO');
        $this->addColumn('CODIGO_USUARIO');
        $this->addColumn('CODIGO_PESSOA');
        $this->addColumn('CODIGO_PROCESSO');
        $this->addColumn('DATA_HORA_INCLUSAO');
        $this->addColumn('DATA_HORA_COMPROMISSO');
        $this->addColumn('LOCAL');
        $this->addColumn('ASSUNTO');
        $this->addColumn('ALTERACAO');
        $this->addColumn('SITUACAO');
        $this->addColumn('PRIVADO');

        $this->setOrder('CODIGO');

        $this->primaryKey = 'ID';
    }
    
}