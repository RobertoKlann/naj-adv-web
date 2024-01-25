<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Model de Monitora Processo Tribunal Buscas.
 *
 * @package    Models
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      23/11/2020
 */
class MonitoraProcessoTribunalBuscasModel extends NajModel {

    protected function loadTable() {
        $this->setTable('monitora_processo_tribunal_buscas');

        $this->addColumn('id', true);
        $this->addColumn('id_monitora_tribunal');
        $this->addColumn('data_hora');
        $this->addColumn('status');
        $this->addColumn('status_mensagem');
        $this->addColumn('id_resultado_busca');
        $this->setOrder('data_hora DESC, id DESC');
        
        
        $this->primaryKey = 'id';
    }
    
    /**
     * Busca último registro em "monitora_processo_tribunal_buscas" com base no "id_monitora_tribunal"
     * 
     * @param int $id_monitora_tribunal
     * @return array
     */
    public function buscaMonitoraProcessoTribunalByIdMonitoraTribunal($id_monitora_tribunal){
        $sql = "SELECT * FROM monitora_processo_tribunal_buscas WHERE id_monitora_tribunal = $id_monitora_tribunal ORDER BY id DESC LIMIT 1;";
        return DB::select($sql);
    }
}
