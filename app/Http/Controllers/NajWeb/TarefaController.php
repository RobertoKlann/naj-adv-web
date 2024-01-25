<?php

namespace App\Http\Controllers\NajWeb;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\NajController;
use App\Http\Controllers\NajWeb\AgendaController;
use App\Http\Controllers\NajWeb\TarefaTipoController;
use App\Http\Controllers\NajWeb\TarefaRelAgendaController;
use App\Models\TarefaModel;
use App\Models\PessoaRelacionamentoUsuarioModel;

/**
 * Controllador das tarefas.
 *
 * @since 2020-08-17
 */
class TarefaController extends NajController {

    public function onLoad() {
        $this->setModel(new TarefaModel);
    }

    public function handleItems($model = null) {
        $action = $this->getCurrentAction();
        
        if ($action === NajController::DESTROY_ACTION) {
            $this->destroyItems($model);
            
            return;
        }
        
        $this->{"{$action}Items"}($model);
    }

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
            $pessoaRelacionamentoUsuarioModel = new PessoaRelacionamentoUsuarioModel();
            //Verifica se o supervisor é um usuário do sistema
            $supevisorIsUsuario = $pessoaRelacionamentoUsuarioModel->verificaSePessoaExisteEmPessoaRelUsuario($toStore['codigo_supervisor']);
            if(!$supevisorIsUsuario){
                $code = 400;
                $data = ['mensagem' =>
                    'Não foi possível cadastrar a tarefa pois o supervisor informado não é um usuário do sistema!'
                ];
                $this->rollback();
                return $this->resolveResponse($data, $code);
            }
            //Verifica se o responsável é um usuário do sistema
            $responsavelIsUsuario = $pessoaRelacionamentoUsuarioModel->verificaSePessoaExisteEmPessoaRelUsuario($toStore['codigo_responsavel']);
            if(!$responsavelIsUsuario){
                $code = 400;
                $data = ['mensagem' =>
                    'Não foi possível cadastrar a tarefa pois o responsável informado não é um usuário do sistema!'
                ];
                $this->rollback();
                return $this->resolveResponse($data, $code);
            }
            unset($pessoaRelacionamentoUsuarioModel);

            $statement = DB::select("SHOW TABLE STATUS LIKE 'tarefas'");
            $nextId    = $statement[0]->Auto_increment;

            $data['model'] = $toStore;

            $model = $this->getModel()->newInstance();

            $model->fill($toStore);

            $result = $model->save();

            if (is_string($result)) {
                $this->throwException('Erro ao inserir o registro. ' . $result);
            }

            $data['model']['id'] = $nextId;

            $this->handleItems($model);

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

    public function storeItems($model) {

        //validando se é para fazer os relacionamentos na AGENDA e TAREFA_REL_AGENDA, isso ta no SYS_CONFIG
        if(!$this->isStoreCompromissoTarefa()) return;

        $bUpdate   = false;
        $id_tarefa = $this->getModel()->max('id');

        if($model->data_prazo_interno) {
            $local = '[TAREFA CÓDIGO: ' . $id_tarefa . '] PRAZO INTERNO';
            $codigoAgenda = $this->callStoreAgenda($model, request()->get('data_hora_compromisso_interno'), $local);
            $this->callStoreTarefaRelAgenda($codigoAgenda, $id_tarefa);
            $bUpdate = true;
        }

        if($model->data_prazo_fatal) {
            $local = '[TAREFA CÓDIGO: ' . $id_tarefa . '] PRAZO FATAL';
            $codigoAgenda = $this->callStoreAgenda($model, request()->get('data_hora_compromisso_fatal'), $local);

            //DESCOBRINDO SE É PARA FAZER UM UPDATE OU STORE NO RELACIONAMENTO
            if($bUpdate) {
                $this->callUpdateTarefaRelAgenda($codigoAgenda, $id_tarefa);
            } else {
                $this->callStoreTarefaRelAgenda($codigoAgenda, $id_tarefa, true);
            }
        }
    }

    private function callStoreAgenda($Tarefa, $data_hora_compromisso, $local) {
        $AgendaController     = new AgendaController();
        $max                  = $AgendaController->getModel()->max('ID') + 1;

        $TarefaTipoController = new TarefaTipoController();
        $TarefaTipo           = $TarefaTipoController->getModel()->find($Tarefa->id_tipo);

        $AgendaController->store([
            'ID'                    => $max,
            'CODIGO_DIVISAO'        => $Tarefa->codigo_divisao,
            'CODIGO_TIPO'           => $TarefaTipo->CODIGO_TIPO_COMPROMISSO,
            'CODIGO_USUARIO'        => $Tarefa->codigo_usuario_criacao,
            'CODIGO_PESSOA'         => $Tarefa->codigo_responsavel,
            'CODIGO_PROCESSO'       => NULL,
            'DATA_HORA_INCLUSAO'    => $Tarefa->data_hora_criacao,
            'DATA_HORA_COMPROMISSO' => $data_hora_compromisso,
            'LOCAL'                 => $local,
            'ASSUNTO'               => $Tarefa->descricao,
            'ALTERACAO'             => 2,
            'SITUACAO'              => 'A',
            'PRIVADO'               => 'N'
        ]);

        return $max;
    }

    private function callStoreTarefaRelAgenda($codigoAgenda, $idTarefa, $isCompromissoFatal = false) {
        $TarefaRelAgendaController = new TarefaRelAgendaController();
        $TarefaRelAgendaController->store([
            'ID_COMPROMISSO_PRAZO_INTERNO' => ($isCompromissoFatal) ? null : $codigoAgenda,
            'ID_COMPROMISSO_PRAZO_FATAL'   => ($isCompromissoFatal) ? $codigoAgenda : null,
            'ID_TAREFA'                    => $idTarefa,
        ]);
    }

    private function callUpdateTarefaRelAgenda($codigoAgenda, $idTarefa) {
        $TarefaRelAgendaController         = new TarefaRelAgendaController();
        $model                             = $TarefaRelAgendaController->getModel()->find($idTarefa);
        $model->ID_COMPROMISSO_PRAZO_FATAL = $codigoAgenda;

        $model->save();
    }

    private function isStoreCompromissoTarefa() {
        $data = DB::table('sys_config')->where(['SECAO' => 'TAREFAS', 'CHAVE' => 'AGENDA_INTEGRADA'])->limit(1)->first();

        if(isset($data->VALOR)) {
            $data = $data->VALOR;
        } else {
            return false;
        }

        if($data == 'SIM' || $data == 'sim' || $data == 'Sim') return true;

        return false;
    }

    public function updateItems($model) {}

    public function destroyItems($model) {}

}