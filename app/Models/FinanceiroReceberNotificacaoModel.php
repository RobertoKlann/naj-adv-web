<?php

namespace App\Models;

use App\Http\Controllers\Api\UsuarioDispositivoApiController;
use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo das Notificações a Pagar do Financeiro.
 * 
 * @package    Models
 * @author     Roberto Oswaldo Klann
 * @since      10/04/2021
 */
class FinanceiroReceberNotificacaoModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('conta');

        $this->addColumn('CODIGO', true);
    }

    public function getAllUsersWithFinancialReceiveNotification() {
        $Sysconfig  = new SysConfigModel();
        $daysNotify = $Sysconfig->searchSysConfig('FINANCEIRO', 'NOTIFICA_CLIENTE_APP_RECEBER_DIAS');

        if(!$daysNotify)
            $daysNotify = "[3,2,1,0]";

        $daysNotify = str_replace('[', '', $daysNotify);
        $daysNotify = str_replace(']', '', $daysNotify);
        $daysNotify = explode(',', $daysNotify);

        $allPerson = [];
        $persons = [];

        foreach($daysNotify as $day) {
            $dateFuture = date('Y-m-d', strtotime("+{$day} day"));
            
            $allPerson = DB::select("
                SELECT CONTA.CODIGO AS codigo_conta,
                    CP.SITUACAO AS situacao,
                    CP.PARCELA AS parcela_atual,
                    (
                        SELECT COUNT(0)
                        FROM CONTA_PARCELA
                        WHERE CODIGO_CONTA = CONTA.CODIGO
                    ) AS parcela_total,
                    DATE_FORMAT(CP.DATA_VENCIMENTO, '%d/%m/%Y') AS data_vencimento,
                    IF (
                            CP.VALOR_PARCIAL > 0,
                            CP.VALOR_PARCELA - CP.VALOR_PARCIAL,
                            CP.VALOR_PARCELA
                    ) AS valor_parcela,
                    P1.NOME AS nome_cliente,
                    P1.CODIGO as codigo_cliente,
                    CONTA.DESCRICAO AS conta_descricao
                FROM CONTA
            INNER JOIN CONTA_PARCELA CP
                    ON CP.CODIGO_CONTA = CONTA.CODIGO
            INNER JOIN NATUREZA_FINANCEIRA N 
                    ON N.CODIGO = CONTA.CODIGO_NATUREZA
            LEFT JOIN PRC PC
                    ON PC.CODIGO = CONTA.CODIGO_PROCESSO
            LEFT JOIN PESSOA P1
                    ON P1.CODIGO = CONTA.CODIGO_PESSOA
                WHERE CP.SITUACAO IN('A')
                AND CONTA.DISPONIVEL_CLIENTE = 'S'
                AND CONTA.CODIGO_PESSOA IN (SELECT pessoa_codigo FROM pessoa_rel_clientes)
                AND ((CONTA.TIPO = 'R' AND CONTA.PAGADOR = '2') OR CONTA.TIPO = 'P') 
                AND (N.TIPO_SUB NOT IN ('M', 'J', 'C') OR N.TIPO_SUB IS NULL)
                AND CP.data_vencimento = '{$dateFuture}'
            ORDER BY cp.situacao, cp.data_vencimento, cp.codigo_conta, cp.parcela asc
            ");

            if(is_array($allPerson) && count($allPerson) > 0)
                $persons = array_merge($persons, $allPerson);
        }

        //Validando se tem alguma pessoa, se não tem só volta
        if(is_array($persons) && count($persons) == 0) return ['status_code' => 200, 'message' => "Não há contas a receber no perído definido."];

        foreach($persons as $person)
            $personsId[] = $person->codigo_cliente;

        $users = DB::select("
            SELECT usuario_id,
                   pessoa_codigo
              FROM pessoa_rel_clientes
             WHERE TRUE
               AND pessoa_codigo IN (" . implode(', ', $personsId) . ")
        ");

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
                if($person->codigo_cliente == $user->pessoa_codigo) {
                    foreach ($devices->naj as $device) {
                        $deviceFormatted = [];
                        //Pegando o device do usuário que tem o relacionamento com o usuário apenas
                        if($device->ativo == 'S' && $device->usuario_id == $user->usuario_id) {
                            $deviceFormatted['usuario_id'] = $device->usuario_id;
                            $deviceFormatted['one_signal_id'] = $device->one_signal_id;
                            $deviceFormatted['pessoa_codigo'] = $person->codigo_cliente;
                            $deviceFormatted['pessoa_nome'] = $person->nome_cliente;
                            $deviceFormatted['days'] = $person->data_vencimento;
                            $deviceFormatted['valor_parcela'] = $person->valor_parcela;
                            $deviceFormatted['descricao'] = $person->conta_descricao;
                            $deviceFormatted['parcela_atual'] = $person->parcela_atual;
                            $deviceFormatted['parcela_total'] = $person->parcela_total;

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