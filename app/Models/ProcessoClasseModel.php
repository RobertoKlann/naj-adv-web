<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo de Processos Classe.
 *
 * @author William Goebel
 * @since 2020-09-14
 */
class ProcessoClasseModel extends NajModel {

    protected function loadTable() {
        $this->setTable('prc_classe');
        
        $this->addColumn('CODIGO', true);
        $this->addColumn('CLASSE');
        $this->addColumn('TIPO');
        
        $this->primaryKey = 'CODIGO';
        
    }
    
    /**
     * Obtêm registros da tabela "prc_classe" que contennham o conteúdo do filtro 
     * 
     * @param string $filter
     * @return array
     */
    public function getProcessoClasseInFilter($filter) {
        return DB::select(
            "SELECT CODIGO,
                    CLASSE,
                    IF(TIPO = 'J', 'Judiacial', 'Amigável') as TIPO
               FROM prc_classe
              WHERE TRUE
                AND CLASSE LIKE'%{$filter}%'
            "
        );
    }
    
    /**
     * Obtêm processo classe pelo nome
     * 
     * @param string $filter
     * @return array
     */
    public function getProcessoClasseByName($filter){
        $sql = "SELECT * FROM prc_classe WHERE CLASSE = '{$filter}'";
        return DB::select($sql);
    }

    public function classeFromChat() {
        return DB::select("
                SELECT count(0) as qtde_prc,
                       c.classe,
                       p.codigo_classe
                  FROM prc p
            INNER JOIN prc_classe c
                    ON c.codigo = p.codigo_classe
                 WHERE p.codigo_situacao IN (
                                              SELECT codigo
                                                FROM prc_situacao
                                               WHERE ativo = 'S'
                                            )
              GROUP BY classe
              ORDER BY qtde_prc desc
        ");
    }

}