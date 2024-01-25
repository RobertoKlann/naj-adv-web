<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\DashboardEspacoDiscoModel;

/**
 * Controller de processos.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Klann
 * @since      31/05/2021
 */
class DashboardEspacoDiscoController extends NajController {

    public function onLoad() {
        $this->setModel(new DashboardEspacoDiscoModel);
    }

    /**
     * Create da rota de Processo
     * @return view
     */
    public function index() {
        return view('najWeb.consulta.DashboardEspacoDiscoConsultaView')->with('is_dashboard', true);
    }

    public function loadData() {
        return response()->json($this->getModel()->getDataToDashboard());
    }

}