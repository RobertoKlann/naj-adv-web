<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Model do Processo.
 *
 * @package    Models
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      21/07/2020
 */
class PrcModel extends NajModel {

    protected function loadTable() {
        $this->setTable('prc');

        $this->addColumn('codigo', true);
        $this->addColumn('numero_processo_new');
        $this->addColumn('numero_processo_new2');
        
        $this->primaryKey = 'codigo';
    }
}
