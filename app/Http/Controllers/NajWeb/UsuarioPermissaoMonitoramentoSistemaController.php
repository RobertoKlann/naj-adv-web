<?php

namespace App\Http\Controllers\NajWeb;

use App\Models\UsuarioModel;
use App\Models\MonitoramentoSistemaModel;
use App\Http\Controllers\NajWeb\MonitoramentoSistemaController;

/**
 * Controller do monitoramento do sistema da rotina de permissão de usuários.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      18/12/2020
 */
class UsuarioPermissaoMonitoramentoSistemaController extends MonitoramentoSistemaController {

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

        $this->nomeRotina = 'Permissões';
        $this->nomeModulo = 'Usuarios';
    }

    protected function getDescriptionActionStore($pessoaCodigo) {
        return "Incluído Permissões para a Pessoa: {$pessoaCodigo}.";
    }

    protected function getDescriptionActionUpdate($pessoaCodigo, $updateData) {
        $description = "Alterou as Permissões para a Pessoa: {$pessoaCodigo}. Divisão: {$updateData['divisao']}";

        foreach ($updateData as $updateColumn => $updateValue) {
            if(!isset($updateValue['modulo'])) continue;

            $description.= " Modulo: {$updateValue['modulo']} ({$updateColumn} = ) [{$updateValue['now']}].";
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