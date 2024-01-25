<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Http\Controllers\Api\OneSignalPushController;
use App\Http\Controllers\Auth\LoginController;
use App\Models\AtividadeAndamentoNotificacaoModel;
use App\Models\SysConfigModel;
use Exception;
use Auth;

/**
 * Controllador das Notificações das atividades e andamentos.
 * 
 * @package    Controllers
 * @author     Roberto Oswaldo Klann
 * @since      15/05/2021
 */
class AtividadeAndamentoNotificacaoController extends NajController {

    protected $actionNotification = '@ACT/new_message';

    public function onLoad() {
        $this->setModel(new AtividadeAndamentoNotificacaoModel());
    }

    public function callSendManyNotifications() {
        $login  = request()->get('login');
        $senha  = request()->get('password');
        $status = request()->get('status');

        $LoginController = new LoginController();

        if(!$login || !$senha || !$status) {
            //Faz logout para matar a sessão
            $LoginController->logout(request());

            return response()->json([
                'status_code' => 400,
                'message' => "Está faltando algum parametro na requisição! LOGIN: {$login}, SENHA: {$senha}, STATUS: {$status}"
            ]);
        }

        $LoginController->login(request());

        return $this->sendManyNotification();
    }

    public function sendManyNotification() {
        $pusherIntergration = request()->get('pusherIntergration');

        if($pusherIntergration == 'onesignal')
            return $this->sendManyNotificationOneSignal();
    }

    private function sendManyNotificationOneSignal() {
        // $user = (object) ['apelido' => 'Robrto'];
        // dd($this->getMessagePush($user));

        if(!$this->validateHasPermissionNotificationAtividadeSended() && !$this->validateHasPermissionNotificationAndamentoSended())
            return response()->json(['status_code' => 200, 'message' => 'Pushers não enviado por conta de este cliente não estar habilitado o envio de pusher de andamentos e atividades!']);

        if(!$this->validateDayOfSendNotification())
            return response()->json(['status_code' => 200, 'message' => 'Pushers não enviado pois hoje não é o dia definido para o envio dos pusher de andamento e atividade!']);

        $users = $this->getModel()->getAllUsersWithAtividadesAndamentoNotification();
        $erros = [];
        $userHasMoreOneDevice = [];

        //Se estiver definido que é testes já pode voltar
        if(request()->get('onlyTest') == true)
            return response()->json($users);


        $this->saveDateHourNotificationSended();

        //Se não tiver usuários ou estiver definido que é testes já pode voltar
        if(!isset($users['users'])) return response()->json($users);

        //fazer o foreach aqui para enviar as notificações
        foreach ($users['users'] as $user) {
            $idUsuario = $user->usuario_id;
            $idOneSignal = $user->one_signal_id;
            $message = $this->getMessagePush($user);
            $messageChat = $this->getMessagePush($user, false);

            if(isset($user->isOciosidade)) {
                $sysconfig = new SysConfigModel();
                $notify = $sysconfig->searchSysConfig('NOTIFICA_CLIENTE_NOVAS_INFO', 'NOTIFICA_CLIENTE_OCIOSO');

                if ($notify == 'NAO' || $notify == 'NÃO' || $notify == 'N')
                    continue;

                $message = $this->getMessageOciosidadePush($user);
                $messageChat = $this->getMessageOciosidadePush($user, false);
            }

            $data = [
                'usuario_id' => $idUsuario,
                'pusher_id' => $idOneSignal,
                'message' => $message,
                'title' => 'Lembrete de novas informações!!!',
                'action' => $this->actionNotification,
            ];

            try {
                (new OneSignalPushController)->newPushNotification($data);

                //se não ta no array de mais de um device envia a notificação no chat
                if(!in_array($idUsuario, $userHasMoreOneDevice))
                    $this->sendMessageToChat($idUsuario, $messageChat);

                //adiciona no array de mais de um device para não criar mais mensagens no chat
                $userHasMoreOneDevice[] = $idUsuario;
            } catch (Exception $e) {
                //Se deu erro vamos monitorar para dizer qual device que deu pau
                $erros[] = "Erro ao enviar o push para o usuário: {$user->usuario_id} - Device: {$idOneSignal}. Mensagem de erro: {$e->getMessage()}; \n";
            }
        }

        if(count($erros) > 0 )
            return response()->json(['status_code' => 200, 'message' => $erros]);

        return response()->json(['status_code' => 200, 'message' => 'Pushers enviado com sucesso!']);
    }

