<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;
use App\Estrutura;

/**
 * Modelo de Monitoramento Tribunal.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      20/07/2020
 */
class MonitoramentoTribunalModel extends NajModel {
     
    /**
     * Carrega Tabela
     */
    protected function loadTable() {
        $this->setTable('monitora_processo_tribunal');
    }
    
    /**
     * Busca a data do último registro em "monitora_processo_tribunal_buscas" 
     * 
     * @return null|date
     */
    public function buscaDataUltimoRegistroMPTB(){
        //Primeiramente vamos verificar se existem registros com status pendente
        //e pegar a data mais antiga entre os registros pendentes caso houver
        $sql = "SELECT min(data_hora) AS DATA
                FROM monitora_processo_tribunal_buscas
                WHERE status = 0
                LIMIT 1;";
        //Executa sql
        $result = DB::select($sql); 
        if($result[0]->DATA != null){
            //Acresenta mais 3 horas pois a data no BD é UTC -3 e na escavador é UTC
            //Acresenta 1 segundo na data hora na busca para não acontecer de pegar novamente callbacks já verificados
            $timestamp = strtotime($result[0]->DATA . " +3 hours +1 second");
            $data_hora = date('Y-m-d H:i:s', $timestamp);
            return $data_hora;
        }
        //Caso não encontrar data mais antiga de registro com status pendente
        //iremos então pegar a data mais atual entre todos os registros
        $sql = "SELECT max(data_hora) AS DATA, status 
                FROM monitora_processo_tribunal_buscas
                LIMIT 1;";
        //Executa sql
        $result = DB::select($sql); 
        if($result[0]->DATA != null){
            //Acresenta mais 3 horas pois a data no BD é UTC -3 e na escavador é UTC
            //Acresenta 1 segundo na data hora na busca para não acontecer de pegar novamente callbacks já verificados
            $timestamp = strtotime($result[0]->DATA . " +3 hours +1 second");
            $data_hora = date('Y-m-d H:i:s', $timestamp);
            return $data_hora;
            
        }
        return null;
    }
    
    public function buscaMonitoramento($id){
        $sql = "SELECT * FROM monitora_processo_tribunal WHERE id = $id";
        return DB::select($sql);
    }

