<?php

namespace App\Http\Controllers\NajWeb;

use App\Models\MonitoramentoSistemaModel;
use App\Http\Controllers\NajController;

/**
 * Controller do monitoramento do sistema.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      17/12/2020
 */
class MonitoramentoSistemaController extends NajController {

    public function onLoad() {
        $this->setModel(new MonitoramentoSistemaModel);
    }

    /**
     * Aqui faz toda magica necessário para fazer o store
     */
    public function storeMonitoramento($action, $model = null, $updateData = null) {
        $a = 1;
        //montando os atributos para o INSERT
        $atributos = [
            'id_modulo'      => $this->getModel()->getIdModulo($this->nomeModulo),
            'codigo_divisao' => 1,
            'codigo_usuario' => $this->getModel()->getCodigoPessoa(),
            'data_hora'      => date('Y-m-d H:i:s'),
            'acao'           => $this->getDescriptionAction($action, $model, $updateData)
        ];

        $this->store($atributos);
    }

    /**
     * Monta a descrição da coluna ACAO, ou seja, monta a descrição do que aconteceu.
     * 
     * OBS: Se for necessário fazer alguma alteração especifica na descrição da coluna ACAO é só sobreescrever esse cara.
     */
    protected function getDescriptionAction($action, $model = null, $updateData = null) {
        switch($action) {
            case self::STORE_ACTION:
                return $this->getDescriptionActionStore($model);

            case self::UPDATE_ACTION:
                return $this->getDescriptionActionUpdate($model, $updateData);

            case self::DESTROY_ACTION:
                return $this->getDescriptionActionDestroy($model);

            case self::PAGINATE_ACTION:
                return $this->getDescriptionActionPaginate($model);

            case self::INDEX_ACTION:
                return $this->getDescriptionActionIndex($model);

            default:
                break;
        }
        
    }

    protected function getDescriptionActionStore($model) {
        return "Incluído dados na rotina {$this->nomeRotina}.";
    }

    protected function getDescriptionActionUpdate($model, $updateData) {
        return "Alteração dados na rotina {$this->nomeRotina}.";
    }
    
    protected function getDescriptionActionDestroy($model) {
        return "Exclui dados na rotina {$this->nomeRotina}.";
    }

    protected function getDescriptionActionPaginate($model) {
        return "Pesquisou por dados na rotina {$this->nomeRotina}";
    }

    protected function getDescriptionActionIndex($model) {
        $ipCliente = $_SERVER['REMOTE_ADDR'];

        return "Acesso ao Sistema NAJ-Web. IP Cliente: {$ipCliente}. Navegador: {$this->getBrowserUsedByUser()}";
    }

    protected function getBrowserUsedByUser() {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $navegador = 'Não identificado.';

        if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) {
            $navegador = 'Internet Explorer';
        } else if(preg_match('/Firefox/i',$u_agent)) {
            $navegador = 'Mozilla Firefox';
        } else if(preg_match('/Chrome/i',$u_agent)) {
            $navegador = 'Google Chrome';
        } else if(preg_match('/Safari/i',$u_agent)) {
            $navegador = 'Apple Safari';
        } else if(preg_match('/Opera/i',$u_agent)) {
            $navegador = 'Opera';
        } else if(preg_match('/Netscape/i',$u_agent)) {
            $navegador = 'Netscape';
        }

        return $navegador;
    }

}