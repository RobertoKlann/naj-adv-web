<?php

namespace App\Http\Controllers\NajWeb;

use App\Models\UsuarioModel;
use App\Models\MonitoramentoSistemaModel;
use App\Http\Controllers\NajWeb\MonitoramentoSistemaController;

/**
 * Controller do monitoramento do sistema da rotina de relacionamentos de usuários.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      18/12/2020
 */
class UsuarioRelacionamentoMonitoramentoSistemaController extends MonitoramentoSistemaController {

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

        $this->nomeRotina = 'Usuários Relacionamentos';
        $this->nomeModulo = 'Usuarios';
    }

    protected function getDescriptionActionStore($model) {
        $UsuarioModel = new UsuarioModel;
        $Usuario      = $UsuarioModel->find($model['usuario_id']);

        return "Incluído o Relacionamento para o Usuário: {$Usuario->id} - {$Usuario->nome}, Relacionamento: [{$model['pessoa_codigo']}].";
    }

    protected function getDescriptionActionUpdate($model, $updateData) {
        $UsuarioModel = new UsuarioModel;
        $Usuario      = $UsuarioModel->find($model['usuario_id']);

        $description = "Alteração no Relacionamento do Usuário: {$Usuario->id} - {$Usuario->nome}, Relacionamento [{$model['pessoa_codigo']}]. ";

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