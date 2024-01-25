<?php

namespace App\Http\Controllers\Api;

use Auth;
use JWTAuth;
use App\Models\UsuarioModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\NajApiController;

/**
 * Controllador dos dispositivos do usuário para API do CPANEL.
 * 
 * @package    Controllers
 * @subpackage Api
 * @author     Roberto Oswaldo Klann
 * @since      04/02/2020
 */
class UsuarioDispositivoApiController extends NajApiController {

    public function getData($codigoUser) {
        $this->setUrlBase(env('CPANEL_URL'));
        $this->setUrl('usuarios/' . $codigoUser . '/dispositivos');
        $this->setToken($this->generationTokenUsuario());
        $response = $this->get();

        return $response;
    }

    public function getAllDevicesUsers($usuarios) {        
        $this->setUrlBase(env('CPANEL_URL'));
        $this->setUrl('usuarios/dispositivosAll');
        $this->setToken($this->generationTokenUsuario());
        $response = $this->post($usuarios);

        return $response;
    }

    public function update($data, $key) {
        $this->setUrlBase(env('CPANEL_URL'));
        $this->setUrl('usuarios/dispositivos/' . $key);
        $this->setToken($this->generationTokenUsuario());
        $response = $this->put($data);

        return $response;
    }

    public function paginate() {        
        $this->setUrlBase(env('CPANEL_URL'));
        $this->setUrl('usuarios/dispositivos/paginate');
        $this->setToken($this->generationTokenUsuario());
        $response = $this->get(['f' => request()->query('f')]);

        return $response;
    }

    public function getWithDispositivoOrEmpty($pessoas) {
        $this->setUrlBase(env('CPANEL_URL'));
        $this->setUrl('usuarios/dispositivosAll/getWithDispositivoOrEmpty');
        $this->setToken($this->generationTokenUsuario());
        $response = $this->post(base64_encode(json_encode($pessoas)));

        return $response;
    }

    public function generationTokenUsuario() {
        $id   = Auth::user()->id;
        $user = UsuarioModel::where('id', $id)->first();

        $token = JWTAuth::fromUser($user);

        return $token;
    }

}