    /**
     * Verifica os monitoramentos em "monitora_processo_tribunal" que atendem os seguintes critérios, 
     * contêm dia de frequencia igual ao dia corrente
     * status seja diferente de 0 = PENDENTE
     * e que não tenha sido executado no dia corrente  
     * (O que significa que para o dia corrente ainda não foi efetuado uma requisição
     * para a Escavador de pesquisa do processo no site do tribunal);
     * 
     * @param string $tipo automacao ou manual
     * @return array
     */
    public function buscaMonitoramentosElegiveisParaBusca($tipo){
        $sqlBase = "SELECT  mpt.id,
                            mpt.numero_cnj, 
                            mpt.frequencia,
                            mpt.abrangencia,
                            #---------------------------------------------------
                            mtb2.id_ultima_busca,
                            mtb2.status_ultima_busca,
                            date_format(mtb2.data_ultima_busca,'%Y-%m-%d') AS data_ultima_busca,
                            #---------------------------------------------------
                            mtb4.id_ultima_busca_pendente,
                            mtb4.status_ultima_busca_pendente,
                            date_format(mtb4.data_ultima_busca_pendente,'%Y-%m-%d') AS data_ultima_busca_pendente
                            #---------------------------------------------------
                    FROM(SELECT id, 
                    numero_cnj,
                    frequencia,
                    (CASE
                        WHEN B.abrangencia = 0 THEN ''
                        WHEN B.abrangencia = 1  THEN 'STJ'
                        WHEN B.abrangencia = 2  THEN 'STF'
                        WHEN B.abrangencia = 3  THEN 'TST'
                        WHEN B.abrangencia = 4  THEN 'TSE'
                        WHEN ISNULL(B.abrangencia) THEN ''
                        ELSE ''
                    END) AS abrangencia
                    FROM monitora_processo_tribunal B
                    WHERE status='A'
                    ) AS mpt
                    #---------------------------------------------------
                    /* NELSON COMENTOU AQUI
                    LEFT JOIN (
                        SELECT id ,
                            id_monitora_tribunal,
                            (SELECT id
                            FROM monitora_processo_tribunal_buscas
                            WHERE id_monitora_tribunal = mpb.id_monitora_tribunal
                            ORDER BY id_monitora_tribunal,
                                data_hora DESC,
                                id DESC
                            limit 1) AS id_ultima_busca
                        FROM monitora_processo_tribunal_buscas as mpb
                        HAVING id = id_ultima_busca
                        ORDER BY mpb.id_monitora_tribunal,
                        mpb.data_hora DESC,
                        mpb.id DESC
                    ) AS mtb1 
                    ON mtb1.id_monitora_tribunal = mpt.id
                    */
                    #- NELSON INCLUI AQUI
                    LEFT JOIN (
                        SELECT max(id) as id_ultima_busca,
                            id_monitora_tribunal
                        FROM monitora_processo_tribunal_buscas
                        group by id_monitora_tribunal
                    ) AS mtb1 
                    ON mtb1.id_monitora_tribunal = mpt.id
                    #---------------------------------------------------
                    LEFT JOIN (SELECT id AS id_ultima_busca, 
                        data_hora AS data_ultima_busca,
                        status AS status_ultima_busca,
                        status_mensagem AS status_mensagem_ultima_busca
                    FROM monitora_processo_tribunal_buscas 
                    ) AS mtb2 
                    ON mtb2.id_ultima_busca = mtb1.id_ultima_busca
                    #---------------------------------------------------
                    /* NELSON COMENTOU AQUI
                    LEFT JOIN (
                        SELECT id ,
                            id_monitora_tribunal,
                            (SELECT id
                            FROM monitora_processo_tribunal_buscas
                            WHERE id_monitora_tribunal = mpb.id_monitora_tribunal
                        AND status = 0
                            ORDER BY id_monitora_tribunal,
                                data_hora DESC,
                                id DESC
                            limit 1) AS id_ultima_busca_pendente
                        FROM monitora_processo_tribunal_buscas as mpb
                        HAVING id = id_ultima_busca_pendente
                        ORDER BY mpb.id_monitora_tribunal,
                        mpb.data_hora DESC,
                        mpb.id DESC
                    ) AS mtb3 
                    ON mtb3.id_monitora_tribunal = mpt.id
                    */
                    #- NELSON INCLUI AQUI
                    LEFT JOIN (
                        SELECT max(id) AS id_ultima_busca_pendente,
                        id_monitora_tribunal
                        FROM monitora_processo_tribunal_buscas
                        where status = 0
                        group by id_monitora_tribunal
                        order by
                        data_hora DESC,
                        id DESC
                    ) AS mtb3 
                    ON mtb3.id_monitora_tribunal = mpt.id
                    #---------------------------------------------------
                    LEFT JOIN (SELECT   id AS id_ultima_busca_pendente, 
                                        data_hora AS data_ultima_busca_pendente,
                                        status AS status_ultima_busca_pendente,
                                        status_mensagem AS status_mensagem_ultima_busca_pendente
                                FROM monitora_processo_tribunal_buscas 
                    ) AS mtb4 
                    ON mtb4.id_ultima_busca_pendente = mtb3.id_ultima_busca_pendente";
        
        //Onde o status_ultima_busca seja maior que 0 (0 == PENDENTE) ou status_ultima_busca seja nulo
        //e data_ultima_busca_pendente seja menor que a data corrente ou data_ultima_busca_pendente seja nulo
        $sql = $sqlBase  . " WHERE ((mtb2.status_ultima_busca > 0) || (mtb2.status_ultima_busca is null))
                             AND ((date_format(mtb4.data_ultima_busca_pendente,'%Y-%m-%d') < current_date()) || (mtb4.data_ultima_busca_pendente is null))";
        
        if($tipo == "automacao"){
            //date('w') - A numeric representation of the day (0 for Sunday, 6 for Saturday) 
            //date('d') - The day of the month (from 01 to 31)
            //Onde frequencia do monitoramento == dia da semana OR sys_config 'TRIBUNAIS'=>'DIAS_MES' == dia do mês
            $sql = $sql ." AND ((mpt.frequencia LIKE '%" . date('w') . "%') OR ((SELECT VALOR FROM sys_config WHERE SECAO = 'TRIBUNAIS' AND CHAVE = 'DIAS_MES') LIKE '%" . date("d") . "%')) "; 
        }
                
        $result = DB::select($sql); 
        if(count($result) > 0){
            return $result;
        }else{
            //Se não foram encontrado monitoramentos elegíveis para busca no SQL anterior então iremos verificar quantos "Monitoramento(s) com busca(s) nos tribunais já executada(s) HOJE!" existem.
            $sql = $sqlBase . " WHERE (mtb4.status_ultima_busca_pendente = 0)
                                AND (date_format(mtb4.data_ultima_busca_pendente,'%Y-%m-%d') = current_date())" ;
            if($tipo == "automacao"){
                $sql = $sql ." AND mpt.frequencia LIKE '%" . date('w') . "%' "; 
            }
            $results = DB::select($sql);
            if(count($results) > 0){
                return count($results) . " Monitoramento(s) com busca(s) nos tribunais já executada(s) HOJE!";
            }else{
                return $result;
            }
        }
    }
    
