<?php

namespace App\Http\Controllers\NajWeb;

use Exception;
use Error;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Estrutura;
use App\FiltraEnvolvidos;
use App\Exceptions\NajException;
use App\Http\Controllers\NajWeb\MonitoramentoController;
use App\Models\TermoMonitoradoModel;
use App\Models\MonitoramentoDiarioModel;
use App\Models\MonitoraTermoProcessoModel;
use App\Models\MonitoraTermoEnvolvidosModel;
use App\Models\MonitoraDiariosModel;

/**
 * Controller do Monitoramento dos Diarios.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      05/05/2020
 */
class MonitoramentoDiarioController extends MonitoramentoController {

    /**
     * Seta o model de Monitoramento Diario ao carregar o controller
     */
    public function onLoad() {
        $this->setModel(new MonitoramentoDiarioModel);
    }

    /**
     * Index da rota de Monitoramento Diario
     * 
     * @return view
     */
    public function index() {
        return view('najWeb.consulta.MonitoramentoDiarioConsultaView')->with('ignora_css_datatable', false)->with('is_monitoramento_diarios', true);
    }

    /**
     * Retorna o max(id) no BD 
     * 
     * @return JSON
     */
    public function proximo() {
        $proximo = $this->getModel()->max('id');
        if(!$proximo){
            $proximo = 0;
        }
        return response()->json($proximo)->content();
    }

    /**
     * Busca o nome de todos os termos do BD
     * 
     * @return array
     */
    public function buscaNomeDosTermos(){
        $termoMonitoradoModel = new TermoMonitoradoModel();
        return $termoMonitoradoModel->buscaNomeDosTermos();
    }
    
    /**
     * Seta o o registro como lido no BD
     * 
     * @return JSON
     */
    public function setaRegistroLido($id){
        $return = $this->getModel()->setaRegistroLido($id);
        if($return){
            return Estrutura::responseNaj("Registro setado como lido com sucesso no BD.");
        }
        return Estrutura::responseNaj("Nenhuma alteração foi realizada no registro.", 400);
    }    
    
    /**
     * Atualiza o campo "pessoa_codigo" em "monitoramento_termo_envolvidos"
     * 
     * @return JSON
     */
    public function atualizaEnvolvido(Request $request){
        $pessoa_codigo  = $request->input('codigo_pessoa');
        $nome_envolvido = $request->input('nome_envolvido');
        $tipo_envolvido = $request->input('tipo_envolvido');
        $return = $this->getModel()->atualizaEnvolvido($pessoa_codigo, $nome_envolvido, $tipo_envolvido);
        
        if($return){
            return Estrutura::responseNaj("Registro atualizado com sucesso no BD.");
        }
        return Estrutura::responseNaj("Nenhuma alteração foi realizada no registro.", 400);
    }
    
    /**
     * Busca o total de novas publicações 
     * 
     * @return JSON
     */
    public function totalPublicacoesNovas(){
        $return = $this->getModel()->totalPublicacoesNovas();
        return Estrutura::responseNaj($return);
    }
    
    /**
     * Busca o total de Pendentes
     * 
     * @return JSON
     */
    public function totalPublicacoesPendentes(){
        $return = $this->getModel()->totalPublicacoesPendentes();
        return Estrutura::responseNaj($return);
    }
    
    /**
     * Busca o total de Descartados
     * 
     * @return JSON
     */
    public function totalPublicacoesDescartados(){
        $return = $this->getModel()->totalPublicacoesDescartados();
        return Estrutura::responseNaj($return);
    }
    
