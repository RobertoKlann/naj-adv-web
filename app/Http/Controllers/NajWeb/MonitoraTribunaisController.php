<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\MonitoraTribunaisModel;

/**
 * Controller do Monitoramento Tribunal.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      23/11/2020
 */
class MonitoraTribunaisController extends NajController {

    public function onLoad() {
        $this->setModel(new MonitoraTribunaisModel);
    }
    
}
