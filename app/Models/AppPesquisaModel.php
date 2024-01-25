<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;
class AppPesquisaModel extends NajModel {

    protected function loadTable() {
		$this->setTable('pesquisa_respostas');
		$this->addColumn('id', true)->setHidden();
		$this->setOrder('pr.id');
		$this->primaryKey = 'id';
		$this->addAllColumns();
		$this->setRawBaseSelect("
				SELECT [COLUMNS]
					FROM pesquisa_respostas pr
					JOIN pesquisa_nps_csat ps
					ON ps.id = pr.id_pesquisa
		");
    }

    public function addAllColumns() {
		$this->addRawColumn("pr.id AS id")
			->addRawColumn("pr.status AS status")
			->addRawColumn("pr.count AS count")
			->addRawColumn("ps.pergunta AS pergunta")
			->addRawColumn("ps.range_min_info AS range_min_info")
			->addRawColumn("ps.range_max_info AS range_max_info")
			->addRawColumn("ps.range_max AS range_max");
    }

    public function getPesquisas() {
      	return DB::select($this->getSelectSql());
    }

    public function refreshVisualizacao($id, $userId) {
		$now = date('Y-m-d H:i');

		DB::update("
			UPDATE pesquisa_respostas
			SET data_hora_visualizacao = '{$now}'
			WHERE id = {$id}
			AND id_usuario = {$userId}
		");
    }

    public function recusado($id, $userId) {
		$motivo = request()->get('motivo');
		$count = request()->get('count');
		$now = date('Y-m-d H:i');
		
		DB::update("
			UPDATE pesquisa_respostas
			SET count = {$count},
				data_hora_exibicao = DATE_ADD('{$now}', INTERVAL 7 DAY),
				status = 'N',
				device = 'APP'
			WHERE id = {$id}
			AND id_usuario = {$userId}
		");
    }

    public function aceito($id, $userId) {
		$motivo = request()->get('motivo');
		$nota = request()->get('nota');
		$now = date('Y-m-d H:i');

		if ($motivo != '') {
			$motivo = "'{$motivo}'";
		} else {
			$motivo = 'null';
		}

		DB::update("
			UPDATE pesquisa_respostas
			SET motivo = {$motivo},
				nota = {$nota},
				status = 'R',
				device = 'APP',
				data_hora_resposta = '{$now}'
			WHERE id = {$id}
			AND id_usuario = {$userId}
		");
    }

}
