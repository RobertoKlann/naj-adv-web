<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Http\Controllers\Api\OneSignalPushController;
use App\Http\Controllers\Auth\LoginController;
use App\Models\FinanceiroReceberNotificacaoModel;
use App\Models\SysConfigModel;
use Exception;
use Auth;

/**
 * Controllador das Notificações do Financeiro.
 * 
 * @package    Controllers
 * @subpackage Api
 * @author     Roberto Oswaldo Klann
 * @since      10/04/2021
 */
class FinanceiroReceberNotificacaoController extends NajController {

    protected $actionNotification = '@ACT/open_to_receive';

    public function onLoad() {
        $this->setModel(new FinanceiroReceberNotificacaoModel);
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
        if(!$this->validateDateHourNotificationSended())
            return response()->json(['status_code' => 200, 'message' => 'Pushers não enviados por conta de já terem sido enviados hoje!']);

        $users = $this->getModel()->getAllUsersWithFinancialReceiveNotification();
        $erros = [];
        $userHasMoreOneDevice = [];

        //Se estiver definido que é testes já pode voltar
        if(request()->get('onlyTest') == true)
            return response()->json($users);

        $this->saveDateHourNotificationSended();

        //Se não tiver usuários já pode voltar
        if(!isset($users['users']))
            return response()->json($users);

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
                $erros[] = "Erro ao enviar o push para o usuário: {$user->pessoa_nome} - {$user->usuario_id} - Device: {$idOneSignal}. Mensagem de erro: {$e->getMessage()}; \n";
            }
        }

        if(count($erros) > 0 )
            return response()->json(['status_code' => 200, 'message' => $erros]);

        return response()->json(['status_code' => 200, 'message' => 'Pushers enviado com sucesso!']);
    }

    private function getMessagePush($user) {
        $message = "";
        $payment = number_format($user->valor_parcela, 2, ',', '.');

        if($user->days == date('d/m/Y'))
            return "Olá {$user->pessoa_nome}, temos um pagamento programado para você receber no dia: {$user->days} (recebe hoje) no valor de R$ {$payment} {$user->descricao}. (Parcela: {$user->parcela_atual} de {$user->parcela_total})";

        if($user->days == date('d/m/Y', strtotime("+1 day")))
            $message = "Olá {$user->pessoa_nome}, temos um pagamento programado para você receber no dia: {$user->days} (recebendo em 1 dia) no valor de R$ {$payment} {$user->descricao}. (Parcela: {$user->parcela_atual} de {$user->parcela_total})";

        if($user->days == date('d/m/Y', strtotime("+2 day")))
            $message = "Olá {$user->pessoa_nome}, temos um pagamento programado para você receber no dia: {$user->days} (recebendo em 2 dias) no valor de R$ {$payment} {$user->descricao}. (Parcela: {$user->parcela_atual} de {$user->parcela_total})";

        // if($user->days == date('d/m/Y', strtotime("+3 day")))
            $message = "Olá {$user->pessoa_nome}, temos um pagamento programado para você receber no dia: {$user->days} (recebendo em 3 dias) no valor de R$ {$payment} {$user->descricao}. (Parcela: {$user->parcela_atual} de {$user->parcela_total})";

        return $message;
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
        if(!$Sysconfig->existsChaveSysConfig('FINANCEIRO', 'NOTIFICA_CLIENTE_APP_RECEBER'))
            return $Sysconfig->createSysConfig('FINANCEIRO', 'NOTIFICA_CLIENTE_APP_RECEBER', date('Y-m-d H:i:s'));

        return $Sysconfig->updateSysConfig('FINANCEIRO', 'NOTIFICA_CLIENTE_APP_RECEBER', date('Y-m-d H:i:s'));
    }

    private function validateDateHourNotificationSended() {
        $Sysconfig  = new SysConfigModel();
        $dateHour   = $Sysconfig->searchSysConfig('FINANCEIRO', 'NOTIFICA_CLIENTE_APP_RECEBER');

        if(!$dateHour) return true;

        if($dateHour <= date('Y-m-d H:i:s', strtotime("-1 day"))) return true;

        return false;
    }

    /**
     * Quando quiser enviar apenas uma mensagem especifica utilizar esse cara aqui
     */
    private function sendNotificationOneSignal($usuarioId) {}

}