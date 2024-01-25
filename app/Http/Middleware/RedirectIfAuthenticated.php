<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $is_valida_token = request()->get('is_valida_token');
        $is_naj_antigo   = request()->get('is_naj_antigo');

        //Se é o naj antigo, valida o token e tem sessão já
        if($is_naj_antigo == 1 && $is_valida_token == 1 && Auth::guard($guard)->check()) {
            return $next($request);
        }

        //Se é para validar o token e não é login
        if($is_valida_token == '1' && is_null($is_naj_antigo)) {
            //Se é para validar o token e não tem sessão então ta invalido
            if(!Auth::guard($guard)->check()) {
                return response()->json(false);
            }

            $ControllerLogin = new LoginController();
            return $ControllerLogin->validaLogin();
        }

        if (Auth::guard($guard)->check()) {
            return redirect('/naj/home');
        }

        return $next($request);
    }

}