<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Model do Monitora Termo Envolvidos.
 *
 * @package    Models
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      21/07/2020
 */
class MonitoraTermoEnvolvidosModel extends NajModel {

    protected function loadTable() {
        $this->setTable('monitora_termo_envolvidos');

        $this->addColumn('id', true);
        $this->addColumn('id_monitora_termo_processo');
        $this->addColumn('tipo');
        $this->addColumn('nome');
        $this->addColumn('principal');
        
        $this->primaryKey = 'id';
    }
}


