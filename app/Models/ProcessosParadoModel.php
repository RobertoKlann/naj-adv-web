<?php

namespace App\Models;

use Auth;
use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo de processos parados.
 *
 * @since 2021-05-18
 */
class ProcessosParadoModel extends NajModel {

    protected $codigoCliente;

    protected function loadTable() {
        $this->setTable('prc');
		$this->codigoCliente = 1;

        $this->addColumn('CODIGO', true);
		$this->addColumn('CODIGO_ADV_CLIENTE');
		$this->addColumn('ID_ORGAO');
		$this->addColumn('NUMERO_PROCESSO_NEW2');
		$this->addColumn('GRAU_JURISDICAO');
		$this->addColumn('CODIGO_CLASSE');
		$this->addColumn('CODIGO_CARTORIO');
		$this->addColumn('ID_AREA_JURIDICA');
		$this->addColumn('CODIGO_COMARCA');
		$this->addColumn('PEDIDOS_PROCESSO');
		$this->addColumn('CODIGO_DIVISAO');
		$this->addColumn('CODIGO_SITUACAO');

		$this->setOrder('COLUNA_ORDER_BY DESC, SITUACAO, DATA_CADASTRO, CODIGO', 'ASC', false);
      
        $this->addAllColumns();

		//Adicionando os Filtros
		if(request()->get('status')) {
			$divisoes = implode(', ', $this->getCodigosDivisao());

			if($divisoes) {
				$this->addRawFilter("prc.codigo_divisao IN ({$divisoes})");
			} else {
				$this->addRawFilter("prc.codigo_divisao IN (-1)");
			}

			$status = request()->get('status');
			$period = request()->get('period');
			
			if($status != 'All')
				$this->addRawFilter("PRC_SITUACAO.ATIVO = '{$status}'");

			$date = date('Y-m-d', strtotime(date('Y-m-d') . " -{$period} month"));

			$having = " COLUNA_ORDER_BY <= '{$date}' ";

			$this->setRawHaving($having);
		}

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
			 LEFT JOIN PRC_SITUACAO
			        ON PRC_SITUACAO.CODIGO = PRC.CODIGO_SITUACAO
		");
   	}

