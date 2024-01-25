<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\AtividadeProcessoModel;

/**
 * Controllador das atividades do processo.
 *
 * @since 2020-12-23
 */
class AtividadeProcessoController extends NajController {

    public function onLoad() {
        $this->setModel(new AtividadeProcessoModel);
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
     *Sobreescreve método da clase mãe para que posssamos armazenar valores nulos
     * 
     * @param string $key
     * @return response
     */
    public function update($key) {
        $this->setCurrentAction(self::UPDATE_ACTION);

        $this->begin();

        $code = 200;

        $data = ['mensagem' => 'Registro alterado com sucesso.', 'model' => null];

        try {
            $toUpdate = $this->resolveValidate(
                $this->getModel()->getFilledAttributesWithoutKey()
            );

            $model = $this->getModel()->newInstanceFromKey($key);

            $totalUpdate   = 0;

            //utilizado no monitoramento
            $columnsUpdate = [];

            foreach ($toUpdate as $updateColumn => $updateValue) {
                if (trim($model->$updateColumn) !== trim($updateValue)) {

                    //adicionando no array de colunas alteradas a informação de como era e como ficou
                    $columnsUpdate[$updateColumn] = [
                        'before' => $model->$updateColumn,
                        'now'    => trim($updateValue)
                    ];

                    $model->$updateColumn = $updateValue;

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
}