    public function buscaMonitoramentosPendentes(){
        $sql = "SELECT	mpt.id,
                mpt.numero_cnj, 
                date_format(mtb.data_ultima_busca,'%Y-%m-%d') AS data_ultima_busca,
                mtb.status,
                mtb.id_resultado_busca
                FROM( 
                    SELECT  id, 
                            numero_cnj,
                            frequencia
                    FROM monitora_processo_tribunal B
                    WHERE status='A'
                ) AS mpt
                INNER JOIN (
                    SELECT  id AS id_ultimo_status,
                            id_monitora_tribunal,
                            (SELECT id
                            FROM monitora_processo_tribunal_buscas
                            WHERE id_monitora_tribunal = mpb.id_monitora_tribunal
                            ORDER BY id_monitora_tribunal,
                            data_hora DESC,
                            id DESC
                            limit 1) AS id_ultimo_status_buscas
                    FROM monitora_processo_tribunal_buscas as mpb
                    HAVING id = id_ultimo_status_buscas
                    ORDER BY mpb.id_monitora_tribunal,
                    mpb.data_hora DESC,
                    mpb.id DESC
                ) AS mtb1 ON mtb1.id_monitora_tribunal = mpt.id
                INNER JOIN (
                    SELECT  id, 
                            data_hora AS data_ultima_busca,
                            status,
                            status_mensagem,
                            id_resultado_busca
                    FROM monitora_processo_tribunal_buscas            
                ) AS mtb ON mtb.id = mtb1.id_ultimo_status
                AND mtb.status = 0";
        
        return DB::select($sql); 
    }
    
    /**
     * Busca os monitoramentos com status "PENDENTE" a mais de 12 horas
     */
    public function buscaMonitoramentosObsoletos(){
        $timestamp = strtotime(date('Y-m-d H:i:s') . " -12 hours");
        $data_hora = date('Y-m-d H:i:s', $timestamp);
        //SQL antigo
//        $sql = "SELECT id_monitora_tribunal, id_resultado_busca 
//                FROM monitora_processo_tribunal_buscas
//                WHERE status = 0
//                AND data_hora < '" . $data_hora . "';";
        //SQL novo que traz o útimo status do monitoramento
        $sql = "SELECT	mpt.id,
			mpt.numero_cnj, 
			mtb.data_hora,
			mtb.status,
                        mtb.id_resultado_busca
                FROM( 
                        SELECT  id, 
                                numero_cnj,
                                frequencia
                        FROM monitora_processo_tribunal B
                        WHERE status='A'
                ) AS mpt
                INNER JOIN (
                            SELECT  id AS id_ultimo_status,
                                    id_monitora_tribunal,
                                    (SELECT id
                                    FROM monitora_processo_tribunal_buscas
                                    WHERE id_monitora_tribunal = mpb.id_monitora_tribunal
                                    ORDER BY id_monitora_tribunal,
                                    data_hora DESC,
                                    id DESC
                                    limit 1) AS id_ultimo_status_buscas
                            FROM monitora_processo_tribunal_buscas as mpb
                            HAVING id = id_ultimo_status_buscas
                            ORDER BY mpb.id_monitora_tribunal,
                            mpb.data_hora DESC,
                            mpb.id DESC
                ) AS mtb1 ON mtb1.id_monitora_tribunal = mpt.id
                INNER JOIN (
                            SELECT  id, 
                                    data_hora,
                                    status,
                                    status_mensagem,
                                    id_resultado_busca
                            FROM monitora_processo_tribunal_buscas            
                ) AS mtb ON mtb.id = mtb1.id_ultimo_status
                AND mtb.status = 0
                AND data_hora < '" . $data_hora . "';";
        return DB::select($sql);
    }
    
    /**
     * Busca a sigla de todos os tribunais cadastrados em monitora_tribunais no BD
     * 
     * @return array
     */
    public function buscaSiglaTribunaisDeMonitoraTribunais(){
        $sql     = "SELECT sigla FROM monitora_tribunais";
        $result  = DB::select($sql); 
        $retorno = [];
        if(count($result) > 0){
            foreach ($result as $item){
                array_push($retorno, $item->sigla);
            }
        }
        return $retorno;
    }
    
    /**
     * Busca a sigla de todos os tribunais cadastrados em prc_orgap no BD
     * 
     * @return array
     */
    public function buscaSiglaTribunaisDePrcOrgao(){
        $sql     = "SELECT ORGAO FROM prc_orgao";
        $result  = DB::select($sql); 
        $retorno = [];
        if(count($result) > 0){
            foreach ($result as $item){
                array_push($retorno, $item->ORGAO);
            }
        }
        return $retorno;
    }
    
    /**
     * Seta o registro 'indefinido' em monitora_tribunais
     * 
     * @return int
     */
    public function setaTribunalIndefinido(){
        //$sql     = "SELECT max(id) id FROM monitora_tribunais";
        //$id      = DB::select($sql)[0]->id + 1;
        $sql     = "INSERT INTO monitora_tribunais VALUES id = 0 nome = 'Indefinido' sigla = '---'";
        return DB::insert($sql); 
    }
    
