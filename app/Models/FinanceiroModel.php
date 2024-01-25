<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo do financeiro.
 *
 * @since 2020-08-10
 */
class FinanceiroModel extends NajModel {

    protected function loadTable() {
        $codigoCliente = implode(',', $this->getRelacionamentoClientes());

        $this->setTable('conta');
        $this->addColumn('CODIGO', true)->setHidden();
        $this->setOrder('CP.DATA_VENCIMENTO, CP.CODIGO_CONTA, CP.PARCELA');
        $this->addAllColumns();
        $this->addRawFilter("CP.SITUACAO IN('A','P')");
        $this->addRawFilter("CONTA.CODIGO_PESSOA IN ({$codigoCliente})");
        $this->setRawBaseSelect("
                SELECT [COLUMNS]
                  FROM CONTA
            INNER JOIN CONTA_PARCELA CP
                    ON CP.CODIGO_CONTA = CONTA.CODIGO
             LEFT JOIN PRC PC
                    ON PC.CODIGO = CONTA.CODIGO_PROCESSO
             LEFT JOIN PESSOA P1
                    ON P1.CODIGO = CONTA.CODIGO_PESSOA
             LEFT JOIN PESSOA P2
                    ON P2.CODIGO = CONTA.CODIGO_ADVERSARIO
             LEFT JOIN PESSOA P3
                    ON P3.CODIGO = PC.CODIGO_ADVERSARIO
        ");
    }

    public function getRelacionamentoClientes() {
        return [1, 2, 3];
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

}