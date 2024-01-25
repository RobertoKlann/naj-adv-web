<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;
use App\Models\PessoaRelacionamentoUsuarioModel;

/**
 * Model de agenda do app
 */
class AppAgendaModel extends NajModel {

    protected function loadTable() {
        $this->setTable('agenda');
        $this->addColumn('ID', true)->setHidden();
        $this->setOrder('A.DATA_HORA_COMPROMISSO');
        $this->primaryKey = 'ID';
        $this->addAllColumns();
        $this->setRawBaseSelect("
                SELECT [COLUMNS]
                  FROM AGENDA A
             LEFT JOIN PRC PC
                    ON PC.CODIGO = A.CODIGO_PROCESSO
             LEFT JOIN PESSOA P1
                    ON P1.CODIGO = PC.CODIGO_CLIENTE
             LEFT JOIN PESSOA P2
                    ON P2.CODIGO = PC.CODIGO_ADVERSARIO
             LEFT JOIN PESSOA P3
                    ON P3.CODIGO = A.CODIGO_PESSOA
             LEFT JOIN PRC_COMARCA CO
                    ON CO.CODIGO = PC.CODIGO_COMARCA
             LEFT JOIN PRC_CARTORIO CA
                    ON CA.CODIGO = PC.CODIGO_CARTORIO
             LEFT JOIN PRC_CLASSE CL
                    ON CL.CODIGO = PC.CODIGO_CLASSE
        ");
    }

    public function addAllColumns() {
        $this->addRawColumn("A.ID AS ID_COMPROMISSO")
            ->addRawColumn("DATE_FORMAT(A.DATA_HORA_COMPROMISSO,'%d/%m/%Y') AS DATA")
            ->addRawColumn("DATE_FORMAT(A.DATA_HORA_COMPROMISSO,'%H:%i:%S') AS HORA")
            ->addRawColumn("A.ASSUNTO")
            ->addRawColumn("A.LOCAL")
            ->addRawColumn("PC.NUMERO_PROCESSO")
            ->addRawColumn("PC.NUMERO_PROCESSO_NEW")
            ->addRawColumn("CL.CLASSE")
            ->addRawColumn("CA.CARTORIO")
            ->addRawColumn("CO.COMARCA")
            ->addRawColumn("CO.UF AS COMARCA_UF")
            ->addRawColumn("PC.VALOR_CAUSA")
            ->addRawColumn("PC.DATA_CADASTRO")
            ->addRawColumn("P1.NOME AS NOME_CLIENTE")
            ->addRawColumn("P2.NOME AS PARTE_CONTRARIA")
            ->addRawColumn("P3.NOME AS RESPONSAVEL")
            ->addRawColumn("PC.DATA_DISTRIBUICAO");
    }

    public function getRelacionamentoClientes($userId) {
        $relations = (new PessoaRelacionamentoUsuarioModel)
            ->getRelationsUserApp($userId);

        $codigos = [];

        foreach ($relations as $relation) {
            $codigos[] = $relation->pessoa_codigo;
        }

        if (empty($codigos)) {
            return '-1';
        }

        return implode(',', array_unique($codigos));
    }

    public function selectAll() {
        $baseSelect = $this->getBaseSelect();

        $filters = $this->resolveFilters([]);

        $baseSelect .= $filters['where'];

        $withColumns = $this->fillWithColumns($baseSelect);

        // registros
        $data = DB::select(
            "{$withColumns} order by {$this->getOrder()}",
            $filters['values']
        );

        return $data;
    }

    public function getTotalEvents($userId) {
        $Sysconfig = new SysConfigModel();
        $hasConfig = $Sysconfig->searchSysConfig('AGENDA_NAJ_CLIENTE', 'TIPO_COMPROMISSO_EXIBIR');
        $conditionCompromisso = '';

        if ($hasConfig)
            $conditionCompromisso = ' AND A.CODIGO_TIPO IN(' . $hasConfig . ') ';

        $codigoCliente = $this->getRelacionamentoClientes($userId);

        if ($codigoCliente == "")
            $codigoCliente = "-1";

        $events = DB::select("
            SELECT COUNT(A.ID) AS quantidade_eventos
              FROM AGENDA A
             WHERE (
                    (
                        A.ID IN(
                            SELECT ID_COMPROMISSO
                              FROM AGENDA_MEMBRO
                             WHERE CODIGO_PESSOA IN({$codigoCliente})
                        )
                    )
                    OR 
                    (
                        A.CODIGO_PROCESSO IN(
                            SELECT CODIGO
                              FROM PRC
                             WHERE CODIGO_CLIENTE IN({$codigoCliente})
                                OR CODIGO IN(
                                    SELECT CODIGO_PROCESSO
                                      FROM PRC_GRUPO_CLIENTE
                                     WHERE CODIGO_CLIENTE IN({$codigoCliente})
                                )
                        )

                    )
                )
        AND DATE(A.DATA_HORA_COMPROMISSO) >= DATE(NOW())
        {$conditionCompromisso}
          ORDER BY A.DATA_HORA_COMPROMISSO ASC
        ");

        return $events;
    }

}