	public function addAllColumns() {
		$sqlGreatest = "";

		$withoutIntimacao = request()->get('withoutIntimacao');
		$withoutProgress = request()->get('withoutProgress');
		$withoutActivits = request()->get('withoutActivits');

		// puta codigo feio esse aqui mas foi o jeito mais simples que pensei para fazer essa consulta com esse filtro de data do caralho
		if (
			($withoutIntimacao == 'SIM' || $withoutIntimacao == 'true')
			&&
			($withoutProgress == 'SIM' || $withoutProgress == 'true')
			&&
			($withoutActivits == 'SIM' || $withoutActivits == 'true')) {
			$sqlGreatest = "
				select GREATEST(
					IFNULL(ULTIMA_ATIVIDADE_DATA, '0001-01-01'), 
					IFNULL(ULTIMO_ANDAMENTO_DATA, '0001-01-01'),
					IFNULL(ULTIMA_INTIMACAO_DATA, '0001-01-01')
		        )
			";
		} else {
			if (!$this->valueIsTrue($withoutIntimacao) && !$this->valueIsTrue($withoutProgress)) {
				$sqlGreatest = "
					SELECT IFNULL(ULTIMA_ATIVIDADE_DATA, '0001-01-01')
				";
			} else if (!$this->valueIsTrue($withoutActivits) && !$this->valueIsTrue($withoutIntimacao)) {
				$sqlGreatest = "
					SELECT IFNULL(ULTIMO_ANDAMENTO_DATA, '0001-01-01')
				";
			} else if (!$this->valueIsTrue($withoutActivits) && !$this->valueIsTrue($withoutProgress)) {
				$sqlGreatest = "
					SELECT IFNULL(ULTIMA_INTIMACAO_DATA, '0001-01-01')
				";
			} else {
				$columnGreatest = "";

				if ($this->valueIsTrue($withoutActivits)) {
					$columnGreatest .= "
						IFNULL(ULTIMA_ATIVIDADE_DATA, '0001-01-01'),
					";
				}

				if ($this->valueIsTrue($withoutProgress)) {
					if (!$this->valueIsTrue($withoutIntimacao)) {
						$columnGreatest .= "
							IFNULL(ULTIMO_ANDAMENTO_DATA, '0001-01-01')
						";
					} else {
						$columnGreatest .= "
							IFNULL(ULTIMO_ANDAMENTO_DATA, '0001-01-01'),
						";
					}
				}

				if ($this->valueIsTrue($withoutIntimacao)) {
					$columnGreatest .= "
						IFNULL(ULTIMA_INTIMACAO_DATA, '0001-01-01')
					";
				}

				$sqlGreatest = "
					select GREATEST(
						{$columnGreatest}
					)
				";
			}
		}

		$this->addRawColumn("PRC.CODIGO AS CODIGO_PROCESSO")
			->addRawColumn("PRC.CODIGO_CLIENTE AS CODIGO_CLIENTE")
			->addRawColumn("IF ((
				SELECT ATIVO
				FROM PRC_SITUACAO
				WHERE CODIGO = PRC.CODIGO_SITUACAO
				) = 'S', 'EM ANDAMENTO', 'ENCERRADO') AS SITUACAO")
			->addRawColumn("P1.NOME AS NOME_CLIENTE")			
			->addRawColumn("P1.CODIGO AS PESSOA_CODIGO_CLIENTE")
			->addRawColumn("PRC.QUALIFICA_CLIENTE")
			->addRawColumn("(
				SELECT COUNT(0)
				FROM PRC_GRUPO_CLIENTE PGC
				WHERE PGC.CODIGO_PROCESSO = PRC.CODIGO
			) AS QTDE_CLIENTES")
			->addRawColumn("P2.NOME AS NOME_ADVERSARIO")
			->addRawColumn("P2.CODIGO AS CODIGO_ADVERSARIO")
			->addRawColumn("PRC.QUALIFICA_ADVERSARIO")
			->addRawColumn("(
				SELECT COUNT(0)
				FROM PRC_GRUPO_ADVERSARIO PGA
				WHERE PGA.CODIGO_PROCESSO = PRC.CODIGO
			) AS QTDE_ADVERSARIOS")
			->addRawColumn("P3.NOME AS NOME_RESPONSAVEL")
			->addRawColumn("P3.CODIGO AS CODIGO_RESPONSAVEL")
			->addRawColumn("P4.NOME AS NOME_ADVOGADO")
			->addRawColumn("P4.CODIGO AS CODIGO_ADVOGADO")
			->addRawColumn("(
				SELECT DESCRICAO_ANDAMENTO
				FROM PRC_MOVIMENTO
				WHERE CODIGO_PROCESSO = PRC.CODIGO
				ORDER BY DATA DESC, ID DESC
				LIMIT 1
			) AS ULTIMO_ANDAMENTO_DESCRICAO")
			->addRawColumn("(
				SELECT data
				FROM PRC_MOVIMENTO
				WHERE CODIGO_PROCESSO = PRC.CODIGO
				ORDER BY DATA DESC, ID DESC
				LIMIT 1
			) AS ULTIMO_ANDAMENTO_DATA")
			->addRawColumn("(
				SELECT HISTORICO
				FROM ATIVIDADE
				WHERE CODIGO_PROCESSO = PRC.CODIGO
				ORDER BY DATA DESC, CODIGO DESC
				LIMIT 1
			) AS ULTIMA_ATIVIDADE_DESCRICAO")
			->addRawColumn("(
				SELECT DATA
				FROM ATIVIDADE
				WHERE CODIGO_PROCESSO = PRC.CODIGO
				ORDER BY DATA DESC, CODIGO DESC
				LIMIT 1
			) AS ULTIMA_ATIVIDADE_DATA")
			->addRawColumn("(
				SELECT DESCRICAO
				FROM PRC_INTIMACAO
				WHERE CODIGO_PROCESSO = PRC.CODIGO
				ORDER BY DATA DESC, PRC.CODIGO DESC
				LIMIT 1
			) AS ULTIMA_INTIMACAO_DESCRICAO")
			->addRawColumn("(
				SELECT DATA
				FROM PRC_INTIMACAO
				WHERE CODIGO_PROCESSO = PRC.CODIGO
				ORDER BY DATA DESC, PRC.CODIGO DESC
				LIMIT 1
			) AS ULTIMA_INTIMACAO_DATA")
			->addRawColumn("PRC.NUMERO_PROCESSO_NEW")
			->addRawColumn("PRC.NUMERO_PROCESSO")
			->addRawColumn("CL.CLASSE")
			->addRawColumn("CA.CARTORIO")
			->addRawColumn("CO.COMARCA")
			->addRawColumn("CO.UF AS COMARCA_UF")
			->addRawColumn("DATE_FORMAT(PRC.DATA_CADASTRO,'%d/%m/%Y') AS DATA_CADASTRO")
			->addRawColumn("DATE_FORMAT(PRC.DATA_DISTRIBUICAO,'%d/%m/%Y') AS DATA_DISTRIBUICAO")
			->addRawColumn("
				(
					{$sqlGreatest}
				) as COLUNA_ORDER_BY
			")
			->addRawColumn("PRC.VALOR_CAUSA");
	}

	private function getCodigosDivisao() {
		$codigoPessoa = (new PessoaRelacionamentoUsuarioModel)->hasRelacionamentoToUser(request()->get('usuario_id'));

		$divisao = DB::select("
			SELECT *
			  FROM USUARIO_PERMISSAO
	         WHERE MODULO = 'F_Rlt_Processo_Parados'
	           AND CODIGO_PESSOA = {$codigoPessoa[0]->pessoa_codigo} #pessoa_rel_usuarios.codigo_pessoa da relação pessoa_rel_usuarios.usuario_id=id do usuário logado
			   AND (ACESSAR = 'S' OR PESQUISAR = 'S')
	      ORDER BY CODIGO_DIVISAO
		");

		$data = [];

		foreach ($divisao as $div)
			$data[$div->CODIGO_DIVISAO] = $div->CODIGO_DIVISAO;

		return $data;
	}

	private function valueIsTrue($value) {
		if (!$value || $value == 'NAO' || $value == 'false')
			return false;

		return true;
	}

}