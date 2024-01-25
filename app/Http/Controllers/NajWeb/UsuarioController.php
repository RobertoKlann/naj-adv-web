<?php

namespace App\Http\Controllers\NajWeb;

use Hash;
use Illuminate\Support\Facades\DB;
use App\Models\UsuarioModel;
use App\Http\Controllers\NajController;
use App\Http\Controllers\NajWeb\ChatController;
use App\Http\Controllers\NajWeb\PessoaController;
use App\Http\Controllers\NajWeb\PessoaClienteController;
use App\Http\Controllers\NajWeb\PessoaUsuarioController;
use App\Http\Controllers\NajWeb\PessoaRelacionamentoUsuario;
use App\Http\Controllers\NajWeb\PessoaUsuario;
use App\Http\Controllers\NajWeb\GrupoPessoaController;
use App\Http\Controllers\NajWeb\UsuarioMonitoramentoSistemaController;
use App\Http\Controllers\Api\UsuarioApiController;

/**
 * Controller dos Usuários.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      09/01/2020
 */
class UsuarioController extends NajController {

    const USER_TYPE_SUPERVISOR = 0;
    const USER_TYPE_ADMIN      = 1;
    const USER_TYPE_USER       = 2;
    const USER_TYPE_CLIENT     = 3;
    const USER_TYPE_PARTNER    = 4;

    public function onLoad() {
        $this->setModel(new UsuarioModel);
        $this->setMonitoramentoController(new UsuarioMonitoramentoSistemaController);
    }

    protected function resolveWebContext($usuarios, $code) {
        return view('najWeb.usuario');
    }

    /**
     * Index da rota de usuários.
     */
    public function index() {
        return view('najWeb.consulta.UsuarioConsultaView')->with('is_usuarios', true);
    }

    /**
     * Create da rota de usuários.
     */
    public function create() {
        return view('najWeb.manutencao.UsuarioManutencaoView')->with('is_usuarios', true);
    }

    public function edit() {
        return view('najWeb.manutencao.UsuarioManutencaoView')->with('is_usuarios', true);
    }

    public function perfil() {
        return view('najWeb.manutencao.PerfilUsuarioManutencaoView');
    }

    public function smtp() {
        return view('najWeb.manutencao.SmtpUsuarioManutencaoView')->with('is_usuarios', true);
    }

    public function estatisticasView() {
        return view('najWeb.consulta.EstatisticasUsuarioManutencaoView')->with('is_usuarios', true);
    }

    public function proximo() {
        $proximo = $this->getModel()->max('id');

        return response()->json($proximo);
    }

    public function handleItems($model = null) {
        $action = $this->getCurrentAction();
        
        if ($action === NajController::DESTROY_ACTION) {
            $this->destroyItems($model);
            
            return;
        }
        
        $this->{"{$action}Items"}($model);
    }
    
    public function storeItems($model) {
        switch($model['usuario_tipo_id']) {
            case self::USER_TYPE_SUPERVISOR:
            case self::USER_TYPE_ADMIN:
            case self::USER_TYPE_USER:
            case self::USER_TYPE_PARTNER:
                $this->afterStoreUserByType($model);
                break;
            case self::USER_TYPE_CLIENT:
                $this->afterStoreUserTypeClient($model);
                break;
        }
    }

