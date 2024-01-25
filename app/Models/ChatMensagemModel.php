<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\NajWeb\ChatMensagemStatusController;
use App\Http\Controllers\Api\UsuarioDispositivoApiController;

/**
 * Modelo de boletos.
 *
 * @package    Models
 * @author     Roberto Oswaldo Klann
 * @since      13/07/2020
 */
class ChatMensagemModel extends NajModel {

    protected function loadTable() {
        $this->setTable('chat_mensagem');

        $this->addColumn('id', true);
        $this->addColumn('id_chat');
        $this->addColumn('id_usuario')->addJoin('usuarios');;
        $this->addColumn('conteudo');
        $this->addColumn('tipo');
        $this->addColumn('data_hora');
        $this->addColumn('file_size');
        $this->addColumn('file_path');
        $this->addColumn('file_type');
        $this->addColumn('tag');

        $this->addColumnFrom('usuarios', 'nome');
        $this->addColumnFrom('usuarios', 'apelido');
    }

    public function getAllMensagensChatPublico($id) {
        $queryFilters   = request()->query('f');
        $filterParse    = json_decode(base64_decode($queryFilters));
        $limit          = $filterParse->limit;
        $isUpdateStatus = $filterParse->updateStatus;
        $offset         = ($this->getOffsetPage($id) - $limit);

        if($offset < 0)
            $offset = 0;

        if($isUpdateStatus)
            $this->setStatusMensagemLida($id);

        $aData = DB::select("
          select c.id_mensagem,
                  c.id_chat,
                  c.id_usuario_mensagem,
                  c.conteudo,
                  c.tipo_conteudo,
                  c.data_hora,
                  c.file_size,
                  c.file_type,
                  c.file_path,
                  c.status_atendimento,
                  c.id_usuario_atendimento,
                  c.data_hora_inicio,
                  c.data_hora_termino,
                  cms2.status_mensagem,
                  cms2.status,
                  c.nome,
                  c.usuario_tipo_id,
                  c.id_atendimento
            from (
                    select cm.id as id_mensagem,
                          cm.id_chat,
                          cm.id_usuario as id_usuario_mensagem,
                          cm.conteudo,
                          cm.tipo as tipo_conteudo,
                          cm.data_hora,
                          cm.file_size,
                          cm.file_type,
                          cm.file_path,
                          ca.status as status_atendimento,
                          ca.id_usuario as id_usuario_atendimento,
                          ca.data_hora_inicio,
                          ca.data_hora_termino,
                          ca.id as id_atendimento,
                          usuarios.nome,
                          usuarios.usuario_tipo_id
                      from chat_mensagem cm
                left join chat_atendimento_rel_mensagem cam 
                        on cam.id_mensagem = cm.id
                left join chat_atendimento ca 
                        on ca.id = cam.id_atendimento
                      join usuarios
                        on usuarios.id = cm.id_usuario
                  ) as c 
        left join # pegando o último id que deve ser com a última data de status da mensagem
                  (
                    select max(s.id) as id_status,
                          s.id_mensagem
                      from chat_mensagem_status s
                  group by s.id_mensagem
                  ) as cms on cms.id_mensagem = c.id_mensagem
        left join # relacionando o último id que possui a data e hora do último status com a mensagem
                  (
                    select id,
                          status_data_hora as status_mensagem,
                          status
                      from chat_mensagem_status
                  ) as cms2 on cms2.id = cms.id_status            
            where c.id_chat = {$id}
          order by c.id_chat DESC,
                  c.data_hora,
                  c.id_mensagem
            LIMIT {$limit}
            OFFSET {$offset}
        ");

        $dados_dispositivos = $this->callLoadDadosDispositivoUsuario($filterParse->id_usuario_chat);

        return ['data' => $aData, 'isLastPage' => ($offset == 0), 'dados_dispositivos' => $dados_dispositivos];
    }

    public function getOffsetPage($id) {
        $aCount = DB::select("
          select c.id_mensagem,
                  c.id_chat,
                  c.id_usuario_mensagem,
                  c.conteudo,
                  c.tipo_conteudo,
                  c.data_hora,
                  c.file_size,
                  c.file_path,
                  c.status_atendimento,
                  c.id_usuario_atendimento,
                  c.data_hora_inicio,
                  c.data_hora_termino,
                  cms2.status_mensagem,
                  cms2.status,
                  c.nome,
                  c.id_atendimento
            from (
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
                          ca.id as id_atendimento,
                          usuarios.nome
                      from chat_mensagem cm
                left join chat_atendimento_rel_mensagem cam 
                        on cam.id_mensagem = cm.id
                left join chat_atendimento ca 
                        on ca.id = cam.id_atendimento
                      join usuarios
                        on usuarios.id = cm.id_usuario
                  ) as c 
        left join # pegando o último id que deve ser com a última data de status da mensagem
                  (
                     select max(s.id) as id_status,
                            s.id_mensagem
                       from chat_mensagem_status s
                   group by s.id_mensagem
                  ) as cms on cms.id_mensagem = c.id_mensagem
        left join # relacionando o último id que possui a data e hora do último status com a mensagem
                  (
                    select id,
                           status_data_hora as status_mensagem,
                           status
                      from chat_mensagem_status
                  ) as cms2 on cms2.id = cms.id_status            
            where c.id_chat = {$id}
         order by c.id_chat DESC,
                  c.data_hora,
                  c.id_mensagem
        ");
        
        return count($aCount);
    }

    public function getLastMessageByUserAndChat($id_usuario, $id_chat) {
      return DB::select("
            SELECT *
              FROM chat_mensagem
              WHERE TRUE
                AND id_chat    = {$id_chat}
                AND id_usuario =  {$id_usuario}
          ORDER BY id DESC
              LIMIT 1
      ");
    }

    private function getNotReadMessages($idChat, $idUsuario) {
      $result = DB::table('chat_mensagem')
          ->where('id_chat', $idChat)
          ->where('id_usuario', '<>', $idUsuario)
          ->whereNotExists(function($query) {
              $query->select(DB::raw(1))
                  ->from('chat_mensagem_status')
                  ->where('chat_mensagem_status.status', 2)
                  ->whereRaw('chat_mensagem_status.id_mensagem = chat_mensagem.id');
          })
          ->get();

      return $result;
    }

    public function setStatusMensagemLida($idChat) {
        $notRead = $this->getNotReadMessages($idChat, Auth::user()->id);

        $ChatMensagemStatusController = new ChatMensagemStatusController();

        foreach($notRead as $Mensagem) {
            $ChatMensagemStatusController->store([
                "id_mensagem"      => $Mensagem->id,
                "status"           => 2,
                "status_data_hora" => date("Y-m-d H:i:s")
            ]);
        }
    }

    private function callLoadDadosDispositivoUsuario($id_usuario) {
        $UsuarioDispositivoApiController = new UsuarioDispositivoApiController();
        $result   = $UsuarioDispositivoApiController->getData($id_usuario);
        $response = json_decode($result->getBody()->getContents());

        if(!isset($response->status_code) || $response->status_code != '200') {
            $this->throwException($response->naj->mensagem);
        }

        return $response;
    }

    public function hasMessagesNotRead($chatId) {
        $usuarioId = Auth::user()->id;

        $messagesNotRead = DB::select("
            SELECT COUNT(0) AS qtde_novas,
                   cms.id_chat
              FROM (
                     SELECT ms.id,
                            ms.status,
                            ms.id_mensagem,
                            m.id_chat,
                            m.id_usuario
                       FROM chat_mensagem_status ms
                 INNER JOIN chat_mensagem m
                         ON m.id = ms.id_mensagem
                    ) AS cms
        INNER JOIN(
                    SELECT max(ms.id) AS id_status
                      FROM chat_mensagem m
                INNER JOIN chat_mensagem_status ms
                        ON ms.id_mensagem = m.id
                  GROUP BY m.id
                  ORDER BY m.id desc
                  ) AS b
                ON b.id_status = cms.id
             WHERE cms.status = 1 #-- 1 para enviadas | 2 para lidas
               AND cms.id_chat = {$chatId} #-- id_chat que está em atendimento prestes a ser encerrado
               AND cms.id_usuario <> {$usuarioId} #-- usuário do atendimento em andamento
        ");

        if(is_array($messagesNotRead) && count($messagesNotRead) > 0)
            return $messagesNotRead[0]->qtde_novas > 0;

        return false;
    }

    public function newMessagesFromChat($id) {
		$queryFilters   = request()->query('f');
        $filterParse    = json_decode(base64_decode($queryFilters));
        $isUpdateStatus = $filterParse->updateStatus;
        $usuarioId      = Auth::user()->id;

        $aData = DB::select("
          select c.id_mensagem,
                  c.id_chat,
                  c.id_usuario_mensagem,
                  c.conteudo,
                  c.tipo_conteudo,
                  c.data_hora,
                  c.file_size,
                  c.file_type,
                  c.file_path,
                  c.status_atendimento,
                  c.id_usuario_atendimento,
                  c.data_hora_inicio,
                  c.data_hora_termino,
                  cms2.status_mensagem,
                  cms2.status,
                  c.nome,
                  c.usuario_tipo_id,
                  c.id_atendimento
            from (
                    select cm.id as id_mensagem,
                          cm.id_chat,
                          cm.id_usuario as id_usuario_mensagem,
                          cm.conteudo,
                          cm.tipo as tipo_conteudo,
                          cm.data_hora,
                          cm.file_size,
                          cm.file_type,
                          cm.file_path,
                          ca.status as status_atendimento,
                          ca.id_usuario as id_usuario_atendimento,
                          ca.data_hora_inicio,
                          ca.data_hora_termino,
                          ca.id as id_atendimento,
                          usuarios.nome,
                          usuarios.usuario_tipo_id
                      from chat_mensagem cm
                left join chat_atendimento_rel_mensagem cam 
                        on cam.id_mensagem = cm.id
                left join chat_atendimento ca 
                        on ca.id = cam.id_atendimento
                      join usuarios
                        on usuarios.id = cm.id_usuario
                  ) as c 
        left join # pegando o último id que deve ser com a última data de status da mensagem
                  (
                    select max(s.id) as id_status,
                          s.id_mensagem
                      from chat_mensagem_status s
                  group by s.id_mensagem
                  ) as cms on cms.id_mensagem = c.id_mensagem
        left join # relacionando o último id que possui a data e hora do último status com a mensagem
                  (
                    select id,
                          status_data_hora as status_mensagem,
                          status
                      from chat_mensagem_status
                  ) as cms2 on cms2.id = cms.id_status            
            where c.id_chat = {$id}
              and cms2.status = 1
              and c.id_usuario_mensagem <> {$usuarioId} #ID DO USUÁRIO LOGADO
          order by c.id_chat DESC,
                  c.data_hora,
                  c.id_mensagem
        ");		

        if($isUpdateStatus)
            $this->setStatusMensagemLida($id);

		$messagesReadCurrentChat = $this->getMessagesReadCurrentChat($usuarioId, $id);

        $dados_dispositivos = $this->callLoadDadosDispositivoUsuario($filterParse->id_usuario_chat);

        return ['data' => $aData, 'dados_dispositivos' => $dados_dispositivos, 'messagesReadCurrentChat' => $messagesReadCurrentChat];
	}

    public function oldMessagesFromChat($id) {
		$queryFilters   = request()->query('f');
        $filterParse    = json_decode(base64_decode($queryFilters));
        $limit          = $filterParse->offset;
        $isUpdateStatus = $filterParse->updateStatus;
        $totalMessages  = $this->getOffsetPage($id);
        $usuarioId      = Auth::user()->id;
        $offset = $limit;

        if($limit > $totalMessages) {
            $quantidePassou = $limit - $totalMessages;
            $newOffsetProvisorio = 20 - $quantidePassou;

            $offset = $totalMessages - $newOffsetProvisorio;
        }

        $aData = DB::select("
          select c.id_mensagem,
                  c.id_chat,
                  c.id_usuario_mensagem,
                  c.conteudo,
                  c.tipo_conteudo,
                  c.data_hora,
                  c.file_size,
                  c.file_type,
                  c.file_path,
                  c.status_atendimento,
                  c.id_usuario_atendimento,
                  c.data_hora_inicio,
                  c.data_hora_termino,
                  cms2.status_mensagem,
                  cms2.status,
                  c.nome,
                  c.usuario_tipo_id,
                  c.id_atendimento
            from (
                    select cm.id as id_mensagem,
                          cm.id_chat,
                          cm.id_usuario as id_usuario_mensagem,
                          cm.conteudo,
                          cm.tipo as tipo_conteudo,
                          cm.data_hora,
                          cm.file_size,
                          cm.file_type,
                          cm.file_path,
                          ca.status as status_atendimento,
                          ca.id_usuario as id_usuario_atendimento,
                          ca.data_hora_inicio,
                          ca.data_hora_termino,
                          ca.id as id_atendimento,
                          usuarios.nome,
                          usuarios.usuario_tipo_id
                      from chat_mensagem cm
                left join chat_atendimento_rel_mensagem cam 
                        on cam.id_mensagem = cm.id
                left join chat_atendimento ca 
                        on ca.id = cam.id_atendimento
                      join usuarios
                        on usuarios.id = cm.id_usuario
                  ) as c 
        left join # pegando o último id que deve ser com a última data de status da mensagem
                  (
                    select max(s.id) as id_status,
                          s.id_mensagem
                      from chat_mensagem_status s
                  group by s.id_mensagem
                  ) as cms on cms.id_mensagem = c.id_mensagem
        left join # relacionando o último id que possui a data e hora do último status com a mensagem
                  (
                    select id,
                          status_data_hora as status_mensagem,
                          status
                      from chat_mensagem_status
                  ) as cms2 on cms2.id = cms.id_status            
            where c.id_chat = {$id}
          order by c.id_chat DESC,
                  c.data_hora DESC,
                  c.id_mensagem
            LIMIT 20
           OFFSET {$offset}
        ");

        if($isUpdateStatus)
            $this->setStatusMensagemLida($id);

        $dados_dispositivos = $this->callLoadDadosDispositivoUsuario($filterParse->id_usuario_chat);

        return ['data' => $aData, 'dados_dispositivos' => $dados_dispositivos];
	}

	private function getMessagesReadCurrentChat($usuarioId, $chatId) {
		return DB::select("
			SELECT cms.id_mensagem
			  FROM chat_mensagem_status cms
			  JOIN chat_mensagem
			    ON chat_mensagem.id = cms.id_mensagem
		     WHERE TRUE
			   AND id_usuario = {$usuarioId}
			   AND id_chat = {$chatId}
			   AND status = 2
		  ORDER BY cms.id DESC
			 LIMIT 20
		");
	}
    
}