<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo do anexo dos processos.
 *
 * @since 2020-10-16
 */
class ProcessoAnexoModel extends NajModel {

   protected function loadTable() {
      $this->setTable('prc_anexos');

      $this->addColumn('id', true);
      $this->addColumn('codigo_processo');
      $this->addColumn('descricao');
      $this->addColumn('data_arquivo');
      $this->addColumn('file_size');

      $this->setOrder('data_arquivo', 'desc');
   }

   public function hasTextoVersao($codigo) {
      $anexo = DB::select("
         SELECT *
           FROM prc_anexos
          WHERE TRUE
            AND id = {$codigo}
      ");

      if (is_array($anexo))
         return $anexo[0]->CODIGO_TEXTO;
      
      return false;
   }

   public function getPathStorage() {
      $conf = DB::select("
          SELECT *
            FROM sys_config
           WHERE TRUE
             AND secao = 'SYNC_FILES'
             AND chave = 'PATH'
      ");

      return $conf[0]->VALOR;
   }

   public function isSyncGoogleStorage() {
      $conf = DB::select("
         SELECT *
            FROM sys_config
         WHERE TRUE
            AND secao = 'SYNC_FILES'
            AND chave = 'SYNC_STORAGE'
      ");

      if(is_array($conf) && $conf[0]->VALOR == 'GOOGLE_STORAGE') {
         return true;
      }

      return false;
   }

   public function getKeyFileGoogleStorage() {
      $conf = DB::select("
          SELECT *
            FROM sys_config
           WHERE TRUE
             AND secao = 'SYNC_FILES'
             AND chave = 'SYNC_STORAGE_KEY_FILE'
      ");

      return $conf[0]->VALOR;
   }

}