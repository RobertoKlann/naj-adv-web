<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Http\Controllers\NajWeb\EmpresaController;
use App\Http\Controllers\Api\OneSignalPushController;
use App\Http\Controllers\Auth\LoginController;
use App\Models\PessoaAniversarianteNotificacaoModel;
use App\Models\SysConfigModel;
use Exception;
use Auth;

/**
 * Controllador das Notificações dos aniversariantes.
 * 
 * @package    Controllers
 * @subpackage Api
 * @author     Roberto Oswaldo Klann
 * @since      19/04/2021
 */
class PessoaAniversarianteNotificacaoController extends NajController {

    protected $actionNotification = '@ACT/new_message';

    public function onLoad() {
        $this->setModel(new PessoaAniversarianteNotificacaoModel);
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
        if(!$this->validateHasPermissionNotificationSended())
            return response()->json(['status_code' => 200, 'message' => 'Pushers não enviados por conta de este cliente não estar habilitado a enviar pusher para aniversariantes!']);

        $users = $this->getModel()->getAllUsersBirthdayTodayNotification();
        $erros = [];
        $userHasMoreOneDevice = [];

        //Se não tiver usuários ou estiver definido que é testes já pode voltar
        if(!isset($users['users']) || request()->get('onlyTest') == true) return response()->json($users);

        //fazer o foreach aqui para enviar as notificações
        foreach ($users['users'] as $user) {
            $idUsuario = $user->usuario_id;
            $idOneSignal = $user->one_signal_id;
            $message = $this->getMessagePush($user);

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
                    $this->sendMessageToChat($idUsuario, $message);

                //adiciona no array de mais de um device para não criar mais mensagens no chat
                $userHasMoreOneDevice[] = $idUsuario;
            } catch (Exception $e) {
                //Se deu erro vamos monitorar para dizer qual device que deu pau
                $erros[] = "Erro ao enviar o push para o Device com ID ONESIGNAL: {$idOneSignal}. Mensagem de erro: {$e->getMessage()}; \n";
            }
        }

        if(count($erros) > 0 )
            return response()->json(['status_code' => 200, 'message' => $erros]);

        return response()->json(['status_code' => 200, 'message' => 'Pushers enviado com sucesso!']);
    }

    private function getMessagePush($user) {
        $Sysconfig = new SysConfigModel();
        $messagePush = $Sysconfig->searchSysConfig('PESSOAS', 'NOTIFICA_ANIVERSARIANETE_APP_MSG');

        if (!$messagePush) {
            $EmpresaController = new EmpresaController();
            $nomeEmpresa = $EmpresaController->getNomeFirstEmpresa();

            $messagePush = "Olá {APELIDO}, PARABÉNS PELO SEU ANIVERSÁRIO! Aproveite seu dia ao máximo, são os votos da {$nomeEmpresa->getData()}.";
        }

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

    private function validateHasPermissionNotificationSended() {
        $Sysconfig = new SysConfigModel();
        $hasConfig = $Sysconfig->searchSysConfig('PESSOAS', 'NOTIFICA_ANIVERSARIANETE_APP');

        if(!$hasConfig || $hasConfig == 'NÃO' || $hasConfig == 'NAO') return false;

        return true;
    }

    /**
     * Quando quiser enviar apenas uma mensagem especifica utilizar esse cara aqui
     */
    private function sendNotificationOneSignal($usuarioId) {}

}