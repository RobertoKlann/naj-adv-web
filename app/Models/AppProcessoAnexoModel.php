<?php

namespace App\Models;

use App\Models\NajModel;

/**
 * Modelo de anexos dos processos (aplicativo).
 *
 * @author Roberto Oswaldo Klann
 * @since 2020-08-28
 */
class AppProcessoAnexoModel extends NajModel {

    protected function loadTable() {
        $this->setTable('prc_anexos');

        $this->addColumn('ID', true);
        $this->addColumn('ID_DIR');
        $this->addColumn('CODIGO_PROCESSO');
        $this->addColumn('CODIGO_TEXTO');
        $this->addColumn('DESCRICAO');
        $this->addColumn('NOME_ARQUIVO');
        $this->addColumn('FILE_PATH');
        $this->addColumn('FILE_SIZE');
        $this->addColumn('SERVICOS_CLIENTE');

        $this->addRawColumn("DATE_FORMAT(DATA_ARQUIVO,'%d/%m/%Y') AS DATA_ARQUIVO");
        $this->addRawColumn("DATE_FORMAT(DATA_MODIFICACAO,'%d/%m/%Y') AS DATA_MODIFICACAO");

        $this->setOrder('DATA_ARQUIVO DESC, ID DESC');
    }

}
