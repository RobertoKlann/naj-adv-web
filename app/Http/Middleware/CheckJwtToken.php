<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use App\Models\UsuarioModel;

class CheckJwtToken
{

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $this->getAuthenticatedUser();

        if (!$user) {
            return $this->respondWithError('Token vazio ou inválido');
        } else {
            try {
                $authUser = UsuarioModel::where('id', $user->id)
                    ->where('usuario_tipo_id', $user->usuario_tipo_id)
                    ->where('login', $user->login)
                    ->first();

                if (!$authUser) {
                    return $this->respondWithError('Usuário não encontrado');
                }

                if ($authUser['status'] != 'A') {
                    return
                        $this->respondWithError('desativado');
                }
            } catch (Exception $e) {
                return $this->respondWithError('Usuário inválido');
            }
        }

        return $next($request);
    }

    public function respondWithError($message, $statusCode = 401)
    {
        return response()->json([
            'naj' => [
                'mensagem' => $message,
            ],
            'status_code' => $statusCode,
        ]);
    }

    public function getAuthenticatedUser()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            $user = false;
        }

        return $user;
    }

    public function validaLogin()
    {
        $user = $this->getAuthenticatedUser();

        if (!$user) {
            return response()->json(false);
        } else {
            return response()->json(true);
        }
    }
}
