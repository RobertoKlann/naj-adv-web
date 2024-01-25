<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo de Unidade Financeira Extrato.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      12/02/2020
 */
class UnidadeFinanceiraExtratoModel extends NajModel {
    
    /**
     * Carrega Tabela
     */
    protected function loadTable() {
        $this->setTable('unidade_financeira_extrato');
        
        $this->addColumn('ID', true);
        $this->addColumn('ID_PARCELA')->addJoin('boleto', 'id_parcela');
        $this->addColumn('CODIGO_UNIDADE')->addJoin('unidade_financeira', 'CODIGO');
        $this->addColumn('DATA');
        $this->addColumn('DATA_CONCILIACAO');
        $this->addColumn('HISTORICO');
        $this->addColumn('VALOR_ENTRADA');
        $this->addColumn('VALOR_SAIDA');
        $this->addColumn('SALDO_ATUAL');
        $this->addColumn('SALDO_ATUAL_CONCILIACAO');
        
        $this->addColumnFrom('unidade_financeira', 'descricao', 'descricao');
        $this->addColumnFrom('boleto', 'data_liberacao', 'data_liberacao');
        
        $this->setOrder('unidade_financeira_extrato.DATA, unidade_financeira_extrato.ID');
        
        $this->primaryKey = 'ID';
        
    }
    
    /**
     * Sobreescreve o metódo makePagination da classe mãe
     * pois para essa rotina é necessário um SQL personalizado
     * @return Array associative
     */
    public function makePagination() {
        $limit = $this->itemsPerPage;

        $pageQuery = request()->query('page');

        $page = $pageQuery > 0 ? (integer) $pageQuery : 1;

        $offset = ($page * $limit) - $limit;

        $columns = '
            unidade_financeira_extrato.ID,
            unidade_financeira_extrato.ID_PARCELA,
            CODIGO_UNIDADE,
            DATA,
            (SELECT max(DATA) FROM unidade_financeira_extrato) AS MAX_DATA,
            DATA_CONCILIACAO,
            (SELECT max(DATA_CONCILIACAO) FROM unidade_financeira_extrato) AS MAX_DATA_CONCILIACAO,
            HISTORICO,
            VALOR_ENTRADA,
            VALOR_SAIDA,
            SALDO_ATUAL,
            SALDO_ATUAL_CONCILIACAO,
            (SELECT boleto.tipo_pagamento
            from boleto
            where boleto.id_parcela = unidade_financeira_extrato.id_parcela
            and boleto.situacao = 1
            order by boleto.id desc limit 1
            ) AS tipo_pagamento,
            (SELECT boleto.data_liberacao
            from boleto
            where boleto.id_parcela = unidade_financeira_extrato.id_parcela
            and boleto.situacao = 1
            order by boleto.id desc limit 1
            ) AS data_liberacao,
            (SELECT boleto_cv_saque.status
            from boleto_cv_saque
            where boleto_cv_saque.id = unidade_financeira_extrato.id_cv_saque
            order by boleto_cv_saque.id desc limit 1
            ) AS status_saque
        ';

        $baseSelect = '
            select [COLUMNS]
            from unidade_financeira_extrato
            ';

        $queryFilters =  request()->query('f');

        if ($queryFilters) {
            $queryFilters = $this->parseQueryFilter($queryFilters);
        }

        $filters = $this->resolveFilters($queryFilters);

        $baseSelect .= $filters['where'];

        // a partir daqui pode aplicar condições fixas
        $baseSelect .= '';
        
        // registros
        $data = DB::select(
            str_replace('[COLUMNS]', $columns, $baseSelect) . "
                order by {$queryFilters[1]->col} desc,
                AGRUPADOR desc,
                ID desc
                limit {$limit} offset {$offset}",
            $filters['values']
        );

        // contador
        $counter = DB::select(
            "select count(1) as total from (
                ". str_replace('[COLUMNS]', '1', $baseSelect) ."
                ) as temp_count",
            $filters['values']
        );

