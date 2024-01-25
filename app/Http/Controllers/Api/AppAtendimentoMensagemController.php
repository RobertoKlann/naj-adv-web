<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\NajController;
use App\Models\AppChatMensagemModel;
use App\Models\AnexoChatStorageModel;
use App\Http\Controllers\Api\AppChatAtendimentoRelUsuarioController;
use App\Http\Controllers\Api\AppChatMensagemStatusController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\NajWeb\GoogleCloudStorageController;
use Exception;

/**
 *
 */
class AppAtendimentoMensagemController extends NajController {

    const MESSAGE_TYPE_TEXT = '0';
    const MESSAGE_TYPE_FILE = '1';

    const FILE_TYPE_IMAGE = '0';
    const FILE_TYPE_DOC   = '1';
    const FILE_TYPE_AUDIO = '2';

    const FOLDER = 'chat/';
    const DISK_NAME = 'local';

    private $laravelStorageDir;

    public function onLoad() {
        $this->setModel(new AppChatMensagemModel);

        $base = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
        @list($dir,) = explode('/public', $base);

        $this->laravelStorageDir = $dir . '/storage/app/chat_files/';
    }

    public function checkNotReadForCurrentUser() {
        $chatId = request()->get('chat_id');
        $user = $this->getUserFromToken();

        $notReadMessages = $this->getModel()->getNotReadMessagesOfCurrentUser($chatId, $user->id);

        foreach ($notReadMessages as $message) {
            $dataStatus = [
                'id_mensagem' => $message->id,
                'status' => 2,
                'status_data_hora' => date('Y-m-d H:i:s'),
            ];

            $AppChatMensagemStatusController = new AppChatMensagemStatusController;
            $AppChatMensagemStatusController->isChild();
            $resultStatus = $AppChatMensagemStatusController->store($dataStatus);

            if ($resultStatus['code'] != 200) {
                $this->throwException('Erro ao inserir o status da mensagem');
            }
        }

        return $this->resolveResponse([
            'user'      => $user->id,
            'mensagem'  => 'Ok',
            'nao_lidas' => $notReadMessages,
        ]);
    }

    public function check() {
        $chatId = request()->get('chat_id');
        $user = $this->getUserFromToken();

        $notReadMessages = $this->getModel()->getNotReadMessagesFromArray($chatId, $user->id);

        foreach ($notReadMessages as $message) {
            $dataStatus = [
                'id_mensagem' => $message->id,
                'status' => 2,
                'status_data_hora' => date('Y-m-d H:i:s'),
            ];

            $AppChatMensagemStatusController = new AppChatMensagemStatusController;
            $AppChatMensagemStatusController->isChild();
            $resultStatus = $AppChatMensagemStatusController->store($dataStatus);

            if ($resultStatus['code'] != 200) {
                $this->throwException('Erro ao inserir o status da mensagem');
            }
        }

        return $this->resolveResponse([
            'mensagem' => 'Ok',
            'nao_lidas' => $notReadMessages,
        ]);
    }

    protected function handleItems($model = null) {
        if ($this->getCurrentAction() != self::STORE_ACTION) {
            return;
        }

        $dataStatus = [
            'id_mensagem' => $model['id'],
            'status' => 1,
            'status_data_hora' => date('Y-m-d H:i:s'),
        ];

        // status da mensagem
        $AppChatMensagemStatusController = new AppChatMensagemStatusController;
        $AppChatMensagemStatusController->isChild();
        $resultStatus = $AppChatMensagemStatusController->store($dataStatus);

        if ($resultStatus['code'] != 200) {
            $this->throwException('Erro ao inserir o status da mensagem');
        }

        $lastChatAtendimento = $this->getModel()->getLastAtendimentoAberto($model['id_chat']);

        // possui um atendimento em aberto
        if ($lastChatAtendimento) {
            $dataAtendimento = [
                'id_mensagem' => $model['id'],
                'id_atendimento' => $lastChatAtendimento->id,
            ];

            $AppChatAtendimentoRelUsuarioController = new AppChatAtendimentoRelUsuarioController;
            $AppChatAtendimentoRelUsuarioController->isChild();
            $resultAtendimento = $AppChatAtendimentoRelUsuarioController->store($dataAtendimento);

            if ($resultAtendimento['code'] != 200) {
                $this->throwException('Erro ao inserir relacionamento atendimento usuário');
            }
        }
    }