    public function updateItems($model) {                
        $codigoPessoa = request()->get('codigo_pessoa_rel_usuario');

        //Se não achou a pessoa so volta pq não tem o que fazer, so chorar mesmo
        if(!$codigoPessoa) return;

        $pessoa = (new PessoaRelacionamentoUsuarioController)->getModel()->verificaSePessoaExisteEmPessoaRelUsuario($codigoPessoa);

        if(!$pessoa)
            $pessoa = (new PessoaRelacionamentoUsuarioController)->getModel()->verificaSePessoaExisteEmPessoaRelCliente($codigoPessoa);

        //Se não achou o relacioanemtno vamos incluir um se for cliente
        if(!$pessoa && $model->usuario_tipo_id == self::USER_TYPE_CLIENT) {
            $this->storePessoaClient(['pessoa_codigo' => $codigoPessoa, 'id' => $model->id]);
        }

        DB::update("UPDATE pessoa_usuario set situacao = ? where CODIGO_PESSOA = ?", [($model['status'] == 'A') ? 'A' : 'I', $codigoPessoa]);
        DB::update("UPDATE pessoa_usuario set perfil = ? where CODIGO_PESSOA = ?", [$this->getConvertTypeUser($model->usuario_tipo_id), $codigoPessoa]);
        DB::update("UPDATE pessoa_usuario set supervisor_agenda = ? where CODIGO_PESSOA = ?", ['S', $codigoPessoa]);

        //verifica se alterou o tipo do usuário, se alterou e era cliente temos que incluir um novo relacionamento e excluir o antigo
        if($model->usuario_tipo_id == self::USER_TYPE_CLIENT) {
            $hasRel = (new UsuarioRelacionamentoController())->getModel()->hasRelacionamentoToUser($model->id);;

            if(!$hasRel) {
                $this->storePessoaClient(['pessoa_codigo' => $codigoPessoa, 'id' => $model->id]);
                DB::delete("DELETE FROM pessoa_rel_usuarios WHERE usuario_id = ?", [$model->id]);
            }
        } else {
            $hasRel = (new PessoaRelacionamentoUsuarioController)->getModel()->hasRelacionamentoToUser($model->id);
            if(!$hasRel) {
                $this->storePessoaRelUsers(['pessoa_codigo' => $codigoPessoa, 'id' => $model->id]);
                DB::delete("DELETE FROM pessoa_rel_clientes WHERE usuario_id = ?", [$model->id]);
            }            
        }
    }

    public function destroyItems($model) {}

    public function update($key) {
        $toUpdate = $this->resolveValidate(
            $this->getModel()->getFilledAttributes()
        );

        $codigoPessoa              = request()->all()['items'][0]['pessoa_codigo'];
        $toUpdate['najWeb']        = 1;
        $toUpdate['codigo_pessoa'] = request()->all()['codigo_pessoa'];
        $toUpdate['pessoa_codigo'] = $codigoPessoa;        
        $toUpdate['items']         = 
        [        
            [
                'pessoa_codigo' => $codigoPessoa,
                'usuario_id'    => request()->get('id')
            ]
        ];
        $toUpdate['senha_alteracao_cadastro'] = request()->get('senha_alteracao_cadastro');

        $UsuarioApiController = new UsuarioApiController();
        $result               = $UsuarioApiController->update($toUpdate, $key);
        $response             = json_decode($result->getBody()->getContents());

        if (!isset($response->status_code) || $response->status_code != '200')
            return $this->resolveResponse(['mensagem' => $response->naj->mensagem], 400);

        if (request()->get('password') && !request()->get('codigoAcesso'))
            request()->merge(['senha_provisoria' => 'S']);

        return parent::update($key);
    }

    public function validateStore($data) {
        if (!request()->get('codigoAcesso'))//Colocando como provisoria a senha
            $data['senha_provisoria'] = 'S';

        //Validando CPF
        $this->validaSeExisteUsuarioComCpfInformado($data);

        $codigoPessoa                = request()->all()['items'][0]['pessoa_codigo'];
        $data['najWeb']              = 1;
        $data['usuarioVeioDoCpanel'] = isset(request()->all()['usuarioVeioDoCpanel']);
        $data['codigo_pessoa']       = request()->all()['codigo_pessoa'];
        $data['pessoa_codigo']       = request()->all()['codigo_pessoa'];
        $data['items']               = 
        [        
            [
                'pessoa_codigo' => $codigoPessoa,
                'usuario_id'    => (isset($data['id'])) ? $data['id'] : 0
            ]
        ];

        $UsuarioApiController = new UsuarioApiController();
        $result = '';

        //Se for a rotina de INSTALL do sistema, então chama um método diferente na inclusão.
        if(request()->get('tokenInstall')) {
            $result = $UsuarioApiController->storeUserByInstall(request()->get('tokenInstall'), $data);
        } else {
            $result = $UsuarioApiController->store($data);
        }
        
        $response = json_decode($result->getBody()->getContents());

        if(!isset($response->status_code) || $response->status_code != '200') {
            $this->throwException($response->naj->mensagem);
        }

        $data['id'] = $response->naj->model->id;

        if(request()->get('tokenInstall') || isset(request()->all()['usuarioVeioDoCpanel'])) {
            $data['password'] = $response->naj->model->password;
        }

        return $data;
    }

