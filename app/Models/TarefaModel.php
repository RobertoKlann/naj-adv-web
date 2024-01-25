<?php

namespace App\Models;

use App\Models\NajModel;

/**
 * Modelo das tarefas.
 *
 * @since 2020-08-17
 */
class TarefaModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('tarefas');

        $this->addColumn('id', true);
        $this->addColumn('codigo_divisao');
        $this->addColumn('codigo_processo');
        $this->addColumn('codigo_cliente');
        $this->addColumn('codigo_usuario_criacao');
        $this->addColumn('codigo_responsavel');
        $this->addColumn('codigo_supervisor');
        $this->addColumn('descricao');
        $this->addColumn('id_tipo');
        $this->addColumn('id_situacao');
        $this->addColumn('id_prioridade');
        $this->addColumn('data_hora_criacao');
        $this->addColumn('data_prazo_interno');
        $this->addColumn('data_prazo_fatal');
        $this->addColumn('hora_prazo_fatal');
        $this->addColumn('hora_prazo_interno');

        $this->setOrder('id');
    }
    
}