<?php

namespace App\Http\Controllers\NajWeb;

use App\Models\UsuarioModel;
use App\Models\SysConfigModel;
use App\Models\SincronizacaoModel;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\NajController;
use App\Http\Controllers\NajWeb\UsuarioController;
use App\Http\Controllers\Api\UsuarioApiController;
use App\Http\Controllers\NajWeb\UsuarioMonitoramentoSistemaController;

/**
 * Controller das sincronizações do sistema.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      18/11/2020
 */
class SincronizacaoController extends NajController {

    const USER_TYPE_SUPERVISOR = 0;
    const USER_TYPE_ADMIN      = 1;
    const USER_TYPE_USER       = 2;
    const USER_TYPE_CLIENT     = 3;
    const USER_TYPE_PARTNER    = 4;

    public function onLoad() {
        $this->setModel(new SincronizacaoModel);
    }

    public function sincronizacaoUsuarios() {
        $login  = request()->get('login');
        $senha  = request()->get('password');
        $status = request()->get('status');

        $LoginController = new LoginController();

        if(!$login || !$senha || !$status) {
            //Faz logout para matar a sessão
            $LoginController->logout(request());

            return response()->json(['status_code' => 400, 'message' => 'Ta faltando algum parametro na requisição!']);
        }

        $LoginController->login(request());

        $SysConfigModel = new SysConfigModel();
        $empresa        = $SysConfigModel->existsEmpresa(['SECAO' => 'CPANEL', 'CHAVE' => 'CLIENTE_ID']);

        //Se não tiver a empresa volta
        if(!is_array($empresa) || count($empresa) == 0) {
            return response()->json(['status_code' => 400, 'message' => 'Não encontrou a empresa!']);
        }

        $UsuarioController    = new UsuarioController();
        $UsuarioApiController = new UsuarioApiController();
        $result               = $UsuarioApiController->getAllUsuariosFromPessoa($empresa[0]->VALOR);
        $usuarios             = json_decode($result->getBody()->getContents());

        if(!is_array($usuarios)) {
            return response()->json(['status_code' => 400, 'message' => 'Aconteceu um erro na requisição ao CPANEL ou não existe usuários para essa advocacia!']);
        }

        request()->merge(['usuarioVeioDoCpanel' => true]);
        $usuariosBaixado = [];
        $usuariosBaixado['usuarios'] = [];
        foreach($usuarios as $usuario) {
            $UsuarioModel = $UsuarioController->getModel()->where('cpf', $usuario->cpf)->first();

            //Validando se tem o usuário
            if(!$UsuarioModel) continue;

            //Pulando o usuário SUPEVISOR
            //No dia 25/03/21 NELSON decidiu sinconizar o SUPERVISOR pq ele estava alterando os dados do usuario e não pegava na sincronização
            // if($UsuarioModel->usuario_tipo_id == 0) continue;

            if(!$usuario) continue;

            //Verificando se tem que DESATIVAR o usuário
            if(!$this->dataUltimoAcessoValida($UsuarioModel)) {
                $UsuarioModel->status          = 'B';
                $UsuarioModel->data_baixa      = date('Y-m-d');
                $usuariosBaixado['usuarios'][] = $usuario->id;
            }

            //Verificar se precisa alterar o ID do usuário local e dar um MAX() + 1
            //Feito isso aqui para caso no CPANEL o cara esteja com ID 10 e LOCAL 11, o certo é altear o LOCAL PARA 10, mas pode ser que já exista um 10 aqui,
            //Então se existir nós vamos pegar esse 10 e colocar o ID dele para MAX() + 1 e setar o 10 para o cara que veio do CPANEL, doidera mas é o que o Nelson decidiu
            $UsuarioLocalCpanel = $UsuarioController->getModel()->find($usuario->id);

            if($UsuarioLocalCpanel) {
                if($UsuarioLocalCpanel->id != $UsuarioModel->id && $UsuarioLocalCpanel->cpf != $UsuarioModel->cpf) {
                    $UsuarioLocalCpanel->id = ($UsuarioController->getModel()->max('id') + 1);
                    $resultadoUpdate = $UsuarioLocalCpanel->save();
                }
            }

            // dd($usuario->data_inclusao, $UsuarioModel->data_inclusao);

            $UsuarioModel->id                 = $usuario->id;
            $UsuarioModel->login              = $usuario->login;
            $UsuarioModel->password           = $usuario->password;
            $UsuarioModel->data_inclusao      = $usuario->data_inclusao;
            $UsuarioModel->data_baixa         = $usuario->data_baixa;
            $UsuarioModel->email_recuperacao  = $usuario->email_recuperacao;
            $UsuarioModel->mobile_recuperacao = $usuario->mobile_recuperacao;
            $UsuarioModel->nome               = $usuario->nome;
            $UsuarioModel->apelido            = $usuario->apelido;
            $UsuarioModel->cpf                = $usuario->cpf;
            $UsuarioModel->status             = $usuario->status;


            $columnsUpdate = [];

            if ($UsuarioModel->getOriginal('id') != trim($UsuarioModel->id))
                $columnsUpdate['id'] = ['before' => $UsuarioModel->getOriginal('id'), 'now' => trim($UsuarioModel->id)];

            if ($UsuarioModel->getOriginal('login') != trim($UsuarioModel->login))
                $columnsUpdate['login'] = ['before' => $UsuarioModel->getOriginal('login'), 'now' => trim($UsuarioModel->login)];

            if ($UsuarioModel->getOriginal('data_inclusao') != trim($UsuarioModel->data_inclusao))
                $columnsUpdate['data_inclusao'] = ['before' => $UsuarioModel->getOriginal('data_inclusao'), 'now' => trim($UsuarioModel->data_inclusao)];

            if ($UsuarioModel->getOriginal('data_baixa') != trim($UsuarioModel->data_baixa))
                $columnsUpdate['data_baixa'] = ['before' => $UsuarioModel->getOriginal('data_baixa'), 'now' => trim($UsuarioModel->data_baixa)];

            if ($UsuarioModel->getOriginal('email_recuperacao') != trim($UsuarioModel->email_recuperacao))
                $columnsUpdate['email_recuperacao'] = ['before' => $UsuarioModel->getOriginal('email_recuperacao'), 'now' => trim($UsuarioModel->email_recuperacao)];

            if ($UsuarioModel->getOriginal('mobile_recuperacao') != trim($UsuarioModel->mobile_recuperacao))
                $columnsUpdate['mobile_recuperacao'] = ['before' => $UsuarioModel->getOriginal('mobile_recuperacao'), 'now' => trim($UsuarioModel->mobile_recuperacao)];

            if ($UsuarioModel->getOriginal('nome') != trim($UsuarioModel->nome))
                $columnsUpdate['nome'] = ['before' => $UsuarioModel->getOriginal('nome'), 'now' => trim($UsuarioModel->nome)];

            if ($UsuarioModel->getOriginal('apelido') != trim($UsuarioModel->apelido))
                $columnsUpdate['apelido'] = ['before' => $UsuarioModel->getOriginal('apelido'), 'now' => trim($UsuarioModel->apelido)];

            if ($UsuarioModel->getOriginal('cpf') != trim($UsuarioModel->cpf))
                $columnsUpdate['cpf'] = ['before' => $UsuarioModel->getOriginal('cpf'), 'now' => trim($UsuarioModel->cpf)];

            if ($UsuarioModel->getOriginal('status') != trim($UsuarioModel->status))
                $columnsUpdate['status'] = ['before' => $UsuarioModel->getOriginal('status'), 'now' => trim($UsuarioModel->status)];

            $UsuarioModel->save();

            if (count($columnsUpdate) > 0) {
                $monitoramento = new UsuarioMonitoramentoSistemaController();
                $monitoramento->storeMonitoramento('update', $UsuarioModel, $columnsUpdate);
            }
        }

        //Verifica se tem que baixar alguem no CPANEL
        if(count($usuariosBaixado['usuarios']) > 0) {
            $usuariosBaixado['auto_cadastro_naj_adv_web'] = true;
            $usuariosBaixado['pessoa']                    = $empresa[0]->VALOR;
            $this->callBaixaUsuarioCpanel($usuariosBaixado);
        }

        //Faz logout para matar a sessão
        $LoginController->logout(request());

        return response()->json(['status_code' => 200, 'message' => 'Usuários sincronizados com sucesso!']);
    }