    /**
     * Verifica Se Movimentacao Já Existe
     * 
     * @param int $id_movimentacao
     * @return boolean
     */
    public function verificaSeMovimentacaoJaExiste($id_movimentacao){
        $sql = "SELECT COUNT(id_movimentacao) qtd
                FROM monitora_processo_movimentacao
                WHERE id_movimentacao = $id_movimentacao";
        $quantidade = DB::select($sql)[0]->qtd;
        if($quantidade > 0){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Busca o id do "monitora_processo_tribunal" com base no "numero_cnj" e na "instancia"
     * 
     * @param  string $numero_processo
     * @param  string $instancia
     * @return null|array|int
     */
    public function buscaIdMonitoraProcessoTribunal($numero_processo, $instancia = null){
        
        if($instancia){
            $instancia_db = ['PRIMEIRO_GRAU' => '1º Instância','SEGUNDO_GRAU' => '2º Instância','TURMA_RECURSAL' => '2º Instância','SUPERIOR' => 'Superior', 'DESCONHECIDA' => '1º Instância'];
            if(array_key_exists($instancia, $instancia_db)){
                $instancia = $instancia_db[$instancia];
            }else{
                throw new NajException("A instância '$instancia' não é reconhecida pelo sistema NAJ, para corrigir esse erro ela deve ser adicionada no código fonte ao array 'instancia_db' no método 'buscaIdMonitoraProcessoTribunal' da classe 'MonitoramentoTribunalModel'");
            }
        }
        //Sql base
        $sql = "SELECT A.id 
                FROM monitora_processo_tribunal as A";
        //Se a instãncia foi informada iremos adicionar o realcionamento com "monitora_processo_tribunal_rel_prc" e com "prc"
        if($instancia){
            $sql .=  " JOIN monitora_processo_tribunal_rel_prc as B
                ON (A.id = B.id_monitora_tribunal)
                JOIN prc as C
                ON (B.codigo_processo = C.codigo)";
        }
        //Condição do processo 
        $sql .= " WHERE numero_cnj = '$numero_processo'"; 
        //Condição da instãcia
        if($instancia){
            $sql .=  " AND C.GRAU_JURISDICAO = '$instancia'
                       LIMIT 1";
        }
        //Executa sql
        $result = DB::select($sql);
        //Se não achou o monitoramento com base na instância iremos procurar apenas com base no número do processo
        if(count($result) == 0 && $instancia != null){
            //Sql para buscar monitoramento com base apenas no número do processo limitando em um registro
            $sql = "SELECT A.id 
                    FROM monitora_processo_tribunal as A 
                    WHERE numero_cnj = '$numero_processo'
                    LIMIT 1";
            //Executa sql
            $result = DB::select($sql);
        }
        if(count($result) == 1){
            if($instancia){
                return $result[0]->id;
            }else{
                return [$result[0]->id];
            }
        }else if(count($result) > 1){
            $array = [];
            foreach($result as $item){
                array_push($array, $item->id);
            }
            return $array;
        }else{
            return null;
        }
    }
    
    /**
     * Atualiza o Id Tribunal em "monitora_processo_tribunal"
     * 
     * @param type $id_monitora_processo_tribunal
     */
    public function atualizaIdTribunalMPT($id_monitora_processo_tribunal, $sigla){
        //Primeiramente vamos obter o id do tribunal em "monitora_tribunais" com base na sigla
        $sql = "SELECT id FROM monitora_tribunais WHERE sigla = '$sigla'";
        $result = DB::select($sql);
        if(count($result) > 0){
            $id_monitora_tribunais = $result[0]->id;
        }else{
            return "Não foi possível encontar o tribunal com a sigla $sigla no Banco de dados";
        }
        //Agora com o id do tribunal obtido vamos atualizar o id_tribunal em "monitora_processo_tribunal"
        $sql    = "UPDATE monitora_processo_tribunal SET id_tribunal = $id_monitora_tribunais WHERE id = $id_monitora_processo_tribunal";
        $result = DB::update($sql);
        return true;
    }
    
    /**
     * Verifica se o envolvido já tem vínculo com alguma pessoa do sistema
     * 
     * @param string $nome_envolvido
     * @param string $tipo_envolvido
     * @return int|null retorna o código da pessoa
     */
    public function verificaCodigoPessoaEnvolvido($nome_envolvido,$tipo_envolvido){
        $sql = 'SELECT pessoa_codigo FROM monitora_termo_envolvidos
                WHERE nome = "$nome_envolvido" and tipo = "$tipo_envolvido"
                limit 1;';
        $result = DB::select($sql); 
        if(count($result) == 1){
            $result[0]->pessoa_codigo;
        }else{
            return null;
        }
    }
    
    /**
     * Sobreescreve o metódo makePagination da classe mãe
     * pois para essa rotina é necessário um SQL personalizado
     * 
     * @return Array associative
     */
    public function makePagination() {
        $limit = $this->itemsPerPage;

        $pageQuery = request()->query('page');

        $page = $pageQuery > 0 ? (integer) $pageQuery : 1;

        $offset = ($page * $limit) - $limit;
        
        $columns = '
            mpt.id as id_mpt, 
            mpt.status as status_mpt,
            mpt.numero_cnj,
            mpt.frequencia,
            mpt.abrangencia,
            mpt.status,
            mt.sigla as sigla_tribunal,
            mt.nome as nome_tribunal,

            (SELECT data FROM monitora_processo_movimentacao pm
            WHERE pm.id_monitora_processo = mpt.id
            ORDER BY data DESC LIMIT 1
            ) AS data_ultimo_andamento,
            
            (SELECT conteudo FROM monitora_processo_movimentacao pm
            WHERE pm.id_monitora_processo = mpt.id
            ORDER BY data DESC LIMIT 1
            ) AS conteudo_ultimo_andamento,
            
            (SELECT count(0) FROM monitora_processo_movimentacao pm
            WHERE pm.id_monitora_processo = mpt.id
            ) AS qtde_total_andamentos,

            (SELECT count(0) FROM monitora_processo_movimentacao pm
            WHERE pm.id_monitora_processo = mpt.id
            AND lido="N"
            ) AS qtde_novas_andamentos,

            (SELECT status FROM monitora_processo_tribunal_buscas mptb
            WHERE mptb.id_monitora_tribunal = mpt.id
            ORDER BY data_hora DESC, id DESC LIMIT 1
            ) AS status_code_ultima_busca,
            
            (SELECT status_mensagem FROM monitora_processo_tribunal_buscas mptb
            WHERE mptb.id_monitora_tribunal = mpt.id
            ORDER BY data_hora DESC, id DESC LIMIT 1
            ) AS status_msg_ultima_busca,
            
            (SELECT date(data_hora) FROM monitora_processo_tribunal_buscas mptb
            WHERE mptb.id_monitora_tribunal = mpt.id
            ORDER BY data_hora DESC, id DESC LIMIT 1
            ) AS data_ultima_busca,

            pc.NUMERO_PROCESSO,
            pc.CODIGO_DIVISAO,
            pc.CODIGO_CLIENTE,
            P1.NOME AS NOME_CLIENTE,
            pc.QUALIFICA_CLIENTE,
            pc.ID_AREA_JURIDICA,

            (SELECT COUNT(0) FROM PRC_GRUPO_CLIENTE PGC
            WHERE PGC.CODIGO_PROCESSO = PC.CODIGO
            ) AS QTDE_CLIENTES,

            P2.NOME AS NOME_ADVERSARIO,
            pc.QUALIFICA_ADVERSARIO,

            (SELECT COUNT(0) FROM PRC_GRUPO_ADVERSARIO PGA
            WHERE PGA.CODIGO_PROCESSO = PC.CODIGO
            ) AS QTDE_ADVERSARIOS,
            
            pc.CODIGO as codigo_processo,
            pc.GRAU_JURISDICAO as instancia,
            cl.CLASSE,
            ca.CARTORIO,
            co.COMARCA,
            co.UF AS COMARCA_UF
        ';
        
        $baseSelect = '
            select [COLUMNS]
            FROM monitora_processo_tribunal mpt
            INNER JOIN monitora_tribunais mt 
            ON mt.id = mpt.id_tribunal
            INNER JOIN monitora_processo_tribunal_rel_prc mptrel 
            ON mptrel.id_monitora_tribunal = mpt.id
            INNER JOIN prc pc 
            ON pc.codigo = mptrel.codigo_processo
            LEFT JOIN PESSOA P1 
            ON P1.CODIGO = pc.CODIGO_CLIENTE
            LEFT JOIN PESSOA P2 
            ON P2.CODIGO = pc.CODIGO_ADVERSARIO
            LEFT JOIN PRC_COMARCA co 
            ON CO.CODIGO = pc.CODIGO_COMARCA
            LEFT JOIN PRC_CARTORIO ca 
            ON CA.CODIGO = pc.CODIGO_CARTORIO
            LEFT JOIN PRC_CLASSE cl 
            ON CL.CODIGO = pc.CODIGO_CLASSE
            ';

        //Obtêm os filtros da requisição
        $queryFilters = request()->query('f');

        //Se houverem filtros na requisição, iremos descriptografa-los
        if ($queryFilters) {
            $queryFilters = $this->parseQueryFilter($queryFilters);
        }
        $condicao = "";
        foreach ($queryFilters as $filter){
            if($filter->col == "numero_cnj"){
                $condicao = " WHERE $filter->col LIKE '%$filter->val%'";
            } else if ($filter->col == "parte"){
                $condicao = "WHERE P1.NOME LIKE '%$filter->val%' 
                            OR P2.NOME LIKE '%$filter->val%'
                            OR (
                                pc.CODIGO IN(
                                    SELECT CODIGO_PROCESSO 
                                    FROM PRC_GRUPO_CLIENTE
                                    WHERE CODIGO_CLIENTE IN(
                                        SELECT CODIGO 
                                        FROM PESSOA 
                                        WHERE NOME LIKE '%$filter->val%'
                                        OR pc.CODIGO IN(
                                            SELECT CODIGO_PROCESSO 
                                            FROM PRC_GRUPO_ADVERSARIO 
                                            WHERE CODIGO_ADVERSARIO IN(
                                                SELECT CODIGO 
                                                FROM PESSOA 
                                                WHERE NOME LIKE '%$filter->val%'
                                            )
                                        )
                                    ) 
                                )
                            )";
            }else if ($filter->col == "data_ultimo_andamento"){
                $condicao .= " HAVING ((data_ultimo_andamento between '$filter->val' and '$filter->val2') or ISNULL(data_ultimo_andamento))";
            }else if ($filter->col == "data_ultima_busca"){
                $condicao .= " HAVING ((data_ultima_busca between '$filter->val' and '$filter->val2') or ISNULL(data_ultima_busca))";
            }else if ($filter->col == "qtde_novas_andamentos"){
                $condicao .= " AND ($filter->col > $filter->val)";
            }else if ($filter->col == "status_code_ultima_busca"){
                $condicao .= " AND ($filter->col = $filter->val)";
            }else if ($filter->col == "qtde_total_andamentos"){
                $condicao .= " AND ($filter->col = $filter->val)";
            }else if ($filter->col == "status"){
                $condicao .= " AND ($filter->col = '$filter->val')";
            }
        }
        
        $sql = str_replace('[COLUMNS]', $columns, $baseSelect). 
                $condicao .
                " order by data_ultimo_andamento desc
                limit {$limit}
                offset {$offset}";
                
        //Executa sql
        $data = DB::select(
            $sql
        );
        
        //Para cada monitoramento busca as movimentações do monitoramento
        foreach($data as $index => $registro){
                
            $sql4 = "SELECT mov.id, 
                            mov.data,
                            mov.conteudo, 
                            mov.lido,
                            mov.instancia, 
                            mov.url_tj, 
                            mov.id_prc_movimento, 
                            mov.id_atividade, 
                            pmv.TRADUCAO_ANDAMENTO,
                            atv.DATA,
                            atv.HORA_INICIO,
                            atv.TEMPO,
                            atv.ID_TIPO_ATIVIDADE,
                            atv.ENVIAR,
                            tri.sigla
                    FROM monitora_processo_movimentacao mov
                    JOIN monitora_processo_tribunal prt
                    ON (mov.id_monitora_processo = prt.id)
                    JOIN prc_movimento pmv
                    ON (pmv.ID = mov.id_prc_movimento)
                    LEFT JOIN atividade atv
                    ON (atv.CODIGO = mov.id_atividade)
                    JOIN monitora_tribunais tri
                    ON (tri.id = prt.id_tribunal)
                    WHERE id_monitora_processo = $registro->id_mpt
                    ORDER BY mov.instancia ASC,
                    mov.data DESC";

            $movimentacoes = DB::select($sql4);

            $data[$index]->movimentacoes = $movimentacoes;
            
        }
        
        //Para cada monitoramento busca envolvidos do grupo cliente do processo "prc_grupo_cliente"
        foreach($data as $index => $registro){
                
            //Busca codigo_processo e numero_novo do processo
            $sql5 = "SELECT PGC.ID AS id_prc_grupo_cliente,
                            PGC.QUALIFICACAO,
                            PES.CODIGO AS pessoa_codigo,
                            PES.NOME
                    FROM prc_grupo_cliente PGC
                    JOIN pessoa PES
                    ON (PES.CODIGO = PGC.CODIGO_CLIENTE)
                    WHERE PGC.CODIGO_PROCESSO = " . $data[$index]->codigo_processo; 

            $envolvidos_grupo_cliente = DB::select($sql5);

            $data[$index]->envolvidos_grupo_cliente = $envolvidos_grupo_cliente;
            
        }
        
        //Para cada monitoramento busca envolvidos do grupo adversario do processo "prc_grupo_adversario" 
        foreach($data as $index => $registro){
                
            $sql6 = "SELECT PGA.ID AS id_prc_grupo_adversario,
                            PGA.QUALIFICACAO,
                            PES.CODIGO AS pessoa_codigo,
                            PES.NOME
                    FROM prc_grupo_adversario PGA
                    JOIN pessoa PES
                    ON (PES.CODIGO = PGA.CODIGO_ADVERSARIO)
                    WHERE PGA.CODIGO_PROCESSO = " . $data[$index]->codigo_processo; 

            $envolvidos_grupo_adversario = DB::select($sql6);

            $data[$index]->envolvidos_grupo_adversario = $envolvidos_grupo_adversario;
            
        }
        
        $sql7 = " select count(1) as total from (" .
                str_replace('[COLUMNS]', $columns, $baseSelect) . $condicao .
                " order by data_ultimo_andamento desc
                ) as temp_count";
            
#        return $sql7;
        
        // contador
        $counter = DB::select(
            $sql7
        );
        
        $total = $counter[0]->total;
        
        return [
            'total'                         => $total,
            'total_novas_movimentacoes'     => $this->quantidadeNovasMovimentacoes(),
            'total_buscas_em_andamento'     => $this->quantidadeBuscasEmAndamento(),
            'total_erro_na_ultima_busca'    => $this->quantidadeErroNaUltimaBusca(),
            'total_sem_movimentacoes'       => $this->quantidadeSemMovimentacoes(),
            'total_monitoramentos_baixados' => $this->quantidadeMonitoramentosBaixados(),
            'pagina'                        => $page,
            'limite'                        => $limit,
            'resultado'                     => $data
        ];
    }
    
    /**
     * Retorna a quantidade de monitoramentos com novas movimentacoes
     * 
     * @return int
     */
    public function quantidadeNovasMovimentacoes(){
        $sql = "SELECT SUM(qtd_andamentos) as total FROM (
                    SELECT (
                        SELECT count(mpm.id) 
                        FROM monitora_processo_movimentacao mpm
                        WHERE mpt.id = mpm.id_monitora_processo
                        AND mpm.lido = 'N') as qtd_andamentos
                    FROM monitora_processo_tribunal mpt
                    WHERE status = 'A'
                    HAVING qtd_andamentos > 0)
                as total";
        return (int) DB::select($sql)[0]->total;
    }
    
