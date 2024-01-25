<?php

namespace App\Models;

use App\Http\Controllers\Api\UsuarioDispositivoApiController;
use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo das Notificações dos aniversariantes.
 * 
 * @package    Models
 * @author     Roberto Oswaldo Klann
 * @since      19/04/2021
 */
class PessoaAniversarianteNotificacaoModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('pessoa');

        $this->addColumn('CODIGO', true);
    }

    public function getAllUsersBirthdayTodayNotification() {
        $dayCurrent = date('d');
        $monthCurrent = date('m');

        $persons = DB::select("
            SELECT NOME AS nome_cliente,
                   CODIGO as codigo_cliente,
                   CPF as cpf
              FROM pessoa
             WHERE TRUE
               AND DATA_NASCTO IS NOT NULL
               AND extract(month from data_nascto) = '{$monthCurrent}'
               AND extract(day from data_nascto) = '{$dayCurrent}'
        ");

        //Validando se tem alguma pessoa, se não tem só volta
        if(is_array($persons) && count($persons) == 0) return ['status_code' => 200, 'message' => "Não há aniversáriantes hoje."];

        foreach($persons as $person)
            $personsId[] = $person->codigo_cliente;

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

        $usersId = [];
        foreach ($users as $user)
            $usersId[] = $user->usuario_id;
        
        $devices = (new UsuarioDispositivoApiController)->getAllDevicesUsers(base64_encode(json_encode(['usuarios' => $usersId])));
        $devices = json_decode($devices->getBody()->getContents());

        if(!isset($devices->status_code) || $devices->status_code != '200')
            return ['status_code' => 400, 'message' => $devices->naj->mensagem];

        //Validando se tem algum usuário com device, se não tem só volta
        if(is_array($devices->naj) && count($devices->naj) == 0) return ['status_code' => 200, 'message' => "Não foi encontrado nenhum dispositivo para envio de pusher."];

        $usersWithDevice = [];
        foreach ($persons as $person) {
            foreach ($users as $user) {
                //Pegando a pessoa que tem o relacionamento com o usuário apenas
                if($person->codigo_cliente == $user->pessoa_codigo && $person->cpf == $user->cpf) {
                    foreach ($devices->naj as $device) {
                        $deviceFormatted = [];
                        //Pegando o device do usuário que tem o relacionamento com o usuário apenas
                        if($device->ativo == 'S' && $device->usuario_id == $user->usuario_id) {
                            $deviceFormatted['usuario_id'] = $device->usuario_id;
                            $deviceFormatted['one_signal_id'] = $device->one_signal_id;
                            $deviceFormatted['pessoa_codigo'] = $person->codigo_cliente;
                            $deviceFormatted['apelido'] = $user->apelido;

                            $usersWithDevice[$device->one_signal_id] = (object) $deviceFormatted;
                        }
                    }
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