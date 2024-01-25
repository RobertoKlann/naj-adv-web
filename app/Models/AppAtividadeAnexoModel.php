<?php

namespace App\Models;

use App\Models\NajModel;

/**
 * Modelo de anexos das atividades (aplicativo).
 *
 * @author Roberto Oswaldo Klann
 * @since 2021-06-18
 */
class AppAtividadeAnexoModel extends NajModel {

    protected function loadTable() {
        $this->setTable('atividade_anexos');

        $this->addColumn('ID', true);
        $this->addColumn('CODIGO_ATIVIDADE');
        $this->addColumn('CODIGO_TEXTO');
        $this->addColumn('DESCRICAO');
        $this->addColumn('NOME_ARQUIVO');
        $this->addColumn('FILE_PATH');
        $this->addColumn('FILE_SIZE');
        $this->addColumn('DATA_ARQUIVO');

        $this->addRawColumn("DATE_FORMAT(DATA_ARQUIVO,'%d/%m/%Y') AS DATA_ARQUIVO");

        $this->setOrder('DATA_ARQUIVO DESC, ID DESC');
    }

}
