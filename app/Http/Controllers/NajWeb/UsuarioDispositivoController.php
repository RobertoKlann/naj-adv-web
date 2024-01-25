<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\UsuarioDispositivoModel;
use App\Http\Controllers\Api\UsuarioDispositivoApiController;
use App\Http\Controllers\NajWeb\UsuarioDispositivoMonitoramentoSistemaController;

/**
 * Controller dos Dispositivos do Usuários.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      16/01/2020
 */
class UsuarioDispositivoController extends NajController {

    public function onLoad() {
        $this->setModel(new UsuarioDispositivoModel);
        $this->setMonitoramentoController(new UsuarioDispositivoMonitoramentoSistemaController);
    }

    protected function resolveWebContext($usuarios, $code) {
        return view('najWeb.usuario');
    }

    /**
     * Index da rota de usuários.
     */
    public function index() {
        return view('najWeb.consulta.UsuarioDispositivoConsultaView')->with('is_usuarios', true);
    }

    /**
     * Create da rota de usuários.
     */
    public function create() {
        return view('najWeb.manutencao.UsuarioDispositivoManutencaoView')->with('is_usuarios', true);
    }

    public function allDispositivosUsuario($codigo) {
        $UsuarioDispositivoApiController = new UsuarioDispositivoApiController();
        $result               = $UsuarioDispositivoApiController->getData($codigo);
        $response             = json_decode($result->getBody()->getContents());

        if(!isset($response->status_code) || $response->status_code != '200') {
            $this->throwException($response->naj->mensagem);
        }

        return response()->json($response);
    }

    public function allDispositivosUsuarios($usuarios) {
        $UsuarioDispositivoApiController = new UsuarioDispositivoApiController();
        $result   = $UsuarioDispositivoApiController->getAllDevicesUsers($usuarios);
        $response = json_decode($result->getBody()->getContents());

        if(!isset($response->status_code) || $response->status_code != '200') {
            $this->throwException($response->naj->mensagem);
        }

        return response()->json($response);
    }

    public function update($key) {
        $toUpdate = $this->resolveValidate(
            $this->getModel()->getFilledAttributes()
        );

        $UsuarioDispositivoApiController = new UsuarioDispositivoApiController();
        $result               = $UsuarioDispositivoApiController->update($toUpdate, $key);
        $response             = json_decode($result->getBody()->getContents());

        if(!isset($response->status_code) || $response->status_code != '200') {
            $this->throwException($response->naj->mensagem);
        }

        //utilizado no monitoramento
        $columnsUpdate = [];
        $before = (request()->get('ativo') == 'N') ? 'S' : 'N';
        
        //adicionando no array de colunas alteradas a informação de como era e como ficou
        $columnsUpdate['ativo'] = [
            'before' => $before,
            'now'    => request()->get('ativo')
        ];

        //verificando se precisa registrar o monitoramento
        if($this->getMonitoramentoController()) {
            $this->getMonitoramentoController()->storeMonitoramento(self::UPDATE_ACTION, $key, $columnsUpdate);
        }

        return response()->json($response->naj);
    }

    public function paginate() {
        $UsuarioDispositivoApiController = new UsuarioDispositivoApiController();
        $result   = $UsuarioDispositivoApiController->paginate();
        $response = json_decode($result->getBody()->getContents());

        if(isset($response->total)) {
            return response()->json($response);
        }

        $this->throwException("Erro ao montar a requisição, tente novamente mais tarde!");
    }

}