    public function validateStore($data) {
        $user = $this->getUserFromToken();
        $chatId = request()->get('chat_id');

        $params = $this->validateRequestParamsExistsByArray([
            'chat_id'  => 'id do chat',
            'tipo'     => 'tipo',
            'conteudo' => 'conteúdo',
        ]);

        $data['id_chat']    = $params['chat_id'];
        $data['id_usuario'] = $user->id;
        $data['data_hora']  = date('Y-m-d H:i:s');
        $data['tipo']       = self::MESSAGE_TYPE_TEXT;
        $data['conteudo']   = trim($params['conteudo']);
        $data['tag']        = "CLIENTE,APP";
        $data['file_type']  = null;

        // arquivo
        if ($params['tipo'] == self::MESSAGE_TYPE_FILE) {
            $data['tipo'] = self::MESSAGE_TYPE_FILE;

            $fileParams = $this->validateRequestParamsExistsByArray([
                'file_size' => 'tamanho do arquivo',
                'file_type' => 'tipo do arquivo',
                'file_data' => 'arquivo',
                'file_name' => 'nome do arquivo',
                'adv_id'    => 'código da advocacia',
            ]);

            foreach (['file_size', 'file_type'] as $column) {
                $data[$column] = $fileParams[$column];
            }

            $extArr = explode('.', $fileParams['file_name']);

            if (count($extArr) != 2) {
                $this->throwException('Nome do arquivo inválido');
            }

            if (!in_array($fileParams['file_type'], [self::FILE_TYPE_IMAGE, self::FILE_TYPE_DOC, self::FILE_TYPE_AUDIO])) {
                $this->throwException('Tipo do arquivo inválido');
            }

            $dataDecoded = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '',$fileParams['file_data']));

            $statement = DB::select("SHOW TABLE STATUS LIKE 'chat_mensagem'");
            $nextId = $statement[0]->Auto_increment;

            $AnexoChatStorageModel = new AnexoChatStorageModel;

            if ($AnexoChatStorageModel->isSyncGoogleStorage()) {
                $data['file_path'] = "{$fileParams['adv_id']}/chat_files/{$nextId}";
            } else {
                $data['file_path'] = $nextId;
            }

            if ($fileParams['file_type'] == self::FILE_TYPE_IMAGE) {
                $data['conteudo'] = "FOTO_CAMERA_{$nextId}_{$data['conteudo']}";
            } elseif ($fileParams['file_type'] == self::FILE_TYPE_AUDIO) {
                $data['conteudo'] = "AUDIO_{$nextId}_{$data['conteudo']}.mp3";
            }

