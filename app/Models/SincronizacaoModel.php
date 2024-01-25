<?php

namespace App\Models;

use App\Models\NajModel;

/**
 * Modelo da sincronização.
 *
 * @since 2020-11-18
 */
class SincronizacaoModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('sync');
    }
    
}