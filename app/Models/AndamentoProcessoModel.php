<?php

namespace App\Models;

use App\Models\NajModel;

/**
 * Modelo do andamento do processo.
 *
 * @since 2020-12-23
 */
class AndamentoProcessoModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('prc_movimento');

        $this->addColumn('ID', true);
        $this->addColumn('CODIGO_PROCESSO');
        $this->addColumn('ID_INTIMACAO');
        $this->addColumn('DATA');
        $this->addColumn('DATA_ALTERACAO');
        $this->addColumn('DESCRICAO_ANDAMENTO');
        $this->addColumn('TRADUCAO_ANDAMENTO');

        $this->setOrder('DATA DESC');

        $this->addAllColumns();        

        $this->setRawBaseSelect("
               SELECT [COLUMNS]
                 FROM PRC_MOVIMENTO
                 JOIN PRC
                  ON PRC.CODIGO = PRC_MOVIMENTO.CODIGO_PROCESSO
      ");
    }

    public function addAllColumns() {
        $this->addRawColumn("DATE_FORMAT(PRC_MOVIMENTO.DATA,'%d/%m/%Y') AS DATA");
    }
    
}