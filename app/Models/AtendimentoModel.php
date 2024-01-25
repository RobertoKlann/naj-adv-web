<?php

namespace App\Models;

use Auth;
use App\Models\NajModel;
use App\Models\SysConfigModels;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\UsuarioDispositivoApiController;

/**
 * Modelo de atendimento.
 *
 * @package    Models
 * @author     Roberto Oswaldo Klann
 * @since      08/07/2020
 */
class AtendimentoModel extends NajModel {

    protected function loadTable() {
         $this->setTable('chat_mensagem');
    }

    public function allMessages() {
        return [
            'todos'       => $this->getMessagesFinished(),
            'emAndamento' => $this->getMessagesInAtendimento(),
            'naFila'      => $this->getMessagesInFila()
        ];
    }

    private function getMessagesFinished($usaLimitOffset = false) {
        $queryFilters = request()->query('f');
        $limit        = request()->query('limit');
		$isReload     = request()->query('isReload');
        $filters      = "HAVING status_atendimento = '1'";

        //Se foi informado algum filtro
        if($queryFilters) {
            $filterParse = json_decode(base64_decode($queryFilters));
  
            if(isset($filterParse->nome_usuario_cliente)) {
                $filters = $filters . " AND u.cliente like '%{$filterParse->nome_usuario_cliente}%'";
            }

            if(isset($filterParse->data_inicial, $filterParse->data_final)) {
               $filters = $filters . " AND data_hora_inicio BETWEEN '{$filterParse->data_inicial}' AND '$filterParse->data_final'";
           }
        }

		if($usaLimitOffset) {
			$offset = $limit;
		} else {
			$offset = 0;
		}

		$totalAtendimentosFinish = $this->getTotalRegistersFinish($filters);

		$acabouMensagens = 1;
		
		if($limit > $totalAtendimentosFinish && !$isReload) {
			return ['hasMoreMessages' => 0];
		} else if($isReload) {
			$offset = 0;			

			if($limit > $totalAtendimentosFinish){
				$limit = $totalAtendimentosFinish;
			} else {
				$limit = 10;
			}
		} else {
			$limit = 10;
		}

        $contacts = DB::select("
				select cru.id_chat,
				       u.id_usuario_cliente,
				       u.cliente,
					   u.apelido,
				       cm.ultima_mensagem,
				       cm.data_hora,
				       cat.id_usuario_atendimento,
				       cat.data_hora_inicio,
				       cat.data_hora_termino,
				       cat.status_atendimento,
					   if({$acabouMensagens} <> 0, 1, 0) AS hasMoreMessages
				from (
						select id   as id_usuario_cliente,
							   nome as cliente,
							   apelido as apelido
						  from usuarios
						 where usuario_tipo_id = '3' #usuários do tipo cliente
					  ) as u
			inner join (
						select id_usuario,
							   id_chat 
						  from chat_rel_usuarios
					  ) as cru 
				   on cru.id_usuario = u.id_usuario_cliente
			left join (
						select max(id) as id_ultima_mensagem,
							   id_chat
						  from chat_mensagem
					  group by id_chat
					  ) as cm2 on cm2.id_chat = cru.id_chat
			left join (
						select max(id) as id_ultimo_atendimento,
							   id_chat
						  from chat_atendimento
					  group by id_chat
					  ) as cat2
				   on cat2.id_chat = cru.id_chat
			inner join (
						select id,
							   conteudo as ultima_mensagem,
							   id_chat,
							   data_hora
						  from chat_mensagem
					  order by data_hora
					  ) as cm
				   on cm.id = cm2.id_ultima_mensagem
			inner join (
						select id,
							   tipo from chat
					  ) as c 
				   on c.id = cm.id_chat            
			left join (
						select id,
							   id_chat,
							   data_hora_inicio,
							   data_hora_termino, 
							   id_usuario as id_usuario_atendimento,
							   status as status_atendimento
						  from chat_atendimento
					  ) as cat
				   on cat.id = cat2.id_ultimo_atendimento
		      where c.tipo='0' #-- chats tipo público (com clientes)
		   group by u.id_usuario_cliente
		           {$filters}
		   order by cm.data_hora desc
                LIMIT {$limit}
			   OFFSET {$offset}
        ");

		return ['hasMoreMessages' => (count($contacts) < $totalAtendimentosFinish), 'data' => $contacts];
    }

    private function getMessagesInFila() {
        $id_usuario = auth()->user()->id;
        return DB::select("
			select cru.id_chat,
			       u.id_usuario_cliente,
			       u.cliente,
			       cm.ultima_mensagem,
			       cm.data_hora,
			       if(cms.qtde_novas is null,0,cms.qtde_novas) as qtde_novas,
			       cat.id_usuario_atendimento,
			       cat.data_hora_inicio,
			       cat.data_hora_termino,
			       cat.status_atendimento
			  from (
				     select id   as id_usuario_cliente,
					    	nome as cliente
				       from usuarios
				      where usuario_tipo_id = '3' #usuários do tipo cliente
			       ) as u
		inner join (
			         select id_usuario,
					     	id_chat 
				       from chat_rel_usuarios
			        ) as cru 
		        on cru.id_usuario = u.id_usuario_cliente
		left join (
			select max(id) as id_ultima_mensagem,
						id_chat
				from chat_mensagem
			group by id_chat
			) as cm2 on  cm2.id_chat = cru.id_chat 
		inner join (
			select cm1.id,
						cm1.conteudo as ultima_mensagem,
						cm1.id_usuario,
						cm1.id_chat,
						cm1.data_hora,
				carm.id_atendimento
				from chat_mensagem cm1
			left join chat_atendimento_rel_mensagem carm on carm.id_mensagem = cm1.id
			order by data_hora
			) as cm
		on  cm.id = cm2.id_ultima_mensagem
		and cm.id_usuario=u.id_usuario_cliente # SOMENTE USUÁRIOS TIPO CLIENTE
		inner join (
			select id,
						tipo from chat
			) as c 
		on c.id = cm.id_chat
		left join (
			select count(0) as qtde_novas, id_chat
			from (
						select ms.id,
								ms.status,
								ms.id_mensagem,
								m.id_chat
							from chat_mensagem_status ms
				inner join chat_mensagem m 
							on m.id = ms.id_mensagem
						) as cms
				inner join(
						SELECT max(ms.id) as id_status
							FROM chat_mensagem m
				inner join chat_mensagem_status ms on ms.id_mensagem = m.id
							where m.id_usuario <> {$id_usuario}
						group by m.id
						order by m.id desc
				) as b on b.id_status = cms.id
			where cms.status = 1 #-- 1 para enviadas | 2 para lidas
			group by id_chat
			) as cms on cms.id_chat = c.id
		left join (
			select id,
						id_chat,
						data_hora_inicio,
						data_hora_termino, 
						id_usuario as id_usuario_atendimento,
						status as status_atendimento
				from chat_atendimento
			) as cat
		on cat.id = cm.id_atendimento
		where c.tipo = '0' #-- chats tipo público (com clientes)
		and status_atendimento is null
		group by u.id_usuario_cliente
		order by cm.data_hora desc
        ");
    }

    private function getMessagesInAtendimento() {
        $id_usuario = auth()->user()->id;
        $data = DB::select("
               select cru.id_chat,
                      u.id_usuario_cliente,
                      u.cliente,
                      u.apelido,
                      cm.ultima_mensagem,
                      cm.data_hora,
                      if(cms.qtde_novas is null,0,cms.qtde_novas) as qtde_novas,
                      if(cat.id_usuario_atendimento = {$id_usuario}, 1, 0) as meu_usuario,
                      cat.id_usuario_atendimento,
                      cat.data_hora_inicio,
                      cat.data_hora_termino,
                      cat.status_atendimento
                 from (
                        select id   as id_usuario_cliente,
                               nome as cliente,
                               apelido as apelido
                          from usuarios
                         where usuario_tipo_id = '3' #usuários do tipo cliente
                      ) as u
           inner join (
                       select id_usuario,
                              id_chat 
                         from chat_rel_usuarios
                      ) as cru 
                   on cru.id_usuario = u.id_usuario_cliente
            left join (
                       select max(id) as id_ultima_mensagem,
                              id_chat
                         from chat_mensagem
                     group by id_chat
                      ) as cm2 on cm2.id_chat = cru.id_chat
            left join (
                        select max(id) as id_ultimo_atendimento,
                               id_chat
                          from chat_atendimento
                      group by id_chat
                      ) as cat2
                   on cat2.id_chat = cru.id_chat
           inner join (
                       select id,
                              conteudo as ultima_mensagem,
                              id_chat,
                              data_hora
                         from chat_mensagem
                     order by data_hora
                      ) as cm
                   on cm.id = cm2.id_ultima_mensagem
           inner join (
                       select id,
                              tipo from chat
                      ) as c 
                   on c.id = cm.id_chat
           left join (
                       select count(0) as qtde_novas, id_chat
                         from (
                              select ms.id,
                                     ms.status,
                                     ms.id_mensagem,
                                     m.id_chat
                                from chat_mensagem_status ms
                          inner join chat_mensagem m 
                                  on m.id = ms.id_mensagem
                            ) as cms
                          inner join(
                               SELECT max(ms.id) as id_status
                                 FROM chat_mensagem m
                           inner join chat_mensagem_status ms on ms.id_mensagem = m.id
                                where m.id_usuario <> 0
                             group by m.id
                             order by m.id desc
                           ) as b on b.id_status = cms.id
                        where cms.status = 1 #-- 1 para enviadas | 2 para lidas
                     group by id_chat
                      ) as cms on cms.id_chat = c.id
            left join (
                       select id,
                              id_chat,
                              data_hora_inicio,
                              data_hora_termino, 
                              id_usuario as id_usuario_atendimento,
                              status as status_atendimento
                         from chat_atendimento
                      ) as cat
                   on cat.id = cat2.id_ultimo_atendimento
                where c.tipo = '0' #-- chats tipo público (com clientes)
                  and status_atendimento = '0'
             group by u.id_usuario_cliente
             order by meu_usuario desc, cm.data_hora desc
        ");

        return $data;
    }

    public function allMessagesChat($id) {
          return DB::select("
               select cm.id as id_mensagem,
                    cm.id_chat,
                    cm.id_usuario as id_usuario_mensagem,
                    cm.conteudo,
                    cm.tipo as tipo_conteudo,
                    cm.data_hora,
                    cm.file_size,
                    cm.file_path,
                    ca.status as status_atendimento,
                    ca.id_usuario as id_usuario_atendimento,
                    ca.data_hora_inicio,
                    ca.data_hora_termino,
                    usuarios.nome,
                    ca.id as id_atendimento
               from chat_mensagem cm
          left join chat_atendimento_rel_mensagem cam on cam.id_mensagem = cm.id
          left join chat_atendimento ca on ca.id = cam.id_atendimento
               join usuarios
                 on usuarios.id = cm.id_usuario
               where cm.id_chat = {$id} -- ID_CHAT para setar o CHAT desejado e carregar todo o histórico de mensagems
               order by cm.id_chat,
                        cm.data_hora,
                        cm.id
          ");
    }

    private function getTotalRegistersFinish($filters) {
		$aCount = DB::select("
				select cru.id_chat,
					u.id_usuario_cliente,
					u.cliente,
					u.apelido,
					cm.ultima_mensagem,
					cm.data_hora,
					cat.id_usuario_atendimento,
					cat.data_hora_inicio,
					cat.data_hora_termino,
					cat.status_atendimento
				from (
						select id   as id_usuario_cliente,
							nome as cliente,
							apelido as apelido
						from usuarios
						where usuario_tipo_id = '3' #usuários do tipo cliente
					) as u
		inner join (
						select id_usuario,
							id_chat 
						from chat_rel_usuarios
					) as cru 
				on cru.id_usuario = u.id_usuario_cliente
		left join (
					select max(id) as id_ultima_mensagem,
							id_chat
						from chat_mensagem
					group by id_chat
					) as cm2 on cm2.id_chat = cru.id_chat
		inner join (
					select cm1.id,
							cm1.conteudo as ultima_mensagem,
							cm1.id_chat,
							cm1.data_hora,
							carm.id_atendimento
						from chat_mensagem cm1
					left join chat_atendimento_rel_mensagem carm
						on carm.id_mensagem = cm1.id
					order by data_hora
					) as cm
				on cm.id = cm2.id_ultima_mensagem
		inner join (
					select id,
							tipo
						from chat
					) as c 
				on c.id = cm.id_chat            
		left join (
					select id,
							id_chat,
							data_hora_inicio,
							data_hora_termino, 
							id_usuario as id_usuario_atendimento,
							status as status_atendimento
					from chat_atendimento
					) as cat
				on cat.id = cm.id_atendimento
			where c.tipo='0' #-- chats tipo público (com clientes)
		group by u.id_usuario_cliente
				{$filters}
		order by cm.data_hora desc
		");

		return count($aCount);
	}

	public function allMessagesFinish() {
		return $this->getMessagesFinished(true);
	}

	public function getQuantidadeClientePessoaGrupoByCard($parametro) {
		return DB::select("
			SELECT COUNT(0) AS qtde_clientes
			  FROM PESSOA P
			 WHERE P.CODIGO_GRUPO = {$parametro->key} # CÓDIGO DO GRUPO DO CARD A SER TOTALIZADO
			    OR P.CODIGO IN (
				                 SELECT CODIGO_PESSOA 
				                   FROM PESSOA_GRUPO_MEMBRO
				                  WHERE CODIGO_GRUPO = {$parametro->key} # CÓDIGO DO GRUPO DO CARD A SER TOTALIZADO
			                   )
		");
	}

    public function quantidadeClienteByCard($parametro) {
       return DB::select("
           SELECT count(0) as qtde_clientes
             FROM pessoa
            WHERE codigo IN (
                              SELECT codigo_cliente
                                FROM prc
                               WHERE {$parametro->nome_filter} = {$parametro->key}
                            )
               OR codigo IN (
                              SELECT codigo_cliente
                                FROM prc_grupo_cliente
                               WHERE codigo_processo IN (
                                                          SELECT codigo
                                                            FROM prc
                                                           WHERE {$parametro->nome_filter} = {$parametro->key}
                                                        )
                            )
       ");
    }

    public function getPessoasConsultaAvancada($parametro) {
		$pessoas = DB::select("
			SELECT codigo
			  FROM pessoa
			 WHERE codigo IN (
							SELECT codigo_cliente
							FROM prc
							WHERE {$parametro->nome_filter} = {$parametro->key}
						)
			OR codigo IN (
						SELECT codigo_cliente
							FROM prc_grupo_cliente
							WHERE codigo_processo IN (
													SELECT codigo
														FROM prc
														WHERE {$parametro->nome_filter} = {$parametro->key}
													)
						)
		");

		if(is_array($pessoas) && count($pessoas) == 0) {
			return false;
		}

		$pessoasCodigo = [];

		foreach($pessoas as $pessoa) {
			$pessoasCodigo[] = $pessoa->codigo;
		}

		$codigos = implode(', ', $pessoasCodigo);

		$clientes = DB::select("
			SELECT P.CODIGO,
			       P.NOME,
				   U.id as ID_USUARIO
			  FROM PESSOA P
		 LEFT JOIN pessoa_rel_clientes rc
		        ON rc.PESSOA_CODIGO = P.CODIGO
		 LEFT JOIN usuarios u
		        ON u.id = rc.usuario_id
			   AND u.usuario_tipo_id = 3 # tipo 3 = clientes
			 WHERE P.CODIGO IN({$codigos})
	      ORDER BY id_usuario
        ");

		$usuarioCodigos = [];
		$usuariosNaoHabilitados = [];

		foreach($clientes as $cliente) {
			if($cliente->ID_USUARIO) {
				$usuarioCodigos[] = $cliente->ID_USUARIO;
			} else {
				$usuariosNaoHabilitados[] = $cliente->CODIGO;
			}
		}

		$empresaCodigo = (new SysConfigModel)->searchSysConfig('CPANEL', 'CLIENTE_ID');
		$noHasDevices = [];
		$hasDevices = [];

		if(is_array($usuarioCodigos) && count($usuarioCodigos) > 0) {
			$responseData = (new UsuarioDispositivoApiController)->getWithDispositivoOrEmpty(['pessoaCodigo' => $empresaCodigo, 'usuarios' => $usuarioCodigos]);
			$response     = json_decode($responseData->getBody()->getContents());

			if (!isset($response->status_code) || $response->status_code != '200') {
				return $this->resolveResponse(['mensagem' => $response->naj->mensagem], 400);
			}

			if($response->naj->noHasDevices) {
				$noHasDevices = $response->naj->noHasDevices;
			}

			if($response->naj->hasDevices) {
				$hasDevices = $response->naj->hasDevices;
			}
		}
		
		return [
			'naoHabilitados' => $usuariosNaoHabilitados,
			'habilitadosSemDevice' => $noHasDevices,
			'habilitadosComDevice' => $hasDevices
		];
    }

	public function getPessoasGrupoConsultaAvancada($parametro) {
		$pessoas = DB::select("
			SELECT P.codigo
			  FROM PESSOA P
			 WHERE (P.CODIGO_GRUPO = {$parametro->key} # CÓDIGO DO GRUPO DO CARD A SER TOTALIZADO
				    OR P.CODIGO IN (
					           SELECT CODIGO_PESSOA 
					             FROM PESSOA_GRUPO_MEMBRO
					            WHERE CODIGO_GRUPO = {$parametro->key} # CÓDIGO DO GRUPO DO CARD A SER TOTALIZADO
				               )
			    )
		");

		if(is_array($pessoas) && count($pessoas) == 0) {
			return false;
		}

		$pessoasCodigo = [];

		foreach($pessoas as $pessoa) {
			$pessoasCodigo[] = $pessoa->codigo;
		}

		$codigos = implode(', ', $pessoasCodigo);

		$clientes = DB::select("
			SELECT P.CODIGO,
			       P.NOME,
				   U.id as ID_USUARIO
			  FROM PESSOA P
		 LEFT JOIN pessoa_rel_clientes rc
		        ON rc.PESSOA_CODIGO = P.CODIGO
		 LEFT JOIN usuarios u
		        ON u.id = rc.usuario_id
			   AND u.usuario_tipo_id = 3 # tipo 3 = clientes
			 WHERE P.CODIGO IN({$codigos})
	      ORDER BY id_usuario
        ");

		$usuarioCodigos = [];
		$usuariosNaoHabilitados = [];

		foreach ($clientes as $cliente) {
			if ($cliente->ID_USUARIO)
				$usuarioCodigos[] = $cliente->ID_USUARIO;
			else
				$usuariosNaoHabilitados[] = $cliente->CODIGO;
		}

		$empresaCodigo = (new SysConfigModel)->searchSysConfig('CPANEL', 'CLIENTE_ID');
		$noHasDevices = [];
		$hasDevices = [];

		if (is_array($usuarioCodigos) && count($usuarioCodigos) > 0) {
			$responseData = (new UsuarioDispositivoApiController)->getWithDispositivoOrEmpty(['pessoaCodigo' => $empresaCodigo, 'usuarios' => $usuarioCodigos]);
			$response     = json_decode($responseData->getBody()->getContents());

			if (!isset($response->status_code) || $response->status_code != '200')
				return ['mensagem' => $response->naj];

			if ($response->naj->noHasDevices)
				$noHasDevices = $response->naj->noHasDevices;

			if ($response->naj->hasDevices)
				$hasDevices = $response->naj->hasDevices;
		}
		
		return [
			'naoHabilitados' => $usuariosNaoHabilitados,
			'habilitadosSemDevice' => $noHasDevices,
			'habilitadosComDevice' => $hasDevices
		];
    }

	public function getPessoasAniversarianteConsultaAvancada($parametro) {
		$pessoas = DB::select("
			SELECT codigo
			  FROM pessoa
		     WHERE TRUE
			   AND DATA_NASCTO IS NOT NULL
			   AND month(DATA_NASCTO) = {$parametro->key}
		");

		if(is_array($pessoas) && count($pessoas) == 0) {
			return false;
		}

		$pessoasCodigo = [];

		foreach($pessoas as $pessoa) {
			$pessoasCodigo[] = $pessoa->codigo;
		}

		$codigos = implode(', ', $pessoasCodigo);

		$clientes = DB::select("
			SELECT P.CODIGO,
			       P.NOME,
				   U.id as ID_USUARIO,
				   u.usuario_tipo_id tipoUsuario
			  FROM PESSOA P
		 LEFT JOIN pessoa_rel_clientes rc
		        ON rc.PESSOA_CODIGO = P.CODIGO
		 LEFT JOIN usuarios u
		        ON u.id = rc.usuario_id
			   AND u.usuario_tipo_id = 3 # tipo 3 = clientes
			 WHERE P.CODIGO IN({$codigos})
	      ORDER BY id_usuario
        ");

		$usuarioCodigos = [];
		$usuariosNaoHabilitados = [];

		foreach($clientes as $cliente) {
			if($cliente->ID_USUARIO) {
				$usuarioCodigos[] = $cliente->ID_USUARIO;
			} else {
				$usuariosNaoHabilitados[] = $cliente->CODIGO;
			}
		}

		$empresaCodigo = (new SysConfigModel)->searchSysConfig('CPANEL', 'CLIENTE_ID');
		$noHasDevices = [];
		$hasDevices = [];

		if(is_array($usuarioCodigos) && count($usuarioCodigos) > 0) {
			$responseData = (new UsuarioDispositivoApiController)->getWithDispositivoOrEmpty(['pessoaCodigo' => $empresaCodigo, 'usuarios' => $usuarioCodigos]);
			$response     = json_decode($responseData->getBody()->getContents());

			if (!isset($response->status_code) || $response->status_code != '200') {
				return $this->resolveResponse(['mensagem' => $response->naj->mensagem], 400);
			}

			if($response->naj->noHasDevices) {
				$noHasDevices = $response->naj->noHasDevices;
			}

			if($response->naj->hasDevices) {
				$hasDevices = $response->naj->hasDevices;
			}
		}
		
		return [
			'naoHabilitados' => $usuariosNaoHabilitados,
			'habilitadosSemDevice' => $noHasDevices,
			'habilitadosComDevice' => $hasDevices
		];
    }
    
}