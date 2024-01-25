<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\PesquisaNpsModel;
use Illuminate\Support\Facades\DB;

/**
 * Controller da pesquisa NPS.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      23/04/2021
 */
class PesquisaNpsController extends NajController {

    public function onLoad() {
        $this->setModel(new PesquisaNpsModel);
    }

    /**
     * Index da rota de pesquisa NPS.
     */
    public function index() {
        return view('najWeb.consulta.PesquisaNpsConsultaView')->with('is_nps', true);
    }

    /**
     * Create da rota de usuários.
     */
    public function create() {
        return view('najWeb.manutencao.PesquisaNpsManutencaoView')->with('is_nps', true);
    }

    public function edit() {
        return view('najWeb.manutencao.PesquisaNpsManutencaoView')->with('is_nps', true);
    }

    public function usuarios() {
        return view('najWeb.consulta.PesquisaNpsUsuariosConsultaView')->with('is_nps', true);
    }

    public function beforeStore($model)
    {
        $statement = DB::select("SHOW TABLE STATUS LIKE '{$this->getModel()->getTable()}'");
        $nextId = $statement[0]->Auto_increment;
        $model->id = $nextId - 1;

        return $model;
    }
    
}