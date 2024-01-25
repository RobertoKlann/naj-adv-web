<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;
use App\Models\PessoaRelacionamentoUsuarioModel;

/**
 * Model de atividades do app
 */
class AppAtividadeModel extends NajModel {

    private $codigoCliente = null;
    private $hasSetRelClienteRawFilter = false;

    protected function loadTable() {
        $this->setTable('atividade');
        $this->addColumn('CODIGO', true)->setHidden();
        $this->setOrder('A.DATA DESC');
        $this->primaryKey = 'CODIGO';
        $this->addAllColumns();
        $this->setRawBaseSelect("
                SELECT [COLUMNS]
                  FROM ATIVIDADE A
            INNER JOIN PESSOA P1
                    ON P1.CODIGO = A.CODIGO_USUARIO
             LEFT JOIN PRC PC
                    ON PC.CODIGO = A.CODIGO_PROCESSO
             LEFT JOIN PRC_COMARCA CO
                    ON CO.CODIGO = PC.CODIGO_COMARCA
             LEFT JOIN PRC_CARTORIO CA
                    ON CA.CODIGO = PC.CODIGO_CARTORIO
             LEFT JOIN PRC_CLASSE CL
                    ON CL.CODIGO = PC.CODIGO_CLASSE
             LEFT JOIN PESSOA P2
                    ON P2.CODIGO = PC.CODIGO_CLIENTE
             LEFT JOIN PESSOA P3
                    ON P3.CODIGO = PC.CODIGO_ADVERSARIO
        ");
    }

    public function addAllColumns() {
        $this->addRawColumn("A.CODIGO")
            ->addRawColumn("A.CODIGO_CLIENTE")
            ->addRawColumn("P1.NOME AS NOME_USUARIO")
            ->addRawColumn("P2.NOME AS NOME_CLIENTE")
            ->addRawColumn("P3.NOME AS NOME_ADVERSARIO")
            ->addRawColumn("PC.QUALIFICA_CLIENTE")
            ->addRawColumn("PC.QUALIFICA_ADVERSARIO")
            ->addRawColumn("A.DATA")
            ->addRawColumn("A.TEMPO")
            ->addRawColumn("A.HORA_INICIO")
            ->addRawColumn("DATE_FORMAT(A.DATA,'%d/%m/%Y') AS DATA_ATIVIDADE")
            ->addRawColumn("DATE_FORMAT(A.DATA,'%H:%i') AS HORA_ATIVIDADE")
            ->addRawColumn("A.HISTORICO AS DESCRICAO")
            ->addRawColumn("PC.NUMERO_PROCESSO")
            ->addRawColumn("PC.NUMERO_PROCESSO_NEW")
            ->addRawColumn("CL.CLASSE")
            ->addRawColumn("CA.CARTORIO")
            ->addRawColumn("CO.COMARCA")
            ->addRawColumn("PC.VALOR_CAUSA")
            ->addRawColumn("PC.DATA_CADASTRO")
            ->addRawColumn("PC.DATA_DISTRIBUICAO")
            ->addRawColumn("
                (
                    SELECT count(*)
                    FROM atividade_anexos
                    WHERE TRUE
                    AND atividade_anexos.codigo_atividade = A.CODIGO
                    AND A.ENVIAR = 'S'
                ) AS quantidade_anexos
            ");
    }

    public function getRelacionamentoClientes($userId) {
        $relations = (new PessoaRelacionamentoUsuarioModel)
            ->getRelationsUserApp($userId);

        $codigos = [];

        foreach ($relations as $relation) {
            if ($relation->atividades == 'S') {
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

        $this->addRawFilter("A.CODIGO_CLIENTE IN ({$this->codigoCliente})");
        $this->addRawFilter("A.ENVIAR = 'S'");
    }

    public function getTotalActivities($userId) {
        $this->setRelacionamentoClienteRawFilter($userId);

        return $this->countAll();
    }

    public function getLastDayOnMonth($month, $year) {
        $lastIs31 = [1, 3, 5, 7, 8, 10, 12];

        if (in_array($month, $lastIs31)) {
            return 31;
        } elseif ($month == 2) {
            if ($year % 4 == 0) {
                return 29;
            }

            return 28;
        }

        return 30;
    }

    public function getMonthActivities($userId, $month, $year) {
        $codigoCliente = $this->codigoCliente;
        
        if (!$codigoCliente || $codigoCliente == -1) {
            $codigoCliente = $this->getRelacionamentoClientes($userId);
        }

        $date = new \DateTime();
        $date->sub(new \DateInterval('P30D'));
        $ago = $date->format('Y-m-d H:i:s');
        $now = date('Y-m-d H:i:s');
        
        $last30Days = "
          SELECT COUNT(0) qtde_30_dias
            FROM atividade 
           WHERE CODIGO_CLIENTE IN({$codigoCliente})
             AND data BETWEEN '{$ago}' AND '{$now}'
             AND enviar = 'S'";
        
        $result = DB::select($last30Days);
        $total = 0;

        if ($result && isset($result[0])) {
            $total = $result[0]->qtde_30_dias;
        }

        return $total;

        /*$this->setRelacionamentoClienteRawFilter($userId);

        $lastDayOnMonth = $this->getLastDayOnMonth($month * 1, $year * 1);
        $month = str_pad($month, 2, '0', 0);

        $initDate = "{$year}-{$month}-01 00:00:00";
        $finalDate = "{$year}-{$month}-{$lastDayOnMonth} 23:59:59";

        $filter = new \stdClass;
        $filter->op = 'B';
        $filter->col = 'A.DATA';
        $filter->val = $initDate;
        $filter->val2 = $finalDate;

        return $this->countAll($filter);*/
    }

    public function getTotalHoras($codigoCliente) {
        $sql = "
            SELECT time_format(SEC_TO_TIME(SUM(TIME_TO_SEC(tempo))),'%H:%i:%s') AS total_horas
              FROM atividade
             WHERE CODIGO_CLIENTE IN ({$codigoCliente})
               AND enviar = 'S'";

        $result = DB::select($sql);

        if (empty($result)) {
            return '00:00:00';
        }

        return $result[0]->total_horas;
    }

}
