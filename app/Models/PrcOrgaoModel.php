<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Model do Processo Orgao.
 *
 * @package    Models
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      05/10/2020
 */
class PrcOrgaoModel extends NajModel {

    protected function loadTable() {
        $this->setTable('prc_orgao');

        $this->addColumn('ID', true);
        $this->addColumn('ORGAO');
        $this->addColumn('URL');
        
        $this->primaryKey = 'ID';
    }
}