        return [
            'total'     => $counter[0]->total,
            'pagina'    => $page,
            'limite'    => $limit,
            'resultado' => $data
        ];
    }
    
    /**
     * Retorna as unidades finaceiras com relação com unidade_financeira extrato e
     * com os respectivos "account_id" caso haja a relação entre ambos
     * @return JSON
     */
    public function unidades(){
        //Seleciona as unidades_finaceiras que estão relacionadas com unidade finaceira_extrato
        $sql = "SELECT CODIGO, DESCRICAO 
                FROM unidade_financeira
                WHERE (CODIGO in (select CODIGO_UNIDADE from unidade_financeira_extrato)
                OR CODIGO in (select UF.CODIGO FROM unidade_financeira UF
                                JOIN pagamento_especie PE 
                                ON (UF.CODIGO = PE.CODIGO_UNIDADE_FINANCEIRA)
                                JOIN boleto_cv BCV 
                                ON (PE.CODIGO = BCV.CODIGO_ESPECIE)))
                AND unidade_financeira.ATIVO = 'S'
                GROUP BY 1, 2
                ORDER BY 2;";
        $unidades_finaceiras = DB::select($sql); 
       
        //Para cada unidade financeira verifica se a mesma está relaciona a um conta virtual em boleto_cv
        for($i = 0; $i < count($unidades_finaceiras); $i++){
            $account_id = DB::select("  SELECT bcv.account_id 
                                        FROM boleto_cv bcv
                                        JOIN pagamento_especie pes
                                        ON (bcv.codigo_especie = pes.codigo)
                                        JOIN unidade_financeira unf
                                        ON (pes.CODIGO_UNIDADE_FINANCEIRA = unf.CODIGO)
                                        WHERE unf.CODIGO = {$unidades_finaceiras[$i]->CODIGO}
                                        AND unf.ATIVO = 'S'
                                        LIMIT 1" );
            
            //Seta o account_id caso o mesmo exista
            if(count($account_id)> 0){
                $unidades_finaceiras[$i]->ACCOUNT_ID = $account_id[0]->account_id;
            } else {
                $unidades_finaceiras[$i]->ACCOUNT_ID = null;
            }        
        }
        
        return $unidades_finaceiras;
    }
    
    /**
     * Retorna o saldo da conta virtual armazenado no BD
     * @param string $account_id
     * @return JSON
     */
    public function saldoContaVirtual($account_id){
        $result = DB::select("SELECT saldo_disponivel_data, saldo_atual_valor, saldo_disponivel_valor FROM boleto_cv bcv
                               WHERE account_id = '$account_id'");
        
        return $result[0];
    }
    
    /**
     * Retorna o saldo anterior da unidade finaceiro armazenado no BD
     * @param string $codigoUnidadeFinanceira
     * @return JSON
     */
    public function saldoAnterior($codigoUnidadeFinanceira, $data, $tipo_data = 1){
        //Subtrai um dia do $pagamentoMaisAntigo 
        $timestamp = strtotime(date("$data") . " -1 days"); 
        //Formata a data
        $data      = date('Y-m-d', $timestamp);
        if($tipo_data == 1){
            $tipo_data = "DATA";
            $tipo_saldo = "SALDO_ATUAL";
        }else{
            $tipo_data = "DATA_CONCILIACAO";
            $tipo_saldo = "SALDO_ATUAL_CONCILIACAO";
        }
        //Busca o SALDO_ATUAL do último registro consistente, o saldo será atualizado a partir do registro seguinte a este
        $ultimoRegistroConsistente = DB::table('unidade_financeira_extrato')
            ->select("$tipo_saldo")
            ->where($tipo_data, '<=', $data)
            ->where('CODIGO_UNIDADE', $codigoUnidadeFinanceira)
            ->orderBy($tipo_data,'desc')
            ->orderBy('agrupador','desc')
            ->orderBy('id','desc')
            ->limit(1)
            ->first();
        
        $result = new \stdClass();
        //Verifica se há registro de saldo anterior
        if($ultimoRegistroConsistente){
            //Obtêm o saldo atual e a data do último registro consistente
            $result->saldo_anterior_valor = $ultimoRegistroConsistente->$tipo_saldo;
        }else{
            //Caso não hover, significa que não há lançamentos anteriores a data inicial do periodo
            $result->saldo_anterior_valor = null;
        }
        
        return $result;
    }
    
    /**
     * Altera a data de um registro da unidade finaceiro armazenado no BD
     * @param int $id
     * @param string $data
     * @param int $tipo
     * @return JSON
     */
    public function editaData($id, $data, $tipo){
        $result = new \stdClass();
        //Inicia transação no BD
        DB::beginTransaction();
        if($tipo == 0){
            $sql = "UPDATE unidade_financeira_extrato SET DATA ='$data' WHERE ID = $id";
        }else{
            $sql = "UPDATE unidade_financeira_extrato SET DATA_CONCILIACAO ='$data' WHERE ID = $id";
        }
        $ok = DB::update($sql);
        If($ok == 0){
            DB::rollBack();
            $result->status_code = 400;
            $result->status_message = "Erro ao atualizar registro em Unidade Financeira!";
        }    
        DB::commit();
        $result->status_code = 200;
        $result->status_message = "Registro alterado com sucesso em Unidade Financeira!";
        return $result;        
    }
    
    /**
     * Retorna o saldo da conta virtual armazenado no BD
     * @param string $data
     * @return string
     */
    public function maxData($tipo_data){
        $data = 'DATA';
        if($tipo_data == 1){
            $data == 'DATA_CONCILIACAO';
        }
        $result = DB::select("SELECT MAX($data) AS DATA FROM admin.unidade_financeira_extrato;");
        if(count($result)){
            return $result[0]->DATA;            
        }
        return null;
    }
    
}
