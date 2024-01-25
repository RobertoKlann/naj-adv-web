<?php

namespace App\Models;

use App\Models\NajModel;

/**
 * Modelo de boletos.
 *
 * @package    Models
 * @author     Roberto Oswaldo Klann
 * @since      30/01/2020
 */
class BoletoModel extends NajModel {
    
    protected function loadTable() {}
    
    public function getJWTIdentifier() {
        return $this->getKey();
    }
    
    public function getJWTCustomClaims() {
        return [];
    }
    
}