<?php

namespace App\Models;

use App\Http\Controllers\Api\UsuarioDispositivoApiController;
use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo das Notificações dos andamentos e atividades.
 * 
 * @package    Models
 * @author     Roberto Oswaldo Klann
 * @since      15/05/2021
 */
class AtividadeAndamentoNotificacaoModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('prc_movimento');

        $this->addColumn('ID', true);
    }

    public function getAllUsersWithAtividadesAndamentoNotification() {
        $dayConfig   = (new SysConfigModel)->searchSysConfig('NOTIFICA_CLIENTE_NOVAS_INFO', 'NOVAS_DIA_NOTIFICACAO');        
        $dayLastSend = date('Y-m-d');

        $dateExploded = explode('-', $dayLastSend);
        $month = $dateExploded[1];
        $year = $dateExploded[0];

        $day = "{$year}-{$month}-{$dayConfig}";

        $dayInitial = date('Y-m-d', strtotime("{$day} -1 month"));

        $Sysconfig = new SysConfigModel();
        $hasAndamento = $Sysconfig->searchSysConfig('NOTIFICA_CLIENTE_NOVAS_INFO', 'NOVOS_ANDAMENTOS');

        $Sysconfig = new SysConfigModel();
        $hasAtividade = $Sysconfig->searchSysConfig('NOTIFICA_CLIENTE_NOVAS_INFO', 'NOVAS_ATIVIDADES');
        $sqlAtividade = "";
        $sqlAtividade2 = "";
        $sqlAndamento = "";

        $sql = "";

        if($hasAtividade && ($hasAtividade != 'NÃO' && $hasAtividade != 'NAO')) {
            $sqlAtividade .= "
                SELECT '1' as tab, #SE TEM CONFIGURAÇÃO PARA NOTIFICA_CLIENTE_NOVAS_INFO.NOVAS_ATIVIDADES=SIM
                        pc.codigo_cliente, 
                        null as codigo_cliente_grupo,
                        (SELECT DATA FROM ATIVIDADE WHERE CODIGO_PROCESSO = pc.CODIGO AND ENVIAR='S' ORDER BY DATA DESC LIMIT 1) as ULTIMA_DATA
                  FROM prc pc
             LEFT JOIN atividade a on a.codigo_processo = pc.codigo
             left join prc_grupo_cliente pg on pg.codigo_processo = pc.codigo
                 WHERE (Pc.codigo_cliente in(select pessoa_codigo from pessoa_rel_clientes)
                    OR  pg.codigo_cliente in(select pessoa_codigo from pessoa_rel_clientes))
            ";
        }

        if($hasAndamento && ($hasAndamento != 'NÃO' && $hasAndamento != 'NAO')) {
            if(strlen($sqlAtividade) > 10)
                $sqlAtividade .= " UNION ";

            $sqlAndamento = "
                SELECT '2' as tab,#SE TEM CONFIGURAÇÃO PARA NOTIFICA_CLIENTE_NOVAS_INFO.NOVOS_ANDAMENTOS=SIM
                       pc.codigo_cliente, 
                       pg.codigo_cliente as codigo_cliente_grupo,
                       (SELECT DATA FROM prc_movimento WHERE CODIGO_PROCESSO = pc.CODIGO ORDER BY DATA DESC LIMIT 1) as ULTIMA_DATA
                  FROM prc pc
             left join prc_grupo_cliente pg on pg.codigo_processo = pc.codigo
                 WHERE (Pc.codigo_cliente in(select pessoa_codigo from pessoa_rel_clientes)
                    OR  pg.codigo_cliente in(select pessoa_codigo from pessoa_rel_clientes))
                   and pc.codigo_situacao in(select codigo from prc_situacao where ativo = 'S')
            ";
        }

        if($hasAtividade && ($hasAtividade != 'NÃO' && $hasAtividade != 'NAO')) {
            if(strlen($sqlAndamento) > 10 || strlen($sqlAtividade) > 10)
                $sqlAtividade2 .= " UNION ";

            $sqlAtividade2 .= "
                SELECT '3' as tab, #SE TEM CONFIGURAÇÃO PARA NOTIFICA_CLIENTE_NOVAS_INFO.NOVAS_ATIVIDADES=SIM
                       a.codigo_cliente, 
                       null as codigo_cliente_grupo,
                      (SELECT DATA FROM ATIVIDADE WHERE CODIGO_CLIENTE = A.CODIGO_CLIENTE AND ENVIAR='S' ORDER BY DATA DESC LIMIT 1) as ULTIMA_DATA
                  FROM atividade a
                 WHERE a.codigo_cliente in(select pessoa_codigo from pessoa_rel_clientes)
                 order by ultima_data desc
            ";
        }

        $sql .= "
              SELECT a.tab, a.codigo_cliente, a.codigo_cliente_grupo, a.ultima_data
                FROM (
                    {$sqlAtividade} {$sqlAndamento} {$sqlAtividade2}
                ) as a
               WHERE TRUE
            GROUP BY codigo_cliente,
                     codigo_cliente_grupo
            HAVING ultima_data >= '{$dayInitial} 00:00:00'  # DATA DE FILTRO É (HOJE - 30 DIAS para NOVAS INFORMAÇÕES)
            ORDER BY codigo_cliente
        ";

        $persons = DB::select($sql);
        
        $Sysconfig      = new SysConfigModel();
        $lastOciosidade = $Sysconfig->searchSysConfig('NOTIFICA_CLIENTE_NOVAS_INFO', 'OCIOSO_ULTIMA_NOTIFICACAO');
        $period         = $Sysconfig->searchSysConfig('NOTIFICA_CLIENTE_NOVAS_INFO', 'OCIOSO_MES_INTERVALO');

        if(!$period)
            $period = 1;

        $dateOciosidade = date('Y-m-d', strtotime(date('Y-m-d') . " -{$period} month"));

        //Se não tem cria a ultimo envio
        if(!$lastOciosidade) {
            $Sysconfig->createSysConfig('NOTIFICA_CLIENTE_NOVAS_INFO', 'OCIOSO_ULTIMA_NOTIFICACAO', date('Y-m-d H:i:s'));
            $lastOciosidade = $Sysconfig->searchSysConfig('NOTIFICA_CLIENTE_NOVAS_INFO', 'OCIOSO_ULTIMA_NOTIFICACAO');
        }

        
        $personsOciosidade = [];
        $personsOciosidade = DB::select($this->getSqlPessoasOciosidade($dateOciosidade));
        
        // dd($sql, $this->getSqlPessoasOciosidade($dateOciosidade), $persons, $personsOciosidade);

        $personsMerge = array_merge($persons, $personsOciosidade);

        //Se não achou nenhuma pessoa então só volta pq não tem nada para enviar
        if(count($personsMerge) == 0)        
            return ['status_code' => 200, 'message' => "Não há novas atualizações nos andamentos."];

        foreach($personsMerge as $person) {
            if ($person->codigo_cliente_grupo)
                $personsId[] = $person->codigo_cliente_grupo;

            if ($person->codigo_cliente)
                $personsId[] = $person->codigo_cliente;
        }

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
        foreach ($personsMerge as $person) {
            foreach ($users as $user) {
                //Pegando a pessoa que tem o relacionamento com o usuário apenas
                // if($person->codigo_cliente == $user->pessoa_codigo && $person->cpf == $user->cpf) {
                if($person->codigo_cliente == $user->pessoa_codigo) {
                    foreach ($devices->naj as $device) {
                        $deviceFormatted = [];
                        //Pegando o device do usuário que tem o relacionamento com o usuário apenas
                        if($device->ativo == 'S' && $device->usuario_id == $user->usuario_id) {
                            $deviceFormatted['usuario_id'] = $device->usuario_id;
                            $deviceFormatted['one_signal_id'] = $device->one_signal_id;
                            $deviceFormatted['pessoa_codigo'] = $person->codigo_cliente;
                            $deviceFormatted['apelido'] = $user->apelido;

                            //Se for ociosidade add esse parametro para a mensagem ser diferenciada
                            if(isset($person->is_ociosidade)) {
                                $deviceFormatted['isOciosidade'] = true;
                                $usersWithDevice[] = (object) $deviceFormatted;
                            } else {
                                $usersWithDevice[$device->one_signal_id] = (object) $deviceFormatted;
                            }
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

    private function getSqlPessoasOciosidade($dateOciosidade) {
        return "
            select a.codigo_cliente, a.codigo_cliente_grupo, a.ultima_data, 1 as is_ociosidade
            from(SELECT pc.codigo_cliente, 
                null as codigo_cliente_grupo,
                    (SELECT DATA FROM ATIVIDADE WHERE CODIGO_PROCESSO = pc.CODIGO AND ENVIAR='S' ORDER BY DATA DESC LIMIT 1) as ULTIMA_DATA
                FROM prc pc
                LEFT JOIN atividade a on a.codigo_processo = pc.codigo
                left join prc_grupo_cliente pg on pg.codigo_processo = pc.codigo
                WHERE (Pc.codigo_cliente in(select pessoa_codigo from pessoa_rel_clientes)
                OR  pg.codigo_cliente in(select pessoa_codigo from pessoa_rel_clientes))
            
                UNION 
                SELECT pc.codigo_cliente, 
                pg.codigo_cliente as codigo_cliente_grupo,
                    (SELECT DATA FROM prc_movimento WHERE CODIGO_PROCESSO = pc.CODIGO ORDER BY DATA DESC LIMIT 1) as ULTIMA_DATA
                FROM prc pc
                left join prc_grupo_cliente pg on pg.codigo_processo = pc.codigo
                WHERE (Pc.codigo_cliente in(select pessoa_codigo from pessoa_rel_clientes)
                OR  pg.codigo_cliente in(select pessoa_codigo from pessoa_rel_clientes))
                and pc.codigo_situacao in(select codigo from prc_situacao where ativo = 'S')
            
                UNION 
                SELECT a.codigo_cliente, 
                null as codigo_cliente_grupo,
                    (SELECT DATA FROM ATIVIDADE WHERE CODIGO_CLIENTE = A.CODIGO_CLIENTE AND ENVIAR='S' ORDER BY DATA DESC LIMIT 1) as ULTIMA_DATA
                FROM atividade a
                WHERE a.codigo_cliente in(select pessoa_codigo from pessoa_rel_clientes)
                order by ultima_data desc
            ) as a
            where  true
            group by codigo_cliente, CODIGO_CLIENTE_GRUPO
            having ultima_data < '{$dateOciosidade} 00:00:00'  # DATA DE FILTRO É (HOJE - MESES DE OCIOSIDADE)
            order by codigo_cliente
        ";
    }
    
}