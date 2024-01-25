<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;
use App\Estrutura;

/**
 * Modelo de Monitoramento Diario.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      20/07/2020
 */
class MonitoramentoDiarioModel extends NajModel {
    
     
    /**
     * Carrega Tabela
     */
    protected function loadTable() {
        $this->setTable('monitora_termo_movimentacao');
        
        $this->addColumn('id', true);
        $this->addColumn('id_prc_movimento');
        $this->addColumn('id_tarefa');
        $this->addColumn('id_atividade');
        
        $this->primaryKey = 'id';
    }
    
    /**
     * Busca a data da última movimentaçao em 'monitora_termo_movimentacao'
     * 
     * @return null|date
     */
    public function buscaDataUltimaMovimentacao(){
        $sql = "SELECT data_hora_inclusao AS DATA
                FROM monitora_termo_movimentacao
                ORDER BY 1 DESC
                LIMIT 1;";
        $result = DB::select($sql); 
        if(count($result) > 0){
            return $result[0]->DATA;
        }
        return null;
    }
    
    /**
     * Seta o o registro como lido no BD
     * 
     * @return bool
     */
    public function setaRegistroLido($id){
        $sql = "UPDATE monitora_termo_movimentacao SET lido = 'S' WHERE (id = '$id');";
        $result = DB::update($sql); 
        if($result){
            return true;
        }
        return false;
    }

