<?php

namespace App\Models;

use App\Models\NajModel;

/**
 * Modelo das atividades do processo.
 *
 * @since 2020-12-23
 */
class AtividadeProcessoModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('atividade');

        $this->addColumn('CODIGO', true);
        $this->addColumn('CODIGO_USUARIO');
        $this->addColumn('CODIGO_DIVISAO');
        $this->addColumn('CODIGO_CLIENTE');
        $this->addColumn('CODIGO_PROCESSO');
        $this->addColumn('ID_TIPO_ATIVIDADE');
        $this->addColumn('CODIGO_TEXTO');
        $this->addColumn('DATA');
        // $this->addColumn("DATE_FORMAT(DATA,'%d/%m/%Y') AS DATA_INICIO");
        $this->addColumn('HORA_INICIO');
        $this->addColumn('TEMPO');
        $this->addColumn('ID_TIPO_ATIVIDADE');
        $this->addColumn('ENVIAR');
        $this->addColumn('HISTORICO');
        $this->addColumn('ID_AREA_JURIDICA');
        
        $this->primaryKey = 'CODIGO';
        
        $this->setOrder('atividade.DATA DESC');

        $this->addAllColumns();

       $this->setRawBaseSelect("
              SELECT [COLUMNS]
                FROM ATIVIDADE
                JOIN PRC
                  ON PRC.CODIGO = ATIVIDADE.CODIGO_PROCESSO
          INNER JOIN PESSOA P1 
                  ON P1.CODIGO = ATIVIDADE.CODIGO_USUARIO
       ");
    }

    public function addAllColumns() {
        $this->addRawColumn("DATE_FORMAT(ATIVIDADE.DATA,'%d/%m/%Y') AS DATA_INICIO")
            //  ->addRawColumn("DATE_FORMAT(ATIVIDADE.DATA_TERMINO,'%d/%m/%Y') AS DATA_TERMINO")
            //  ->addRawColumn("DATE_FORMAT(ATIVIDADE.HORA_INICIO,'%H:%m:%s') AS HORA_INICIO")
            //  ->addRawColumn("DATE_FORMAT(ATIVIDADE.HORA_TERMINO,'%H:%m:%s') AS HORA_TERMINO")
            //  ->addRawColumn("DATE_FORMAT(ATIVIDADE.TEMPO,'%H%m:%s') AS TEMPO")
             ->addRawColumn("ATIVIDADE.HISTORICO AS DESCRICAO")
             ->addRawColumn("P1.NOME AS NOME_USUARIO");
    }
    
    /**
     *Sobreescreve método da clase mãe para que posssamos armazenar valores nulos
     * 
     * @param bool $reqAttrs
     * @return array
     */
    public function getFilledAttributes($reqAttrs = null) {
        $toFill = [];

        $columns = !$this->incrementing ? $this->getTableColumns() : $this->getTableColumnsWithoutKeys();

        foreach ($columns as $column) {
            if ($reqAttrs) {
                $reqValue = isset($reqAttrs[$column]) ? $reqAttrs[$column] : null;

                $toFill[$column] = $reqValue;
            } else {
                $reqValue = request()->get($column);
                $toFill[$column] = $reqValue;
            }
        }

        return $toFill;
    }
    
}