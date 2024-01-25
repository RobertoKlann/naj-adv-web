<?php

namespace App\Http\Controllers\NajWeb;

use App\Models\MonitoramentoSistemaModel;
use App\Http\Controllers\NajWeb\MonitoramentoSistemaController;

/**
 * Controller do monitoramento do sistema da rotina de usuários.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      16/12/2020
 */
class UsuarioMonitoramentoSistemaController extends MonitoramentoSistemaController {

    /**
     * Nome da rotina.
     */
    public $nomeRotina;

    /**
     * Nome do modulo dessa rotina.
     */
    public $nomeModulo;

    public function onLoad() {
        $this->setModel(new MonitoramentoSistemaModel);

        $this->nomeRotina = 'Usuarios';
        $this->nomeModulo = 'Usuarios';
    }

    protected function getDescriptionActionStore($model) {
        return "Incluído o Cadastro: {$model['id']} - {$model['nome']}";
    }

    protected function getDescriptionActionUpdate($model, $updateData) {
        $description = "Alteração no Cadastro: {$model['id']} - {$model['nome']}. ";

        foreach ($updateData as $updateColumn => $updateValue) {
            $description.= "Campo: {$updateColumn} Alterado de: [{$updateValue['before']}] Para: [{$updateValue['now']}] ";
        }

        return $description;
    }
    
    protected function getDescriptionActionDestroy($model) {
        return sprintf(
            '%s dados na rotina %s',
            $this->getAcaoCurrent($action),
            $this->nomeRotina
        );
    }

}