    private function getMessagePush($user, $complete = true) {
        $Sysconfig = new SysConfigModel();
        $messagePush = $Sysconfig->searchSysConfig('NOTIFICA_CLIENTE_NOVAS_INFO', 'NOVAS_MENSAGEM');

        if (!$complete)
            $messagePush = "Olá {APELIDO}, estou passando para te atualizar das novas informações que processamos no último mês.";

        if(!$messagePush && $complete)
            $messagePush = 'Olá {APELIDO}, estou passando para te atualizar das novas informações que processamos no último mês. Clique aqui para saber mais!';

        return str_replace("{APELIDO}", $user->apelido, $messagePush);
    }

    private function getMessageOciosidadePush($user, $complete = true) {
        $Sysconfig = new SysConfigModel();
        $messagePush = $Sysconfig->searchSysConfig('NOTIFICA_CLIENTE_NOVAS_INFO', 'OCIOSO_MENSAGEM');

        if (!$complete)
            $messagePush = "Olá {APELIDO}, estou passando para te dizer que não temos novas informações sobre a(s) demanda(s), mas que estamos atentos e te informaremos assim que tivermos novidades!";

        if(!$messagePush && $complete)
            $messagePush = 'Olá {APELIDO}, estou passando para te dizer que não temos novas informações sobre a(s) demanda(s), mas que estamos atentos e te informaremos assim que tivermos novidades! Clique aqui para saber mais!';

        return str_replace("{APELIDO}", $user->apelido, $messagePush);
    }

    private function sendMessageToChat($idUsuario, $message) {
        $ChatAtendimentoController = new ChatAtendimentoController();

        $chat = $ChatAtendimentoController->hasChat($idUsuario);
        $idChat = $chat['id_chat'];

        if(!isset($chat['id'])) {
            request()->merge(['data_hora' => date('Y-m-d H:i:s')]);
            $chat_store = $ChatAtendimentoController->storeChat();
            $idChat = $chat_store['model']['id'];
        }

        $atendimento = $ChatAtendimentoController->getModel()->hasAtendimentoOpen($idChat);

        if(isset($atendimento[0]->id)) {
            $atendimento = $atendimento[0]->id;
        } else {
            $atendimento = null;
        }

        request()->merge(['id_atendimento' => $atendimento]);

        $data = [
            'id_chat'        => $idChat,
            'id_usuario'     => Auth::user()->id,
            'conteudo'       => $message,
            'tipo'           => 0,
            'data_hora'      => date('Y-m-d H:i:s'),
            'file_size'      => null,
            'file_path'      => null,
            'file_type'      => null,
            'tag'            => 'BOT',
            'id_atendimento' => $atendimento,
        ];

        return (new ChatMensagemController)->store($data);
    }

    private function validateHasPermissionNotificationAtividadeSended() {
        $Sysconfig = new SysConfigModel();
        $hasConfig = $Sysconfig->searchSysConfig('NOTIFICA_CLIENTE_NOVAS_INFO', 'NOVAS_ATIVIDADES');

        if(!$hasConfig || $hasConfig == 'NÃO' || $hasConfig == 'NAO') return false;

        return true;
    }

    private function validateHasPermissionNotificationAndamentoSended() {
        $Sysconfig = new SysConfigModel();
        $hasConfig = $Sysconfig->searchSysConfig('NOTIFICA_CLIENTE_NOVAS_INFO', 'NOVOS_ANDAMENTOS');

        if(!$hasConfig || $hasConfig == 'NÃO' || $hasConfig == 'NAO') return false;

        return true;
    }

    private function validateDayOfSendNotification() {
        $Sysconfig = new SysConfigModel();
        $dayConfig = $Sysconfig->searchSysConfig('NOTIFICA_CLIENTE_NOVAS_INFO', 'NOVAS_DIA_NOTIFICACAO');
        $dayToday  = date('d');

        if(!$dayConfig)
            $dayConfig = '1';

        return $dayToday == $dayConfig;
    }

    private function saveDateHourNotificationSended() {
        $Sysconfig = new SysConfigModel();

        //Se ainda não existe um registro inclui
        if(!$Sysconfig->existsChaveSysConfig('FINANCEIRO', 'OCIOSO_ULTIMA_NOTIFICACAO'))
            return $Sysconfig->createSysConfig('FINANCEIRO', 'OCIOSO_ULTIMA_NOTIFICACAO', date('Y-m-d H:i:s'));

        return $Sysconfig->updateSysConfig('FINANCEIRO', 'OCIOSO_ULTIMA_NOTIFICACAO', date('Y-m-d H:i:s'));
    }

    /**
     * Quando quiser enviar apenas uma mensagem especifica utilizar esse cara aqui
     */
    private function sendNotificationOneSignal($usuarioId) {}

}