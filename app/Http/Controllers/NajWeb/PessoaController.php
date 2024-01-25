<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\PessoaModel;
use App\Models\GrupoPessoaModel;
use App\Models\DivisaoModel;

/**
 * Controller de Pessoas.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @author     William Goebel
 * @since      17/01/2020
 */
class PessoaController extends NajController {

    /**
     * Seta o model de Pessoa ao carregar o controller
     */
    public function onLoad() {
        $this->setModel(new PessoaModel);
    }

    protected function resolveWebContext($pessoas, $code) {
        return view('najWeb.pessoa');
    }

    /**
     * Retorna o max(id) no BD 
     * @return integer
     */
    public function proximo() {
        $proximo = $this->getModel()->max('codigo');
        if(!$proximo){
            $proximo = 0;
        }
        return response()->json($proximo)->content();
    }
    
    /**
     * Create da rota de Pessoa
     * @return view
     */
    public function create() {
        return view('najWeb.manutencao.PessoaManutencaoView');
    }

    /**
     * Obtêm todas as pessoas que contenham o filtro contido no nome 
     * 
     * @param string $filter
     * @return JSON
     */
    public function getPessoasFilter($filter) {
        $response = $this->getModel()->allPessoasInFilter($filter);

        return response()->json([
            'data' => $response
        ]);
    }
    
    /**
     * Obtêm a pessoas que contenham o filtro contido no nome 
     * 
     * @param string $filter
     * @return JSON
     */
    public function getPessoaFilter($filter) {
        $response = $this->getModel()->getPessoaByNome($filter);

        return response()->json($response);
    }

    public function getPessoasUsuarioInFilter($filter) {
        $response = $this->getModel()->getPessoasUsuarioInFilter($filter);

        return response()->json([
            'data' => $response
        ]);
    }

    /**
     * Obtêm pessoa pelo seu cpf
     * 
     * @param string $cpf
     * @return JSON
     */
    public function getPessoaByCpf($cpf) {
        return response()->json($this->getModel()->getPessoaByCpf($cpf));
    }
    
    /**
     * Obtêm pessoa pelo seu cnpj
     * 
     * @param string $cnpj
     * @return JSON
     */
    public function getPessoaByCnpj($cnpj) {
        return response()->json($this->getModel()->getPessoaByCnpj($cnpj));
    }
    
    /**
     * Obtêm todos os registros de Divisão
     * 
     * @return JSON
     */
    public function getAllDivisao() {
        $divisaoModel = new DivisaoModel();
        return response()->json($divisaoModel->getAllDivisao());
    }
    
    /**
     * Obtêm todos os registros de Grupo Pessoa onde grupo seja igual a principal
     * 
     * @return JSON
     */
    public function getAllGrupoPessoa() {
        $grupoPessoaModel = new GrupoPessoaModel();
        return response()->json($grupoPessoaModel->getAllGrupoPessoa());
    }

    public function getPessoasFisicaByNome($name) {
        return response()->json(['data' => $this->getModel()->getPessoasFisicaByNome($name)]);
    }

    public function getGrupoClienteRelacionadas($filter) {
        return response()->json(['data' => $this->getModel()->getGrupoClienteRelacionadas($filter)]);
    }

    public function getGrupoClienteRelacionadasByCodigo($filter) {
        return response()->json(['data' => $this->getModel()->getGrupoClienteRelacionadasByCodigo($filter)]);
    }

    public function allPessoaGrupoFromChat() {
        return response()->json($this->getModel()->allPessoaGrupoFromChat());
    }

    public function getQuantidadeByCardPessoaAniversariantes() {
        return response()->json($this->getModel()->getQuantidadeByCardPessoaAniversariantes());
    }

}