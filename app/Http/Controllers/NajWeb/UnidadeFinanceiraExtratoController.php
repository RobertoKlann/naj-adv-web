<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\UnidadeFinanceiraExtratoModel;
use Illuminate\Http\Request;

/**
 * Controller de Unidade Finaceira Extrato.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      12/02/2020
 */
class UnidadeFinanceiraExtratoController extends NajController {
    
    /**
     * Index da rota de Unidade Financeira Extrato
     * @return view
     */
    public function index() {
        return view('najWeb.consulta.UnidadeFinanceiraExtratoConsultaView')->with('is_extrato_financeiro', true);
    }
    
    /**
     * Seta o model de Unidade Financeira Extrato ao carregar o controller
     */
    public function onLoad() {
        $this->setModel(new UnidadeFinanceiraExtratoModel);
    }

    /**
     * Retorna o max(id) no BD 
     * @return integer
     */
    public function proximo() {
        $proximo = $this->getModel()->max('id');
        if(!$proximo){
            $proximo = 0;
        }
        return response()->json($proximo)->content();
    }
    
    /**
     * Retorna as unidades finaceiras com relação com unidade_financeira extrato e
     * com os respectivos "account_id" caso haja a relação entre ambos
     * @return JSON
     */
    public function unidades(){
        $result = $this->getModel()->unidades();
        return response()->json($result)->content();
    }
    
    /**
     * Retorna o saldo da conta virtual armazenado no BD
     * @param string $account_id
     * @return JSON
     */
    public function saldoContaVirtual($account_id){
        $result = $this->getModel()->saldoContaVirtual($account_id);
        return response()->json($result)->content();
    }
    
    /**
     * Retorna o saldo anterior da unidade finaceiro armazenado no BD
     * @param Request $request
     * @return JSON
     */
    public function saldoAnterior(Request $request){
        $uf        = $request->input('uf');
        $data      = $request->input('data');
        $tipo_data = $request->input('tipo_data') ? $request->input('tipo_data') : 1;
        $result    = $this->getModel()->saldoAnterior($uf, $data, $tipo_data);
        return response()->json($result)->content();
    }
    
    /**
     * Altera a data de um registro da unidade finaceiro armazenado no BD
     * @param Request $request
     * @return JSON
     */
    public function editaData(Request $request){
        $id     = $request->input('id');
        $data   = $request->input('data');
        $tipo   = $request->input('tipo');
        $result = $this->getModel()->editaData($id, $data, $tipo);
        return response()->json($result)->content();
    }
    
    /**
     * Retorna o max da data ou data_conciliacao
     * @param numeric $data 0 == DATA, 1 == DATA_CONCILIACAO
     * @return string
     */
    public function maxData($tipo_data){
        $result = $this->getModel()->maxData($tipo_data);
        return response()->json($result)->content();
    }
}