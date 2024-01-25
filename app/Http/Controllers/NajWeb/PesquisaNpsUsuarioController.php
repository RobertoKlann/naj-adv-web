<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\PesquisaNpsUsuarioModel;
use App\Models\UsuarioNpsModel;
use Illuminate\Support\Facades\DB;

/**
 * Controller da pesquisa NPS usuários.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      27/04/2021
 */
class PesquisaNpsUsuarioController extends NajController {

    public function onLoad() {
        $this->setModel(new PesquisaNpsUsuarioModel);
    }

    /**
     *
     * @return type
     */
    public function store($attrs = null) {
        $this->setCurrentAction(self::STORE_ACTION);

        $this->begin();

        $code = 200;

        $data = ['mensagem' => 'Registro inserido com sucesso.', 'model' => null, 'user_invalid' => []];

        $users = request()->get('users');
        $dataHour = date('Y-m-d H:i:s');

        foreach($users as $user) {
            try {
                $toStore = $this->resolveValidate(
                    $this->getModel()->getFilledAttributes($attrs)
                );

                $toStore['id_usuario'] = $user['id'];
                $toStore['data_hora_inclusao'] = $dataHour;
                $toStore['data_hora_exibicao'] = $dataHour;
                $toStore['status'] = 'P';

                //Se não validou passa para o proximo cara
                if(!$this->validateStoreCustom($toStore)) {
                    $data['user_invalid'][] = $user['id'];
                    continue;
                }
    
                if (empty($toStore)) {
                    throw new NajException('Empty model.');
                }
    
                $data['model'] = $toStore;
    
                $model = $this->getModel()->newInstance();
    
                $model->fill($toStore);
    
                $result = $model->save();
    
                if (is_string($result)) {
                    $this->throwException('Erro ao inserir o registro. ' . $result);
                }
    
                $this->handleItems($model);
    
                $data['model'] = $this->beforeStore($model);
    
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
        }

        return $this->resolveResponse($data, $code);
    }

    public function validateStoreCustom($data) {
        //Não deixa incluir o usuário caso ele já foi relacionado na pesquisa
        $hasRelationship = $this->getModel()->userHasRelationshipWithSearch($data['id_pesquisa'], $data['id_usuario']);

        //Se achou é por que já foi relacionado então não deixa relacionar novamente
        if(is_array($hasRelationship) && count($hasRelationship) > 0)
            return false;

        return true;
    }

    public function paginateUsuarios() {
        return $this->processPaginationAfter(
            (new UsuarioNpsModel)->makePagination()
        );
    }

    public function pendentesNotRead($pesquisa) {
        return response()->json($this->getModel()->pendentesNotRead(json_decode(base64_decode($pesquisa))));
    }

    public function updateLido() {
        $response = request()->get('keys');

        foreach($response as $model) {
            DB::update('UPDATE pesquisa_respostas set lido = ? where id = ?', ['S', $model['id']]);
        }
    }
    
}