<?php

namespace App\Http\Controllers\NajWeb;

use App\Estrutura;
use App\Http\Controllers\NajController;
use App\Models\ValidacaoProcessosModel;
use App\Models\MonitoraProcessoTribunalModel;
use App\Models\MonitoraProcessoTribunalRelPrcModel;
use App\Exceptions\NajException;
use Illuminate\Support\Facades\DB;

/**
 * Controller de Validacao Processos.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      31/03/2021
 */
class ValidacaoProcessosController extends NajController {

    /**
     * Seta o model de Validacao Processos ao carregar o controller
     */
    public function onLoad() {
        $this->setModel(new ValidacaoProcessosModel);
    }

    /**
     * Retorna o max(id) no BD 
     * @return JSON
     */
    public function proximo() {
        $proximo = $this->getModel()->max('CODIGO');
        if(!$proximo){
            $proximo = 0;
        }
        return response()->json($proximo)->content();
    }

    public function monitorarTodosOsProcessosValidosAtivos($situacao, $nomeArquivoLog = "ValidacaoProcessosController/monitorarTodosOsProcessosValidosAtivos"){
        try{
            $count_monitoramentos_cadastrados = 0;
            // obtêm o total de processos válidos e ativos
            if($situacao == "todos"){
                $situacao = null;
            }
            $data                      = $this->getModel()->obtemTodosOsProcessosDisponiveis($situacao);
            $totalQuotas               = $this->getModel()->getTotalQuotas();
            $totalMonitoramentosAtivos = $this->getModel()->getTotalMonitoramentosAtivos();     
            if($totalMonitoramentosAtivos > $totalQuotas){
                Throw new NajException("A quota de cadastro de monitoramentos é de $totalQuotas monitoramentos, no momento já existem $totalMonitoramentosAtivos processo já monitorados no sistema, sendo assim não é possível cadastrar novos monitoramentos, entre em contato com o suporte técnico.");
            }
            if(($totalMonitoramentosAtivos + count($data)) > $totalQuotas){
                Throw new NajException("A quota de cadastro de monitoramentos é de $totalQuotas monitoramentos, no momento já existem $totalMonitoramentosAtivos processo já monitorados no sistema, e você está tentando adicionar " . count($data) . " novos monitoramentos, sendo assim não é possível cadastrar novos monitoramentos, entre em contato com o suporte técnico.");
            }
            foreach ($data as $index => $registro){
                if(($data[$index]->CNJ_VALIDO) && (!$data[$index]->REVISAR_INSTANCIA) && (!$data[$index]->MONITORADO)){
                    DB::beginTransaction();
                    //Seta os valores do 'monitota_processo_tribunal_buscas' em seus respectivos campos
                    $monitoraProcessoTribunalModel                   = new MonitoraProcessoTribunalModel();
                    $monitoraProcessoTribunalModel->id               = MonitoraProcessoTribunalModel::max('id') + 1;
                    $monitoraProcessoTribunalModel->id_tribunal      = 0;
                    $monitoraProcessoTribunalModel->id_monitoramento = null;
                    $monitoraProcessoTribunalModel->numero_cnj       = $data[$index]->NUMERO_PROCESSO_NEW;
                    $monitoraProcessoTribunalModel->frequencia       = null;
                    $monitoraProcessoTribunalModel->status           = 'A';
                    $monitoraProcessoTribunalModel->abrangencia      = 0;

                    //Salva registro
                    $ok = $monitoraProcessoTribunalModel->save();
                    if(!$ok){
                        Throw new NajException('Erro ao salvar registro em monitota_processo_tribunal.');
                    }

                    //Seta os valores do 'monitota_processo_tribunal_buscas' em seus respectivos campos
                    $monitoraProcessoTribunalRelPrcModel                       = new MonitoraProcessoTribunalRelPrcModel();
                    $monitoraProcessoTribunalRelPrcModel->id                   = MonitoraProcessoTribunalModel::max('id') + 1;
                    $monitoraProcessoTribunalRelPrcModel->codigo_processo      = $data[$index]->CODIGO;
                    $monitoraProcessoTribunalRelPrcModel->id_monitora_tribunal = $monitoraProcessoTribunalModel->id ;
                    //Salva registro
                    $ok = $monitoraProcessoTribunalRelPrcModel->save();
                    if(!$ok){
                        Throw new NajException('Erro ao salvar registro em monitota_processo_tribunal_rel_prc.');
                    }

                    unset($monitoraProcessoTribunalModel);
                    unset($monitoraProcessoTribunalRelPrcModel);
                    
                    $count_monitoramentos_cadastrados++;
                    DB::commit();
                }
            }
            //Retorno de sucesso
            return Estrutura::responseNaj("$count_monitoramentos_cadastrados monitoramentos cadastrados!");
        } catch (NajException $e) {
            DB::rollback();
            return Estrutura::responseNajExeption($e, $nomeArquivoLog);
        } catch (Exception $e) {
            DB::rollback();
            return Estrutura::responseNajExeption($e, $nomeArquivoLog);
        } catch (Error $e) {
            DB::rollback();
            return Estrutura::responseNajExeption($e, $nomeArquivoLog);
        }
    }
}
