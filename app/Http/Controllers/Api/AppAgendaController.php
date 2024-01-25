<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\NajController;
use App\Models\AppAgendaModel;
use App\Http\Traits\MonitoraTrait;
use App\Models\SysConfigModel;

/**
 * Controller de agenda do app
 */
class AppAgendaController extends NajController {
    
    use MonitoraTrait;

    public function onLoad() {
        $AppAgendaModel = new AppAgendaModel;

        $user = $this->getUserFromToken();

        $codigoCliente = $AppAgendaModel->getRelacionamentoClientes($user->id);

        if (!$codigoCliente)
            $this->throwException('Usuário sem relacionamento com cliente');

        $conditionCompromisso = '';

        if (request()->get('filterTypeEvent')) {
            $Sysconfig = new SysConfigModel();
            $hasConfig = $Sysconfig->searchSysConfig('AGENDA_NAJ_CLIENTE', 'TIPO_COMPROMISSO_EXIBIR');

            if ($hasConfig)
                $conditionCompromisso = ' AND A.CODIGO_TIPO IN(' . $hasConfig . ') ';
        }

        $conditionDateEvent = '';

        if (request()->get('filterDateEvent'))
            $conditionDateEvent = ' AND DATE(A.DATA_HORA_COMPROMISSO) >= DATE(NOW()) ';

        $AppAgendaModel->addRawFilter("
            ((
                A.ID IN (
                    SELECT ID_COMPROMISSO
                    FROM AGENDA_MEMBRO
                    WHERE CODIGO_PESSOA IN ({$codigoCliente})
                )
            )
            OR (
                A.CODIGO_PROCESSO IN (
                    SELECT CODIGO
                    FROM PRC
                    WHERE CODIGO_CLIENTE IN ({$codigoCliente})
                        OR CODIGO IN (
                            SELECT CODIGO_PROCESSO
                            FROM PRC_GRUPO_CLIENTE
                            WHERE CODIGO_CLIENTE IN ({$codigoCliente})
                        )
                )
            ))
            {$conditionDateEvent}
            {$conditionCompromisso}
        ");

        $this->setModel($AppAgendaModel);
    }

    public function getAll() {
        /*$periodo = request()->query('periodo');

        if (!$periodo) {
            $this->throwException('Período não definido');
        }*/
        $res = $this->getModel()->selectAll();

        return $this->resolveResponse([
            'total'     => count($res),
            'resultado' => $res,
        ]);
    }

    public function getAllEvents() {
        $res = $this->getModel()->selectAll();

        return $this->resolveResponse([
            'total'     => count($res),
            'resultado' => $res,
        ]);
    }
    
    public function monitoracao() {
        return $this->resolveResponse(
            $this->monitora(
                'AcessoAreaClienteAPPAgenda',
                'Clicou na rotina Agendamentos'
            )
        );
    }
    
    public function monitoracaoTipo($_tipo) {
        $tipo = '';
        
        switch ($_tipo) {
            case 'consulta':
                $tipo = 'Agendar uma Consulta';
                break;
            case 'reuniao':
                $tipo = 'Agendar uma Reunião';
                break;
            case 'visita':
                $tipo = 'Agendar uma Visita';
                break;
            case 'outro':
                $tipo = 'Outro tipo de Agendamento';
                break;
            default:
                $tipo = $_tipo;
                break;
        }
        
        return $this->resolveResponse(
            $this->monitora(
                'AcessoAreaClienteAPPAgenda',
                "Clicou em {$tipo}"
            )
        );
    }

}
