<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo dos anexos do GCS.
 *
 * @package    Models
 * @subpackage NajWeb
 * @author     Roberto Klann
 * @since      04/08/2020
 */
class AnexoChatStorageModel extends NajModel {

    protected function loadTable() {
        $this->setTable('prc_anexo');

        $this->addColumn('id', true);
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

    public function getOriginalNameForDownload($id) {
        $mensagem = DB::select("
            SELECT *
              FROM chat_mensagem
             WHERE TRUE
               AND id = {$id}
        ");


        if(is_array($mensagem) && $mensagem[0]->conteudo != '') {
            return $mensagem[0]->conteudo;
        }

        return false;
    }

}