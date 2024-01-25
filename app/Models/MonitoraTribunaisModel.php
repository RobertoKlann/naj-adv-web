<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Model de Tribunais.
 *
 * @package    Models
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      23/11/2020
 */
class MonitoraTribunaisModel extends NajModel {

    protected function loadTable() {
        $this->setTable('monitora_tribunais');

        $this->addColumn('id', true);
        $this->addColumn('nome');
        $this->addColumn('sigla');
        $this->addColumn('busca_nome');
        $this->addColumn('busca_processo');
        $this->addColumn('creditos_busca_processo');
        $this->addColumn('creditos_busca_nome');
        $this->addColumn('disponivel_autos');
        
        $this->primaryKey = 'id';
    }
    
}
