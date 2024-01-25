<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo de Processo Area Juridica.
 *
 * @author William Goebel
 * @since 2020-09-15
 */
class ProcessoAreaJuridicaModel extends NajModel {

    protected function loadTable() {
        $this->setTable('prc_area_juridica');
        
        $this->addColumn('ID', true);
        $this->addColumn('AREA');
        
        $this->primaryKey = 'ID';
        
    }
    
    /**
     * Obtêm registros da tabela "prc_area_juridica" que contenham o conteúdo do filtro 
     * 
     * @param string $filter
     * @return array
     */
    public function getProcessoAreaJuridicaInFilter($filter) {
        return DB::select(
            "SELECT ID,
                    AREA
               FROM prc_area_juridica
              WHERE TRUE
                AND AREA LIKE'%{$filter}%'
            "
        );
    }
    
    /**
     * Obtêm os registros de "prc_orgao"
     * 
     * @return JSON
     */
    public function getProcessoAreaJuridica(){
        $sql = "
            SELECT ID, AREA FROM prc_area_juridica;
        ";

        $result = DB::select($sql);

        return $result;
    }

    public function areasFromChat() {
        return DB::select("
                select count(0) as qtde_prc, 
                       a.area,
                       p.id_area_juridica
                  from prc p
            inner join prc_area_juridica a
                    on a.id = p.id_area_juridica
                 where p.codigo_situacao in (
                                              select codigo
                                                from prc_situacao
                                               where ativo = 'S'
                        )
              group by area
              order by qtde_prc desc
        ");
    }

}