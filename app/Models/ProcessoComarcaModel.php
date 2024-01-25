<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo de Processos Comarca.
 *
 * @author William Goebel
 * @since 2020-09-14
 */
class ProcessoComarcaModel extends NajModel {

    protected function loadTable() {
        $this->setTable('prc_comarca');
        
        $this->addColumn('CODIGO', true);
        $this->addColumn('COMARCA');
        $this->addColumn('UF');
        
        $this->primaryKey = 'CODIGO';
        
    }
    
    /**
     * Obtêm registros da tabela "prc_comarca" que contennham o conteúdo do filtro 
     * 
     * @param string $filter
     * @return array
     */
    public function getProcessoComarcaInFilter($filter) {
        return DB::select(
            "SELECT CODIGO,
                    COMARCA,
                    UF
               FROM prc_comarca
              WHERE TRUE
                AND COMARCA LIKE'%{$filter}%'
            "
        );
    }

    /**
     * Obtêm comarca pelo nome
     * 
     * @param string $filter
     * @return array
     */
    public function getProcessoComarcaByName($filter){
        $sql = "SELECT * FROM prc_comarca WHERE COMARCA = '{$filter}'";
        return DB::select($sql);
    }

    public function comarcaFromChat() {
        return DB::select("
                SELECT count(0) as qtde_prc,
                       c.comarca,
                       c.uf,
                       p.codigo_comarca
                  FROM prc p
            INNER JOIN prc_comarca c
                    ON c.codigo = p.codigo_comarca
                 WHERE p.codigo_situacao IN (
                                              SELECT codigo
                                                FROM prc_situacao
                                               WHERE ativo = 'S'
                                            )
              GROUP BY comarca
              ORDER BY qtde_prc desc
        ");
    }
    
}