<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;
use App\Models\PessoaRelacionamentoUsuarioModel;

/**
 * Modelo de processos (aplicativo)
 *
 * @since 2020-04-14
 */
class AppProcessoModel extends NajModel {

    private $codigoCliente = null;
    private $hasSetRelClienteRawFilter = false;

    protected function loadTable() {
        $this->setTable('prc');
        $this->addColumn('CODIGO', true)->setHidden();
        $this->setOrder('PRC.DATA_CADASTRO');
        $this->addAllColumns();
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
        ");
    }

    public function getNewBaseSelect($filters, $isCount = false) {
        /*$dataOrderBy = ",IF (p.ULTIMA_ATIVIDADE_DATA_ORIGEM > p.ULTIMO_ANDAMENTO_DATA_ORIGEM,
                             p.ULTIMA_ATIVIDADE_DATA_ORIGEM,
                         IF (p.ULTIMO_ANDAMENTO_DATA_ORIGEM > p.DATA_CADASTRO_ORIGEM,
                             p.ULTIMO_ANDAMENTO_DATA_ORIGEM, p.DATA_CADASTRO_ORIGEM)
                        ) AS DATA_ORDER_BY";*/
        $dataOrderBy = ",if(p.ULTIMA_ATIVIDADE_DATA_ORIGEM<>'0001-01-01 00:00:00'
                    or p.ULTIMO_ANDAMENTO_DATA_ORIGEM<>'0001-01-01 00:00:00',
				if(p.ULTIMA_ATIVIDADE_DATA_ORIGEM>p.ULTIMO_ANDAMENTO_DATA_ORIGEM,
					p.ULTIMA_ATIVIDADE_DATA_ORIGEM,
					p.ULTIMO_ANDAMENTO_DATA_ORIGEM
				),
				DATE_SUB(p.DATA_CADASTRO_ORIGEM,INTERVAL 100 year)
				
			) as DATA_ORDER_BY
        ";

        $orderBy = "ORDER BY SITUACAO, DATA_ORDER_BY DESC";

        if ($isCount) {
            $orderBy = "";
            $dataOrderBy = "";
        }

