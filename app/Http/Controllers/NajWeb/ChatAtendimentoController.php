<?php

namespace App\Http\Controllers\NajWeb;

use Auth;
use App\Models\ChatAtendimentoModel;
use App\Models\UsuarioModel;
use App\Models\ChatRelacionamentoUsuarioModel;
use App\Http\Controllers\NajController;
use App\Http\Controllers\NajWeb\ChatController;
use App\Http\Controllers\NajWeb\AnexoChatStorageController;

/**
 * Controller do chat atendimento.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      13/07/2020
 */
class ChatAtendimentoController extends NajController {

    public function onLoad() {
        $this->setModel(new ChatAtendimentoModel);
    }

    protected function resolveWebContext($usuarios, $code) {}

    /**
     *
     * @return type
     */
    public function store($attrs = null) {
        $this->setCurrentAction(self::STORE_ACTION);

        $this->begin();

        $code = 200;

        $data = ['mensagem' => 'Registro inserido com sucesso.', 'model' => null];

        try {
            $toStore = $this->resolveValidate(
                $this->getModel()->getFilledAttributes($attrs)
            );

            $data['model'] = $toStore;

            $model = $this->getModel()->newInstance();

            $model->fill($toStore);

            $result = $model->save();

            if (is_string($result)) {
                $this->throwException('Erro ao inserir o registro. ' . $result);
            }

            $this->handleItems($model);

            //GEITINHO BRASILEIRO ESSA LINHA DE CODIGO ABAIXO :D KAKAKAKAKAKAKAK OBS: RINDO DE MEDO
            $toStore['id'] = $this->getModel()->getLastAtendimentoByUserAndChat($model['id_usuario'], $model['id_chat'])[0]->id;
            $data['model'] = $toStore;

            $this->commit();
        } catch (Exception $e) {
            $code = 400;

            $data = ['mensagem' =>
                $this->extractMessageFromException($e)
            ];

            $this->rollback();
        }

        return $this->resolveResponse($data, $code);
    }

    public function update($key) {
        $this->setCurrentAction(self::UPDATE_ACTION);

        $this->begin();

        $code = 200;

        $data = ['mensagem' => 'Registro alterado com sucesso.', 'model' => null];

        try {
            $toUpdate = $this->resolveValidate(
                $this->getModel()->getFilledAttributesWithoutKey()
            );

            if(isset($toUpdate['error']))
                return $this->resolveResponse(['message' => 'Aguarde alguns segundos ou atualize a página, estamos carregando as mensagens não lidas!'], 422);

            $model = $this->getModel()->newInstanceFromKey($key);

            if(!$model) {
                $atendimento = $this->getModel()->hasAtendimentoOpen(request()->get('chat'));
                if($atendimento) {
                    $model = $this->getModel()->newInstanceFromKey(base64_encode(json_encode(['id' => $atendimento[0]->id])));
                }
            }

            $totalUpdate   = 0;

            //utilizado no monitoramento
            $columnsUpdate = [];

            foreach ($toUpdate as $updateColumn => $updateValue) {
                if (trim($model->$updateColumn) !== trim($updateValue)) {
                    
                    //verificando se precisa registrar o monitoramento
                    if($this->getMonitoramentoController()) {
                        //adicionando no array de colunas alteradas a informação de como era e como ficou
                        $columnsUpdate[$updateColumn] = [
                            'before' => $model->$updateColumn,
                            'now'    => trim($updateValue)
                        ];
                    }

                    $model->$updateColumn = trim($updateValue);

                    $totalUpdate++;
                }
            }

            if ($totalUpdate === 0) {
                $this->throwException('Nenhuma alteração encontrada.');
            }

            $result = $model->save();

            if (is_string($result)) {
                $this->throwException('Erro ao atualizar o registro. ' . $result);
            }

            $this->handleItems($model);

            //verificando se precisa registrar o monitoramento
            if($this->getMonitoramentoController()) {
                $this->getMonitoramentoController()->storeMonitoramento(self::UPDATE_ACTION, $model, $columnsUpdate);
            }

            $this->commit();

            $data['model'] = $model;
        } catch (Exception $e) {
            $code = 400;

            $data = ['mensagem' =>
                $this->extractMessageFromException($e)
            ];

            $this->rollback();
        }

        return $this->resolveResponse($data, $code);
    }

    public function handleItems($model = null) {
        $action = $this->getCurrentAction();
        
        if ($action === NajController::DESTROY_ACTION) {
            $this->destroyItems($model);
            
            return;
        }
        
        $this->{"{$action}Items"}($model);
    }

