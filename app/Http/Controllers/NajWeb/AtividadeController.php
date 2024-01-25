<?php

namespace App\Http\Controllers\NajWeb;

use App\Exceptions\NajException;
use App\Http\Controllers\NajController;
use App\Models\AtividadeModel;
use Exception;

/**
 * Controller de Atividades.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Klann
 * @since      27/11/2023
 */
class AtividadeController extends NajController {

    public function onLoad() {
        $this->setModel(new AtividadeModel);
    }

    /**
     * Index da rota de atividades.
     */
    public function index() {
        return view('najWeb.consulta.AtividadeConsultaView')->with('is_atividades', true);
    }

    /**
     * Retorna o max(id) no BD 
     * @return integer
     */
    public function proximo() {
        $proximo = $this->getModel()->max('CODIGO');
        if(!$proximo){
            $proximo = 0;
        }
        return response()->json($proximo)->content();
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

            // dd($toStore);

            if (empty($toStore))
                throw new NajException('Empty model.');

            $data['model'] = $toStore;

            $model = $this->getModel()->newInstance();

            $model->fill($toStore);
            $model->CODIGO = $this->getModel()->max('CODIGO') + 1;

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
     * Obtêm todos os registros de Atividade Tipo
     * 
     * @return JSON
     */
    public function getAllAtividadesTipos() {
        return response()->json($this->getModel()->getAllAtividades());
    }
    

    /**
     * Busca o nome de todos os termos do BD
     * 
     * @return array
     */
    public function buscaNomeDonoAtividade(){
        $atividadeModel = new AtividadeModel();
        return $atividadeModel->buscaNomeDonoAtividade();
    }
}