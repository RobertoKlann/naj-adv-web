<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Model de prc_movimentoo.
 *
 * @package    Models
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      15/12/2020
 */
class PrcMovimentoModel extends NajModel {

    protected function loadTable() {
        $this->setTable('prc_movimento');

        $this->addColumn('ID', true);
        $this->addColumn('CODIGO_PROCESSO');
        $this->addColumn('ID_INTIMACAO');
        $this->addColumn('DATA');
        $this->addColumn('DATA_ALTERACAO');
        $this->addColumn('DESCRICAO_ANDAMENTO');
        $this->addColumn('TRADUCAO_ANDAMENTO');
        $this->addColumn('NOTIFICADO');
        $this->addColumn('NOTIFICAR');
        $this->addColumn('ULTIMA_NOTIFICACAO');
        
        $this->primaryKey = 'ID';
    }
    
}
