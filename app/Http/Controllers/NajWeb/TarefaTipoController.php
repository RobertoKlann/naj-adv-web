<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Http\Controllers\NajWeb\AgendaTipoCompromissoController;
use App\Models\TarefaTipoModel;

/**
 * Controllador do tipos de tarefas.
 *
 * @since 2020-08-12
 */
class TarefaTipoController extends NajController {

    public function onLoad() {
        $this->setModel(new TarefaTipoModel);
    }

    public function validateStore($data) {
        $AgendaTipoCompromissoController = new AgendaTipoCompromissoController();
        $model = $AgendaTipoCompromissoController->getModel()->where('DESCRICAO', $data['TIPO'])->first();

        if(is_null($model)) {
            $AgendaTipoCompromissoController->store(
              [
                  'CODIGO'    => $AgendaTipoCompromissoController->getModel()->max('CODIGO') + 1,
                  'DESCRICAO' => $data['TIPO']
              ]
            );

            $model = $AgendaTipoCompromissoController->getModel()->where('DESCRICAO', $data['TIPO'])->first();
            $data['CODIGO_TIPO_COMPROMISSO'] = $model->getOriginal()['CODIGO'];
        } else {
            $data['CODIGO_TIPO_COMPROMISSO'] = $model['CODIGO'];
        }

        return $data;
    }

    public function handleItems($model = null) {
        $action = $this->getCurrentAction();
        
        if ($action === NajController::DESTROY_ACTION) {
            $this->destroyItems($model);
            
            return;
        }
        
        $this->{"{$action}Items"}($model);
    }

    public function updateItems($model) {
        $AgendaTipoCompromissoController = new AgendaTipoCompromissoController();
        $TipoCompromisso                 = $AgendaTipoCompromissoController->getModel()->find($model->CODIGO_TIPO_COMPROMISSO);
        $TipoCompromisso->DESCRICAO      = $model->TIPO;

        $TipoCompromisso->save();
    }

    public function storeItems($model) {}

    public function destroyItems($model) {}

}