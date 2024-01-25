<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo de Atividade Tipo.
 *
 * @author Roberto Klann
 * @since 27/11/2023
 */
class AtividadeModel extends NajModel {

    protected function loadTable() {
        $this->setTable('atividade');
        $this->addColumn('CODIGO', true)->setHidden();

        $this->addColumn('CODIGO_USUARIO');
        $this->addColumn('CODIGO_DIVISAO');
        $this->addColumn('CODIGO_CLIENTE');
        $this->addColumn('CODIGO_PROCESSO');
        $this->addColumn('ID_TIPO_ATIVIDADE');
        $this->addColumn('CODIGO_TEXTO');
        $this->addColumn('DATA');
        // $this->addColumn("DATE_FORMAT(DATA,'%d/%m/%Y') AS DATA_INICIO");
        $this->addColumn('HORA_INICIO');
        $this->addColumn('TEMPO');
       //  $this->addColumn('ID_TIPO_ATIVIDADE');
       //  $this->addColumn('ENVIAR');
       //  $this->addColumn('HISTORICO');
       //  $this->addColumn('ID_AREA_JURIDICA');
  
        $this->setOrder('ATIVIDADE.DATA', 'DESC');
  
        $this->addAllColumns();
       //  $this->addRawFilter("ENVIAR = 'S'");
        // $this->addRawFilter("ATIVIDADE.CODIGO_CLIENTE IN ({$codigoCliente})");
        $this->setRawBaseSelect("
                 SELECT [COLUMNS]
                   FROM ATIVIDADE ATIVIDADE
             INNER JOIN PESSOA P1 
                     ON P1.CODIGO = ATIVIDADE.CODIGO_USUARIO            
              LEFT JOIN PRC PC
                     ON PC.CODIGO = ATIVIDADE.CODIGO_PROCESSO
              LEFT JOIN PESSOA P3
                    ON P3.CODIGO = PC.CODIGO_CLIENTE
              LEFT JOIN PESSOA P2
                     ON P2.CODIGO = PC.CODIGO_ADVERSARIO
              LEFT JOIN PESSOA PESSOA_CLIENTE
                     ON PESSOA_CLIENTE.CODIGO = ATIVIDADE.CODIGO_CLIENTE
              LEFT JOIN PRC_COMARCA CO 
                     ON CO.CODIGO = PC.CODIGO_COMARCA
              LEFT JOIN PRC_CARTORIO CA 
                     ON CA.CODIGO = PC.CODIGO_CARTORIO
              LEFT JOIN PRC_CLASSE CL 
                     ON CL.CODIGO = PC.CODIGO_CLASSE
              LEFT JOIN PRC_GRAU_RISCO PGR
                     ON PGR.ID = PC.ID_GRAU_RISCO
        ");
     }
  
     public function addAllColumns() {
        $this->addRawColumn("DATE_FORMAT(ATIVIDADE.DATA,'%d/%m/%Y') AS DATA_INICIO")
           ->addRawColumn("ATIVIDADE.CODIGO AS CODIGO")
           ->addRawColumn("ATIVIDADE.ENVIAR AS ENVIAR")
           ->addRawColumn("ATIVIDADE.CODIGO_PROCESSO AS CODIGO_PROCESSO")
           ->addRawColumn("DATE_FORMAT(ATIVIDADE.DATA_TERMINO,'%d/%m/%Y') AS DATA_TERMINO")
           ->addRawColumn("TIME_FORMAT(ATIVIDADE.HORA_INICIO,'%H:%i:%s') AS HORA_INICIO")
           ->addRawColumn("TIME_FORMAT(ATIVIDADE.HORA_TERMINO,'%H:%i:%s') AS HORA_TERMINO")
           ->addRawColumn("TIME_FORMAT(ATIVIDADE.TEMPO,'%H:%i:%s') AS TEMPO")
           ->addRawColumn("ATIVIDADE.HISTORICO AS DESCRICAO")
           ->addRawColumn("P1.NOME AS NOME_USUARIO")
           ->addRawColumn("P3.NOME AS NOME_CLIENTE")
           ->addRawColumn("P2.NOME AS NOME_ADVERSARIO")
           ->addRawColumn("PESSOA_CLIENTE.NOME AS PESSOA_CLIENTE_NOME")
           ->addRawColumn("PC.QUALIFICA_ADVERSARIO")
           ->addRawColumn("PC.QUALIFICA_CLIENTE")
           ->addRawColumn("PC.NUMERO_PROCESSO")
           ->addRawColumn("PC.NUMERO_PROCESSO_NEW")
           ->addRawColumn("CL.CLASSE")
           ->addRawColumn("CA.CARTORIO")
           ->addRawColumn("CO.COMARCA")
           ->addRawColumn("CO.UF AS COMARCA_UF")
           ->addRawColumn("PC.ID_GRAU_RISCO")
           ->addRawColumn("PC.VALOR_RISCO")
           ->addRawColumn("PC.VALOR_CAUSA")
           ->addRawColumn("PC.OBSERVACAO")
           ->addRawColumn("PGR.DESCRICAO AS DESCRICAO_RISCO")
           ->addRawColumn("PC.DATA_CADASTRO")
           ->addRawColumn("PC.DATA_DISTRIBUICAO")
           ->addRawColumn("PC.PEDIDOS_PROCESSO")
           ->addRawColumn("
              (
                  SELECT COUNT(0) 
                  FROM ATIVIDADE_ANEXOS ATV_ANEXO
                  JOIN ATIVIDADE
                    ON ATIVIDADE.CODIGO = ATV_ANEXO.CODIGO_ATIVIDADE
                  WHERE ATV_ANEXO.CODIGO_ATIVIDADE = ATIVIDADE.CODIGO
                    AND ENVIAR = 'S'
              ) AS QTDE_ANEXOS_ATIVIDADE
           ");
     }

    public function buscaNomeDonoAtividade() {
        $result = DB::select("
            SELECT codigo, nome 
              FROM pessoa
             WHERE codigo IN (SELECT codigo_usuario FROM atividade)
            ORDER BY nome
        ");

        $retorno = [];

        if (count($result) > 0 )
            $retorno = $result;

        return $retorno;
    }

    /**
     *Sobreescreve método da clase mãe para que posssamos armazenar valores nulos
     * 
     * @param bool $reqAttrs
     * @return array
     */
    public function getFilledAttributes($reqAttrs = null) {
       $toFill = [];

       $columns = !$this->incrementing ? $this->getTableColumns() : $this->getTableColumnsWithoutKeys();

       foreach ($columns as $column) {
           if ($reqAttrs) {
               $reqValue = isset($reqAttrs[$column]) ? $reqAttrs[$column] : null;

               $toFill[$column] = $reqValue;
           } else {
               $reqValue = request()->get($column);
               $toFill[$column] = $reqValue;
           }
       }

       return $toFill;
   }
    
}