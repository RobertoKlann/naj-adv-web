<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo do anexo dos processos.
 *
 * @since 2020-10-16
 */
class PessoaAnexoModel extends NajModel {

   protected function loadTable() {
      $this->setTable('pessoa_anexos');

      $this->addColumn('id', true);
      $this->addColumn('id_dir');
      $this->addColumn('codigo_pessoa');
      $this->addColumn('codigo_texto');
      $this->addColumn('descricao');
      $this->addColumn('nome_arquivo');
      $this->addColumn('data_arquivo');
      $this->addColumn('file_path');
      $this->addColumn('file_size');

      $this->setOrder('data_arquivo', 'desc');
   }

   public function hasTextoVersao($codigo) {
      $anexo = DB::select("
         SELECT *
           FROM pessoa_anexos
          WHERE TRUE
            AND id = {$codigo}
      ");

      return $anexo[0]->CODIGO_TEXTO;
   }

   public function findAnexoByNomeByPessoa($codigoPessoa, $nomeArquivo) {
      return DB::select("
         SELECT *
           FROM pessoa_anexos
          WHERE TRUE
            AND codigo_pessoa = {$codigoPessoa}
            AND nome_arquivo  = '{$nomeArquivo}'
      ");
   }

   public function hasPessoaAnexoDirArquivoMensagem($codigoPessoa) {
      return DB::select("
         SELECT *
           FROM pessoa_anexos_dir
          WHERE TRUE
            AND descricao LIKE'%ANEXOS DAS MENSAGENS%'
            AND codigo_pessoa = {$codigoPessoa}
      ");
   }

}