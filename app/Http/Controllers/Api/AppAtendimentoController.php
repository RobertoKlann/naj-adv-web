<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\NajException;
use App\Http\Controllers\NajController;
use App\Http\Controllers\Api\AppChatController;
use App\Http\Controllers\Api\AppChatUsuarioController;
use App\Models\AppAtendimentoModel;

/**
 * Controller de atendimento
 */
class AppAtendimentoController extends NajController {

    public function onLoad() {
        $this->setModel(new AppAtendimentoModel);
    }

    public function validateStore($data) {
        $chatName = trim(request()->get('nome'));

        if (strlen($chatName) < 3) {
            throw new NajException('Nome do chat inválido (deve possuir 3 caracteres ou mais).');
        }

        $user = $this->getUserFromToken();
        $chatData = $this->storeChat($chatName);
        $this->storeChatUser($user->id, $chatData['id']);

        $data['id_chat'] = $chatData['id'];
        $data['id_usuario'] = $user->id;
        $data['status'] = 'A';

        return $data;
    }

    private function storeChatUser($userId, $chatId) {
        $dataChat = [
            'id_usuario' => $userId,
            'id_chat' => $chatId,
        ];

        $AppChatUsuarioController = new AppChatUsuarioController;
        $AppChatUsuarioController->isChild();

        $result = $AppChatUsuarioController->store($dataChat);

        if ($result['code'] != 200) {
            throw new NajException('Houve um erro ao inserir o usuário do chat.');
        }
    }

    private function storeChat($nome) {
        $dataChat = [
            'data_inclusao' => date('Y-m-d'),
            'tipo' => 'A',
            'nome' => $nome,
        ];

        $AppChatController = new AppChatController;
        $AppChatController->isChild();

        $result = $AppChatController->store($dataChat);

        if ($result['code'] != 200) {
            throw new NajException('Houve um erro ao inserir o chat.');
        }

        return $result['data']['persisted']->toArray();
    }

}
