<?php

namespace App\Http\Controllers\NajWeb;

use Illuminate\Support\Facades\DB;
use App\Models\PessoaAnexoModel;
use App\Http\Controllers\NajController;
use App\Http\Controllers\NajWeb\AnexoChatStorageController;

/**
 * Controller de Pessoas.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      08/12/2020
 */
class PessoaAnexoController extends NajController {

    public function onLoad() {
        $this->setModel(new PessoaAnexoModel);
    }

    protected function resolveWebContext($usuarios, $code) {
        return view('najWeb.home');
    }

    /**
     *
     * @return type
     */
    public function store($attrs = null) {
        $this->setCurrentAction(self::STORE_ACTION);

        $this->begin();

        $code = 200;

        $data = ['mensagem' => 'Registro inserido com sucesso.', 'model' => null];

        try {
            $toStore = $this->resolveValidate(
                $this->getModel()->getFilledAttributes($attrs)
            );

            //Buscando o ID_DIR das mensagens da pessoa
            $pessoaAnexoDirArquivoMensagem = $this->getModel()->hasPessoaAnexoDirArquivoMensagem($toStore['codigo_pessoa']);
            $toStore['id_dir'] = $pessoaAnexoDirArquivoMensagem[0]->ID;

            //Buscando o proximo ID
            $toStore['id'] = $this->getProximoIdStore();

            if (empty($toStore)) {
                throw new NajException('Empty model.');
            }

            $data['model'] = $toStore;

            $model = $this->getModel()->newInstance();

            $model->fill($toStore);

            $result = $model->save();

            if (is_string($result)) {
                $this->throwException('Erro ao inserir o registro. ' . $result);
            }

            $this->handleItems($model);

            $this->commit();

            $data['persisted'] = $model;
        } catch (Exception $e) {
            $code = 400;

            $data = ['mensagem' =>
                $this->extractMessageFromException($e)
            ];

            $this->rollback();
        }

        return $this->resolveResponse($data, $code);
    }

    public function validateStore($data) {
        //Validando o NOME DO ARQUIVO
        $this->validaNomeArquivoByPessoa($data);

        //Validando se já existe um ID_DIR para os ANEXOS DE MENSAGENS
        $this->validaHasPessoaAnexoDirArquivoMensagem($data);
        
        return $data;
    }

    private function validaNomeArquivoByPessoa($data) {
        $pessoaAnexo = $this->getModel()->findAnexoByNomeByPessoa($data['codigo_pessoa'], $data['nome_arquivo']);

        if(count($pessoaAnexo) > 0) {
            $this->throwException('Registro não inserido, já existe um arquivo com este nome!');
        }
    }

    private function validaHasPessoaAnexoDirArquivoMensagem($data) {
        $pessoaAnexoDirArquivoMensagem = $this->getModel()->hasPessoaAnexoDirArquivoMensagem($data['codigo_pessoa']);

        //Se não tiver precisa INCLUIR
        if(count($pessoaAnexoDirArquivoMensagem) == 0) {
            $this->callStorePessoaAnexoDirArquivoMensagem($data);
        }
    }

    private function callStorePessoaAnexoDirArquivoMensagem($data) {
        $proximo = DB::select("
            SELECT MAX(ID) proximo
              FROM pessoa_anexos_dir
        ");

        $result = DB::insert(
            'INSERT INTO pessoa_anexos_dir (id, codigo_pessoa, descricao, data_criacao) VALUES (?, ?, ?, ?)',
            [
                ($proximo[0]->proximo + 1),
                $data['codigo_pessoa'],
                'ANEXOS DAS MENSAGENS',
                date('Y-m-d')
            ]
        );

        return $result;
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
        $this->callStorePessoaAnexo($model);
    }

    private function callStorePessoaAnexo($model) {
        $AnexoChatStorageController = new AnexoChatStorageController();
        $AnexoChatStorageController->callStorePessoaAnexo($model);
    }

    private function getProximoIdStore() {
        $proximo = DB::select("
            SELECT MAX(ID) proximo
              FROM pessoa_anexos
        ");

        return ($proximo[0]->proximo + 1);
    }

    public function updateItems($model) {}
    
    public function destroyItems($model) {}

}