        return "
            select p.*{$dataOrderBy}
            from(
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
                    {$filters["where"]}
                 ) AS p {$orderBy}";
    }

    public function makePagination() {
        $limit = $this->itemsPerPage;

        $pageQuery = request()->query('page');

        $page = $pageQuery > 0 ? (integer) $pageQuery : 1;

        $offset = ($page * $limit) - $limit;

        $queryFilters =  request()->query('f');

        if ($queryFilters) {
            $queryFilters = $this->parseQueryFilter($queryFilters);
        }

        $filters = $this->resolveFilters($queryFilters);
        $baseSelect = $this->getNewBaseSelect($filters);

        // registros
        $withColumns = $this->fillWithColumns($baseSelect);
        $selectForPagination = "{$withColumns} LIMIT {$limit} OFFSET {$offset}";
        $data = DB::select($selectForPagination, $filters['values']);

        // contador
        $withColumn = str_replace('[COLUMNS]', '1', $this->getNewBaseSelect($filters, true));
        $selectForCount = "select count(1) as total from ({$withColumn}) as temp_count";
        $counter = DB::select($selectForCount, $filters['values']);

        $dev = null;

        if ($this->withSql || request()->query('withSql') == '1') {
            $dev = [
                'sql'    => $selectForPagination,
                'values' => $filters['values'],
            ];
        }

        return [
            'total'     => $counter[0]->total,
            'pagina'    => $page,
            'limite'    => $limit,
            'resultado' => $data,
            'dev'       => $dev
        ];
    }

    public function getRelacionamentoClientes($userId) {
        $relations = (new PessoaRelacionamentoUsuarioModel)
            ->getRelationsUserApp($userId);

        $codigos = [];

        foreach ($relations as $relation) {
            if ($relation->processos == 'S') {
                $codigos[] = $relation->pessoa_codigo;
            }
        }

        if (empty($codigos)) {
            $codigos = ['-1'];
        }

        $codigoCliente = implode(',', array_unique($codigos));
        $this->codigoCliente = $codigoCliente;

        return $codigoCliente;
    }

    private function setRelacionamentoClienteRawFilter($userId) {
        if (is_null($this->codigoCliente)) {
            $this->getRelacionamentoClientes($userId);
        }

        if ($this->hasSetRelClienteRawFilter) {
            return;
        }

        $this->hasSetRelClienteRawFilter = true;

        $this->addRawFilter("(PRC.CODIGO_CLIENTE IN ({$this->codigoCliente})
            OR PRC.CODIGO IN (
                SELECT CODIGO_PROCESSO
                  FROM PRC_GRUPO_CLIENTE
                 WHERE CODIGO_CLIENTE IN ({$this->codigoCliente})
        ))");
    }

    public function getTotalProcess($userId) {
        $this->setRelacionamentoClienteRawFilter($userId);

        return $this->countAll();
    }

    public function addAllColumns() {
        $this->addRawColumn("PRC.CODIGO AS CODIGO_PROCESSO")
            ->addRawColumn("IF ((
                SELECT ATIVO
                  FROM PRC_SITUACAO
                 WHERE CODIGO = PRC.CODIGO_SITUACAO
             ) = 'S', 'EM ANDAMENTO', 'ENCERRADO') AS SITUACAO")
            ->addRawColumn("P1.NOME AS NOME_CLIENTE")
            ->addRawColumn("(
               SELECT COUNT(0)
                 FROM PRC_GRUPO_CLIENTE PGC
                WHERE PGC.CODIGO_PROCESSO = PRC.CODIGO
            ) AS QTDE_CLIENTES")
            ->addRawColumn("P2.NOME AS NOME_ADVERSARIO")
            ->addRawColumn("(
               SELECT COUNT(0)
                 FROM PRC_GRUPO_ADVERSARIO PGA
                WHERE PGA.CODIGO_PROCESSO = PRC.CODIGO
            ) AS QTDE_ADVERSARIOS")
            ->addRawColumn("P3.NOME AS NOME_RESPONSAVEL")
            ->addRawColumn("P4.NOME AS NOME_ADVOGADO")
            ->addRawColumn("(
               SELECT DESCRICAO_ANDAMENTO
                 FROM PRC_MOVIMENTO
                WHERE CODIGO_PROCESSO = PRC.CODIGO
             ORDER BY DATA DESC, ID DESC
                LIMIT 1
            ) AS ULTIMO_ANDAMENTO_DESCRICAO")
            ->addRawColumn("IFNULL((
               SELECT DATA
                 FROM PRC_MOVIMENTO
			       WHERE CODIGO_PROCESSO = PRC.CODIGO
			    ORDER BY DATA DESC LIMIT 1
			   ),'0001-01-01 00:00:00'
	        ) AS ULTIMO_ANDAMENTO_DATA_ORIGEM")
            ->addRawColumn("(
                SELECT DATE_FORMAT(DATA, '%d/%m/%Y')
                  FROM PRC_MOVIMENTO
                 WHERE CODIGO_PROCESSO = PRC.CODIGO
              ORDER BY DATA DESC LIMIT 1
            ) as ULTIMO_ANDAMENTO_DATA")
            ->addRawColumn("(
               SELECT HISTORICO
                 FROM ATIVIDADE
                WHERE CODIGO_PROCESSO = PRC.CODIGO
                  AND ATIVIDADE.ENVIAR = 'S'
             ORDER BY DATA DESC, CODIGO DESC
                LIMIT 1
            ) AS ULTIMA_ATIVIDADE_DESCRICAO")
            ->addRawColumn("IFNULL((
               SELECT DATA
                 FROM ATIVIDADE
                WHERE CODIGO_PROCESSO = PRC.CODIGO
                  AND ATIVIDADE.ENVIAR = 'S'
				 ORDER BY DATA DESC LIMIT 1
			   ), '0001-01-01 00:00:00'
	        ) AS ULTIMA_ATIVIDADE_DATA_ORIGEM")
            ->addRawColumn("(
                SELECT DATE_FORMAT(DATA, '%d/%m/%Y')
                  FROM ATIVIDADE
                 WHERE CODIGO_PROCESSO = PRC.CODIGO
                   AND ATIVIDADE.ENVIAR = 'S'
              ORDER BY DATA DESC LIMIT 1
            ) as ULTIMA_ATIVIDADE_DATA")
            ->addRawColumn("PRC.NUMERO_PROCESSO_NEW")
            ->addRawColumn("PRC.NUMERO_PROCESSO")
            ->addRawColumn("CL.CLASSE")
            ->addRawColumn("CA.CARTORIO")
            ->addRawColumn("CO.COMARCA")
            ->addRawColumn("CO.UF AS COMARCA_UF")
            ->addRawColumn("PRC.DATA_CADASTRO AS DATA_CADASTRO_ORIGEM")
            ->addRawColumn("DATE_FORMAT(PRC.DATA_CADASTRO,'%d/%m/%Y') AS DATA_CADASTRO")
            ->addRawColumn("DATE_FORMAT(PRC.DATA_DISTRIBUICAO,'%d/%m/%Y') AS DATA_DISTRIBUICAO")
            ->addRawColumn("PRC.VALOR_CAUSA")
            ->addRawColumn("
                (
                    SELECT count(*)
                      FROM prc_anexos
                     WHERE TRUE
                       AND prc_anexos.codigo_processo = prc.codigo
                       AND prc_anexos.descricao <> 'DIR'
                       AND prc_anexos.servicos_cliente = 'S'
                ) as QTD_ANEXOS
            ");
    }

    public function getPartes($key) {
        $key = $this->parseQueryFilter($key);

        $sql = "(
           SELECT P1.NOME,
                  PGC.QUALIFICACAO
             FROM PRC_GRUPO_CLIENTE PGC
             JOIN PESSOA P1
               ON P1.CODIGO = PGC.CODIGO_CLIENTE
            WHERE PGC.CODIGO_PROCESSO = ?
         ORDER BY P1.NOME
        ) UNION (
           SELECT P1.NOME,
                  PGA.QUALIFICACAO
             FROM PRC_GRUPO_ADVERSARIO PGA
             JOIN PESSOA P1
               ON P1.CODIGO = PGA.CODIGO_ADVERSARIO
            WHERE PGA.CODIGO_PROCESSO = ?
         ORDER BY P1.NOME
        )";

        $result = DB::select($sql, [$key->CODIGO, $key->CODIGO]);

        return $result;
    }

    public function getTotalProcess30Days($userId) {
        $this->setRelacionamentoClienteRawFilter($userId);
        $codigoCliente = $this->codigoCliente;

        $initialDate = date('Y-m-d', strtotime('-30 days'));

        $sql = "
        	SELECT SUM(
                        (
						  SELECT COUNT(0)
                            FROM PRC PC
					  INNER JOIN PRC_MOVIMENTO PM
					          ON PM.CODIGO_PROCESSO = PC.CODIGO
                           WHERE (
							       PC.CODIGO_CLIENTE IN({$codigoCliente})
								   OR
								   PC.CODIGO IN (
												  SELECT CODIGO_PROCESSO
												    FROM PRC_GRUPO_CLIENTE
                                                   WHERE CODIGO_CLIENTE IN({$codigoCliente})
                                                )
                                 )
                             AND DATA >= '{$initialDate}' #MAIOR QUE HOJE - 30 DIAS
                        )
            
			+ 
			
			   (
				SELECT COUNT(0)
				  FROM PRC PC
			INNER JOIN ATIVIDADE AT
					ON AT.CODIGO_PROCESSO = PC.CODIGO
				 WHERE (
				 		 PC.CODIGO_CLIENTE IN({$codigoCliente})
				 		 OR
				 		 PC.CODIGO IN ( 
										SELECT CODIGO_PROCESSO
										  FROM PRC_GRUPO_CLIENTE
										 WHERE CODIGO_CLIENTE IN({$codigoCliente})
									  )
						)
				   AND AT. ENVIAR='S'
				   AND AT.DATA >= '{$initialDate}'# MAIOR QUE HOJE - 30 DIAS
			    )
			) AS total
       ";

       $result = DB::select($sql);

       return [
           'trinta_dias' => $result[0]->total + 0,
           'data_incial' => $initialDate,
           'codigo'      => $codigoCliente,
       ];
    }

}
