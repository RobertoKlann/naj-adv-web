<?php

namespace App\Http\Controllers\NajWeb;

use App\Exceptions\NajException;
use Illuminate\Support\Facades\DB;
use App\Models\ChatMensagemModel;
use App\Http\Controllers\NajController;
use Exception;

/**
 * Controller do chat mensagem.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      13/07/2020
 */
class ChatMensagemController extends NajController {

    public function onLoad() {
        $this->setModel(new ChatMensagemModel);
    }

    protected function resolveWebContext($usuarios, $code) {}

    public function handleItems($model = null) {
        $action = $this->getCurrentAction();
        
        if ($action === NajController::DESTROY_ACTION) {
            $this->destroyItems($model);
            
            return;
        }
        
        $this->{"{$action}Items"}($model);
    }

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

            if (empty($toStore)) {
                throw new NajException('Empty model.');
            }

            $statement = DB::select("SHOW TABLE STATUS LIKE 'chat_mensagem'");
            $nextId = $statement[0]->Auto_increment;

            $data['model'] = array_merge(['id' => $nextId], $toStore);

            $model = $this->getModel()->newInstance();

            $model->fill($toStore);

            $result = $model->save();

            if (is_string($result)) {
                $this->throwException('Erro ao inserir o registro. ' . $result);
            }

            $this->handleItems($model);

            //verificando se precisa registrar o monitoramento
            if($this->getMonitoramentoController()) {
                $this->getMonitoramentoController()->storeMonitoramento(self::STORE_ACTION, $model);
            }

            $this->commit();

            $data['persisted'] = $model;
        } catch (Exception $e) {
            $code = 400;

            $data = ['mensagem' =>
                $this->extractMessageFromException($e)
            ];

            $this->rollback();
        }

        return $this->resolveResponse($data, $code);
    }

    /**
     *
     * @return type
     */
    public function storeMessageAnexo($attrs = null) {
        $code = 200;

        $data = ['mensagem' => 'Registro inserido com sucesso.', 'model' => null];

        try {
            $toStore = $this->resolveValidate(
                $this->getModel()->getFilledAttributes($attrs)
            );

            $data['model'] = $toStore;

            $model = $this->getModel()->newInstance();
            
            $statement = DB::select("SHOW TABLE STATUS LIKE 'chat_mensagem'");
            $nextId = $statement[0]->Auto_increment;

            $conf = DB::select("
                SELECT *
                  FROM sys_config
                 WHERE TRUE
                   AND secao = 'SYNC_FILES'
                   AND chave = 'SYNC_STORAGE'
            ");

            if(is_array($conf) && $conf[0]->VALOR == 'GOOGLE_STORAGE') {
                $toStore['file_path'] = "{$attrs['id_cliente']}/chat_files/{$nextId}";
            } else {
                $toStore['file_path'] = $nextId;
            }

            $data['model'] = array_merge(['id' => $nextId], $toStore);
            $data['model']['id'] = $nextId;

            $model->fill($toStore);

            $result = $model->save();

            if (is_string($result)) {
                $this->throwException('Erro ao inserir o registro. ' . $result);
            }

            $this->handleItems($model);
            
        } catch (Exception $e) {
            $code = 400;

            $data = ['mensagem' =>
                $this->extractMessageFromException($e)
            ];
        }

        return $this->resolveResponse($data, $code);
    }
    
    public function storeItems($model) {
        $this->callStoreChatMensagemStatus($model);
        $this->callStoreChatAtendimentoRelMensagem($model);
    }

    public function updateItems($model) {}

    public function destroyItems($model) {}

    /**
     * Busca todas as mensagens da advocacia.
     */
    public function allMessages() {
        return response()->json(['data' => $this->getModel()->allMessages()]);
    }

    public function allMessagesChat($id) {
        return response()->json(['data' => $this->getModel()->allMessagesChat($id)]);
    }

    private function callStoreChatMensagemStatus($model) {
        $UltimaMensagem = $this->getModel()->getLastMessageByUserAndChat($model['id_usuario'], $model['id_chat']);
        $ChatMensagemStatusController = new ChatMensagemStatusController();
        $ChatMensagemStatusController->store([
            "id_mensagem"      => $UltimaMensagem[0]->id,
            "status"           => 1,
            "status_data_hora" => $model['data_hora']
        ]);
    }

    private function callStoreChatAtendimentoRelMensagem($model) {
        //se não tem o id do atendimento so volta
        if(!request()->get('id_atendimento')) return;

        $UltimaMensagem = $this->getModel()->getLastMessageByUserAndChat($model['id_usuario'], $model['id_chat']);
        $ChatAtendimentoRelMensagemController = new ChatAtendimentoRelacionamentoMensagemController();
        $ChatAtendimentoRelMensagemController->store([
            "id_mensagem"    => $UltimaMensagem[0]->id,
            "id_atendimento" => request()->get('id_atendimento')
        ]);
    }

    /**
     * Busca todas as mensagens do chat publico passado por parâmetro.
     */
    public function getAllMensagensChatPublico($id) {
        $data = $this->getModel()->getAllMensagensChatPublico($id);

        return response()->json(['data' => $data['data'], 'isLastPage' => $data['isLastPage'], 'dados_dispositivos' => $data['dados_dispositivos']]);
    }

    public function newMessagesFromChat($id) {
        $data = $this->getModel()->newMessagesFromChat($id);

        return response()->json([
            'data' => $data['data'],
            'dados_dispositivos' => $data['dados_dispositivos'],
            'messagesReadCurrentChat' => $data['messagesReadCurrentChat'],
        ]);
    }

    public function oldMessagesFromChat($id) {
        $data = $this->getModel()->oldMessagesFromChat($id);

        return response()->json(['data' => $data['data'], 'dados_dispositivos' => $data['dados_dispositivos']]);
    }

}