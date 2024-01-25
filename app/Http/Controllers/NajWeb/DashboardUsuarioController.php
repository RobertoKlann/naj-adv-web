<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\DashboardUsuarioModel;

/**
 * Controller de dashboard dos usuários.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Klann
 * @since      05/06/2021
 */
class DashboardUsuarioController extends NajController {

    public function onLoad() {
        $this->setModel(new DashboardUsuarioModel);
    }

    public function indexGeral() {
        return view('najWeb.consulta.DashboardUsuariosGeralConsultaView')->with('is_dashboard', true);
    }

    public function indexUser() {
        return view('najWeb.consulta.DashboardUsuariosConsultaView')->with('is_dashboard', true);
    }

    public function indexClient() {
        return view('najWeb.consulta.DashboardUsuariosClienteConsultaView')->with('is_dashboard', true);
    }

    public function indexDispositivo() {
        return view('najWeb.consulta.DashboardDispositivoConsultaView')->with('is_dashboard', true);
    }

    public function dataByGeral() {
        return response()->json($this->getModel()->getDataByGeral());
    }

    public function dataByUserTypeUser($limit) {
        return response()->json($this->getModel()->getDataByUserTypeUser($limit));
    }

    public function dataByUserTypeClient($limit) {
        return response()->json($this->getModel()->getDataByUserTypeClient($limit));
    }

    public function dataByDispositivo($limit) {
        return response()->json($this->getModel()->getDataByDispositivo($limit));
    }

    public function dataByTypeSystem() {
        return response()->json($this->getModel()->getDataByTypeSystem());
    }

}