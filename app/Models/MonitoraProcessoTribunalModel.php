<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo de Monitora Processo Tribunal.
 *
 * @author William Goebel
 * @since 2020-09-14
 */
class MonitoraProcessoTribunalModel extends NajModel {

    protected function loadTable() {
        $this->setTable('monitora_processo_tribunal');
        
        $this->addColumn('id', true);
//        $this->addColumn('codigo_processo'); SE DER PAU EM ALGUM LUGAR DESCOMENTAR ESSA LINHA
        $this->addColumn('id_tribunal');
        $this->addColumn('id_monitoramento');
        $this->addColumn('numero_cnj');
        $this->addColumn('frequencia');
        $this->addColumn('status');
        $this->addColumn('abrangencia');
        
        $this->primaryKey = 'id';
        
    }

    /**
     * Verifica Se Monitoramento Já Existe
     * 
     * @param int $codigo_processo
     * @return bool|string
     */
    public function verificaSeMonitoramentoJaExiste($codigo_processo){
        $sql = "SELECT id FROM monitora_processo_tribunal_rel_prc WHERE codigo_processo = $codigo_processo";
        if(count(DB::select($sql)) > 0){
            return "O monitoramento para este código de processo já existe!";
        }else{
            return false;
        }
    }
    
    /**
     * Verifica Se Processo Existe
     * 
     * @param int $codigo_processo
     * @return bool|string
     */
    public function verificaSeProcessoExiste($codigo_processo){
        $sql = "SELECT CODIGO FROM prc WHERE CODIGO = $codigo_processo";
        if(count(DB::select($sql)) > 0){
            return true;
        }else{
            return "O código de processo $codigo_processo não existe no sistema!";;
        }
    }
    
    /**
     * Insere registro em monitora_processo_tribunal e em monitora_processo_tribunal_rel_prc
     * 
     * @param Request $request
     * @return bool
     */
    public function insere($dados){
        //Inicia transação no BD
        DB::beginTransaction();
        $sql1    = "INSERT INTO monitora_processo_tribunal VALUES ($dados->id, $dados->id_tribunal, null, '$dados->numero_cnj', '$dados->frequencia', '$dados->status', '$dados->abrangencia')";
        $result1 = DB::insert($sql1);
        if($result1){
            $sql2                          = "SELECT max(id) as max FROM monitora_processo_tribunal_rel_prc LIMIT 1";
            $id                            = DB::select($sql2)[0]->max + 1;
            $sql3                          = "SELECT max(id) as max FROM monitora_processo_tribunal";
            $id_monitora_processo_tribunal = DB::select($sql3)[0]->max;
            $sql4                          = "INSERT INTO monitora_processo_tribunal_rel_prc (id, id_monitora_tribunal, codigo_processo) VALUES ($id, $id_monitora_processo_tribunal, $dados->codigo_processo)";
            $result2                       = DB::insert($sql4);
            if($result2){
                DB::commit();
                return true;
            }else{
                DB::rollback();
                return false;
            }
        }else{
            DB::rollback();
            return false;
        }
    }
    
    /**
     * Atualiza registro em monitora_processo_tribunal e em monitora_processo_tribunal_rel_prc
     * 
     * @param object $dados
     * @return JSON
     */
    public function atualiza($dados){
        //Primeiramente vamos buscar o registro do BD
        $sql1 = "SELECT * FROM monitora_processo_tribunal WHERE id = $dados->id";
        $mpt = DB::select($sql1)[0];
        //Vamos verificar se houve alguma alteração no registro
        if(($mpt->frequencia != $dados->frequencia) || ($mpt->status != $dados->status) || ($mpt->abrangencia || $dados->abrangencia)){
            //Vamos alterar o registro no BD
            $sql2 = "UPDATE monitora_processo_tribunal SET frequencia = '$dados->frequencia', status = '$dados->status', abrangencia = '$dados->abrangencia' WHERE id = $dados->id";
            $result = DB::update($sql2);
            if($result == 1){
                return json_decode('{"menssage":"Registro alterado com sucesso."}');
            }else{
                return json_decode('{"menssage":"Não foi possível alterar o registro, contate o suporte."}');
            }
        }else{
            return json_decode('{"menssage":"Nenhuma alteração encontrada."}');
        }
        
    }
    
    /**
     * Atualiza o campo frequencia de todos os registro em monitora_processo_tribunal
     * 
     * @param string $frequencia
     * @return JSON
     */
    public function atualizaFrequencia($frequencia){
        //Primeiro iremos verificar se existe registros para serem atualizados
        $sql = "select count('id')as total from monitora_processo_tribunal;";
        $result = DB::select($sql);
        if($result[0]->total == 0){
            //Se não hoverem regisros iremos retornar true pois nesse caso não foi necessário atualizar os registros então está tudo certo
            return true;
        }
        //Caso existam registros iremos atualizar o campo frequencia de todos os registros
        if(is_null($frequencia) || ($frequencia == 'null')){
            $sql = "UPDATE monitora_processo_tribunal SET frequencia = null;";
        }else{
            $sql = "UPDATE monitora_processo_tribunal SET frequencia = '$frequencia';";
        }
        $result = DB::update($sql);
        if($result > 0){
            return true;
        }else{
            return false;
        }
    }
    
}