<?php

namespace App\Models;

use App\Models\NajModel;

/**
 * Modelp do Google Cloud Storage.
 *
 * @package    Models
 * @subpackage NajWeb
 * @author     Roberto Klann
 * @since      04/08/2020
 */
class GoogleCloudStorageModel extends NajModel {

    protected function loadTable() {
        $this->setTable('monitora_termo_diario');

        $this->addColumn('id', true);
        $this->addColumn('id_monitoramento');
    }

}