    private function afterStoreUserByType($model) {
        $data = [];
        if($model['cpf']) $data = $this->getModel()->getDataByColumn('cpf', $model['cpf'], 'pessoa');

        if(is_array($data) && count($data) > 1) {
            $this->throwException('Registro não inserido, existe duas pessoas com o mesmo CPF.');
        }

        if(!$data) {
            $model['codigo_divisao'] = '1';
            $model['codigo_grupo']   = $this->getCodigoGrupoFromPessoa(($model['usuario_tipo_id'] == 3) ? 'usuario' : 'usuario');

            $pessoa = $this->storePessoa($model);
            $model['pessoa_codigo'] = $pessoa->original['model']['CODIGO'];
            $model['codigo_pessoa'] = $pessoa->original['model']['CODIGO'];
        } else {
            $model['pessoa_codigo'] = $data[0]->CODIGO;
            $model['codigo_pessoa'] = $data[0]->CODIGO;
        }

        $this->storePessoaRelUsers($model, $data);
        $this->storePessoaUser($model, $data);
    }

    private function afterStoreUserTypeClient($model) {
        $data = $this->getModel()->getDataByColumn('cpf', $model['cpf'], 'pessoa');

        if(!$data) {
            $model['codigo_divisao'] = '1';
            $model['codigo_grupo']   = $this->getCodigoGrupoFromPessoa('usuario');

            $pessoa = $this->storePessoa($model);
            $model['pessoa_codigo'] = $pessoa->original['model']['CODIGO'];
            $model['codigo_pessoa'] = $pessoa->original['model']['CODIGO'];
        } else {
            $model['pessoa_codigo'] = $data[0]->CODIGO;
            $model['codigo_pessoa'] = $data[0]->CODIGO;
        }

        $this->storePessoaClient($model, $data);
        $this->storePessoaUser($model, $data);

        //CRIA UM CHAT E RELACIONA O USUÁRIO
        $this->storeChatUsuario($model);
    }

    private function storePessoa($model) {
        $PessoaController = new PessoaController();

        $atributos = [
            'CODIGO'         => ($PessoaController->proximo() + 1),
            'DATA_CADASTRO'  => $model['data_inclusao'],
            'TIPO'           => 'F',
            'SITUACAO'       => 'A',
            'NOME'           => $model['nome'],
            'CODIGO_DIVISAO' => $model['codigo_divisao'],
            'CODIGO_GRUPO'   => $model['codigo_grupo'],
            'CPF'            => $model['cpf']
        ];

        return $PessoaController->store($atributos);
    }

    private function storePessoaRelUsers($model, $data = null) {
        $PessoaRelUsuarioController = new PessoaRelacionamentoUsuarioController();
        $response = $PessoaRelUsuarioController->store(['pessoa_codigo' => $model['pessoa_codigo'], 'usuario_id' => $model['id']]);

        if(!isset($response->original['model'])) {
            $this->throwException('Registro não inserido, tente novamente.');
        }
    }

    private function storePessoaUser($model, $data) {        
        $PessoaUsuarioController = new PessoaUsuarioController();
        $response = $PessoaUsuarioController->getModel()->getDataByColumn('codigo_pessoa', $model['codigo_pessoa'], 'pessoa_usuario');

        //Se já existe o relacionamento não faz nada.
        if($response)
            return;

        $response = $PessoaUsuarioController->store(
            [
                'codigo_pessoa' => $model['codigo_pessoa'],
                'perfil'        => $this->getConvertTypeUser($model['usuario_tipo_id']),
                'externo'       => 'S',
                'situacao'      => 'A',
                'email_origem'  => $model['email_recuperacao'],
                'supervisor_agenda'  => 'S'
            ]
        );

        if(!isset($response->original['model'])) {
            $this->throwException('Registro não inserido, tente novamente.');
        }
    }

    private function storePessoaClient($model) {
        $PessoaClienteController = new PessoaClienteController();
        $response = $PessoaClienteController->store(
            [
                'pessoa_codigo' => $model['pessoa_codigo'],
                'usuario_id'    => $model['id']
            ]
        );

        if(!isset($response->original['model'])) {
            $this->throwException('Registro não inserido, tente novamente.');
        }
    }