    /**
     * Busca o id_diario de todos os diários cadastrados no BD
     * 
     * @return array
     */
    public function buscaId_Diarios(){
        $sql = "SELECT id_diario FROM monitora_diarios";
        $result = DB::select($sql); 
        $retorno = [];
        if(count($result) > 0){
            foreach ($result as $item){
                array_push($retorno, $item->id_diario);
            }
        }
        return $retorno;
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
        
        //Para essa consulta precisar fazer um UNION, sendo assim precisamos criar dois Statements 
        //com a mesma quantidade de colunas, alterando apenas nos JOINS e WHERES  
        
        //Se algum dia precisar de informações completas sobre o registro adiionar ao sql "movimentacao.conteudo_json,"
        
        $columns = '
            movimentacao.id,
            movimentacao.id_diario,
            movimentacao.id_movimentacao,
            movimentacao.id_monitora_termo,
            movimentacao.id_processo,
            movimentacao.data_hora_inclusao,
            movimentacao.data_disponibilizacao,
            movimentacao.data_publicacao,
            movimentacao.conteudo_publicacao,
            movimentacao.conteudo_json,
            movimentacao.pagina,
            movimentacao.secao,
            movimentacao.tipo,
            movimentacao.lido,
            movimentacao.descartada,
            movimentacao.id_prc_movimento,
            movimentacao.id_tarefa,
            movimentacao.id_atividade,
            termo.termo_pesquisa,
            termo.variacoes as termo_variacoes,
            diarios.nome as diario_nome,
            diarios.competencia as diario_competencia,
            atividade.DATA,
            atividade.TEMPO,
            atividade.ID_TIPO_ATIVIDADE,
            atividade.ENVIAR,
            atividade.HORA_INICIO,
            prc_movimento.ID as id_prc_movimento,
            prc_movimento.DATA as data_prc_movimento,
            prc_movimento.DESCRICAO_ANDAMENTO,
            prc_movimento.TRADUCAO_ANDAMENTO
        ';
        
        $baseSelect = '
            select [COLUMNS]
            from monitora_termo_movimentacao as movimentacao
            join monitora_termo_diario as termo
            on (termo.id = movimentacao.id_monitora_termo)
            join monitora_diarios as diarios
            on (diarios.id = movimentacao.id_diario)
            left join monitora_termo_processo as mtp
            on (mtp.id = movimentacao.id_processo)
            left join monitora_processo_tribunal_rel_prc as mptrp
            on (mtp.codigo_processo = mptrp.codigo_processo)
            left join atividade
            on (movimentacao.id_atividade = atividade.CODIGO)
            left join prc_movimento 
            on (movimentacao.id_prc_movimento = prc_movimento.ID)
            ';

        //Obtêm os filtros da requisição
        $queryFilters =  request()->query('f');

        //Se houverem filtros na requisição, iremos descriptografa-los
        if ($queryFilters) {
            //Filtros do primeiro Statement
            $queryFilters1 = $this->parseQueryFilter($queryFilters);
            //Filtros do segundo Statement
            $queryFilters2 = $this->parseQueryFilter($queryFilters);
        }
        
        //No primeiro Statement precisamos remover o filtro de "processo.codigo_processo" caso exista,
        //pois neste Statement não iremos fazer o JOIN com a tabela "monitora_termo_processo"
        foreach($queryFilters1 as $index => $filter){
            if($filter->col == "processo.codigo_processo"){
                //Remove o filtro
                unset($queryFilters1[$index]);
                break;
            }
        } 

        $filters1 = $this->resolveFilters($queryFilters1);
        
        //Se conter a claussula "id_processo" iremos remover essa claussula para o segundo sql do UNION para que traga os dados corretamente
        $isFiltroPendente = false;
        foreach ($queryFilters2 as $index => $queryFilter2){
            if($queryFilter2->col == "id_processo"){
                unset($queryFilters2[$index]);
                $isFiltroPendente = true;
            }
        }
        
        $filters2 = $this->resolveFilters($queryFilters2);

        //Primeiro Stantment do UNION
        $sql1 = str_replace('[COLUMNS]', $columns, $baseSelect . $filters1['where']);
        
        //Segundo Stantment do UNION
        $sql2 = str_replace('[COLUMNS]', $columns, $baseSelect) . 
                " join monitora_termo_processo as processo
                on (processo.id = movimentacao.id_processo)" . 
                $filters2['where'] . 
                " order by 1 desc
                limit {$limit}
                offset {$offset}";
                
        //Sql completo
        $sql3 = $sql1 . " union " . $sql2;
        
        //Como estamos utilizando um UINION precisamos repetir os filtros
        $filtros = $filters2['values'];
        foreach($filtros as $index => $filtro){
            $filtros[] = $filtros[$index];
        }
        
        //Executa sql
        $data = DB::select(
            $sql3,
            $filtros
        );
        
        foreach($data as $index => $registro){
            
            //Vamos separar os atributos da atividade dentro de um atributo chamado atividade
            $data[$index]->atividade                    = new \stdClass();
            $data[$index]->atividade->CODIGO            = $data[$index]->id_atividade;
            $data[$index]->atividade->DATA              = $data[$index]->DATA;
            $data[$index]->atividade->TEMPO             = $data[$index]->TEMPO;
            $data[$index]->atividade->ID_TIPO_ATIVIDADE = $data[$index]->ID_TIPO_ATIVIDADE;
            $data[$index]->atividade->ENVIAR            = $data[$index]->ENVIAR;
            $data[$index]->atividade->HORA_INICIO       = $data[$index]->HORA_INICIO;
            
            //Vamos remover os atributos da atividade de dentro do primeiro nível do registro
            unset($data[$index]->id_atividade);
            unset($data[$index]->DATA);
            unset($data[$index]->TEMPO);
            unset($data[$index]->ID_TIPO_ATIVIDADE);
            unset($data[$index]->ENVIAR);
            unset($data[$index]->HORA_INICIO);
            
            //Vamos separar os atributos do prc_movimento dentro de um atributo chamado prc_movimento
            $data[$index]->prc_movimento                      = new \stdClass();
            $data[$index]->prc_movimento->ID                  = $data[$index]->id_prc_movimento;
            $data[$index]->prc_movimento->DATA                = $data[$index]->data_prc_movimento;
            $data[$index]->prc_movimento->DESCRICAO_ANDAMENTO = $data[$index]->DESCRICAO_ANDAMENTO;
            $data[$index]->prc_movimento->TRADUCAO_ANDAMENTO  = $data[$index]->TRADUCAO_ANDAMENTO;
            
            //Vamos remover os atributos do prc_movimento de dentro do primeiro nível do registro
            unset($data[$index]->id_prc_movimento);
            unset($data[$index]->data_prc_movimento);
            unset($data[$index]->DESCRICAO_ANDAMENTO);
            unset($data[$index]->TRADUCAO_ANDAMENTO);
            
            if(!is_null($registro->id_processo)){
                
                //Busca codigo_processo e numero_novo do processo
                $sql4 = "SELECT processo.codigo_processo,
                                processo.numero_novo
                        FROM monitora_termo_processo AS processo
                        WHERE processo.id = $registro->id_processo";
                
                $processo = DB::select($sql4)[0];
                
                $data[$index]->processo = new \stdClass();
                $data[$index]->processo->codigo_processo       = $processo->codigo_processo;
                $data[$index]->processo->numero_novo           = $processo->numero_novo;
                $data[$index]->processo->processos_semelhantes = [];
                $data[$index]->processo->monitoramento         = null;
                
                //Sql para buscar demais dados do processo
                $sql_base = "SELECT 
                            PRC.CODIGO as codigo_processo,
                            PRC.URL_TJ,
                            PRC.GRAU_JURISDICAO, 
                            PRC.NUMERO_PROCESSO_NEW, 
                            PRC.NUMERO_PROCESSO_NEW2, 
                            PRC.CODIGO_DIVISAO,
                            PRC.ID_AREA_JURIDICA,
                            PRC.CODIGO_CLIENTE,
                            PRU.usuario_id,
                            PS.NOME as NOME_CLIENTE, 
                            CL.CLASSE, 
                            CA.CARTORIO, 
                            CO.COMARCA, 
                            CO.UF AS COMARCA_UF,
                            IF ((
                                SELECT ATIVO
                                FROM PRC_SITUACAO
                                WHERE CODIGO = PRC.CODIGO_SITUACAO
                             ) = 'S', 'EM ANDAMENTO', 'ENCERRADO') AS SITUACAO
                            FROM PRC
                            LEFT JOIN PRC_COMARCA CO
                            ON CO.CODIGO = PRC.CODIGO_COMARCA
                            LEFT JOIN PRC_CARTORIO CA
                            ON CA.CODIGO = PRC.CODIGO_CARTORIO
                            LEFT JOIN PRC_CLASSE CL
                            ON CL.CODIGO = PRC.CODIGO_CLASSE
                            LEFT JOIN PESSOA PS
                            ON PS.CODIGO = PRC.CODIGO_CLIENTE
                            LEFT JOIN pessoa_rel_usuarios PRU
                            ON PS.CODIGO = PRU.usuario_id";
                
                //Se tiver "codigo_processo" busca os dados do processo
                if($processo->codigo_processo){
                    $codigo_processo                              = $processo->codigo_processo;
                    $sql_base                                    .= " WHERE PRC.CODIGO = " . $processo->codigo_processo;
                    $processo                                     = DB::select($sql_base)[0];
                    $data[$index]->processo->URL_TJ               = $processo->URL_TJ;
                    $data[$index]->processo->GRAU_JURISDICAO      = $processo->GRAU_JURISDICAO;
                    $data[$index]->processo->NUMERO_PROCESSO_NEW  = $processo->NUMERO_PROCESSO_NEW;
                    $data[$index]->processo->NUMERO_PROCESSO_NEW2 = $processo->NUMERO_PROCESSO_NEW2;
                    $data[$index]->processo->CLASSE               = $processo->CLASSE;
                    $data[$index]->processo->CARTORIO             = $processo->CARTORIO;
                    $data[$index]->processo->COMARCA              = $processo->COMARCA;
                    $data[$index]->processo->COMARCA_UF           = $processo->COMARCA_UF;
                    $data[$index]->processo->SITUACAO             = $processo->SITUACAO;
                    $data[$index]->processo->CODIGO_DIVISAO       = $processo->CODIGO_DIVISAO;
                    $data[$index]->processo->ID_AREA_JURIDICA     = $processo->ID_AREA_JURIDICA;
                    $data[$index]->processo->CODIGO_CLIENTE       = $processo->CODIGO_CLIENTE;
                    $data[$index]->processo->usuario_id           = $processo->usuario_id;
                    $data[$index]->processo->NOME_CLIENTE         = $processo->NOME_CLIENTE;
                    
                    //Vamos verifica se o processo está sendo monitorado
                    $sql5  = "select rel.id,
                                    rel.codigo_processo,
                                    rel.id_monitora_tribunal,
                                    mpt.id_tribunal,
                                    mpt.id_monitoramento,
                                    mpt.numero_cnj,
                                    mpt.frequencia,
                                    mpt.status,
                                    mpt.abrangencia
                             from monitora_processo_tribunal_rel_prc rel
                             join monitora_processo_tribunal mpt
                             on(rel.id_monitora_tribunal = mpt.id)
                             where codigo_processo = {$codigo_processo} 
                             limit 1;";
                    $monitora_processo_tribunal = DB::select($sql5);
                    if(count($monitora_processo_tribunal)){
                        $data[$index]->processo->monitoramento = $monitora_processo_tribunal[0];
                    }
                }else if(is_null($processo->codigo_processo)){
                //Se o "codigo_processo" for vazio iremos buscar no sistema os processos semelhantes com o número do processo
                    $sql_base                                     .= " WHERE NUMERO_PROCESSO_NEW2 LIKE '" . strval(Estrutura::removeFormatacaoNumeroProcesso($processo->numero_novo)) . "%';";    
                    $processos_semelhantes                         = DB::select($sql_base);
                    $data[$index]->processo->processos_semelhantes = $processos_semelhantes;
                }
                
                //Busca envolvidos
                $sql6 = "
                        SELECT id, 
                               nome, 
                               tipo, 
                               pessoa_codigo 
                        FROM monitora_termo_envolvidos AS envolvidos
                        WHERE envolvidos.id_monitora_termo_processo = $registro->id_processo";
                $envolvidos = DB::select($sql6);
                $data[$index]->processo->envolvidos = $envolvidos;
                
            }
        }

        $sql7 = " select count(1) as total from (" .
                str_replace('[COLUMNS]', '1', $baseSelect . $filters1['where']) .
                ") as temp_count";
        
        $sql8 = " select count(1) as total from (" .
        str_replace('[COLUMNS]', '1', $baseSelect) . 
        " join monitora_termo_processo as processo
        on (processo.id = movimentacao.id_processo)" . 
        $filters2['where'] . 
        ") as temp_count";
        
        $sql9 = $sql7 . " union " . $sql8;
            
        // contador
        $counter = DB::select(
            $sql9,
            $filtros
        );
        
        //Se o fitro for pendente
        if($isFiltroPendente){
            $total = 0;
            //Soma os totais de registros obtidos
            foreach ($counter as $index => $item){
                $total += $item->total;
            }
        }else{
            $total = $counter[0]->total;
        }
        
        $totalPublicacoesNovas       = $this->totalPublicacoesNovas();
        $totalPublicacoesPendentes   = $this->totalPublicacoesPendentes();
        $totalPublicacoesDescartados = $this->totalPublicacoesDescartados();
        
        return [
            'total'                         => $total,
            'total_publicacoes_novas'       => $totalPublicacoesNovas,
            'total_publicacoes_pendentes'   => $totalPublicacoesPendentes,
            'total_publicacoes_descartados' => $totalPublicacoesDescartados,
            'pagina'                        => $page,
            'limite'                        => $limit,
            'resultado'                     => $data
        ];
    }
    