    /**
     * Retorna quantidade de monitoramentos com buscas em andamento
     * 
     * @return int
     */
    public function quantidadeBuscasEmAndamento(){
        $sql = "SELECT count(1) as total FROM (
                    SELECT  status,
                            (
                            SELECT mptb.status 
                            FROM monitora_processo_tribunal_buscas mptb
                            WHERE mptb.id_monitora_tribunal = mpt.id
                            ORDER BY data_hora DESC, 
                            id DESC 
                            LIMIT 1
                            ) AS status_code_ultima_busca
                    FROM monitora_processo_tribunal mpt
                ) as registros
                where registros.status_code_ultima_busca = 0
                and registros.status = 'A'";
        return (int) DB::select($sql)[0]->total;
    }
    
    /**
     * Retorna quantidade de monitoramentos com erros na última busca
     * 
     * @return int
     */
    public function quantidadeErroNaUltimaBusca(){
        $sql = "SELECT count(1) as total FROM (
                    SELECT  status,
                            (
                            SELECT mptb.status 
                            FROM monitora_processo_tribunal_buscas mptb
                            WHERE mptb.id_monitora_tribunal = mpt.id
                            ORDER BY data_hora DESC,
                            id DESC 
                            LIMIT 1
                            ) AS status_code_ultima_busca
                    FROM monitora_processo_tribunal mpt
                ) as registros
                where registros.status_code_ultima_busca = 2
                and registros.status = 'A'";
        return (int) DB::select($sql)[0]->total;
    }

    /**
     * Retorna a quantidade de monitoramentos sem movimentação
     * 
     * @return int
     */
    public function quantidadeSemMovimentacoes(){
        $sql = "SELECT count(1) as total FROM (SELECT mpt.id,
                (SELECT count(mpm.id) 
                    FROM monitora_processo_movimentacao mpm
                    WHERE mpt.id = mpm.id_monitora_processo) as qtd_andamentos
                FROM monitora_processo_tribunal mpt
                HAVING qtd_andamentos = 0) as total;";
        return (int) DB::select($sql)[0]->total;
    }
    
    /**
     * Retorna a quantidade de monitoramentos baixados
     * 
     * @return int
     */
    public function quantidadeMonitoramentosBaixados(){
        $sql = "SELECT count(mpt.id) total 
                FROM monitora_processo_tribunal mpt
                WHERE mpt.status = 'B';";
        return (int) DB::select($sql)[0]->total;
    }
    
    /**
     * Retorna os monitoramentos com erros na última busca
     * 
     * @return array
     */
    public function monitoramentosComErroNaUltimaBusca(){
        $sql = "SELECT id,
                numero_cnj,
                abrangencia,
                status,
                status_code_ultima_busca FROM (
                    SELECT  id,
                                    numero_cnj,
                                    abrangencia,
                                    status,
                                    (
                                    SELECT mptb.status 
                                    FROM monitora_processo_tribunal_buscas mptb
                                    WHERE mptb.id_monitora_tribunal = mpt.id
                                    ORDER BY data_hora DESC,
                                    id DESC 
                                    LIMIT 1
                                    ) AS status_code_ultima_busca
                    FROM monitora_processo_tribunal mpt
                ) as registros
                where registros.status_code_ultima_busca = 2
                and registros.status = 'A'";
        return (array) DB::select($sql);
    }
    
    /**
     * Exclui os registros de "monitora_processo_movimentacao" e seus relacionamentos em "prc_movimento" e "atividade"
     * 
     * @param type $id        id do monitoramento do processo
     * @param type $instancia instância do processo
     */
    public function excluirMovimentacao($id, $instancia){
        //Inicia transação no BD
        DB::beginTransaction();
        if($instancia == 1){
            //Foi definido na regra de negócio que processos de instâncias "DESCONHECIDA" seria tratado como 'PRIMEIRO_GRAU'
            $instancia = " (instancia = 'PRIMEIRO_GRAU' || instancia = 'DESCONHECIDA')";
        }else if($instancia == 2){
            $instancia = " instancia = 'SEGUNDO_GRAU'";
        }
        $sql1 = "SELECT id, id_prc_movimento, id_atividade FROM monitora_processo_movimentacao
               WHERE id_monitora_processo = $id AND $instancia" ;
        $movimentacaoes = DB::select($sql1);
        $movimentacaoesExcluidas = 0;
        foreach ($movimentacaoes as $movimentacao){
            $sql2 = "DELETE FROM monitora_processo_movimentacao WHERE id = " . $movimentacao->id;
            $result1 = DB::delete($sql2);
            if($result1 > 0){
                $movimentacaoesExcluidas++;
            }
            if($movimentacao->id_prc_movimento){
                $sql3 = "DELETE FROM prc_movimento WHERE ID = " . $movimentacao->id_prc_movimento;
                $result2 = DB::delete($sql3);
            }
            if($movimentacao->id_atividade){
                $sql4 = "DELETE FROM atividade WHERE CODIGO = " . $movimentacao->id_atividade;
                $result3 = DB::delete($sql4);
            }
        }
        //Verifica se existe movimentações que deveriam ser excluidas 
        if(count($movimentacaoes) > 0){
            //Verifica se a quntidade de movimentações excluidas é igual a quantidade de movimentações de fato excluidas
            if(count($movimentacaoes) == $movimentacaoesExcluidas){
                $code = 200;
                $msgRetorno = $movimentacaoesExcluidas . " movimentações excluidas com sucesso.";
                //Comita transação
                DB::commit();
            }else{
                $code = 400;
                $msgRetorno = "Erro ao excluir as movimentações, contate o suporte.";
                DB::rollback();
            }
        }else{
            $code = 200;
            $msgRetorno = "Não foram detectadas movimentações de {$instancia} para este processo.";
            DB::rollback();
        }
        return ['code' => $code, 'msg_retorno' => $msgRetorno];
    }
    
    /**
     * Seta registro de "monitora_processo_movimentacao" como lido
     */
    public function setaRegistroComoLido($id){
        $sql = "UPDATE monitora_processo_movimentacao 
               SET lido = 'S'
               WHERE id = $id";
        DB::update($sql);
    }
    
    /**
     * Seta todos os registro de "monitora_processo_movimentacao" como lido
     */
    public function setaTodosRegistrosComolidos(){
        $sql = "UPDATE monitora_processo_movimentacao 
               SET lido = 'S'";
        DB::update($sql);
    }
    
    /**
     * Busca ID de "prc_movimento" com base no "CODIGO_PROCESSO"
     */
    public function buscaIdProcessoMovimento($codigo_processo){
        $sql = "SELECT ID FROM prc_movimento
            WHERE CODIGO_PROCESSO = $codigo_processo";
        $result = DB::select($sql);        
        if(count($result) == 0){
            return null;
        }else{
            return $result[0]->ID;
        }
    }
    
    /**
     * Obtêm o código do processo no banco de dados com base no id do monitoramento
     * 
     * @param int $id_monitora_processo_tribunal
     * @return null|int
     */
    public function obterCodigoProcessoComBaseIdMonitoramento($id_monitora_processo_tribunal){
        $sql = "SELECT rel.codigo_processo codigo_processo
                FROM monitora_processo_tribunal mon
                JOIN monitora_processo_tribunal_rel_prc rel
                ON(mon.id = rel.id_monitora_tribunal)
                WHERE mon.id = $id_monitora_processo_tribunal
                limit 1;";
        $result = DB::select($sql);        
        if(count($result) == 0){
            return null;
        }else{
            return $result[0]->codigo_processo;
        }
    }
 
    /**
     * Busca o total de monitoramentos no sistema
     * 
     * @return int
     */
    public function totalDeMonitoramentosNoSistema(){
        $sql = "SELECT count(id) as total FROM monitora_processo_tribunal";
        return DB::select($sql)[0]->total;
    }
    
    /**
     * Busca o total de monitoramentos ativos no sistema
     * 
     * @return int
     */
    public function totalDeMonitoramentosAtivosNoSistema(){
        $sql = "SELECT count(id) as total FROM monitora_processo_tribunal where status = 'A'";
        return DB::select($sql)[0]->total;
    }
    
    /**
     * Verifica se CNJ já tem monitoramento
     * 
     * @param Request $request
     * @return bool
     */
    public function verificaSeCNJjaTemMonitoramento($numero_cnj, $abrangencia){
        $sql = "SELECT mpt.id,
                    mpt.numero_cnj,
                    mpt.frequencia,
                    mpt.status,
                    mpt.abrangencia,
                    mptb.data_hora,
                    mptb.status status_busca,
                    mptb.status_mensagem,
                    mptb.id_resultado_busca
            FROM monitora_processo_tribunal mpt
            JOIN monitora_processo_tribunal_buscas mptb
            ON (mpt.id = mptb.id_monitora_tribunal)
            WHERE mpt.numero_cnj = '$numero_cnj'
            AND mpt.abrangencia  = $abrangencia
            AND mptb.status <> 2
            ORDER BY id DESC
            LIMIT 1;";
        return DB::select($sql);
    }
    
}