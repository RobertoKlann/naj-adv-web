<?php

namespace App\Models;

use App\Http\Controllers\Api\UsuarioDispositivoApiController;
use App\Models\NajModel;
use Illuminate\Support\Facades\DB;
use App\Models\SysConfigModel;

/**
 * Modelo das Notificações dos eventos da agenda.
 * 
 * @package    Models
 * @author     Roberto Oswaldo Klann
 * @since      11/08/2021
 */
class AgendaEventoNotificacaoModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('prc_movimento');

        $this->addColumn('ID', true);
    }

    public function getAllUsersWithEventsNotification() {
        $Sysconfig = new SysConfigModel();
        $daysNotify = $Sysconfig->searchSysConfig('AGENDA_NAJ_CLIENTE', 'AGENDA_DIA_NOTIFICACAO');
        $hasConfig = $Sysconfig->searchSysConfig('AGENDA_NAJ_CLIENTE', 'TIPO_COMPROMISSO_EXIBIR');
        $conditionCompromisso = '';

        if ($hasConfig)
            $conditionCompromisso = ' AND A.CODIGO_TIPO IN(' . $hasConfig . ') ';

        if(!$daysNotify)
            return ['status_code' => 200, 'message' => "Não há a configuração AGENDA_DIA_NOTIFICACAO que informa os dias que serão considerados na busca dos eventos."];

        $daysNotify = json_decode($daysNotify);

        $events = [];

        foreach ($daysNotify as $day) {
            $date = date('Y-m-d', strtotime("+{$day} day"));

            unset($data);

            $sql = "
                SELECT A.ID AS ID_COMPROMISSO,
                    DATE_FORMAT(A.DATA_HORA_COMPROMISSO,'%d/%m/%Y') AS data_evento,
                    DATE_FORMAT(A.DATA_HORA_COMPROMISSO,'%H:%i:%S') AS HORA,
                    A.codigo_usuario AS codigo_usuario,
                    am.codigo_pessoa as cliente_relacionado,
                    {$day} AS dia_evento
                FROM AGENDA A
                INNER JOIN AGENDA_MEMBRO as am on am.id_compromisso = A.id
                WHERE TRUE
                  AND am.cliente = 'S'
                  AND A.DATA_HORA_COMPROMISSO BETWEEN '{$date} 00:00:00' AND '{$date} 23:59:59'
                    {$conditionCompromisso}
                ORDER BY A.DATA_HORA_COMPROMISSO DESC
            ";

            // dd($sql);

            $data = DB::select($sql);

            if(is_array($data) && count($data) > 0)
                $events[] = $data;
        }

        // dd($events);

        //Se não achou nenhuma pessoa então só volta pq não tem nada para enviar
        if(count($events) == 0)
            return ['status_code' => 200, 'message' => "Não há eventos para os dias definido."];

        foreach($events as $event) {
            foreach($event as $e)
                $personsId[] = $e->cliente_relacionado;
        }

        $personsId = array_unique($personsId);

        // dd($personsId);

        $users = DB::select("
            SELECT usuario_id,
                   pessoa_codigo,
                   apelido,
                   cpf
              FROM pessoa_rel_clientes
              JOIN usuarios
                ON pessoa_rel_clientes.usuario_id = usuarios.id
             WHERE TRUE
               AND pessoa_codigo IN (" . implode(', ', $personsId) . ")
        ");

        // dd($users, $personsId);

        $usersId = [];

        foreach ($users as $user)
            $usersId[] = $user->usuario_id;
        
        $devices = (new UsuarioDispositivoApiController)->getAllDevicesUsers(base64_encode(json_encode(['usuarios' => $usersId])));
        $devices = json_decode($devices->getBody()->getContents());

        if(!isset($devices->status_code) || $devices->status_code != '200')
            return ['status_code' => 400, 'message' => $devices->naj->mensagem];

        //Validando se tem algum usuário com device, se não tem só volta
        if(is_array($devices->naj) && count($devices->naj) == 0) return ['status_code' => 200, 'message' => "Não foi encontrado nenhum dispositivo para envio de pusher."];

        // dd($devices->naj);
        
        $usersWithDevice = [];
        foreach ($events as $event) {
            foreach ($event as $newEvent) {
                foreach ($users as $user) {
                    //Pegando a pessoa que tem o relacionamento com o usuário apenas
                    // if($event->codigo_usuario == $user->pessoa_codigo && $event->cpf == $user->cpf) {
                    // if($newEvent->codigo_usuario == $user->pessoa_codigo) {
                        foreach ($devices->naj as $device) {
                            $deviceFormatted = [];
                            //Pegando o device do usuário que tem o relacionamento com o usuário apenas
                            if($device->ativo == 'S' && $device->usuario_id == $user->usuario_id) {
                                $deviceFormatted['usuario_id'] = $device->usuario_id;
                                $deviceFormatted['one_signal_id'] = $device->one_signal_id;
                                $deviceFormatted['pessoa_codigo'] = $newEvent->codigo_usuario;
                                $deviceFormatted['apelido'] = $user->apelido;
                                $deviceFormatted['day_event'] = $newEvent->dia_evento;
                                $deviceFormatted['date_event'] = $newEvent->data_evento;

                                $usersWithDevice[$device->one_signal_id] = (object) $deviceFormatted;
                            }
                        }
                    // }
                }
            }
        }

        return [
            'status_code' => 200,
            'message' => 'Usuários buscados com succeso!',
            'users' => $usersWithDevice
        ];
    }
    
}