    /**
     * Atualiza o campo "pessoa_codigo" em "monitoramento_termo_envolvidos"
     * 
     * @param $id
     * @param $pessoa_codigo
     * 
     * @return bool
     */
    public function atualizaEnvolvido($pessoa_codigo, $nome_envolvido, $tipo_envolvido){
        $sql = "UPDATE monitora_termo_envolvidos
        SET pessoa_codigo = $pessoa_codigo
        WHERE nome = '$nome_envolvido' and tipo = '$tipo_envolvido'";
        $result = DB::update($sql); 
        if($result){
            return true;
        }else{
            return false;
        }
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
                WHERE nome = "' . $nome_envolvido . '" and tipo = "' . $tipo_envolvido . '"
                limit 1;';
        $result = DB::select($sql); 
        if(count($result) == 1){
            $result[0]->pessoa_codigo;
        }else{
            return null;
        }
    }


    /**
     * Busca o total de novas publicações 
     * 
     * @return int
     */
    public function totalPublicacoesNovas(){
        $sql = "SELECT count(id) as total 
                FROM monitora_termo_movimentacao
                WHERE lido = 'N';";
        return DB::select($sql)[0]->total; 
    }
    
    /**
     * Busca o total de Pendentes
     * 
     * @return int
     */
    public function totalPublicacoesPendentes(){
        //Seleciona todas as citações não descartadas
        $sql = "SELECT count(id) as total
                FROM monitora_termo_movimentacao
                WHERE id_processo IS NULL 
                AND descartada = 'N';";
        
        $result1 = DB::select($sql)[0]->total; 
        
        //Seleciona todas as publicações e processos que não estão relacionadas a um processo
        $sql = "SELECT count(monitora_termo_processo.id) as total
                FROM monitora_termo_processo
                JOIN  monitora_termo_movimentacao
                ON  (monitora_termo_movimentacao.id_processo = monitora_termo_processo.id)
                WHERE codigo_processo IS NULL
                AND monitora_termo_movimentacao.descartada = 'N'";
        
        $result2 = DB::select($sql)[0]->total; 
        
        return $result1 + $result2;
    }
    
    /**
     * Busca o total de descartados
     * 
     * @return int
     */
    public function totalPublicacoesDescartados(){
        $sql = "SELECT count(id) as total 
                FROM monitora_termo_movimentacao
                WHERE descartada = 'S';";
        return DB::select($sql)[0]->total; 
    }
    
    /**
     * Descarta publicação
     */
    public function descartarPublicacao($id){
        $sql     = "UPDATE monitora_termo_movimentacao SET descartada = 'S' WHERE id = {$id}; ";
        $retorno = DB::update($sql);
        return $retorno;
    }
    
}