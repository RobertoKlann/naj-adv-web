<?php

namespace App\Http\Controllers\NajWeb;

use App\Models\UsuarioModel;
use App\Http\Controllers\NajController;
use App\Http\Controllers\Api\AcessoApiController;
use App\Http\Controllers\NajWeb\UsuarioController;

/**
 * Controller dos código de acesso dos Usuários.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      24/02/2020
 */
class CodigoAcessoController extends NajController {

    public function onLoad() {
        $this->setModel(new UsuarioModel);
    }

    protected function resolveWebContext($usuarios, $code) {}

    public function store($attrs = null) {
        $UsuarioController = new UsuarioController();
        $usuario = $this->getModel()->find(request()->get('id'));

        if($usuario) {
            return $UsuarioController->update(base64_encode(json_encode(['id' => request()->get('id')])));
        } else {
            return $UsuarioController->store($attrs);
        }
    }

    public function update($key) {
        $data                = request()->all();
        $AcessoApiController = new AcessoApiController();
        $result              = $AcessoApiController->update($data, $key);
        $response            = json_decode($result->getBody()->getContents());

        if(!isset($response->status_code) || $response->status_code != '200') {
            $this->throwException($response->naj);
        }

        return response()->json(['mensagem' => 'Código de acesso validado com sucesso!']);
    }

    public function validaCodigoAcesso($cpf) {
        $AcessoApiController = new AcessoApiController();
        $result              = $AcessoApiController->validaCodigoAcesso($cpf);
        $response            = json_decode($result->getBody()->getContents());

        if(isset($response)) {
            return response()->json($response);
        }
    }

}