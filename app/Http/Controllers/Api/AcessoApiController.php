<?php

namespace App\Http\Controllers\Api;

use Auth;
use JWTAuth;
use App\Models\UsuarioModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\NajApiController;

/**
 * Controllador dos códigos de acesso para API do CPANEL.
 * 
 * @package    Controllers
 * @subpackage Api
 * @author     Roberto Oswaldo Klann
 * @since      25/02/2020
 */
class AcessoApiController extends NajApiController {

    public function update($data, $key) {
        $this->setUrlBase(env('CPANEL_URL'));
        $this->setUrl('usuarios/acessos/' . $key);
        $this->setToken($this->generationTokenUsuario());
        $response = $this->put($data);

        return $response;
    }

    public function validaCodigoAcesso($cpf) {
        $this->setUrlBase(env('CPANEL_URL'));
        $this->setUrl('usuarios/userByCpf/' . $cpf. '?XDEBUG_SESSION_START');
        $this->setToken($this->generationTokenUsuario());
        $response = $this->get();

        return $response;
    }

    public function generationTokenUsuario() {
        $id   = Auth::user()->id;
        $user = UsuarioModel::where('id', $id)->first();

        $token = JWTAuth::fromUser($user);

        return $token;
    }

}