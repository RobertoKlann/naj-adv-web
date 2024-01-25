<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Model Conta Virtual.
 *
 * @package    Models
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      03/02/2020
 */
class ContaVirtualModel extends NajModel {

    /**
     * Carrega Tabela
     */
    protected function loadTable() {
        $this->setTable('boleto_cv');

        $this->addColumn('id', true);
        $this->addColumn('codigo_especie')->addJoin('pagamento_especie', 'codigo');
        $this->addColumn('codigo_unidade')->addJoin('unidade_financeira', 'codigo');
        $this->addColumn('account_id');
        $this->addColumn('nome');
        $this->addColumn('live_api_token');
        $this->addColumn('test_api_token');
        $this->addColumn('user_token');
        $this->addColumn('multa');
        $this->addColumn('mora');
        $this->addColumn('banco');
        $this->addColumn('agencia');
        $this->addColumn('tipo_conta');
        $this->addColumn('status');
        $this->addColumn('desconto_percentual');
        $this->addColumn('valor_comissao_boleto');
        $this->addColumn('valor_tarifa_saque');
        $this->addColumn('saque_semanal');
        $this->addColumn('saque_montante');
        $this->addColumn('saque_minimo');
        $this->addColumn('dias_apos');

        $this->addColumnFrom('pagamento_especie', 'especie', 'especie_descricao');
        $this->addColumnFrom('unidade_financeira', 'descricao', 'unidade_descricao');
        
        $this->primaryKey = 'id';
    }
    
    /**
     * Verifica se as naturezas de taxas de cartão e boleto forma informadas
     * no "sys_config" e se os valores informados correspondem a registros en "natureza_financeira"
     * @return string
     */
    public function verificaNaturezaFinanceira(){
        $chaves = ['CODIGO_NATUREZA_DESCONTO', 'CODIGO_NATUREZA_JUROS', 'NATUREZA_TAXA_BOLETO', 'NATUREZA_TAXA_CARTAO'];
        $msg = '';
        foreach ($chaves as $chave){
            $sql = "SELECT SECAO, CHAVE, VALOR 
                    FROM sys_config
                    WHERE SECAO = 'BOLETO_IUGU'
                    AND CHAVE = '$chave'
                    LIMIT 1";
        
            $result = DB::select($sql);
            if(count($result) > 0){
                $sysConfig = $result[0];
                if(!empty($sysConfig->VALOR)){
                    $sql = "SELECT * FROM natureza_financeira
                           WHERE CODIGO = " . $sysConfig->VALOR; 

                    $registro = DB::select($sql);
                    if(!count($registro) > 0 ){
                        $msg .= " O Valor $sysConfig->VALOR da Chave \"$sysConfig->CHAVE\" informado no \"sys_config\" não consta em \"natureza_financeira\". </br>";
                    }
                }else{
                    $msg .= " O valor da chave \"$chave\" não foi informado no \"sys_config\". </br>";
                }
            }else{
                $msg .= " A chave \"$chave\" não foi informada no \"sys_config\". </br>";
            }
        }    
        if(!empty($msg)){
            $msg . " Contate o suporte!";
        }
        return $msg;
    }
}
