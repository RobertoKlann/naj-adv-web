<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\NajController;
use App\Models\AppFinanceiroModel;
use App\Models\AppDashboardModel;
use App\Models\AppAtividadeModel;
use App\Models\AppProcessoModel;
use App\Models\AppChatMensagemModel;
use App\Models\UsuarioModel;
use Exception;
use App\Http\Traits\MonitoraTrait;
use App\Models\AppAgendaModel;

/**
 * Controller de dashboard (aplicativo)
 *
 */
class AppDashboardController extends NajController {
    
    use MonitoraTrait;

    public function onLoad() {
        $user = $this->getUserFromToken();

        $AppDashboardModel = new AppDashboardModel;
        $AppDashboardModel->setUserId($user->id);

        $this->setModel($AppDashboardModel);
    }

    public function dashboard() {
        return $this->resolveResponse(
            $this->getModel()->dashboard()
        );
    }

    public function refreshLastAccess($userId) {
        $message = 'ok';

        try {
            $UsuarioModel = UsuarioModel::where('id', $userId)->first();
            $UsuarioModel->ultimo_acesso = date('Y-m-d H:i:s');
            $result = $UsuarioModel->save();

            if (is_string($result)) {
                $this->throwException('Erro ao atualizar o registro. ' . $result);
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        return $message;
    }

    public function home() {
        $chatId = request()->get('chat_id');
        $user = $this->getUserFromToken();
        $isFirstAccess = $user->ultimo_acesso ? '0' : '1';

        // mensagens
        $AppChatMensagemModel = new AppChatMensagemModel;
        $notReadMessages = $AppChatMensagemModel->getNotReadMessages($chatId, $user->id);
        $totalMessages = $AppChatMensagemModel->getTotalMessages($chatId, $user->id);

        $messages = [
            'total'     => $totalMessages,
            'nao_lidas' => $notReadMessages,
        ];

        // atividades
        $AppAtividadeModel = new AppAtividadeModel;
        $totalActivities = $AppAtividadeModel->getTotalActivities($user->id);
        $monthActivities = $AppAtividadeModel->getMonthActivities($user->id, date('m'), date('Y'));

        $activities = [
            'total'     => $totalActivities,
            'mes_atual' => $monthActivities,
        ];

        // eventos
        $AppAgendaModel = new AppAgendaModel;
        $totalEvents = $AppAgendaModel->getTotalEvents($user->id);

        if (is_array($totalEvents) && count($totalEvents) > 0)
            $totalEvents = $totalEvents[0]->quantidade_eventos;

        $events = [
            'total' => $totalEvents
        ];

        // processos
        $AppProcessoModel = new AppProcessoModel;
        $totalProcess = $AppProcessoModel->getTotalProcess($user->id);
        $totalProcess30Days = $AppProcessoModel->getTotalProcess30Days($user->id);

        /*$process = [
            'total'        => $totalProcess,
            'trinta_dias'  => $totalProcess30Days,
        ];*/

        $process = array_merge(['total' => $totalProcess], $totalProcess30Days);

        // financeiro
        $AppFinanceiroModel = new AppFinanceiroModel;

        $AppFinanceiroModel->setToPay();
        $toPayValue = $AppFinanceiroModel->getToPayValue($user->id);
        $AppFinanceiroModel->setToReceive();
        $toReceiveValue = $AppFinanceiroModel->getToReceiveValue($user->id);

        $refreshResult = $this->refreshLastAccess($user->id);

        $data = [
            'primeiro_acesso'   => $isFirstAccess,
            'acesso_atualizado' => $refreshResult,
            'mensagens'         => $messages,
            'atividades'        => $activities,
            'eventos'           => $events,
            'processos'         => $process,
            'valor_pagar'       => $toPayValue,
            'valor_receber'     => $toReceiveValue,
        ];

        return $this->resolveResponse($data);
    }
    
    /**
     * Quando o usuário muda de advocacia
     */
    public function monitoracao() {
        return $this->resolveResponse(
            $this->monitora(
                'AcessoAreaClienteAPP',
                'Acessou o cliente'
            )
        );
    }

    /**
     * Quando o usuário acessa o app
     */
    public function monitoracaoHome() {
        return $this->resolveResponse(
            $this->monitora(
                'AcessoAreaClienteAPP',
                'Acessou o aplicativo'
            )
        );
    }

}
