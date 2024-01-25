<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;
use App\Models\PessoaRelacionamentoUsuarioModel;

/**
 * Modelo de Pessoas.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @author     William Goebel
 * @since      23/01/2020
 */
class PessoaModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('pessoa');
        
        $this->addColumn('CODIGO', true);
        $this->addColumn('CODIGO_DIVISAO');
        $this->addColumn('CODIGO_GRUPO');
        $this->addColumn('NOME');
        $this->addColumn('CPF');
        $this->addColumn('CNPJ');
        $this->addColumn('DATA_NASCTO');
        $this->addColumn('SITUACAO');
        $this->addColumn('DATA_CADASTRO');
        $this->addColumn('TIPO');
        $this->addColumn('ENDERECO_TIPO');
        $this->addColumn('ENDERECO');
        $this->addColumn('NUMERO');
        $this->addColumn('BAIRRO');
        $this->addColumn('COMPLEMENTO');
        $this->addColumn('CIDADE');
        $this->addColumn('UF');
        
        $this->primaryKey = 'CODIGO';
        
    }

    /**
     * Obtêm todas as pessoas que contenham o filtro contido no nome 
     * 
     * @param string $filter
     * @return array
     */
    public function allPessoasInFilter($filter) {
        return DB::select(
            "SELECT codigo AS pessoa_codigo,
                    nome,
                    cpf,
                    cnpj,
                    cidade
               FROM pessoa
              WHERE TRUE
                AND nome LIKE'%{$filter}%'
            "
        );
    }
    
    public function getPessoaByNome($nome) {
        $result = DB::select(
            "SELECT codigo, nome,
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                        nome,'Ä','A')
                        ,'ä','a')
                        ,'Ë','E')
                        ,'ë','e')
                        ,'Ï','I')
                        ,'ï','i')
                        ,'Ö','O')
                        ,'ö','o')
                        ,'Ü','u')
                        ,'ü','u'
                ) as nome_sem_formatacao
               FROM pessoa
              WHERE TRUE
                AND tipo = 'F'
                HAVING nome_sem_formatacao LIKE'%{$nome}%'
                LIMIT 1;"
        );
        if(count($result) > 0){
            return $result[0];
        } else {
            return null;
        }
    }

    public function getPessoasUsuarioInFilter($filter) {
        return DB::select(
            "SELECT codigo AS pessoa_codigo,
                    nome,
                    cpf,
                    cnpj,
                    cidade
               FROM pessoa
               JOIN pessoa_usuario
                 ON pessoa_usuario.codigo_pessoa = pessoa.codigo
              WHERE TRUE
                AND nome LIKE'%{$filter}%'
            "
        );
    }

    /**
     * Obtêm pessoa pelo seu cpf
     * 
     * @param string $cpf
     * @return array
     */
    public function getPessoaByCpf($cpf) {
        return DB::select(
            "SELECT *
               FROM pessoa
              WHERE TRUE
                AND cpf = '{$cpf}'
            "
        );
    }
    
    /**
     * Obtêm pessoa pelo seu cnpj
     * 
     * @param string $cpf
     * @return array
     */
    public function getPessoaByCnpj($cnpj) {
        return DB::select(
            "SELECT *
               FROM pessoa
              WHERE TRUE
                AND cpf = '{$cnpj}'
            "
        );
    }

    public function getPessoasFisicaByNome($nome) {
        return DB::select(
            "SELECT codigo AS pessoa_codigo,
                    nome,
                    cpf,
                    cnpj,
                    cidade
               FROM pessoa
              WHERE TRUE
                AND tipo = 'F'
                AND nome LIKE'%{$nome}%'
            "
        );
    }

    public function getGrupoClienteRelacionadas($filter) {
        $filter     = json_decode(base64_decode($filter));

        //Busca as pessoas do relacionamento
        $PessoaRelUsuarioModel = new PessoaRelacionamentoUsuarioModel();
        $relacionamentos       = $PessoaRelUsuarioModel->getRelacionamentosUsuario($filter->usuario_id);
        $aCodigo = [];

        foreach($relacionamentos as $relacionamento) {
            $aCodigo[] = $relacionamento->pessoa_codigo;
        }

        //busca os processos para filtrar no GRUPO_CLIENTE
        $processos = $this->getProcessosFromPessoa($aCodigo);

        //Busca as pessoas do PROCESSO GRUPO CLIENTE
        $processosGrupoCliente = $this->getPessoasGrupoCliente($processos);

        foreach($processosGrupoCliente as $pessoa) {
            $aCodigo[] = $pessoa->CODIGO_CLIENTE;
        }

        $pessoasRel = implode(',', $aCodigo);

        if($pessoasRel == '') {
            return false;
        }

        return DB::select("
            SELECT codigo AS pessoa_codigo,
                   nome,
                   cpf,
                   cnpj,
                   cidade
              FROM pessoa
             WHERE TRUE
               AND nome LIKE'%{$filter->nome}%'
               AND codigo IN ({$pessoasRel})
        ");
    }

    public function getGrupoClienteRelacionadasByCodigo($filter) {
        $filter     = json_decode(base64_decode($filter));

        //Busca as pessoas do relacionamento
        $PessoaRelUsuarioModel = new PessoaRelacionamentoUsuarioModel();
        $relacionamentos       = $PessoaRelUsuarioModel->getRelacionamentosUsuario($filter->usuario_id);
        $hasRelacionamento     = false;

        foreach($relacionamentos as $relacionamento) {
            if($relacionamento->pessoa_codigo == $filter->pessoa_codigo) {
                $hasRelacionamento = true;
                break;
            }
        }

        //busca os processos para filtrar no GRUPO_CLIENTE
        $processos = $this->getProcessosFromPessoa([$filter->pessoa_codigo]);

        //Busca as pessoas do PROCESSO GRUPO CLIENTE
        $processosGrupoCliente = $this->getPessoasGrupoCliente($processos);

        foreach($processosGrupoCliente as $pessoa) {
            if($pessoa->CODIGO_CLIENTE == $filter->pessoa_codigo) {
                $hasRelacionamento = true;
                break;
            }
        }

        if(!$hasRelacionamento) {
            return false;
        }

        return DB::select("
            SELECT *
              FROM pessoa
             WHERE TRUE
               AND codigo = {$filter->pessoa_codigo}
        ");
    }

    private function getProcessosFromPessoa($relacionamentos) {
        if(count($relacionamentos) == 0) return [];

        $processoCodigos = implode(', ', $relacionamentos);

        return DB::select("
            SELECT CODIGO
              FROM prc
             WHERE TRUE
               AND codigo_cliente IN ({$processoCodigos})
        ");
    }

    private function getPessoasGrupoCliente($processos) {
        if(count($processos) == 0) return [];

        $processoCodigos = [];
        foreach($processos as $processo) {
            $processoCodigos[] = $processo->CODIGO;
        }

        $processoCodigos = implode(', ', $processoCodigos);

        return DB::select("
            SELECT *
              FROM prc_grupo_cliente
             WHERE TRUE
               AND codigo_processo IN ({$processoCodigos})
        ");
    }

    public function allPessoaGrupoFromChat() {
        return DB::select("
            SELECT *
              FROM pessoa_grupo
             WHERE codigo IN (
                               SELECT codigo_grupo
                                 FROM pessoa
                                WHERE codigo IN(
                                                 select codigo_cliente from prc
                                               )
                             )
            OR codigo IN(
                          SELECT codigo_grupo
                            FROM pessoa_grupo_membro
                           WHERE codigo_pessoa IN(
                                                   SELECT codigo_cliente
                                                     FROM prc
                                                 )
                         )
            ORDER BY GRUPO
        ");
    }

    public function getQuantidadeByCardPessoaAniversariantes() {
        return DB::select("
            SELECT COUNT(0) AS quantidade_cliente
              FROM pessoa
             WHERE TRUE
               AND DATA_NASCTO IS NOT NULL
          GROUP BY month(DATA_NASCTO)
        ");
    }
    
}