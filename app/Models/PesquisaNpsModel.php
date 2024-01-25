<?php

namespace App\Models;

use App\Models\NajModel;

/**
 * Modelo da pesquisa NPS.
 *
 * @package    Models
 * @author     Roberto Oswaldo Klann
 * @since      23/04/2021
 */
class PesquisaNpsModel extends NajModel {

    protected function loadTable() {
        $this->setTable('pesquisa_nps_csat');

        $this->addColumn('id', true);
        $this->addColumn('descricao');
        $this->addColumn('pergunta');
        $this->addColumn('data_hora_inclusao');
        $this->addColumn('data_hora_inicio');
        $this->addColumn('range_max');
        $this->addColumn('range_min_info');
        $this->addColumn('range_max_info');
        $this->addColumn('situacao');

        $this->addAllColumns();

        $this->setRawBaseSelect("
                SELECT [COLUMNS]
                  FROM pesquisa_nps_csat
        ");
    }

    public function addAllColumns() {
        $this->addRawColumn("
                (
                  SELECT count(0)
                    FROM pesquisa_respostas
                   WHERE TRUE
                     AND id_pesquisa = pesquisa_nps_csat.id
                     AND status = 'N'
                ) as quantidade_recusado")
             ->addRawColumn("
                (
                    SELECT count(0)
                      FROM pesquisa_respostas
                     WHERE TRUE
                       AND id_pesquisa = pesquisa_nps_csat.id
                ) as quantidade_participante
             ")
             ->addRawColumn("
                (
                  SELECT count(0)
                    FROM pesquisa_respostas
                   WHERE TRUE
                     AND id_pesquisa = pesquisa_nps_csat.id
                     AND status = 'P'
                ) as quantidade_pendente
             ")
             ->addRawColumn("
                (
                  SELECT count(0)
                    FROM pesquisa_respostas
                   WHERE TRUE
                     AND id_pesquisa = pesquisa_nps_csat.id
                     AND status = 'R'
                ) as quantidade_respondido
        ");
    }
    
}