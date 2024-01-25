<?php

namespace App\Models;

use App\Models\NajModel;

/**
 * Modelo de atividades de processos (aplicativo)
 *
 * @since 2020-04-20
 */
class AppProcessoAtividadesModel extends NajModel {

    protected function loadTable() {
        $this->setTable('ATIVIDADE');
        $this->addColumn('CODIGO', true);
        $this->addColumn('CODIGO_PROCESSO');
        $this->addColumn('HISTORICO');
        $this->addRawColumn("DATE_FORMAT(DATA,'%d/%m/%Y') AS DATA");
        $this->setOrder('DATA DESC, CODIGO DESC');
    }

}