            $this->storageFile($AnexoChatStorageModel, $dataDecoded, $fileParams, $data['file_path']);
        }

        return $data;
    }

    private function storageFile($AnexoChatStorageModel, $base64File, $fileParams, $filePath) {
        $pathStorage = $AnexoChatStorageModel->getPathStorage();

        if ($AnexoChatStorageModel->isSyncGoogleStorage()) {
            $GCSController = new GoogleCloudStorageController($AnexoChatStorageModel->getKeyFileGoogleStorage(), $pathStorage);

            $GCSController->storeFile($base64File, $filePath);

            return;
        }

        Storage::disk('local')->put("/chat_files/{$filePath}", $base64File);

        $temporaryFile = $this->laravelStorageDir . $filePath;
        $destinationFile = $pathStorage . "/" . $fileParams['adv_id'] . "/chat_files/" . $filePath;

        $result = rename($temporaryFile, $destinationFile);

        Storage::disk('local')->delete("/chat_files/{$filePath}");
    }

    public function getFile() {
        $err = 0;
        $message = '';

        try {
            $params = $this->validateRequestParamsExistsByArray([
                'adv_id'     => 'Id da advocacia',
                'message_id' => 'Id da mensagem',
            ]);
        } catch (Exception $e) {
            $err = 1;
            $message = $e->getMessage();
        }

        if ($err) {
            return $this->resolveResponse([
                'existe'  => 0,
                'base64'  => null,
                'erro'    => 1,
                'message' => $message,
            ], 401);
        }

        $AnexoChatStorageModel = new AnexoChatStorageModel;

        $params['file_path'] = "{$params['adv_id']}/chat_files/{$params['message_id']}";
        $params['storage_path'] = $AnexoChatStorageModel->getPathStorage();

        if ($AnexoChatStorageModel->isSyncGoogleStorage()) {
            $response = $this->downloadFromGoogle($AnexoChatStorageModel, $params);
        } else {
            $response = $this->downloadFromLocal($params);
        }

        return $this->resolveResponse($response, $response['erro'] == 1 ? 404 : 200);
    }

    private function downloadFromGoogle($AnexoChatStorageModel, $params) {
        $existe = 0;
        $base64 = null;
        $erro = 0;
        $message = 'ok';

        try {
            $GCSController = new GoogleCloudStorageController(
                $AnexoChatStorageModel->getKeyFileGoogleStorage(),
                $AnexoChatStorageModel->getPathStorage()
            );

            $data = $GCSController->downloadFile($params['file_path']);

            if (!$data) {
                throw new Exception('Arquivo não encontrado');
            }

            $base64 = base64_encode($data);
            $existe = 1;
        } catch (Exception $e) {
            $erro = 1;
            $message = $e->getMessage();
        }

        return [
            'existe'  => $existe,
            'base64'  => $base64,
            'erro'    => $erro,
            'message' => $message,
        ];
    }

    private function downloadFromLocal($params) {
        $existe = 0;
        $base64 = null;
        $erro = 0;
        $message = 'ok';

        $nameFile = "{$params['storage_path']}/{$params['file_path']}";

        try {
            if (!file_exists($nameFile)) {
                throw new Exception('Arquivo não encontrado');
            }

            $base64 = base64_encode(file_get_contents($nameFile));
            $existe = 1;
        } catch (Exception $e) {
            $erro = 1;
            $message = $e->getMessage();
        }

        return [
            'existe'  => $existe,
            'base64'  => $base64,
            'erro'    => $erro,
            'message' => $message,
        ];
    }

    private function validateRequestParamsExistsByArray($params) {
        // validando os parâmetros
        foreach ($params as $paramName => $paramAlias) {
            $value = request()->get($paramName);

            if (!$value && $value != 0 || is_null($value)) {
                $this->throwException("Parâmetro {$paramAlias} não definido");
            }

            $params[$paramName] = $value;
        }

        return $params;
    }

    protected function processPaginationAfter($data) {
        $user = $this->getUserFromToken();

        foreach ($data['resultado'] as $index => $message) {
            $data['resultado'][$index]->is_owner = $message->id_usuario == $user->id ? 1 : 0;
            $arrPath = explode('/', $message->file_path);

            if ($message->tipo == self::MESSAGE_TYPE_FILE && count($arrPath) > 0) {
                //$data['resultado'][$index]->file_origin_name = $message->conteudo;
                $data['resultado'][$index]->file_origin_name = $message->conteudo;
                $data['resultado'][$index]->conteudo = $arrPath[count($arrPath) - 1];
            }
        }

        //$data['resultado'] = array_reverse($data['resultado']);

        return $data;
    }

}
