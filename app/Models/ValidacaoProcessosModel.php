<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Model de Validacao Processos.
 *
 * @package    Models
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      31/03/2021
 */
class ValidacaoProcessosModel extends NajModel {

    protected function loadTable() {
        $this->setTable('PRC');
        $this->addColumn('CODIGO', true);
        $this->addColumn('NUMERO_PROCESSO_NEW');
        $this->addColumn('GRAU_JURISDICAO');
        //$this->setOrder('QTDE_PRC_COM_MESMO_CNJ DESC, PRC.NUMERO_PROCESSO_NEW, PRC.GRAU_JURISDICAO');
        $this->setOrder('CNJ_VALIDO desc, prc.numero_processo_new, prc.grau_jurisdicao');
        $this->addRawColumn("PRC.CODIGO AS CODIGO_PROCESSO")
        ->addRawColumn("IF ((
            SELECT ATIVO
            FROM PRC_SITUACAO
            WHERE CODIGO = PRC.CODIGO_SITUACAO
        ) = 'S', 'ATIVO', 'BAIXADO') AS SITUACAO")
        ->addRawColumn("P1.NOME AS NOME_CLIENTE")
        ->addRawColumn("P1.CODIGO AS CODIGO_CLIENTE")
        ->addRawColumn("(
            SELECT COUNT(0)
               FROM PRC_GRUPO_CLIENTE PGC
               WHERE PGC.CODIGO_PROCESSO = PRC.CODIGO
        ) AS QTDE_CLIENTES")
        ->addRawColumn("P2.NOME AS NOME_ADVERSARIO")
        ->addRawColumn("P2.CODIGO AS CODIGO_ADVERSARIO")
        ->addRawColumn("(
            SELECT COUNT(0)
               FROM PRC_GRUPO_ADVERSARIO PGA
               WHERE PGA.CODIGO_PROCESSO = PRC.CODIGO
        ) AS QTDE_ADVERSARIOS")
        ->addRawColumn("CL.CLASSE")
        ->addRawColumn("CA.CARTORIO")
        ->addRawColumn("CO.COMARCA")
        ->addRawColumn("CO.UF AS COMARCA_UF")
        ->addRawColumn("REL.id_monitora_tribunal")
        ->addRawColumn("IF ((MPT.status) = 'A', 'ATIVO', 'BAIXADO') AS STATUS_MONITORAMENTO")
        //Usaremos esse subselect para ordenar por processos iguais por primeiro
        //->addRawColumn("(SELECT count(0) FROM prc as pcaa WHERE pcaa.numero_processo_new = prc.numero_processo_new) AS QTDE_PRC_COM_MESMO_CNJ")
        //Usaremos esse subselect para descobrir se o processo já está sendo monitorado
        ->addRawColumn("IF( (SELECT count(0) FROM monitora_processo_tribunal_rel_prc WHERE codigo_processo = PRC.codigo)>=1, TRUE, FALSE) AS MONITORADO") 
        //Usaremos esse subselect pra verificar se o CNJ é válido
        ->addRawColumn("(SELECT CASE WHEN(
                            (SELECT CASE WHEN LENGTH(NUMERO_PROCESSO_NEW) = 25 THEN
                                TRUE 
                            ELSE
                                FALSE
                            END)
                        AND
                            (SELECT IF(locate('-', NUMERO_PROCESSO_NEW)>0, TRUE, FALSE) )
                        AND
                            (SELECT CASE WHEN (LENGTH(NUMERO_PROCESSO_NEW) - LENGTH( REPLACE ( NUMERO_PROCESSO_NEW, '.', ''))) = 4 THEN
                                TRUE
                            ELSE
                                FALSE
                            END)
                        ) = TRUE THEN
                            TRUE 
                        ELSE
                            FALSE
                        END)  AS CNJ_VALIDO")
        //Usaremos esse subselect pra verificar se é necessário revisar a instância
        ->addRawColumn("(SELECT CASE WHEN(
                            IF(length(GRAU_JURISDICAO)>1, FALSE, TRUE)
                        OR
                            IF((SELECT count(0)  FROM prc AS PCAB WHERE PCAB.NUMERO_PROCESSO_NEW = PRC.NUMERO_PROCESSO_NEW and PCAB.GRAU_JURISDICAO = PRC.GRAU_JURISDICAO) > 1, TRUE, FALSE) 
                        ) = TRUE THEN
                            TRUE
                        ELSE
                            FALSE
                        END) AS REVISAR_INSTANCIA"); 
        $this->setRawBaseSelect("
               SELECT [COLUMNS]
               FROM PRC
            LEFT JOIN PESSOA P1
                  ON P1.CODIGO = PRC.CODIGO_CLIENTE
            LEFT JOIN PESSOA P2
                  ON P2.CODIGO = PRC.CODIGO_ADVERSARIO
            LEFT JOIN PESSOA P3
                  ON P3.CODIGO = PRC.CODIGO_RESPONSAVEL
            LEFT JOIN PESSOA P4
                  ON P4.CODIGO = PRC.CODIGO_ADV_CLIENTE
            LEFT JOIN PRC_COMARCA CO
                  ON CO.CODIGO = PRC.CODIGO_COMARCA
            LEFT JOIN PRC_CARTORIO CA
                  ON CA.CODIGO = PRC.CODIGO_CARTORIO
            LEFT JOIN PRC_CLASSE CL
                  ON CL.CODIGO = PRC.CODIGO_CLASSE
            LEFT JOIN monitora_processo_tribunal_rel_prc REL
                ON (PRC.CODIGO = REL.codigo_processo)
            LEFT JOIN monitora_processo_tribunal MPT
                ON (REL.id_monitora_tribunal = MPT.id)
        ");
    }

    /**
     * Sobreescreve o método da classe mãe
     * 
     * @return array
     */
    public function makePagination() {
        $limit = $this->itemsPerPage;

        $pageQuery = request()->query('page');

        $page = $pageQuery > 0 ? (integer) $pageQuery : 1;

        $offset = ($page * $limit) - $limit;
        
        $queryFilters =  request()->query('f');

        if ($queryFilters) {
            $queryFilters = $this->parseQueryFilter($queryFilters);
        }
        
        $cnj_invalido       = false;
        $revisar_instancia  = false;
        $monitorado         = false;
        $situacao           = false;
        
        //Aplica as condições
        foreach ($queryFilters as $index => $filter){
            //Acresenta as cláusulas having
            //if (($filter->col == "CNJ_VALIDO") || ($filter->col == "REVISAR_INSTANCIA") || ($filter->col == "MONITORADO") || ($filter->col == "STATUS_MONITORAMENTO")){
                if($index == 0){
                    if($filter->col == "STATUS_MONITORAMENTO"){
                        $this->having .= " ($filter->col = '$filter->val')";
                    }elseif($filter->col == "SITUACAO"){
                        $this->having .= " ($filter->col = '$filter->val')";
                        $situacao = $filter->val;
                    }else{
                        $this->having .= " ($filter->col = $filter->val)";
                    }
                }else{
                    if($filter->col == "STATUS_MONITORAMENTO"){
                        $this->having .= " AND ($filter->col = '$filter->val')";
                    }elseif($filter->col == "SITUACAO"){
                        $this->having .= " AND ($filter->col = '$filter->val')";
                        $situacao = $filter->val;
                    }else{
                        $this->having .= " AND ($filter->col = $filter->val)";
                    }
                }
            //}
            //Validação para verificar se filtraremos por processos disponíveis
            if(($filter->col == "CNJ_VALIDO") && ($filter->val == 0)){
                $cnj_invalido      = true;
            }elseif(($filter->col == "REVISAR_INSTANCIA") && ($filter->val == 1)){
                $revisar_instancia = true;
            }elseif($filter->col == "STATUS_MONITORAMENTO"){
                $monitorado        = true;
            }
        }

        $baseSelect = $this->getBaseSelect();

        // registros
        $selectForPagination = $this->fillSelectForPagination($baseSelect, $limit, $offset);
        $data = DB::select($selectForPagination);
        
        // obtêm o total de processos válidos e ativos
        if($cnj_invalido || $revisar_instancia || $monitorado){
            $total_processos_validos = 0;
        }else{
            $total_processos_validos = count($this->obtemTodosOsProcessosDisponiveis($situacao));
        }

        // contador
        $selectForCount = $this->fillSelectForPaginationCounter($baseSelect);
        $counter = DB::select($selectForCount);

        return [
            'total'                   => $counter[0]->total,
            'total_processos_validos' => $total_processos_validos,
            'pagina'                  => $page,
            'limite'                  => $limit,
            'resultado'               => $data,
            'dev'                     => null
        ];
    }
     
    /**
     * Busca
     * 
     * @return array 
     */
    public function obtemTodosOsProcessosDisponiveis($situacao = false){
        $sql = "SELECT  pc.CODIGO, 
                        pc.NUMERO_PROCESSO_NEW, 
                        pc.GRAU_JURISDICAO,
                        (SELECT count(0) FROM prc WHERE NUMERO_PROCESSO_NEW = pc.NUMERO_PROCESSO_NEW) AS QTDE_PRC,
                        (SELECT CASE WHEN(
                                (SELECT CASE WHEN LENGTH(NUMERO_PROCESSO_NEW) = 25 THEN
                                        TRUE 
                                ELSE
                                        FALSE
                                END)

                                AND

                                (SELECT IF(locate('-', NUMERO_PROCESSO_NEW)>0, TRUE, FALSE) )

                                AND

                                (SELECT CASE WHEN (LENGTH(NUMERO_PROCESSO_NEW) - LENGTH( REPLACE ( NUMERO_PROCESSO_NEW, '.', ''))) = 4 THEN
                                        TRUE
                                ELSE
                                        FALSE
                                END)
                        ) = TRUE THEN
                                TRUE 
                        ELSE
                                FALSE
                        END)  AS CNJ_VALIDO,
                        (SELECT CASE WHEN(
                                SELECT IF(length(GRAU_JURISDICAO) > 0, FALSE, TRUE)
                                OR
                                IF ((SELECT count(0)  FROM prc WHERE NUMERO_PROCESSO_NEW = pc.NUMERO_PROCESSO_NEW AND GRAU_JURISDICAO = pc.GRAU_JURISDICAO) > 1, TRUE, FALSE) 
                        ) = TRUE THEN
                                TRUE
                        ELSE
                                FALSE
                        END) AS REVISAR_INSTANCIA,
                        IF( (SELECT count(0) FROM monitora_processo_tribunal_rel_prc WHERE codigo_processo = pc.codigo)>=1,TRUE, FALSE) AS MONITORADO,
                        IF ((
                                SELECT ATIVO
                                FROM PRC_SITUACAO
                                WHERE CODIGO = pc.CODIGO_SITUACAO
                        ) = 'S', 'ATIVO', 'BAIXADO') AS SITUACAO
                FROM prc pc
                HAVING CNJ_VALIDO = 1 AND REVISAR_INSTANCIA = 0 AND MONITORADO = 0";
                if($situacao){
                    $sql .= " AND SITUACAO = '$situacao'";
                }
                $sql .= " ORDER BY QTDE_PRC, pc.NUMERO_PROCESSO_NEW, pc.GRAU_JURISDICAO";
                
        return DB::select($sql);
    }

    /**
     * Obtêm o total de Quotas
     * 
     * @return int 
     */
    public function getTotalQuotas(){
        $sql = "SELECT VALOR FROM sys_config WHERE SECAO = 'PROCESSOS' && CHAVE = 'MONITORAMENTO_TRIBUNAL_QUOTA'";
        $result = DB::select($sql);
        if(count($result) > 0){
            return (int) $result[0]->VALOR;
        }
    }
    
    /**
     * Obtêm o total de monitoramentos Ativos
     * 
     * @return int 
     */
    public function getTotalMonitoramentosAtivos(){
        $sql = "SELECT count(id) total FROM naj999999999.monitora_processo_tribunal;";
        $result = DB::select($sql);
        if(count($result) > 0){
            return (int) $result[0]->total;
        }
    }
    
}
