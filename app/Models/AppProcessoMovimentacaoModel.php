<?php

namespace App\Models;

use App\Models\NajModel;

/**
 * Modelo de movimentação de processos (aplicativo)
 *
 * @since 2020-04-20
 */
class AppProcessoMovimentacaoModel extends NajModel {

    protected function loadTable() {
        $this->setTable('PRC_MOVIMENTO');
        $this->addColumn('ID', true);
        $this->addColumn('CODIGO_PROCESSO');
        $this->addColumn('DESCRICAO_ANDAMENTO');
        $this->addRawColumn("DATE_FORMAT(DATA,'%d/%m/%Y') AS DATA");
        $this->setOrder('DATA DESC, ID DESC');
    }

}
