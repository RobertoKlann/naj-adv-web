<?php

namespace App\Http\Controllers\NajWeb;

use App\Models\UsuarioModel;
use App\Models\MonitoramentoSistemaModel;
use App\Http\Controllers\NajWeb\MonitoramentoSistemaController;

/**
 * Controller do monitoramento do sistema da rotina de dispositivos de usuários.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      18/12/2020
 */
class UsuarioDispositivoMonitoramentoSistemaController extends MonitoramentoSistemaController {

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

        $this->nomeRotina = 'Usuário Dispositivo';
        $this->nomeModulo = 'Usuarios';
    }

    protected function getDescriptionActionUpdate($key, $updateData) {
        $UsuarioModel = new UsuarioModel;
        $model        = json_decode(base64_decode($key));
        $Usuario      = $UsuarioModel->find($model->usuario_id);
        $modelo       = request()->get('modelo');
        $versao_so    = request()->get('versao_so');

        $description = "Alteração no Dispositivo do Usuário: {$Usuario->id} - {$Usuario->nome}, Dispositivo [{$model->id}] - Modelo [$modelo] - Versão [$versao_so] ";

        foreach ($updateData as $updateColumn => $updateValue) {
            $description.= "Campo: {$updateColumn} Alterado de: [{$updateValue['before']}] Para: [{$updateValue['now']}] ";
        }

        return $description;
    }

}