    private function getCodigoGrupoFromPessoa($nomeGrupo) {
        $grupo = $this->getModel()->getCodigoGrupoFromPessoa($nomeGrupo);

        if(!$grupo) {
            $GrupoPessoaController = new GrupoPessoaController();
            $GrupoPessoa           = $GrupoPessoaController->store(
                [
                    'codigo'    => ($GrupoPessoaController->proximo() + 1),
                    'grupo'     => $nomeGrupo,
                    'principal' => 'S'
                ]
            );

            return $GrupoPessoa->original['model']['codigo'];
        }

        return $grupo[0]->codigo;
    }

    public function getUserByCpfInCpanel($cpf) {
        $UsuarioApiController = new UsuarioApiController();

        $result   = $UsuarioApiController->getUserByCpf($cpf);
        $response = json_decode($result->getBody()->getContents());

        if($response) {
            return response()->json($response);
        }
    }

    protected function getConvertTypeUser($tipo_usuario) {
        switch($tipo_usuario) {
            case self::USER_TYPE_ADMIN:
                return 'A';
            case self::USER_TYPE_USER:
                return 'U';
            case self::USER_TYPE_PARTNER:
                return 'P';
            case self::USER_TYPE_CLIENT:
                return 'C';
            default:
                return 'S';
        }
    }

    private function validaSeExisteUsuarioComCpfInformado($model) {
        $data = [];
        if($model['cpf']) $data = $this->getModel()->getDataByColumn('cpf', $model['cpf'], 'usuarios');

        if(is_array($data) && count($data) > 0) {
            $this->throwException('Registro não inserido, este CPF já está sendo usado!');
        }
    }

    public function updatePassword($id) {
        $UsuarioApiController = new UsuarioApiController();
        $result   = $UsuarioApiController->updatePassword($id, request()->all());
        $response = json_decode($result->getBody()->getContents());

        if(!isset($response->status_code) || $response->status_code != '200') {
            if($response->mensagem == 'Senha antiga incorreta') {
                return $this->resolveResponse(['mensagem' => 'Senha antiga incorreta!'], 200);
            }
            $this->resolveResponse(['mensagem' => $response->naj->mensagem], 200);
        }

        $parameters        = json_decode(base64_decode($id));
        $usuario           = $this->getModel()->find($parameters->id);
        $usuario->password = $response->password;
        $result = $usuario->save();

        if($result) {
            return $this->resolveResponse(['mensagem' => 'Senha alterada com sucesso!'], 200);
        }

        return $this->resolveResponse(['mensagem' => 'Não foi possível alterar a senha, tente novamente mais tarde!'], 200);
    }

    public function updateSenhaProvisora($id) {
        $parameters = json_decode(base64_decode($id));
        $usuario    = $this->getModel()->find($parameters->id);

        $usuario->senha_provisoria = 'N';
        $result = $usuario->save();

        if($result) {
            return $this->resolveResponse(['mensagem' => 'Status da senha provisoria alterado com sucesso!'], 200);
        }

        return $this->resolveResponse(['mensagem' => 'Não foi possível alterar o status da senha provisória, tente novamente mais tarde!'], 200);
    }

