<?php

namespace App\Http\Controllers\NajWeb;

use Auth;
use App\Models\ModuloModel;
use App\Models\UsuarioPermissaoModel;
use App\Http\Controllers\NajController;
use App\Http\Controllers\NajWeb\UsuarioPermissaoMonitoramentoSistemaController;
use App\Models\PessoaRelacionamentoUsuarioModel;

/**
 * Controller das Permissões do Usuário.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      16/01/2020
 */
class UsuarioPermissaoController extends NajController {

    public function onLoad() {
        $this->setModel(new UsuarioPermissaoModel);
        $this->setMonitoramentoController(new UsuarioPermissaoMonitoramentoSistemaController);
    }

    protected function resolveWebContext($usuarios, $code) {
        return view('najWeb.UsuarioPermissaoConsultaView');
    }

    /**
     * Index da rota de permissões do usuários.
     */
    public function index() {
        return view('najWeb.consulta.UsuarioPermissaoConsultaView')->with('is_usuarios', true);
    }

    /**
     * Create da rota de permissões do suários.
     */
    public function create() {
        return view('najWeb.manutencao.UsuarioPermissaoManutencaoView')->with('is_usuarios', true);
    }

    public function proximo() {
        $proximo = $this->getModel()->max('id');

        return response()->json($proximo);
    }

    public function store($attrs = null) {
        $this->setCurrentAction(self::STORE_ACTION);

        $this->begin();

        $code = 200;

        $data = ['mensagem' => 'Registro inserido com sucesso.', 'model' => null];

        $pessoaCodigo = null;
        $bUpdate      = false;
        $bStore       = false;

        //utilizado no monitoramento
        $columnsUpdate = [];

        try {
            $aPermissaoDivisao = request()->get('permissao');
            foreach($aPermissaoDivisao as $permissaoDivisao) {
                foreach($permissaoDivisao as $permissao) {
                    $pessoaCodigo = $permissao['codigo_pessoa'];

                    //Validando se é STORE e se foi realmente informado algo, se não vai acabar incluindo uma permissão sem nada liberado, oq não faz sentido
                    if($permissao['hasPermissao'] == 'N' && $permissao['acessar'] == 'N' && $permissao['pesquisar'] == 'N' && $permissao['incluir'] == 'N' && $permissao['alterar'] == 'N' && $permissao['excluir'] == 'N') continue;

                    //Validando se o modulo é global, se for tem que excluir para não ter registro duplicado, mais uma peculiaridade de permissões
                    if($this->moduloIsGlobal($permissao['modulo'])) {
                        $this->getModel()->deletePermissaoGlobalByModulo($permissao['codigo_pessoa'], $permissao['modulo']);
                    }

                    //Verificando se é UPDATE ou STORE, a rotina de PERMISSÃO tem essas peculiaridades, loucura total.
                    if($permissao['hasPermissao'] == 'S' && !$this->moduloIsGlobal($permissao['modulo'])) {
                        $ModuloModel    = new ModuloModel;
                        $PermissaoModel = $ModuloModel->hasPermissaoFromModulo($permissao['modulo'], $permissao['codigo_divisao'], $permissao['codigo_pessoa']);

                        foreach($permissao as $updateColumn => $updateValue) {

                            //validando se tem a coluna no array
                            if(!isset($PermissaoModel[0]->$updateColumn)) continue;

                            //pegando o que alterou de fato
                            if(trim($PermissaoModel[0]->$updateColumn) !== trim($updateValue)) {
                
                                //adicionando no array de colunas alteradas a informação de como era e como ficou
                                $columnsUpdate['divisao'] = $permissao['codigo_divisao'];
                                $columnsUpdate[$updateColumn] = [
                                    'modulo'  => $permissao['modulo'],
                                    'before'  => $PermissaoModel[0]->$updateColumn,
                                    'now'     => trim($updateValue)
                                ];
                            }
                        }

                        $this->getModel()->executaUpdate($permissao);

                        $bUpdate = true;
                    } else {
                        $this->getModel()->executaStore($permissao);
                        $bStore = true;
                    }
                }
            }

            //verificando se precisa registrar o monitoramento e se foi incluido alguma coisa
            if($this->getMonitoramentoController() && $bStore) {
                $this->getMonitoramentoController()->storeMonitoramento(self::STORE_ACTION, $pessoaCodigo);
            }

            //verificando se precisa registrar o monitoramento e se foi alterado alguma coisa
            if($this->getMonitoramentoController() && $bUpdate && count($columnsUpdate) > 0) {
                $this->getMonitoramentoController()->storeMonitoramento(self::UPDATE_ACTION, $pessoaCodigo, $columnsUpdate);
            }            

            $this->commit();
        } catch (Exception $e) {
            $code = 400;

            $data = ['mensagem' =>
                $this->extractMessageFromException($e)
            ];

            $this->rollback();
        }

        return $this->resolveResponse($data, $code);
    }

    public function copiar() {
        $this->setCurrentAction(self::STORE_ACTION);

        $this->begin();

        $code = 200;

        $data = ['mensagem' => 'Registro inserido com sucesso.', 'model' => null];

        try {
            $pessoa_codigo         = request()->get('pessoa_codigo');
            $pessoa_codigo_destino = request()->get('pessoa_codigo_destino');
            $aPermissao            = $this->getModel()->getAllModulosByUsuario($pessoa_codigo);

            if(count($aPermissao) == 0) {
                return $this->resolveResponse(['mensagem' => "O usuário informado não possui permissões!"], 400);
            }
            
            //Dropando todas as permissões que existam para esse cara
            $this->getModel()->deleteAllPermissaoByUsuario($pessoa_codigo_destino);
            foreach($aPermissao as $permissao) {
                $permissaoFormatted = $this->convertObjectInArray($permissao);
                $permissaoFormatted['codigo_pessoa'] = $pessoa_codigo_destino;
                $this->getModel()->executaStore($permissaoFormatted);
            }

            $this->commit();
        } catch (Exception $e) {
            $code = 400;

            $data = ['mensagem' =>
                $this->extractMessageFromException($e)
            ];

            $this->rollback();
        }

        return $this->resolveResponse($data, $code);
    }

    private function convertObjectInArray($permissao) {
        return [
            'id'             => $permissao->ID,
            'codigo_pessoa'  => $permissao->CODIGO_PESSOA,
            'codigo_divisao' => $permissao->CODIGO_DIVISAO,
            'modulo'         => $permissao->MODULO,
            'aplicacao'      => $permissao->APLICACAO,
            'acessar'        => $permissao->ACESSAR,
            'pesquisar'      => $permissao->PESQUISAR,
            'alterar'        => $permissao->ALTERAR,
            'incluir'        => $permissao->INCLUIR,
            'excluir'        => $permissao->EXCLUIR
        ];
    }

    private function moduloIsGlobal($modulo) {
        return ModuloModel::moduloIsGlobal($modulo);
    }

    public function permissions() {
        $pessoa = (new PessoaRelacionamentoUsuarioModel)->getRelacionamentosUsuario(Auth::user()->id);

        return response()->json(['permissions' => $this->getModel()->getAllModulosByUsuario($pessoa[0]->pessoa_codigo)]);
    }

    public function handleItems($model = null) {
        $action = $this->getCurrentAction();
        
        if ($action === NajController::DESTROY_ACTION) {
            $this->destroyItems($model);
            
            return;
        }
        
        $this->{"{$action}Items"}($model);
    }
    
    public function storeItems($model) {}

    public function updateItems($model) {
        
    }
    
    public function destroyItems($model) {}

}