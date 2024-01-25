<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\NajController;
use App\Models\AppPesquisaModel;

class AppPesquisasController extends NajController {

    public function onLoad() {
        $AppPesquisaModel = new AppPesquisaModel;

        $user = $this->getUserFromToken();
        $now = date('Y-m-d H:i');

        $AppPesquisaModel->addRawFilter("pr.status in ('P', 'N')");
        $AppPesquisaModel->addRawFilter("'{$now}' >= pr.data_hora_exibicao");
        $AppPesquisaModel->addRawFilter("ps.situacao = 'A'");
        $AppPesquisaModel->addRawFilter("pr.id_usuario = {$user->id}");

        $this->setModel($AppPesquisaModel);
    }

    public function pesquisas() {
		return [
			'resultado' => $this->getModel()->getPesquisas(),
		];
    }

    public function refreshVisualizacao($id) {
		$user = $this->getUserFromToken();
		$this->getModel()->refreshVisualizacao($id, $user->id);

		return $this->resolveResponse(['mensagem' => 'ok']);
    }

    public function recusado($id) {
		$user = $this->getUserFromToken();
		$this->getModel()->recusado($id, $user->id);

		return $this->resolveResponse(['mensagem' => 'ok']);
    }

    public function aceito($id) {
		$user = $this->getUserFromToken();
		$this->getModel()->aceito($id, $user->id);

		return $this->resolveResponse(['mensagem' => 'ok']);
    }

}