    private function dataUltimoAcessoValida($UsuarioModel) {        
        //Validando se o cara já acessou o sistema
        if(!$UsuarioModel->ultimo_acesso) return true;

        $data_prazo_acesso = '';
        $ultimo_acesso     = explode(' ', $UsuarioModel->ultimo_acesso);
        $ultimo_acesso     = str_replace('-', '/', $ultimo_acesso[0]);
        $data_atual        = date('Y/m/d');

        switch($UsuarioModel->usuario_tipo_id) {
            case self::USER_TYPE_ADMIN:
            case self::USER_TYPE_USER:
                $data_prazo_acesso = date('Y/m/d', strtotime('-90 days', strtotime($data_atual)));
                break;

            case self::USER_TYPE_CLIENT:
            case self::USER_TYPE_PARTNER:
                $data_prazo_acesso = date('Y/m/d', strtotime('-365 days', strtotime($data_atual)));
                break;
        }

        $data_prazo  = \DateTime::createFromFormat ('Y/m/d', $data_prazo_acesso);
        $data_acesso = \DateTime::createFromFormat ('Y/m/d', $ultimo_acesso);

        return $data_acesso > $data_prazo;
    }

    private function callBaixaUsuarioCpanel($usuariosBaixado) {
        $UsuarioApiController = new UsuarioApiController();
        $result               = $UsuarioApiController->callBaixaUsuarioCpanel($usuariosBaixado);
        $response             = json_decode($result->getBody()->getContents());
    }

}