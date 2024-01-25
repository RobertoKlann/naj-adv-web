<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Http\Controllers\Api\OneSignalPushController;
use App\Http\Controllers\Auth\LoginController;
use App\Models\AgendaEventoNotificacaoModel;
use App\Models\SysConfigModel;
use Exception;
use Auth;

/**
 * Controllador das Notificações de eventos da agenda.
 * 
 * @package    Controllers
 * @author     Roberto Oswaldo Klann
 * @since      11/08/2021
 */
class AgendaEventoNotificacaoController extends NajController {

    protected $actionNotification = '@ACT/open_events';

    public function onLoad() {
        $this->setModel(new AgendaEventoNotificacaoModel);
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
        if(!$this->validateSendNotification())
            return response()->json(['status_code' => 200, 'message' => 'Pusher não enviado pois o cliente não tem configurado o envio de notificação de eventos!']);

        $users = $this->getModel()->getAllUsersWithEventsNotification();
        $erros = [];
        $userHasMoreOneDevice = [];

        //Se estiver definido que é testes já pode voltar
        if(request()->get('onlyTest') == true)
            return response()->json($users);

        // $this->saveDateHourNotificationSended();

        //Se não tiver usuários já pode voltar
        if(!isset($users['users']))            
            return response()->json($users);

        //fazer o foreach aqui para enviar as notificações
        foreach ($users['users'] as $user) {
            $idUsuario = $user->usuario_id;
            $idOneSignal = $user->one_signal_id;
            $message = $this->getMessagePush($user);
            $newMessage = $this->getMessagePush($user, true);

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
                    $this->sendMessageToChat($idUsuario, $newMessage);

                //adiciona no array de mais de um device para não criar mais mensagens no chat
                $userHasMoreOneDevice[] = $idUsuario;
            } catch (Exception $e) {
                //Se deu erro vamos monitorar para dizer qual device que deu pau
                $erros[] = "Erro ao enviar o push para o usuário: {$user->pessoa_nome} - {$user->usuario_id} - Device: {$idOneSignal}. Mensagem de erro: {$e->getMessage()}; \n";
            }
        }

        if(count($erros) > 0)
            return response()->json(['status_code' => 200, 'message' => $erros]);

        return response()->json(['status_code' => 200, 'message' => 'Pushers enviado com sucesso!']);
    }

    private function getMessagePush($user, $withoutInformation = false) {
        $daysToEvent = 'Faltam ' . $user->day_event . ' dia(s) para o evento';

        if ($user->day_event == 0)
            $daysToEvent = "O evento ocorre hoje";

        if ($withoutInformation)
            return "Olá {$user->apelido}, você possui um evento agendado para a data {$user->date_event} ({$daysToEvent}).";
        
        return "Olá {$user->apelido}, você possui um evento agendado para a data {$user->date_event} ({$daysToEvent}). Clique para mais informações.";
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

    private function saveDateHourNotificationSended() {
        $Sysconfig = new SysConfigModel();

        //Se ainda não existe um registro inclui
        if(!$Sysconfig->existsChaveSysConfig('FINANCEIRO', 'NOTIFICA_CLIENTE_APP_PAGAR'))
            return $Sysconfig->createSysConfig('FINANCEIRO', 'NOTIFICA_CLIENTE_APP_PAGAR', date('Y-m-d H:i:s'));

        return $Sysconfig->updateSysConfig('FINANCEIRO', 'NOTIFICA_CLIENTE_APP_PAGAR', date('Y-m-d H:i:s'));
    }

    private function validateSendNotification() {
        $Sysconfig = new SysConfigModel();
        $hasConf   = $Sysconfig->searchSysConfig('AGENDA_NAJ_CLIENTE', 'NOTIFICA_AGENDA_APP');

        if(!$hasConf || $hasConf == 'N' || $hasConf == 'NAO' || $hasConf == 'NÃO') return false;

        return true;
    }

    /**
     * Quando quiser enviar apenas uma mensagem especifica utilizar esse cara aqui
     */
    private function sendNotificationOneSignal($usuarioId) {}





    

}