    public function smtpUpdate($id) {
        $parameters = json_decode(base64_decode($id));
        $usuario    = $this->getModel()->find($parameters->id);

        $toUpdate = [
            'smtp_host'  => request()->get('smtp_host'),
            'smtp_login' => request()->get('smtp_login'),
            'smtp_senha' => request()->get('smtp_senha'),
            'smtp_porta' => request()->get('smtp_porta'),
            'smtp_ssl'   => request()->get('smtp_ssl')
        ];

        //utilizado no monitoramento
        $columnsUpdate = [];

        foreach ($toUpdate as $updateColumn => $updateValue) {
            if (trim($usuario->$updateColumn) !== trim($updateValue)) {

                //adicionando no array de colunas alteradas a informação de como era e como ficou
                $columnsUpdate[$updateColumn] = [
                    'before' => $usuario->$updateColumn,
                    'now'    => trim($updateValue)
                ];
            }
        }

        $usuario->smtp_host  = request()->get('smtp_host');
        $usuario->smtp_login = request()->get('smtp_login');
        $usuario->smtp_senha = request()->get('smtp_senha');
        $usuario->smtp_porta = request()->get('smtp_porta');
        $usuario->smtp_ssl   = request()->get('smtp_ssl');

        $result = $usuario->save();

        //verificando se precisa registrar o monitoramento
        if($this->getMonitoramentoController() && count($columnsUpdate) > 0) {
            $this->getMonitoramentoController()->storeMonitoramento(self::UPDATE_ACTION, $usuario, $columnsUpdate);
        }

        if($result) {
            return $this->resolveResponse(['mensagem' => 'E-mail configurado com sucesso!', 'status_code' => 200], 200);
        }

        return $this->resolveResponse(['mensagem' => 'Não foi possível realizar a configuração do E-mail, tente novamente mais tarde!', 'status_code' => 400], 200);
    }

    public function atualizarDados($key) {
        $parameters = json_decode(base64_decode($key));
        $usuario    = $this->getModel()->find($parameters->id);

        $UsuarioApiController = new UsuarioApiController();
        $result   = $UsuarioApiController->atualizarDados($key, request()->all());
        $response = json_decode($result->getBody()->getContents());

        if(!isset($response->status_code) || $response->status_code != '200') {
            if(isset($response->naj->original)) {
                return $this->resolveResponse(['mensagem' => $response->naj->original->naj->mensagem], 200);
            } else {
                return $this->resolveResponse(['mensagem' => $response->naj], 200);
            }
        }

        if(!isset($response->naj->is_update)) {
            request()->merge(['usuarioVeioDoCpanel' => false]);
        }

        //Verificar se precisa alterar o ID do usuário local e dar um MAX() + 1
        //Feito isso aqui para caso no CPANEL o cara esteja com ID 10 e LOCAL 11, o certo é altear o LOCAL PARA 10, mas pode ser que já exista um 10 aqui,
        //Então se existir nós vamos pegar esse 10 e colocar o ID dele para MAX() + 1 e setar o 10 para o cara que veio do CPANEL, doidera mas é o que o Nelson decidiu
        $UsuarioLocalCpanel = $this->getModel()->find($response->naj->model->id);

        if($UsuarioLocalCpanel) {
            if($UsuarioLocalCpanel->cpf != request()->get('cpf')) {
            
                $UsuarioLocalCpanel->id = ($this->getModel()->max('id') + 1);
                $resultadoUpdate = $UsuarioLocalCpanel->save();
            }
        }

        $usuario->id                 = $response->naj->model->id;
        $usuario->password           = $response->naj->model->password;
        $usuario->nome               = request()->get('nome');
        $usuario->cpf                = request()->get('cpf');
        $usuario->apelido            = request()->get('apelido');
        $usuario->login              = request()->get('login');
        $usuario->email_recuperacao  = request()->get('email_recuperacao');
        $usuario->mobile_recuperacao = request()->get('mobile_recuperacao');
        $usuario->senha_provisoria   = 'N';

        $result = $usuario->save();

        if($result) {
            return $this->resolveResponse(['mensagem' => 'Atualização realizada com sucesso!', 'status_code' => 200], 200);
        }

        return $this->resolveResponse(['mensagem' => 'Não foi possível alterar os dados, tente novamente mais tarde!', 'status_code' => 400], 200);
    }

    private function storeChatUsuario($model) {
        $ChatController = new ChatController();

        $max = $ChatController->getModel()->max('id') + 1;
        $nome = '#PUBLICO_' . $max;

        request()->merge(['id_usuario' => $model['id']]);
        return $ChatController->store([
            'data_inclusao' => $model['data_inclusao'],
            'tipo'          => 0,
            'nome'          => $nome
        ]);
    }

    public function dataEstatisticasUser($userId) {
        // return response()->json($this->getModel()->dataEstatisticasUser($userId)); //Desativando esse SQL aqui pq ele não traz as info de web e app
        return response()->json($this->getModel()->getStatistics($userId));
    }

    public function dataByUserTypeClient($userId) {
        return response()->json($this->getModel()->getDataByUserAdmin($userId));
    }

}