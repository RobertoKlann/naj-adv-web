<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;
use App\Models\PessoaRelacionamentoUsuarioModel;

/**
 * Modelo de financeiro (aplicativo)
 *
 * @since 2020-04-27
 */
class AppFinanceiroModel extends NajModel {

    const TYPE_FILTER_TO_PAY = 'to_pay';
    const TYPE_FILTER_TO_RECEIVE = 'to_receive';

    private $typeFilter = false;

    protected function loadTable() {
        //$codigoCliente = implode(',', $this->getRelacionamentoClientes());

        $this->setTable('conta');
        $this->addColumn('CODIGO', true)->setHidden();
        $this->setOrder('CP.SITUACAO, CP.DATA_VENCIMENTO, CP.CODIGO_CONTA, CP.PARCELA ASC');
        $this->addAllColumns();
        $this->addRawFilter("CP.SITUACAO IN('A','P')");
        //$this->addRawFilter("CONTA.CODIGO_PESSOA IN ({$codigoCliente})");
        $this->setRawBaseSelect("
                SELECT [COLUMNS]
                  FROM CONTA
            INNER JOIN CONTA_PARCELA CP
                    ON CP.CODIGO_CONTA = CONTA.CODIGO
            INNER JOIN NATUREZA_FINANCEIRA N 
                    ON N.CODIGO = CONTA.CODIGO_NATUREZA
             LEFT JOIN PRC PC
                    ON PC.CODIGO = CONTA.CODIGO_PROCESSO
             LEFT JOIN PESSOA P1
                    ON P1.CODIGO = CONTA.CODIGO_PESSOA
             LEFT JOIN PESSOA P2
                    ON P2.CODIGO = CONTA.CODIGO_ADVERSARIO
             LEFT JOIN PESSOA P3
                    ON P3.CODIGO = PC.CODIGO_ADVERSARIO
             LEFT JOIN PRC_CLASSE CL 
                    ON CL.CODIGO = PC.CODIGO_CLASSE
             LEFT JOIN PRC_CARTORIO CA 
                    ON CA.CODIGO = PC.CODIGO_CARTORIO
             LEFT JOIN PRC_COMARCA CO 
                    ON CO.CODIGO = PC.CODIGO_COMARCA
        ");
    }

    public function setToPay() {
        $this->typeFilter = self::TYPE_FILTER_TO_PAY;
    }

    public function setToReceive() {
        $this->typeFilter = self::TYPE_FILTER_TO_RECEIVE;
    }

    protected function handleCustomFilter($flt) {
        $filter = '';

        switch (strtolower($flt->col)) {
            case '':

                break;
            default:
                $this->throwException('Filtro customizado não tratado');
                break;
        }

        return [
            $filter,
            false,
        ];
    }

    /*public function getRelacionamentoClientes() {
        return [1, 2, 3];
    }*/

    public function getRelacionamentoClientes($userId) {
        $relations = (new PessoaRelacionamentoUsuarioModel)
            ->getRelationsUserApp($userId);

        $codigos = [];

        foreach ($relations as $relation) {
            switch ($this->typeFilter) {
                case self::TYPE_FILTER_TO_PAY:
                    if ($relation->contas_pagar == 'S') {
                        $codigos[] = $relation->pessoa_codigo;
                    }
                break;
                case self::TYPE_FILTER_TO_RECEIVE:
                    if ($relation->contas_receber == 'S') {
                        $codigos[] = $relation->pessoa_codigo;
                    }
                break;
                default:
                    $codigos[] = $relation->pessoa_codigo;
                break;
            }
        }

        if (empty($codigos)) {
            return '-1';
        }

        return implode(',', array_unique($codigos));
    }

    public function addAllColumns() {
        $this->addRawColumn("CONTA.CODIGO AS CODIGO_CONTA")
            ->addRawColumn("CONTA.TIPO AS TIPO_CONTA")
            ->addRawColumn("CP.ID AS ID_PARCELA")
            ->addRawColumn("CP.SITUACAO")
            ->addRawColumn("CP.PARCELA AS PARCELA_ATUAL")
            ->addRawColumn("(
                SELECT COUNT(0)
                  FROM CONTA_PARCELA
                 WHERE CODIGO_CONTA = CONTA.CODIGO
            ) AS PARCELA_TOTAL")
            ->addRawColumn("DATE_FORMAT(CP.DATA_VENCIMENTO, '%d/%m/%Y') AS DATA_VENCIMENTO")
            ->addRawColumn("DATE_FORMAT(CP.DATA_PAGAMENTO, '%d/%m/%Y') AS DATA_PAGAMENTO")
            ->addRawColumn("IF (
                CP.VALOR_PARCIAL > 0,
                CP.VALOR_PARCELA - CP.VALOR_PARCIAL,
                CP.VALOR_PARCELA
            ) AS VALOR_PARCELA")
            ->addRawColumn("IF (
                (
                    SELECT SUM(VALOR_PAGAMENTO)
                      FROM CONTA_PARCELA_PARCIAL
                     WHERE ID_PARCELA = CP.ID
                ) > 0, 'SIM', 'NÃO'
            ) AS PAGAMENTOS_PARCIAIS")
            ->addRawColumn("IF (
                CP.DATA_PAGAMENTO IS NOT NULL,
                CP.VALOR_PAGAMENTO, (
                    SELECT SUM(VALOR_PAGAMENTO)
                      FROM CONTA_PARCELA_PARCIAL
                     WHERE ID_PARCELA = CP.ID
                )
            ) AS VALOR_PAGAMENTO")
            ->addRawColumn("P1.NOME AS NOME_CLIENTE")
            ->addRawColumn("IF (
                CONTA.CODIGO_ADVERSARIO IS NOT NULL,
                P2.NOME,
                P3.NOME
            ) AS NOME_ADVERSARIO")
            ->addRawColumn("CONTA.DESCRICAO");
    }

    /**
     * a pagar
     */
    public function getToPayValue($userId) {
        $codigoCliente = $this->getRelacionamentoClientes($userId);

        $sqlFinalizado = "
            SELECT (
                        sum(VALOR_PARCIAL) +
                        sum(VALOR_PAGAMENTO)
                    ) AS TOTAL_PAGO
              FROM CONTA C
             INNER JOIN CONTA_PARCELA CP
                   ON CP.CODIGO_CONTA = C.CODIGO
             INNER JOIN NATUREZA_FINANCEIRA N
                   ON N.CODIGO = C.CODIGO_NATUREZA
             WHERE CP.SITUACAO IN('A','P')
               AND C.DISPONIVEL_CLIENTE = 'S'
               AND C.CODIGO_PESSOA IN ({$codigoCliente})
               AND (N.TIPO_SUB NOT IN('M','J','C') OR N.TIPO_SUB IS NULL)
               AND C.TIPO='R' AND (C.PAGADOR='1' or C.PAGADOR is null)";

        $sqlAberto = "
          SELECT IF(sum(VALOR_PARCELA-VALOR_PARCIAL) IS NULL,0.00,sum(VALOR_PARCELA-VALOR_PARCIAL)) AS TOTAL_EM_ABERTO
            FROM CONTA C
           INNER JOIN CONTA_PARCELA CP
                 ON CP.CODIGO_CONTA = C.CODIGO
           INNER JOIN NATUREZA_FINANCEIRA N
                 ON N.CODIGO = C.CODIGO_NATUREZA
           WHERE CP.SITUACAO IN('A','P')
             AND C.CODIGO_PESSOA IN ({$codigoCliente})
             AND situacao = 'A'
             AND C.DISPONIVEL_CLIENTE = 'S'
             AND (N.TIPO_SUB NOT IN('M','J','C') OR N.TIPO_SUB IS NULL)
             AND C.TIPO='R' AND (C.PAGADOR='1' or C.PAGADOR is null)";

        $resultFinalizado = DB::select($sqlFinalizado);
        $resultAberto = DB::select($sqlAberto);

        $aberto = $resultAberto[0]->TOTAL_EM_ABERTO ?? 0;
        $finalizado = $resultFinalizado[0]->TOTAL_PAGO ?? 0;

        return [
            'aberto'     => $aberto + 0,
            'finalizado' => $finalizado + 0,
            'codigo' => $codigoCliente,
        ];
    }

    /**
     * a receber
     */
    public function getToReceiveValue($userId) {
        $codigoCliente = $this->getRelacionamentoClientes($userId);

        $sqlFinalizado = "
            SELECT (
                        sum(VALOR_PARCIAL) +
                        sum(VALOR_PAGAMENTO)
                    ) AS TOTAL_RECEBIDO
              FROM CONTA C
        INNER JOIN CONTA_PARCELA CP
                ON CP.CODIGO_CONTA = C.CODIGO
        INNER JOIN NATUREZA_FINANCEIRA N
                ON N.CODIGO = C.CODIGO_NATUREZA
            WHERE CP.SITUACAO IN('A', 'P')
              AND C.DISPONIVEL_CLIENTE = 'S'
              AND C.CODIGO_PESSOA IN ({$codigoCliente})
              AND (N.TIPO_SUB NOT IN('M','J','C') OR N.TIPO_SUB IS NULL)
              AND ((C.TIPO='R' AND C.PAGADOR='2') OR C.TIPO='P')";

        $sqlAberto = "
              SELECT IF(sum(VALOR_PARCELA-VALOR_PARCIAL) IS NULL, 0.00, sum(VALOR_PARCELA-VALOR_PARCIAL)) AS TOTAL_EM_ABERTO
                FROM CONTA C
          INNER JOIN CONTA_PARCELA CP
                  ON CP.CODIGO_CONTA = C.CODIGO
          INNER JOIN NATUREZA_FINANCEIRA N
                  ON N.CODIGO = C.CODIGO_NATUREZA
              WHERE CP.SITUACAO IN('A', 'P')
                AND C.CODIGO_PESSOA IN ({$codigoCliente})
                AND situacao = 'A'
                AND C.DISPONIVEL_CLIENTE = 'S'
                AND (N.TIPO_SUB NOT IN('M','J','C') OR N.TIPO_SUB IS NULL)
                AND ((C.TIPO='R' AND C.PAGADOR='2') OR C.TIPO='P')";

        $resultFinalizado = DB::select($sqlFinalizado);
        $resultAberto = DB::select($sqlAberto);

        $aberto = $resultAberto[0]->TOTAL_EM_ABERTO ?? 0;
        $finalizado = $resultFinalizado[0]->TOTAL_RECEBIDO ?? 0;

        return [
            'aberto'     => $aberto + 0,
            'finalizado' => $finalizado + 0,
            'codigo' => $codigoCliente,
        ];
    }

}
