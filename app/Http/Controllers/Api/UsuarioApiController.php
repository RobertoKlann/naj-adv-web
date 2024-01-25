<?php

namespace App\Http\Controllers\Api;

use Auth;
use JWTAuth;
use App\Models\UsuarioModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\NajApiController;

/**
 * Controllador do usuário para API do CPANEL.
 * 
 * @package    Controllers
 * @subpackage Api
 * @author     Roberto Oswaldo Klann
 * @since      16/01/2020
 */
class UsuarioApiController extends NajApiController {

    public function store($data) {
        $this->setUrlBase(env('CPANEL_URL'));
        $this->setUrl('usuarios?XDEBUG_SESSION_START');
        $this->setToken($this->generationTokenUsuario());
        $response = $this->post($data);

        return $response;
    }

    public function update($data, $key) {
        $this->setUrlBase(env('CPANEL_URL'));
        $this->setUrl('usuarios/' . $key . '?XDEBUG_SESSION_START');
        $this->setToken($this->generationTokenUsuario());
        $response = $this->put($data);

        return $response;
    }

    public function storeUserByInstall($token, $data) {
        $this->setUrlBase(env('CPANEL_URL'));
        $this->setUrl('usuarios?XDEBUG_SESSION_START');
        $this->setToken($token);
        $response = $this->post($data);

        return $response;
    }

    public function getUserByCpf($cpf) {
        $this->setUrlBase(env('CPANEL_URL'));
        $this->setUrl('usuarios/cpf/' . $cpf);
        $this->setToken($this->generationTokenUsuario());
        $response = $this->get();

        return $response;
    }

    public function updatePassword($id, $data) {
        $this->setUrlBase(env('CPANEL_URL'));
        $this->setUrl('usuarios/updatePassword/' . $id);
        $this->setToken($this->generationTokenUsuario());
        $response = $this->put($data);

        return $response;
    }

    public function resetPassword($id, $data) {
        $this->setUrlBase(env('CPANEL_URL'));
        $this->setUrl('usuarios/resetPassword/' . $id);
        $this->setToken($this->generationTokenUsuario());
        $response = $this->put($data);

        return $response;
    }

    public function atualizarDados($id, $data) {
        $this->setUrlBase(env('CPANEL_URL'));
        $this->setUrl('usuarios/atualizarDados/' . $id . '?XDEBUG_SESSION_START');
        $this->setToken($this->generationTokenUsuario());
        $response = $this->put($data);

        return $response;
    }

    public function getAllUsuariosFromPessoa($pessoa) {
        $this->setUrlBase(env('CPANEL_URL'));
        $this->setUrl('usuarios/allFromPessoa/' . $pessoa);
        $this->setToken($this->generationTokenUsuario());
        $response = $this->get();

        return $response;
    }

    public function callBaixaUsuarioCpanel($usuariosBaixado) {
        $this->setUrlBase(env('CPANEL_URL'));
        $this->setUrl('usuarios/baixa?XDEBUG_SESSION_START');
        $this->setToken('xxxxx');
        $response = $this->post($usuariosBaixado);

        return $response;
    }

    public function generationTokenUsuario() {
        $id    = Auth::user()->id;
        $user  = UsuarioModel::where('id', $id)->first();
        $token = JWTAuth::fromUser($user);

        return $token;
    }

}