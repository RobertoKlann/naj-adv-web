<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Middleware de validação da senha do usuário.
 *
 * @package    Middleware
 * @author     Roberto Oswaldo Klann
 * @since      17/01/2020
 */
class CheckPasswordProvisoria {

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $rota             = $request->route()->getName();
        $rotas_nao_valida = $this->getRotasNaoValida();

        if(in_array($rota, $rotas_nao_valida)) {
            return $next($request);
        }

        if($rota == 'password.update' || $rota == 'usuario.update-password' || $rota == 'usuario.update-senha-provisoria' || $rota == 'empresa.identificador-empresa' || $rota == 'usuario.atualizar-dados') {
            return $next($request);
        }

        $senha_provisoria = auth()->user()->senha_provisoria;

        if($senha_provisoria == 'S' && $rota != 'password.update') {
            return redirect('/naj/password/update');
        }

        return $next($request);
    }

    private function getRotasNaoValida() {
        return [
            'empresa.store',
            'usuario.store',
            'password.update',
            'usuario.update-password',
            'usuario.update-senha-provisoria',
            'empresa.identificador-empresa',
            'usuario.atualizar-dados',
            'sincronizacao.usuarios',
            'monitoramento.diarios.obtermovimentacoesdiario',
            'monitoramento.tribunais.obtermovimentacoestribunal',
            'monitoramento.tribunais.obterpendentestribunal',
            'notificacao.financeiro.pagar-call-send-many',
            'notificacao.financeiro.receber-call-send-many',
            'notificacao.pessoa.aniversariante-call-send-many',
            'notificacao.novos.atividade-andamento-call-send-many',
            'notificacao.agenda.evento-call-send-many',
        ];
    }

}