    public function storeItems($model) {
        //GEITINHO BRASILEIRO ESSA LINHA DE CODIGO ABAIXO :D KAKAKAKAKAKAKAK
        $this->model->id = $this->getModel()->getLastAtendimentoByUserAndChat($model['id_usuario'], $model['id_chat']);

        //Se tiver o ID do chat, não tiver data de termino e o STATUS for Em aberto seta como lido a mensagem imediatamente
        if($model['id_chat'] && !$model['data_hora_termino'] && $model['status'] == 0) {
            $messageRead = (new ChatMensagemController)->getModel()->setStatusMensagemLida($model['id_chat']);
        }
    }

    public function updateItems($model) {}

    public function novoAtendimento() {
        $usuarios  = request()->get('usuarios');
        $mensagem  = request()->get('mensagem');
        $data_hora = request()->get('data_hora');
        $data      = request()->get('data');
        $tag      = request()->get('tag');
        $user_with_atendimento = [];
        $dataResponse = [];

        foreach($usuarios as $usuario) {
            $newUser = is_array($usuario) ? $usuario['id'] : $usuario;
            $chat = $this->hasChat($newUser);

            //Verificando se tem um chat para o usuário já
            if(isset($chat['id'])) {

                //Não pode iniciar um novo atendimento caso já exista um aberto para o usuário
                if($this->hasAtendimentoOpen($chat['id_chat'])) {
                    $UsuarioModel = new UsuarioModel();
                    $user_with_atendimento[] = $UsuarioModel->where('id', $newUser)->first()->getOriginal();
                    continue;
                }
                
                //DESDE O DIA 21/12/2020 O NELSON DECIDIU NÃO CRIAR MAIS ATENDIMENTO
                //DEPOIS DESDE O DIA 05/03/2021 O NELSON DECIDIU CRIAR COM DATA DE TERMINO E INICIO IGUAIS MAS NÃO CRIANDO MENSAGEM
                $chat_atendimento = $this->store([
                    'id_chat'           => $chat['id_chat'],
                    'id_usuario'        => Auth::user()->id,
                    'data_hora_inicio'  => $data_hora,
                    'data_hora_termino' => $data_hora,
                    'status'            => 1
                ]);

                request()->merge(['id_atendimento' => $chat_atendimento->original['model']['id']]);

                //INCLUINDO A MENSAGEM DE INICIO DO ATENDIMENTO
                // $this->storeChatMensagem([
                //     'id_chat'    => $chat['id_chat'],
                //     'id_usuario' => Auth::user()->id,
                //     'conteudo'   => Auth::user()->nome . ' - Iniciou o atendimento',
                //     'tipo'       => 0,
                //     'data_hora'  => $data_hora
                // ]);

                if($mensagem) {
                    //INCLUINDO A MENSAGEM QUE O CARA DIGITOU
                    $this->storeChatMensagem([
                        'id_chat'    => $chat['id_chat'],
                        'id_usuario' => Auth::user()->id,
                        'conteudo'   => $mensagem,
                        'tipo'       => 0,
                        'data_hora'  => $data_hora,
                        'tag'        => $tag
                    ]);
                }

                $UsuarioModel = new UsuarioModel();
                $user = $UsuarioModel->where('id', $newUser)->first()->getOriginal();

                $objectResponse = new \stdClass;
                $objectResponse->chat    = $chat['id_chat'];
                $objectResponse->usuario = $newUser;
                $objectResponse->nome    = $user['nome'];
                $objectResponse->apelido = $user['apelido'];

                $dataResponse[] = $objectResponse;

                if(count(request()->get('files')) > 0) {
                    //INCLUINDO OS ANEXOS
                    $this->callStoreAnexos($newUser, $chat['id_chat']);
                }

            } else {
                request()->merge(['id_usuario' => $newUser]);
                $chat_store = $this->storeChat();

                //DESDE O DIA 21/12/2020 O NELSON DECIDIU NÃO CRIAR MAIS ATENDIMENTO
                //DEPOIS DESDE O DIA 05/03/2021 O NELSON DECIDIU CRIAR COM DATA DE TERMINO E INICIO IGUAIS MAS NÃO CRIANDO MENSAGEM

                $chat_atendimento = $this->store([
                    'id_chat'          => $chat_store['model']['id'],
                    'id_usuario'       => Auth::user()->id,
                    'data_hora_inicio'  => $data_hora,
                    'data_hora_termino' => $data_hora,
                    'status'            => 1
                ]);

                request()->merge(['id_atendimento' => $chat_atendimento->original['model']['id']]);

                //INCLUINDO A MENSAGEM DE INICIO DO ATENDIMENTO
                // $this->storeChatMensagem([
                //     'id_chat'    => $chat_store['model']['id'],
                //     'id_usuario' => Auth::user()->id,
                //     'conteudo'   => Auth::user()->nome . ' - Iniciou o atendimento',
                //     'tipo'       => 0,
                //     'data_hora'  => $data_hora
                // ]);

                if($mensagem) {
                   //INCLUINDO A MENSAGEM QUE O CARA DIGITOU
                    $this->storeChatMensagem([
                        'id_chat'    => $chat_store['model']['id'],
                        'id_usuario' => Auth::user()->id,
                        'conteudo'   => $mensagem,
                        'tipo'       => 0,
                        'data_hora'  => $data_hora,
                        'tag'        => $tag
                    ]); 
                }

                $UsuarioModel = new UsuarioModel();
                $user = $UsuarioModel->where('id', $newUser)->first()->getOriginal();

                $objectResponse = new \stdClass;
                $objectResponse->chat    = $chat_store['model']['id'];
                $objectResponse->usuario = $newUser;
                $objectResponse->nome    = $user['nome'];
                $objectResponse->apelido = $user['apelido'];

                $dataResponse[] = $objectResponse;

                if(request()->get('files')) {
                    //INCLUINDO OS ANEXOS
                    $this->callStoreAnexos($newUser, $chat_store['model']['id']);
                }
            }
        }

        return response()->json(['hasAtendimento' => $user_with_atendimento, 'data_response' => $dataResponse], 200);
    }