    /**
     * Persiste no BD as publicações dos termos nos diários
     * 
     * @return JSON
     */
    public function persistePublicacoes($nomeArquivoLog = 'MonitoramentoDiarioController/persistePublicacoes'){
        try{
            
            //Seta o limite de execução do script
            set_time_limit(0); // COLOCAR ZERO PARA TORNAR ILIMITADO

            //Seta o limite da memória
            ini_set('memory_limit', '1024M');

            //Seta a Time Zone
            date_default_timezone_set('America/Sao_Paulo');
            
            DB::beginTransaction();

            //Primeiramente vamos verificar se ainda não tem diarios cadastrados no BD
            $sql = "SELECT id FROM monitora_diarios;";
            $result = DB::select($sql);
            if(count($result) == 0){
                //Se ainda não tiver diários iremos busca-los na Escavador e persisti-los no BD 
                $this->persisteDiarios();
            }
            
            //Seta a quantidade de páginas com 1 inicialmente
            $paginas = 1;
            
            //Contador de movimentações obtidas
            $movimentacoesObtidas = 0;
            
            //Contador de citações obtidas
            $citacoesObtidas = 0;
            
            //Busca a data da ultima movimentação
            $data_minima = $this->getModel()->buscaDataUltimaMovimentacao();
            
            $monitoraDiariosModel = new MonitoraDiariosModel();
            
            //Interage sobre a quantidade de páginas disponíveis
            for($i = 1; $i <= $paginas; $i++){
                
                //Busca movimentações na Escavador
                $content = $this->buscaCallbacksEscavador($i, $data_minima, $nomeArquivoLog, false);
                
                //Verifica se a requisição para a Escavador foi bem sucedida
                if($content->code != 200){
                    return response()->json($content)->content();
                }
                
                //Verifica o total de itens da requisição
                if($content->content->paginator->total == 0){
                    return Estrutura::responseNaj('No momento não existem novas ocorrências.');
                }
                
                //Seta o total de páginas somente na primeira interação
                if($i == 1){
                    $paginas = $content->content->paginator->total_pages;
                }

                //Vamos percorrer pelos itens do callback
                foreach ($content->content->items as $item){
                    
                    //Extrai otipo de evento do objeto
                    $event = $item->resultado->event;
                    
                    //Primeiramente vamos verificar se o evento do item corrente do callback é referente a monitoramentos do diário 
                    if($event != 'diario_movimentacao_nova' && $event != 'diario_citacao_nova'){
                        //Se o evento não referente a monitoramentos do diário for iremos pular para o próximo item do callback
                        continue;
                    }

                    //Extrai o id do termo da Escavador no callback
                    $idTermoEscavador     = $item->resultado->monitoramento[0]->id; //Id do termo na Escavador
                    $termoMonitoradoModel = new TermoMonitoradoModel();
                    $id_monitora_termo    = $termoMonitoradoModel->buscaIdTermo($idTermoEscavador);
                    unset($termoMonitoradoModel);
                    
                    //Se o termo não estiver cadastrado no BD então iremos verificar o próximo item do callback
                    if(empty($id_monitora_termo)){
                        continue;
                        //Throw new NajException("O termo $termo a qual o callback se refere não foi encontardo no banco de dados");
                    }
                    
                    //Vamos verificar o tipo de evento do item do callback

                    //Nova publicação encontrada no Monitoramento de Diários Oficiais e o Escavador identificou o processo
                    //Ocorre quando um Monitoramento de Diários Oficiais encontra algum resultado novo
                    //e o Escavador identificou qual o processo na página do Diário Oficial. 
                    if($item->resultado->event == 'diario_movimentacao_nova'){ 
                        
                        if($item->resultado->movimentacao->processo->numero_novo){
                            
                            //Incrementa movimentações obtidas
                            $movimentacoesObtidas++;

                            $id_diario_escavador = $item->resultado->movimentacao->diario->origem->id;

                            //Seta os valores que serão salvos em 'monitora_termo_movimentacao'
                            $id_diario             = $monitoraDiariosModel->getIdMonitoraDiarios($id_diario_escavador);
                            $id_movimentacao       = $item->resultado->movimentacao->id;
                            $data_disponibilizacao = isset($item->resultado->movimentacao->diario->data_disponibilizacao) ? $item->resultado->movimentacao->diario->data_disponibilizacao : $item->resultado->movimentacao->diario->data; 
                            $data_publicacao       = $item->resultado->movimentacao->diario->data_publicacao;
                            $conteudo_publicacao   = $item->resultado->movimentacao->conteudo; 
                            $pagina                = $item->resultado->movimentacao->pagina;
                            $secao                 = Estrutura::replaceUnderline($item->resultado->movimentacao->secao);
                            $tipo                  = $item->resultado->movimentacao->tipo;
                        
                            //Busca a quantidade e o código do processo que tem o mesmo número de processo
                            $registro = DB::select('SELECT count(0) as qtde, CODIGO FROM PRC WHERE NUMERO_PROCESSO_NEW2 = ' . Estrutura::removeFormatacaoNumeroProcesso($item->resultado->movimentacao->processo->numero_novo));
                            //Se tiver somente 1 cadastro de processo faz o relacionamento com a PUBLICAÇÃO

                            //RELACIONANDO O NÚMERO DO PROCESSO OBTIDO DA PUBLICAÇÃO COM O CADASTRO DE PROCESSOS DO SISTEMA
                            if($registro[0]->qtde == 1){
                                $codigo_processo = $registro[0]->CODIGO;
                            }else{
                                $codigo_processo = null;
                            }
                            
                            //Cadastra registro em 'monitota_termo_processo'

                            //Seta os valores do 'monitota_termo_processo' em seus respectivos campos
                            $monitotaTermoProcessoModel                           = new MonitoraTermoProcessoModel(); 
                            $monitotaTermoProcessoModel->id                       = $id_monitora_termo_processo = $id_processo = MonitoraTermoProcessoModel::max('id') + 1;
                            $monitotaTermoProcessoModel->codigo_processo          = $codigo_processo;
                            $monitotaTermoProcessoModel->numero_antigo            = $item->resultado->movimentacao->processo->numero_antigo;
                            $monitotaTermoProcessoModel->numero_novo              = $item->resultado->movimentacao->processo->numero_novo;
                            $monitotaTermoProcessoModel->status                   = "P"; //P = Pendente, C = Cadastrado
                            $monitotaTermoProcessoModel->data_inclusao            = date('Y-m-d');
                            $monitotaTermoProcessoModel->data_ultima_movimentacao = $item->resultado->movimentacao->processo->updated_at;
                            $monitotaTermoProcessoModel->tipo                     = "M"; //D = Descoberta de Processos, M=Monitoração de Termo

                            //Salva registro
                            $ok = $monitotaTermoProcessoModel->save();
                            unset($monitotaTermoProcessoModel);
                            if(!$ok){
                                Throw new NajException('Erro ao salvar registro em monitota_termo_processo.');
                            }

                            //Filtragem dos envolvidos para remover aqueles que aparecem repetidos
                            $filtraEnvolvidos             = new FiltraEnvolvidos;
                            $filtraEnvolvidos->envolvidos = $item->resultado->movimentacao->envolvidos;
                            $envolvidos                   = $filtraEnvolvidos->removeDuplicates();

                            //Cadastra registro dos envolvidos em 'monitota_termo_envolvidos'

                            foreach($envolvidos as $envolvido){

                                //Seta os valores do 'monitota_termo_envolvidos' em seus respectivos campos
                                $monitoraTermoEnvolvidosModel                             = new MonitoraTermoEnvolvidosModel();
                                $monitoraTermoEnvolvidosModel->id                         = MonitoraTermoEnvolvidosModel::max('id') + 1;
                                $monitoraTermoEnvolvidosModel->id_monitora_termo_processo = $id_monitora_termo_processo;
                                $monitoraTermoEnvolvidosModel->tipo                       = $envolvido->pivot_tipo;
                                $monitoraTermoEnvolvidosModel->nome                       = Estrutura::replaceTrema($envolvido->nome);
                                if($envolvido->nome == "Felipe Roberge Sens"){
                                    $a = 1;
                                }
                                //Verifica se o envolvido já está cadastrado como pessoa no sistema
                                $monitoraTermoEnvolvidosModel->pessoa_codigo              = $this->getModel()->verificaCodigoPessoaEnvolvido($envolvido->nome, $envolvido->pivot_tipo);

                                //Salva registro
                                $ok = $monitoraTermoEnvolvidosModel->save();
                                unset($monitoraTermoEnvolvidosModel);
                                if(!$ok){
                                    Throw new NajException('Erro ao salvar registro em monitota_termo_envolvidos.');
                                }
                            }
                            
                        }else{
                            
                            //Se não vir número NOVO, trata a publicação como CITAÇÃO
                            
                            //Incrementa citações obtidas
                            $citacoesObtidas++;

                            $id_diario_escavador   = $item->resultado->movimentacao->diario->origem->id;

                            //Seta os valores que serão salvos em 'monitora_termo_movimentacao'
                            $id_diario             = $monitoraDiariosModel->getIdMonitoraDiarios($id_diario_escavador);
                            $id_movimentacao       = null;
                            $id_processo           = null;
                            $data_disponibilizacao = isset($item->resultado->movimentacao->diario->data_disponibilizacao) ? $item->resultado->movimentacao->diario->data_disponibilizacao : $item->resultado->movimentacao->diario->data; 
                            $data_publicacao       = $item->resultado->movimentacao->diario->data_publicacao;
                            $conteudo_publicacao   = $item->resultado->movimentacao->conteudo; 
                            $pagina                = $item->resultado->movimentacao->pagina;
                            $secao                 = null;
                            $tipo                  = null;
                        }
                        
                    //Nova publicação encontrada no Monitoramento de Diários Oficiais
                    //Ocorre quando um Monitoramento de Diários Oficiais encontra algum resultado novo e o Escavador 
                    //não identificou qual é o processo na página do Diário Oficial ou não tem processo nessa página. 
                    //Você pode simular um envio desse callback para o seu servidor.
                    } elseif ($item->resultado->event == 'diario_citacao_nova') {
                        
                        //Incrementa citações obtidas
                        $citacoesObtidas++;
                        
                        $id_diario_escavador   = $item->resultado->diario->origem->id;
                        
                        //Seta os valores que serão salvos em 'monitora_termo_movimentacao'
                        $id_diario             = $monitoraDiariosModel->getIdMonitoraDiarios($id_diario_escavador);
                        $id_movimentacao       = null;
                        $id_processo           = null;
                        $data_disponibilizacao = isset($item->resultado->diario->data_disponibilizacao) ? $item->resultado->diario->data_disponibilizacao : $item->resultado->diario->data; 
                        $data_publicacao       = $item->resultado->diario->data_publicacao;
                        $conteudo_publicacao   = $item->resultado->pagina_diario->conteudo; 
                        $pagina                = $item->resultado->pagina_diario->numero_pagina;
                        $secao                 = null;
                        $tipo                  = null;
                    }

                    //Cadastra registro em 'monitora_termo_movimentacao'

                    //Seta os valores do 'monitora_termo_movimentacao' em seus respectivos campos
                    $monitoramentoDiarioModel                        = new MonitoramentoDiarioModel();
                    $monitoramentoDiarioModel->id                    = MonitoramentoDiarioModel::max('id') + 1;
                    $monitoramentoDiarioModel->id_diario             = $id_diario;
                    $monitoramentoDiarioModel->id_movimentacao       = $id_movimentacao;
                    $monitoramentoDiarioModel->id_monitora_termo     = $id_monitora_termo;
                    $monitoramentoDiarioModel->id_processo           = $id_processo;
                    $monitoramentoDiarioModel->data_hora_inclusao    = date('Y-m-d H:i:s'); 
                    $monitoramentoDiarioModel->data_disponibilizacao = $data_disponibilizacao; 
                    $monitoramentoDiarioModel->data_publicacao       = $data_publicacao;
                    $monitoramentoDiarioModel->conteudo_publicacao   = $conteudo_publicacao;
                    $monitoramentoDiarioModel->conteudo_json         = json_encode($item->resultado);
                    $monitoramentoDiarioModel->pagina                = $pagina;
                    $monitoramentoDiarioModel->secao                 = $secao;
                    $monitoramentoDiarioModel->tipo                  = $tipo;
                    $monitoramentoDiarioModel->lido                  = "N";
                    $monitoramentoDiarioModel->descartada            = "N";

                    $ok = $monitoramentoDiarioModel->save();
                    unset($monitoramentoDiarioModel);
                    if(!$ok){
                        Throw new NajException('Erro ao salvar registro em monitora_termo_movimentacao');
                    }

                }
                
            }
            
            unset($monitoraDiariosModel);
            
            //Comita transação
            DB::commit();
            
            $menssagemRetorno = '';
            
            if($movimentacoesObtidas > 0){
                $menssagemRetorno .= $movimentacoesObtidas . ' movimentações obtidas com sucesso! <br>';
            }
            
            if($citacoesObtidas > 0){
                $menssagemRetorno .= $citacoesObtidas . ' citações obtidas com sucesso!';
            }
            
            if($movimentacoesObtidas == 0 && $citacoesObtidas == 0){
                $menssagemRetorno = 'No momento não existem novas ocorrências.';
            }
            
            //Retorno de sucesso
            return Estrutura::responseNaj($menssagemRetorno);
            
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
    
    /**
     * Busca os diarios na Escavador
     * 
     * @param string $nomeArquivoLog nome do arquivod de log
     * @param bool   $print_r        define se ira imprimir o resultado em tela
     * 
     * @return object
     */
    public function buscaDiariosEscavador($nomeArquivoLog = 'MonitoramentoDiarioController/buscaDiariosEscavador', $JSON = true){
        $escavadorController = new EscavadorController();
        $content             = $escavadorController->retornarOrigens($nomeArquivoLog);
        if($JSON){
            return $content;
        }
        $content = json_decode($content);
        return $content;
    }
    
    /**
     * Persiste os diarios do Escavados no BD
     * 
     * @return JSON
     */
    public function persisteDiarios($nomeArquivoLog = 'MonitoramentoDiarioController/persisteDiarios'){
        try{
            //Inicia transação no BD
            DB::beginTransaction();
            
            //Contador de diários salvos no BD
            $countDiarios = 0;
            
            //Busca o id_diario de todos os diários cadastrados no BD
            $diariosBD = $this->getModel()->buscaId_Diarios();
            
            //Busca os diarios na Escavador
            $content = $this->buscaDiariosEscavador($nomeArquivoLog, false);
            
            //Verifica se requisição para a Escavador foi bem sucedida
            if($content->code != 200){
                return response()->json($content)->content();
            }
            
            //Para cada item do conteúdo...
            foreach ($content->content as $item){
                
                //Para cada diário do item...
                foreach ($item->diarios as $diario){
                    
                    //Verifica se diário da Escavador já está cadastrado no BD, se naõ estiver iremos cadastra-lo
                    if(!in_array($diario->id, $diariosBD)){
                        
                        //Cadastra registro em 'monitora_diarios'
                        //Seta os valores do 'monitora_diarios' em seus respectivos campos
                        $monitoraDiariosModel              = new MonitoraDiariosModel();
                        $monitoraDiariosModel->id          = MonitoraDiariosModel::max('id') + 1;
                        $monitoraDiariosModel->id_diario   = $diario->id;
                        $monitoraDiariosModel->nome        = $diario->nome;
                        $monitoraDiariosModel->sigla       = $diario->sigla;
                        $monitoraDiariosModel->estado      = $diario->estado;
                        $monitoraDiariosModel->competencia = $diario->competencia;

                        $ok = $monitoraDiariosModel->save();
                        unset($monitoraDiariosModel);
                        if(!$ok){
                            Throw new NajException('Erro ao salvar registro em monitora_diarios.');
                        }

                        $countDiarios++;

                    }
                }
            }
            
            //Comita transação
            DB::commit();
            
            if($countDiarios > 0){
                //Retorno de sucesso
                return Estrutura::responseNaj($countDiarios . ' Diários do Escavador salvos com sucesso!');
            } else {
                //Retorno de sucesso
                return Estrutura::responseNaj('No momento não existe novos diários na Escavador.');
            
            }
            
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
    
    /**
     * Descarta publicação
     */
    public function descartarPublicacao($id){
        $retorno = $this->getModel()->descartarPublicacao($id);
        return response()->json($retorno)->content();
    }

}
