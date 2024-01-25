<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo da pesquisa NPS usuários.
 *
 * @package    Models
 * @author     Roberto Oswaldo Klann
 * @since      27/04/2021
 */
class PesquisaNpsUsuarioModel extends NajModel {

    protected function loadTable() {
        $this->setTable('pesquisa_respostas');

        $this->addColumn('id', true);
        $this->addColumn('id_pesquisa')->addJoin('pesquisa_nps_csat');
        $this->addColumn('id_usuario')->addJoin('usuarios');
        $this->addColumn('data_hora_inclusao');
        $this->addColumn('data_hora_exibicao');
        $this->addColumn('data_hora_visualizacao');
        $this->addColumn('count');
        $this->addColumn('data_hora_resposta');
        $this->addColumn('nota');
        $this->addColumn('motivo');
        $this->addColumn('status');
        $this->addColumn('device');
        $this->addColumn('lido');

		$this->setOrder('status2, nota', 'asc', false);

        $this->addAllColumns();

        $this->setRawBaseSelect("
                SELECT [COLUMNS]
                  FROM pesquisa_respostas
                  JOIN pesquisa_nps_csat
                    ON pesquisa_nps_csat.id = pesquisa_respostas.id_pesquisa
                  JOIN usuarios
                    ON usuarios.id = pesquisa_respostas.id_usuario
        ");
    }

    public function addAllColumns() {
        $this->addRawColumn("usuarios.id as usuarioId")
             ->addRawColumn("usuarios.nome AS nomeUsuario")
             ->addRawColumn("
			    (
				  CASE WHEN pesquisa_respostas.status = 'R' THEN '1'
			           WHEN pesquisa_respostas.status = 'N' THEN '2'
			           ELSE '3'
				   END
				) AS status2")
             ->addRawColumn("pesquisa_nps_csat.range_max AS rangeMax")
             ->addRawColumn("pesquisa_nps_csat.descricao AS pesquisaTitulo");
    }

	public function userHasRelationshipWithSearch($search, $user) {
		return DB::select("
			SELECT *
			  FROM pesquisa_respostas
			 WHERE TRUE
			   AND id_usuario = {$user}
			   AND id_pesquisa = {$search}
		");
	}

	public function pendentesNotRead($pesquisa) {
		return DB::select("
			SELECT count(0) as quantidade
			  FROM pesquisa_respostas
		     WHERE TRUE
			   AND lido = 'N'
			   AND status <> 'P'
			   AND id_pesquisa = {$pesquisa->pesquisa}
		");
	}

}