    /**
     * Retorna se tem chat para o usuário.
     * 
     * @return boolean
     */
    public function hasChat($id) {
        $ChatRelUsuarioModel = new ChatRelacionamentoUsuarioModel();
        $chat = $ChatRelUsuarioModel->where('id_usuario', $id)->first();

        if(is_null($chat)) {
            return false;
        }

        return $chat->getOriginal();
    }

    public function storeChat() {
        $ChatController = new ChatController();
        $max = $ChatController->getModel()->max('id') + 1;
        $nome = '#PUBLICO_' . $max;

        $chat = $ChatController->store([
            'data_inclusao' => request()->get('data_hora'),
            'tipo'          => 0,
            'nome'          => $nome
        ]);

        $chat->original['model']['id'] = $ChatController->getModel()->max('id');
        return $chat->original;
    }

    private function storeChatMensagem($parametros) {
        $ChatMensagemController = new ChatMensagemController();
        $ChatMensagemController->store($parametros);
    }

    /**
     * Retorn se tem um atendimento aberto para o chat.
     * 
     * @return boolean
     */
    private function hasAtendimentoOpen($id) {
        $atendimento = $this->getModel()->hasAtendimentoOpen($id);

        return isset($atendimento[0]->id);
    }

    private function callStoreAnexos($id_usuario, $id_chat) {
        $ChatMensagemController     = new ChatMensagemController();
        $AnexoChatStorageController = new AnexoChatStorageController();
        $files                      = request()->get('files');

        try {
            $ChatMensagemController->setCurrentAction(self::STORE_ACTION);
            $ChatMensagemController->begin();
            foreach($files as $oFile) {
                $oFile['id_chat'] = $id_chat;
                $model = $ChatMensagemController->storeMessageAnexo($oFile);

                $AnexoChatStorageController->callStoreFile($oFile, $model->original['model']['file_path']);
            }
        } catch(Exception $e) {
            $ChatMensagemController->rollback();

            return response()->json(['status_code' => 400, 'mensagem' => 'Não foi possível enviar os anexos, tente novamente mais tarde.']);
        }

        $ChatMensagemController->commit();
    }

    public function validateStore($data) {
        if($this->hasAtendimentoOpen($data['id_chat'])) {
            $this->throwException('Este chat já está sendo atendindido por alguém, atualize a página!');
        }

        return $data;
    }

    public function validateUpdate($data) {
        if($this->hasMessagesNotRead($data['id_chat']))
            return ['error' => true];

        return $data;
    }

    private function hasMessagesNotRead($chatId) {
        return (new ChatMensagemController)->getModel()->hasMessagesNotRead($chatId);
    }

}