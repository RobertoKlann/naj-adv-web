<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Model de Diarios.
 *
 * @package    Models
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      21/07/2020
 */
class MonitoraDiariosModel extends NajModel {

    protected function loadTable() {
        $this->setTable('monitora_diarios');

        $this->addColumn('id', true);
        $this->addColumn('nome');
        $this->addColumn('sigla');
        $this->addColumn('estado');
        $this->addColumn('competencia');
        
        $this->primaryKey = 'id';
    }
    
    /**
     * Obtêm o id do registro em "monitora_diarios" com base do "id_diario" da escavador
     * 
     * @param int $id_diario Id do Diario na Escavador
     * @return int|null
     */
    public function getIdMonitoraDiarios($id_diario){
        $sql = "SELECT id FROM monitora_diarios
                WHERE id_diario = $id_diario;";
        
        $result = DB::select($sql);
        if(count($result) > 0){
            return $result[0]->id;
        }else{
            return null;
        }
        
    }
}
