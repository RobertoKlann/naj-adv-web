<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 *
 */
class AppChatMensagemModel extends NajModel {

    public $incrementing = true;

    protected function loadTable() {
        $this->setTable('chat_mensagem');
        $this->addColumn('id', true);
        $this->addColumn('id_chat')->addJoin('chat');
        $this->addColumn('id_usuario')->addJoin('usuarios');
        $this->addColumn('conteudo');
        $this->addColumn('tipo');
        $this->addColumn('data_hora');
        $this->addColumn('file_size');
        $this->addColumn('file_path');
        $this->addColumn('file_type');
        $this->addColumn('tag');
        //$this->addColumn('file_origin_name');
        $this->addRawColumn("COALESCE((
            SELECT 'S'
              FROM chat_mensagem_status
             WHERE status = 2
               AND id_mensagem = chat_mensagem.id
             LIMIT 1
        ), 'N') AS lido");

        $this->addColumnFrom('usuarios', 'nome');
        $this->addColumnFrom('usuarios', 'apelido');

        $this->setOrder('chat_mensagem.id', 'desc');

        $this->primaryKey = 'id';
    }

    public function getLastAtendimentoAberto($idChat) {
        $result = DB::table('chat_atendimento')
            ->where('id_chat', $idChat)
            ->where('status', 0)
            ->orderByDesc('id')
            ->first();

        return $result;
    }

    public function getTotalMessages($idChat, $idUsuario) {
        $result = DB::table('chat_mensagem')
            ->where('id_chat', $idChat)
            ->count();

        return $result;
    }

    public function getNotReadMessagesFromArray($idChat, $idUsuario) {
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

    public function getNotReadMessages($idChat, $idUsuario) {
        $result = DB::table('chat_mensagem')
            ->where('id_chat', $idChat)
            ->where('id_usuario', '<>', $idUsuario)
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('chat_mensagem_status')
                    ->where('chat_mensagem_status.status', 2)
                    ->whereRaw('chat_mensagem_status.id_mensagem = chat_mensagem.id');
            })
            ->count();

        return $result;
    }

    public function getNotReadMessagesOfCurrentUser($idChat, $idUsuario) {
        $result = DB::table('chat_mensagem')
            ->where('id_chat', $idChat)
            ->where('id_usuario', '=', $idUsuario)
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('chat_mensagem_status')
                    ->where('chat_mensagem_status.status', 2)
                    ->whereRaw('chat_mensagem_status.id_mensagem = chat_mensagem.id');
            })
            ->get();

        return $result;
    }

}
