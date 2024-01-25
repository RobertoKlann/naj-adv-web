<?php

namespace App\Http\Controllers\NajWeb;

use Exception;
use GuzzleHttp\Exception\ClientException;
use Error;
use App\Estrutura;
use App\Exceptions\NajException;
use App\Http\Controllers\NajWeb\MonitoramentoController;
use App\Http\Controllers\NajWeb\EscavadorController;
use App\Models\MonitoramentoTribunalModel;
use App\Models\MonitoraTribunaisModel;
use App\Models\MonitoraProcessoTribunalBuscasModel;
use App\Models\MonitoraProcessoMovimentacaoModel;
use App\Models\prcMovimentoModel;
use App\Models\prcOrgaoModel;
use App\Models\ProcessoModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/**
 * Controller do Monitoramento dos Tribunais.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      8/11/2020
 */
class MonitoramentoTribunalController extends MonitoramentoController {

    /**
     * Seta o model de Conta Virtual ao carregar o controller
     */
    public function onLoad() {
        $this->setModel(new MonitoramentoTribunalModel);
    }

    /**
     * Index da rota de Monitoramento Tribunal.
     */
    public function index() {
        $codigo = isset($_GET['codigo']) ? $_GET['codigo'] : null;
        $cnj    = isset($_GET['cnj'])    ? $_GET['cnj']    : null;
        return view('najWeb.consulta.MonitoramentoTribunalConsultaView')->with('ignora_css_datatable', false)->with('is_monitoramento_tribunais', true)->with('codigo', $codigo)->with('cnj', $cnj);
    }

    /**
     * Retorna o max(id) no BD 
     * 
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
     * Insere Registro em Monitora Processo Tribunal Buscas
     * 
     * @param int       $id_monitora_tribunal
     * @param timestamp $data_hora      
     * @param int       $status               status
     * @param string    $status_mensagem      mensagem do status
     * @param int       $movimentacoesObtidas default null
     * @param string    $id_resultado_busca   Identificador numérico do resultado da busca, default null
     * @return string   status_mensagem
     * @throws NajException
     */
    public function insereRegistroMonitoraProcessoTribunalBuscas($id_monitora_tribunal, $data_hora, $status, $status_mensagem = "", $movimentacoesObtidas = null, $id_resultado_busca = null){
        //Status aceitos no BD
        $status_num    = ['PENDENTE' => 0, 'SUCESSO' => 1, 'ERRO' => 2];
        $valor_extenso = Estrutura::valorPorExtenso($movimentacoesObtidas);
        //Status mensagem pré definidos, somente a mensagem de ERRO é personalizada
        $status_msg    = [
            "PENDENTE" => ": Busca em andamento." . $status_mensagem, 
            "SUCESSO"  => ": Concluído com sucesso com $movimentacoesObtidas ($valor_extenso) andamentos novos. ($status_mensagem)", 
            "ERRO"     => ": " . $status_mensagem
        ];

        //Define os valores dos status
        $status_number   = $status_num[$status];
        $status_mensagem = $status . $status_msg[$status];
        
        //Seta os valores do 'monitota_processo_tribunal_buscas' em seus respectivos campos
        $monitoraProcessoTribunalBuscasModel                       = new MonitoraProcessoTribunalBuscasModel();
        $monitoraProcessoTribunalBuscasModel->id                   = MonitoraProcessoTribunalBuscasModel::max('id') + 1;
        $monitoraProcessoTribunalBuscasModel->id_monitora_tribunal = $id_monitora_tribunal;
        $monitoraProcessoTribunalBuscasModel->data_hora            = $data_hora;
        $monitoraProcessoTribunalBuscasModel->status               = $status_number;
        $monitoraProcessoTribunalBuscasModel->status_mensagem      = $status_mensagem; 
        $monitoraProcessoTribunalBuscasModel->id_resultado_busca   = $id_resultado_busca; 

        //Salva registro
        $ok = $monitoraProcessoTribunalBuscasModel->save();
        unset($monitoraProcessoTribunalBuscasModel);
        if(!$ok){
            Throw new NajException('Erro ao salvar registro em monitota_processo_tribunal_buscas.');
        }

        return $status_mensagem;
       
    }
    
    /**
     * Persiste Movimentacao Pela Busca Especifica
     * 
     * @param object $monitoramento
     * @param string $nomeArquivoLog
     * @return array
     * @throws NajException
     */
    function persisteMovimentacaoPelaBuscaEspecifica($monitoramento, $nomeArquivoLog = 'MonitoramentoTribunalController/persisteMovimentacaoPelaBuscaEspecifica'){
        //Busca o id do monitota_processo_tribunal com base apenas no número do processo
        $id_monitora_processo_tribunal = $this->getModel()->buscaIdMonitoraProcessoTribunal($monitoramento->numero_cnj);
        //Se o processo não estiver cadastrado na tabela "monitora_processo_tribunal" do BD significa que este processo não está sendo monitorado então iremos verificar o próximo item do callback
        if(empty($id_monitora_processo_tribunal)){
            return['menssagemRetorno' => "", 'movimentacoesObtidasGeral' => 0];
        }
        $menssagemRetorno = "";
        $movimentacoesObtidasGeral = 0;
        $monitoramentosPendentes   = 0;
        //Estancia o controllador da Escavador
        $escavadorController = new EscavadorController();
        if(!is_null($monitoramento->id_resultado_busca)){
            $resultado_busca = $escavadorController->resultadoEspecíficoDeUmaBuscaAssincrona($monitoramento->id_resultado_busca, $nomeArquivoLog);
            $resultado_busca = json_decode($resultado_busca);
            if($resultado_busca->code == 200){
                $conteudo           = $resultado_busca->content;
                //$timestamp          = strtotime($conteudo->created_at->date . " -3 hours"); //UTC -3 para horário de Brasília
                //$data_hora          = date('Y-m-d H:i:s', $timestamp);
                $data_hora          = date('Y-m-d H:i:s');
                $id_resultado_busca = $conteudo->id;

                //Extrai o numero_processo do conteudo
                $numero_processo = $conteudo->numero_processo;
                //Extrai status da pesquisa do conteudo
                $status_pesquisa = $conteudo->status;

                //Vamos verificar primeiramente se houve erro interno da Escavador ou se o processo não foi encontrado nos sites dos tribunais
                if($status_pesquisa === "ERRO"){

                    $monitoramentosPendentes++;

                    //Se o processo não foi encontrado nos sites dos tribunais precisamos registrar essa situação em "monitora_processo_tribunal_buscas" no BD

                    //Para cada monitoramento do processo encontrado iremos inserir o registro da situação da pesquisa em "monitora_processo_tribunal_buscas"
                    foreach($id_monitora_processo_tribunal as $id){
                        $this->insereRegistroMonitoraProcessoTribunalBuscas($id, $data_hora, "ERRO", $conteudo->resposta->message, null, $id_resultado_busca);
                    }
                
                //Vamos verificar primeiramente se houve erro interno da Escavador ou se o processo não foi encontrado nos sites dos tribunais
                }else if($status_pesquisa === "NAO_ENCONTRADO"){
                    
                    //Se o processo não foi encontrado nos sites dos tribunais precisamos registrar essa situação em "monitora_processo_tribunal_buscas" no BD

                    //Para cada monitoramento do processo encontrado iremos inserir o registro da situação da pesquisa em "monitora_processo_tribunal_buscas"
                    foreach($id_monitora_processo_tribunal as $id){
                        $this->insereRegistroMonitoraProcessoTribunalBuscas($id, $data_hora, "ERRO", $conteudo->resposta->message, null, $id_resultado_busca);
                    }


                //Se a pesquisa do processo nos sites dos tribunais for de "SUCESSO" iremos verificar quais foram as movimentações obtidas    
                }else if($status_pesquisa === "SUCESSO"){

                    $totalInstancias = count($conteudo->resposta->instancias);
                    
                    //Vamos percorrer pelas instancias do item do callback
                    foreach($conteudo->resposta->instancias as $indexInstacia => $instancia){

                        $indexInstacia++;
                        
                        //Busca o id do monitota_processo_tribunal com base no número do processo e na instância
                        $id_monitora_processo_tribunal = $this->getModel()->buscaIdMonitoraProcessoTribunal($monitoramento->numero_cnj, $instancia->instancia);
                        
                        Estrutura::gravaLog($nomeArquivoLog, "Início da transação da instância $indexInstacia de $totalInstancias, instância: $instancia->instancia do monitoramento de código: $id_monitora_processo_tribunal , CNJ: $monitoramento->numero_cnj, id_resultado_busca: $id_resultado_busca");

                        //Se o processo não estiver cadastrado na tabela "monitora_processo_tribunal" do BD significa que este processo não está sendo monitorado então iremos verificar o próximo item do callback
                        if(empty($id_monitora_processo_tribunal)){
                            Estrutura::gravaLog($nomeArquivoLog, "O processo $monitoramento->numero_cnj não foi encontrado na tabela monitora_processo_tribunal do BD.");
                            continue;
                        }

                        //Agora iremos obter o código do processo no BD, pois iremos precisar dele para poder inserir os registros em 'prc_movimento' e para atualizar o 'URL_TJ' em 'prc'
                        $codigoProcesso = $this->getModel()->obterCodigoProcessoComBaseIdMonitoramento($id_monitora_processo_tribunal);
                        if(empty($codigoProcesso)){
                            $menssagemErro = "Código de processo não encontrado para o id_monitora_processo_tribunal = $id_monitora_processo_tribunal, pois não há relacionamento deste id_monitora_processo_tribunal com nenhum processo em monitora_processo_tribunal_rel_prc.";
                            Estrutura::gravaLog($nomeArquivoLog, $menssagemErro);
                            //Iremos inserir o registro da situação da pesquisa em "monitora_processo_tribunal_buscas" e pular para proxima instância
                            $this->insereRegistroMonitoraProcessoTribunalBuscas($id_monitora_processo_tribunal, $data_hora, "ERRO", $menssagemErro, null, $id_resultado_busca);
                            continue;
                        }
                        
                        //Atualiza o Id Tribunal em "monitora_processo_tribunal"
                        $result = $this->getModel()->atualizaIdTribunalMPT($id_monitora_processo_tribunal, $conteudo->tribunal->sigla);                          
                        if($result !== true){
                             Throw new NajException($result);
                        }

                        //Verifica se esse processo é do tipo "segredo de justiça"
                        if($instancia->segredo){
                            //Iremos inserir o registro da situação da pesquisa em "monitora_processo_tribunal_buscas" e pular para proxima instância
                            $this->insereRegistroMonitoraProcessoTribunalBuscas($id_monitora_processo_tribunal, $data_hora, "ERRO", "Processos em segredo de justiça não são possíveis de capturar os andamentos!", null, $id_resultado_busca);
                            continue;
                        }

                        //Contador de movimentações obtidas
                        $movimentacoesObtidas = 0;

                        //Vamos percorrer pelas movimentacões da instancia do item do callback
                        foreach($instancia->movimentacoes as $key => $movimentacao){

                            //Atualiza o Processo
                            $ProcessoModel = new ProcessoModel();
                            $ProcessoModel->atualizaURL_TJ($instancia->url, $codigoProcesso);
                            $ProcessoModel->atualizaID_ORGAO($conteudo->tribunal->sigla, $instancia->url, $codigoProcesso);
                            unset($ProcessoModel);
                            
                            //Vamos primeiramente verificar se esta movimentação já foi cadastrada em uma pesquisa passada, caso tenha sido cadastrada iremos pular para a próxima movimentação
                            $movimentacao_ja_existe = $this->getModel()->verificaSeMovimentacaoJaExiste($movimentacao->id);
                            if($movimentacao_ja_existe){
                                continue;
                            }

                            //Seta os valores que serão salvos em 'prc_movimento'
                            $prcMovimentoModel                      = new prcMovimentoModel();
                            $id_prc_movimento                       = prcMovimentoModel::max('id') + 1;
                            $prcMovimentoModel->ID                  = $id_prc_movimento;
                            $prcMovimentoModel->CODIGO_PROCESSO     = $codigoProcesso;
                            $prcMovimentoModel->DESCRICAO_ANDAMENTO = Estrutura::Utf8_ansi($movimentacao->conteudo);
                            $prcMovimentoModel->NOTIFICADO          = "N";
                            $prcMovimentoModel->NOTIFICAR           = "N";
                            $prcMovimentoModel->DATA                = implode('-', array_reverse(explode('/', $movimentacao->data)));

                            $ok = $prcMovimentoModel->save();
                            unset($prcMovimentoModel);
                            if(!$ok){
                                Throw new NajException('Erro ao salvar registro em "prc_movimento"');
                            }

                            //Seta os valores que serão salvos em 'monitora_processo_movimentacao'
                            $monitoraProcessoMovimentacaoModel                       = new MonitoraProcessoMovimentacaoModel();
                            $monitoraProcessoMovimentacaoModel->id                   = MonitoraProcessoMovimentacaoModel::max('id') + 1;
                            $monitoraProcessoMovimentacaoModel->id_monitora_processo = $id_monitora_processo_tribunal;
                            $monitoraProcessoMovimentacaoModel->id_movimentacao      = intval($movimentacao->id);
                            $monitoraProcessoMovimentacaoModel->id_prc_movimento     = $id_prc_movimento;
                            $monitoraProcessoMovimentacaoModel->conteudo             = Estrutura::Utf8_ansi($movimentacao->conteudo);
                            $monitoraProcessoMovimentacaoModel->conteudo_json        = json_encode($movimentacao);
                            $monitoraProcessoMovimentacaoModel->data                 = implode('-', array_reverse(explode('/', $movimentacao->data)));
                            $monitoraProcessoMovimentacaoModel->lido                 = "N";
                            $monitoraProcessoMovimentacaoModel->instancia            = $instancia->instancia;
                            $monitoraProcessoMovimentacaoModel->url_tj               = $instancia->url;
                            $monitoraProcessoMovimentacaoModel->sistema              = $instancia->sistema;
                            $monitoraProcessoMovimentacaoModel->assunto              = $instancia->assunto;
                            $monitoraProcessoMovimentacaoModel->classe               = $instancia->classe;
                            $monitoraProcessoMovimentacaoModel->area                 = $instancia->area;
                            $monitoraProcessoMovimentacaoModel->data_distribuicao    = date('Y-m-d', strtotime($instancia->data_distribuicao));
                            $monitoraProcessoMovimentacaoModel->valor_causa          = $instancia->valor_causa;
                            $monitoraProcessoMovimentacaoModel->orgao_julgador       = $instancia->orgao_julgador;
                            $monitoraProcessoMovimentacaoModel->data_hora_inclusao   = date('Y-m-d H:m:i');
                            $monitoraProcessoMovimentacaoModel->data_hora_cadastro   = $data_hora;                            

                            $ok = $monitoraProcessoMovimentacaoModel->save();
                            unset($monitoraProcessoMovimentacaoModel);
                            if(!$ok){
                                Throw new NajException('Erro ao salvar registro em monitora_processo_movimentacao');
                            }

                            //Incrementa movimentações obtidas
                            $movimentacoesObtidas++;

                        }

                        $instancia_db = ['PRIMEIRO_GRAU' => '1º Instância','SEGUNDO_GRAU' => '2º Instância','TURMA_RECURSAL' => '2º Instância','SUPERIOR' => 'Superior', 'DESCONHECIDA' => '1º Instância'];
                        if(array_key_exists($instancia->instancia, $instancia_db)){
                            $instancia = $instancia_db[$instancia->instancia];
                        }else{
                            throw new NajException("A instância '$instancia->instancia' não é reconhecida pelo sistema NAJ, para corrigir esse erro ela deve ser adicionada no código fonte ao array 'instancia_db' no método 'buscaIdMonitoraProcessoTribunal' da classe 'MonitoramentoTribunalModel'");
                        }
                        //Iremos inserir o registro da situação da pesquisa em "monitora_processo_tribunal_buscas"
                        $this->insereRegistroMonitoraProcessoTribunalBuscas($id_monitora_processo_tribunal, $data_hora, $status_pesquisa, $instancia, $movimentacoesObtidas, $id_resultado_busca);

                        $movimentacoesObtidasGeral += $movimentacoesObtidas;

                        Estrutura::gravaLog($nomeArquivoLog, "Fim da transação da instância $indexInstacia de $totalInstancias, com SUCESSO!");
                        
                    }

                }else if($status_pesquisa === "PENDENTE"){
                    $monitoramentosPendentes++;
                }
            }else if($resultado_busca->code == 404){
                //Para cada monitoramento do processo encontrado iremos inserir o registro da situação da pesquisa em "monitora_processo_tribunal_buscas"
                foreach($id_monitora_processo_tribunal as $id){
                    //Iremos inserir o registro da situação da pesquisa em "monitora_processo_tribunal_buscas"
                    $this->insereRegistroMonitoraProcessoTribunalBuscas($id, date('Y-m-d H:i:s'), "ERRO", $resultado_busca->message, null, $monitoramento->id_resultado_busca);
                }
            }else{
                $code     = $resultado_busca->code    ? "Code: " . $resultado_busca->code : "";
                $mensagem = $resultado_busca->message ? $resultado_busca->message         : " Desconhecido, contate o suporte.";
                //Para cada monitoramento do processo encontrado iremos inserir o registro da situação da pesquisa em "monitora_processo_tribunal_buscas"
                foreach($id_monitora_processo_tribunal as $id){
                    //Iremos inserir o registro da situação da pesquisa em "monitora_processo_tribunal_buscas"
                    $this->insereRegistroMonitoraProcessoTribunalBuscas($id, date('Y-m-d H:i:s'), "ERRO", "$code $mensagem" , null, $monitoramento->id_resultado_busca);
                }
            }
        }else{
            $menssagemRetorno .= "O monitoramento do CNJ $monitoramento->numero_cnj não contêm o id do resultado da busca, contate o suporte!</br>";
        }
        unset($escavadorController);
        return ['menssagemRetorno' => $menssagemRetorno, 'movimentacoesObtidasGeral' => $movimentacoesObtidasGeral, 'monitoramentosPendentes' => $monitoramentosPendentes];
    }
    
    /**
     * Persiste no BD as movimentações dos processos em "monitora_processo_movimentacao" 
     * e seus respectivos envolvidos em "monitora_processos_envolvidos"
     * além de inserir o novo status da movimentação do processo em "monitora_processo_tribunal_buscas"
     * 
     * @param string $nomeArquivoLog 
     * @return JSON
     */
    public function persisteMovimentacoesPelaBuscaEspecifica($nomeArquivoLog = 'MonitoramentoTribunalController/persisteMovimentacoesPelaBuscaEspecifica'){
        try{
            
            //Total movimentacoes obtidas no geral
            $movimentacoesObtidasGeral = 0;
            //Total monitoramentos pendentes na Escavador
            $monitoramentosPendentes   = 0;
            
            //Monitoramentos pendentes para buscar na escavador
            $monitoramentos = $this->getModel()->buscaMonitoramentosPendentes();
            
            $menssagemRetorno = "";
            
            $monitoramentoCount = 1;
            
            Estrutura::gravaLog($nomeArquivoLog, "INICIO DO PROCESSO DE PERSISTÊNCIA DAS MOVIMENTAÇÕES NO BANCO DE DADOS PELA BUSCA ESPECÍFICA");
            Estrutura::gravaLog($nomeArquivoLog, "Total de Monitoramentos: " . count($monitoramentos));
            
            foreach($monitoramentos as $monitoramento){
                
                Estrutura::gravaLog($nomeArquivoLog, "Início da transação do monitoramento de código: " . $monitoramento->id . ", CNJ: " . $monitoramento->numero_cnj  . ", id_resultado_busca: " . $monitoramento->id_resultado_busca . ", " . $monitoramentoCount  ." de " . count($monitoramentos));
                
                //Inicia transação no BD
                DB::beginTransaction();
                
                $result                     = $this->persisteMovimentacaoPelaBuscaEspecifica($monitoramento, $nomeArquivoLog);
                $menssagemRetorno          .= $result['menssagemRetorno'];
                $movimentacoesObtidasGeral += $result['movimentacoesObtidasGeral'];
                $monitoramentosPendentes   += $result['monitoramentosPendentes'];
                
                //Comita transação
                DB::commit();
                
                Estrutura::gravaLog($nomeArquivoLog, "Fim da transação concluída com SUCESSO do monitoramento de código: " . $monitoramento->id . ", " . $monitoramentoCount  ." de " . count($monitoramentos));
            
                $monitoramentoCount++;
            }
            
            Estrutura::gravaLog($nomeArquivoLog, "FIM DO PROCESSO DE PERSISTÊNCIA DAS MOVIMENTAÇÕES NO BANCO DE DADOS PELA BUSCA ESPECÍFICA");
            
            if($movimentacoesObtidasGeral > 0){
                $menssagemRetorno .= $movimentacoesObtidasGeral . ' movimentações obtidas com sucesso!';
            }else{
                $menssagemRetorno .= 'No momento não foram detectadas novas movimentações.';
            }
            if($monitoramentosPendentes > 0){
                $menssagemRetorno .= '</br>' . $monitoramentosPendentes . ' Monitoramento(s) pendente(s) com busca em andamento!';
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
     * Persiste no BD as movimentações dos processos em "monitora_processo_movimentacao" (FUNÇÂO DESCONTÍNUADA, foi substítuida pela função persisteMovimentacoesPelaBuscaEspecifica)
     * e seus respectivos envolvidos em "monitora_processos_envolvidos"
     * além de inserir o novo status da movimentação do processo em "monitora_processo_tribunal_buscas"
     * 
     * @param string $nomeArquivoLog 
     * @return JSON
     */
    public function persisteMovimentacoesPelosCallbacks($nomeArquivoLog = 'MonitoramentoTribunalController/persisteMovimentacoes'){
        try{
            
            //Inicia transação no BD
            DB::beginTransaction();
            
            //Seta a quantidade de páginas com 1 inicialmente
            $paginas = 1;
            
            //Total movimentacoes obtidas no geral
            $movimentacoesObtidasGeral = 0;
                    
            //Busca a data do último registro em "monitora_processo_tribunal_buscas" 
            $data_minima = $this->getModel()->buscaDataUltimoRegistroMPTB();
            
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
                    return Estrutura::responseNaj('No momento não foram detectadas novas movimentações.');
                }
                
                //Seta o total de páginas somente na primeira interação
                if($i == 1){
                    $paginas = $content->content->paginator->total_pages;
                }

                //Vamos percorrer pelos itens do callback
                foreach ($content->content->items as $item){
                    
                    //Extrai do objeto o tipo de evento, status callback 
                    $event              = $item->resultado->event;
                    $id_resultado_busca = $this->extraiIdLinkApiEscavador($item->resultado->link_api);
                    $status_callback    = $item->status;
                    //$timestamp          = strtotime($item->created_at . " -3 hours"); //UTC -3 para horário de Brasília
                    //$data_hora          = date('Y-m-d H:i:s', $timestamp);
                    $data_hora          = date('Y-m-d H:i:s');
                    
                    //Primeiramente vamos verificar se o evento e o tipo do item corrente do callback é referente a monitoramento do processo no site do tribunal 
                    if($event != 'resultado_processo_async'){
                        //Se o evento não é referente a monitoramento do processo no site do tribunal iremos pular para o próximo item do callback
                        continue;
                    }

                    //Extrai o numero_processo do item do callback
                    $numero_processo = $item->resultado->numero_processo;
                    //Extrai status da pesquisa do processo nos sites dos tribunais
                    $status_pesquisa = $item->resultado->status;
                    
                    //Vamos verificar primeiramente se o processo não foi encontrado nos sites dos tribunais
                    if($status_pesquisa === "NAO_ENCONTRADO"){
                        
                        //Se o processo não foi encontrado nos sites dos tribunais precisamos registrar essa situação em "monitora_processo_tribunal_buscas" no BD
                        
                        //Busca o id do monitota_processo_tribunal com base apenas no número do processo
                        $id_monitora_processo_tribunal = $this->getModel()->buscaIdMonitoraProcessoTribunal($numero_processo);
                        
                        //Se o processo não estiver cadastrado na tabela "monitora_processo_tribunal" do BD significa que este processo não está sendo monitorado então iremos verificar o próximo item do callback
                        if(empty($id_monitora_processo_tribunal)){
                            continue;
                            //Throw new NajException("O processo $id_monitora_processo_tribunal em "monitora_processo_tribunal" a qual o callback se refere não foi encontardo no banco de dados");
                        }
                        
                        //Para cada monitoramento do processo encontrado iremos inserir o registro da situação da pesquisa em "monitora_processo_tribunal_buscas"
                        foreach($id_monitora_processo_tribunal as $id){
                            $this->insereRegistroMonitoraProcessoTribunalBuscas($id, $data_hora, "ERRO", $item->resultado->resposta->message, null, $id_resultado_busca);
                        }
                     
                    //Se a pesquisa do processo nos sites dos tribunais for de "SUCESSO" iremos verificar quais foram as movimentações obtidas    
                    }else if($status_pesquisa === "SUCESSO"){
                        
                        //Vamos percorrer pelas instancias do item do callback
                        foreach($item->resultado->resposta->instancias as $instancia){
                            
                            //Busca o id do monitota_processo_tribunal com base no número do processo e na instância
                            $id_monitora_processo_tribunal = $this->getModel()->buscaIdMonitoraProcessoTribunal($numero_processo, $instancia->instancia);
                            
                            //Se o processo não estiver cadastrado na tabela "monitora_processo_tribunal" do BD significa que este processo não está sendo monitorado então iremos verificar o próximo item do callback
                            if(empty($id_monitora_processo_tribunal)){
                                Estrutura::gravaLog($nomeArquivoLog, 'O processo $id_monitora_processo_tribunal em "monitora_processo_tribunal" a qual o callback se refere não foi encontardo no banco de dados');
                                continue;
                            }
                            
                            //Agora iremos obter o código do processo no BD, pois iremos precisar dele para poder inserir os registros em 'prc_movimento' e para atualizar o 'URL_TJ' em 'prc'
                            $codigoProcesso = $this->getModel()->obterCodigoProcessoComBaseIdMonitoramento($id_monitora_processo_tribunal);
                            if(empty($codigoProcesso)){
                                Estrutura::gravaLog($nomeArquivoLog, "ERRO: Código de processo não encontrado para o id_monitora_processo_tribunal = $id_monitora_processo_tribunal, pois não há relacionamento deste id_monitora_processo_tribunal com nenhum processo em monitora_processo_tribunal_rel_prc.");
                                //Iremos inserir o registro da situação da pesquisa em "monitora_processo_tribunal_buscas" e pular para proxima instância
                                $this->insereRegistroMonitoraProcessoTribunalBuscas($id_monitora_processo_tribunal, $data_hora, "ERRO", "Código de processo não encontrado para o monitoramento de processo de código  = $id_monitora_processo_tribunal, pois não há relacionamento deste monitoramento de processo com nenhum processo.", null, $id_resultado_busca);
                                continue;
                            }
                            
                            //Atualiza o Id Tribunal em "monitora_processo_tribunal"
                            $resul = $this->getModel()->atualizaIdTribunalMPT($id_monitora_processo_tribunal, $item->resultado->tribunal->sigla);                          
                            if($resul !== true){
                                 Throw new NajException($resul);
                            }
                            
                            //Verifica se esse processo é do tipo "segredo de justiça"
                            if($instancia->segredo){
                                //Iremos inserir o registro da situação da pesquisa em "monitora_processo_tribunal_buscas" e pular para proxima instância
                                $this->insereRegistroMonitoraProcessoTribunalBuscas($id_monitora_processo_tribunal, $data_hora, "ERRO", "Processos em segredo de justiça não são possíveis de capturar os andamentos!", null, $id_resultado_busca);
                                continue;
                            }
                            
                            //Contador de movimentações obtidas
                            $movimentacoesObtidas = 0;

                            //Vamos percorrer pelas movimentacões da instancia do item do callback
                            foreach($instancia->movimentacoes as $movimentacao){
                                
                                //Vamos primeiramente veridficar se esta movimentação já foi cadastrada em uma pesquisa passada, caso tenha sido cadastrada iremos pular para a próxima movimentação
                                $movimentacao_ja_existe = $this->getModel()->verificaSeMovimentacaoJaExiste($movimentacao->id);
                                if($movimentacao_ja_existe){
                                    continue;
                                }
                                
                                //Seta os valores que serão salvos em 'prc_movimento'
                                $prcMovimentoModel                      = new prcMovimentoModel();
                                $id_prc_movimento                       = prcMovimentoModel::max('id') + 1;
                                $prcMovimentoModel->ID                  = $id_prc_movimento;
                                $prcMovimentoModel->CODIGO_PROCESSO     = $codigoProcesso;
                                $prcMovimentoModel->DESCRICAO_ANDAMENTO = Estrutura::Utf8_ansi($movimentacao->conteudo);
                                $prcMovimentoModel->NOTIFICADO          = "N";
                                $prcMovimentoModel->NOTIFICAR           = "N";
                                $prcMovimentoModel->DATA                = implode('-', array_reverse(explode('/', $movimentacao->data)));
                                
                                $ok = $prcMovimentoModel->save();
                                unset($prcMovimentoModel);
                                if(!$ok){
                                    Throw new NajException('Erro ao salvar registro em "prc_movimento"');
                                }
                                
                                //Seta os valores que serão salvos em 'monitora_processo_movimentacao'
                                $monitoraProcessoMovimentacaoModel                       = new MonitoraProcessoMovimentacaoModel();
                                $monitoraProcessoMovimentacaoModel->id                   = MonitoraProcessoMovimentacaoModel::max('id') + 1;
                                $monitoraProcessoMovimentacaoModel->id_monitora_processo = $id_monitora_processo_tribunal;
                                $monitoraProcessoMovimentacaoModel->id_movimentacao      = $movimentacao->id;
                                $monitoraProcessoMovimentacaoModel->conteudo             = Estrutura::Utf8_ansi($movimentacao->conteudo);
                                $monitoraProcessoMovimentacaoModel->conteudo_json        = json_encode($movimentacao);
                                $monitoraProcessoMovimentacaoModel->data                 = implode('-', array_reverse(explode('/', $movimentacao->data)));
                                $monitoraProcessoMovimentacaoModel->lido                 = "N";
                                $monitoraProcessoMovimentacaoModel->instancia            = $instancia->instancia;
                                $monitoraProcessoMovimentacaoModel->url_tj               = $instancia->url;
                                $monitoraProcessoMovimentacaoModel->sistema              = $instancia->sistema;
                                $monitoraProcessoMovimentacaoModel->assunto              = $instancia->assunto;
                                $monitoraProcessoMovimentacaoModel->classe               = $instancia->classe;
                                $monitoraProcessoMovimentacaoModel->area                 = $instancia->area;
                                $monitoraProcessoMovimentacaoModel->data_distribuicao    = date('Y-m-d', strtotime($instancia->data_distribuicao));
                                $monitoraProcessoMovimentacaoModel->valor_causa          = $instancia->valor_causa;
                                $monitoraProcessoMovimentacaoModel->orgao_julgador       = $instancia->orgao_julgador;
                                $monitoraProcessoMovimentacaoModel->data_hora_inclusao   = date('Y-m-d H:m:i');
                                $monitoraProcessoMovimentacaoModel->data_hora_cadastro   = $data_hora;
                                $monitoraProcessoMovimentacaoModel->id_prc_movimento     = $id_prc_movimento;
//                                $monitoraProcessoMovimentacaoModel->id_atividade         = $id_atividade;

                                $ok = $monitoraProcessoMovimentacaoModel->save();
                                unset($monitoraProcessoMovimentacaoModel);
                                if(!$ok){
                                    Throw new NajException('Erro ao salvar registro em monitora_processo_movimentacao');
                                }
                                
                                //Atualiza o Processo
                                $ProcessoModel = new ProcessoModel();
                                $ProcessoModel->atualizaURL_TJ($instancia->url, $codigoProcesso);
                                unset($ProcessoModel);
                                
                                //Incrementa movimentações obtidas
                                $movimentacoesObtidas++;

                            }

                            //Iremos inserir o registro da situação da pesquisa em "monitora_processo_tribunal_buscas"
                            $this->insereRegistroMonitoraProcessoTribunalBuscas($id_monitora_processo_tribunal, $data_hora, $item->resultado->status, "",  $movimentacoesObtidas, null, $id_resultado_busca);

                            $movimentacoesObtidasGeral += $movimentacoesObtidas;
                            
                        }
                        
                    }
                    
                }
                
            }
            
            //Comita transação
            DB::commit();
            
            if($movimentacoesObtidasGeral > 0){
                $menssagemRetorno = $movimentacoesObtidasGeral . ' movimentações obtidas com sucesso!';
            }else{
                $menssagemRetorno = 'No momento não foram detectadas novas movimentações.';
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
     * Verifica os monitoramentos de processos aptos para pesquisa 
     * e requesita para a Escavador que faça a pesquisa dos mesmos nos sites dos tribunais
     * 
     * @param string $tipo automacao ou manual
     * @param string $nomeArquivoLog 
     * @return JSON
     */
    public function pesquisarOsProcessosNoSiteDoTribunal($tipo, $nomeArquivoLog = 'MonitoramentoTribunalController/pesquisarOsProcessosNoSiteDoTribunal'){
        try{
            Estrutura::gravaLog($nomeArquivoLog, "INICIO DO PROCESSO DE PESQUISA OS PROCESSOS NOS SITES DOS TRIBUNAIS PELA ESCAVADOR");
            //Busca no DB os monitoramentos de processos aptos para pesquisa
            $monitoramentos = $this->getModel()->buscaMonitoramentosElegiveisParaBusca($tipo);

            if(is_string($monitoramentos)){
                Estrutura::gravaLog($nomeArquivoLog, $monitoramentos);
                //Retorno de sucesso
                return Estrutura::responseNaj($monitoramentos);
            }

            Estrutura::gravaLog($nomeArquivoLog, "Total de Monitoramentos: " . count($monitoramentos));
            $monitoramentosCadastrados  = 0;
            $escavadorController        = new EscavadorController();
            $idsDasPesquisasNaEscavador = [];
            foreach($monitoramentos as $indexMonitoramento => $monitoramento){
                Estrutura::gravaLog($nomeArquivoLog, "Início da pesquisa do processo de CNJ: " . $monitoramento->numero_cnj . ", " . ($indexMonitoramento + 1) . " de " . count($monitoramentos));
                //Primeiramente vamos verificar se o CNJ do monitoramento corrente já teve uma pesquisa realizada na Escavador para essa execução
                //Extrai os CNJs que já foram pesquisados na Escavador nessa execução
                $cnjsPesquisadosNaEscavador = array_keys($idsDasPesquisasNaEscavador);
                //Verifica se o CNJ do monitoramento corrente já foi pesquisado na Escavador em um índice anterior do foreach, 
                //isso é feito porque pode acontecer de um CNJ ter mais de um monitoramento, sendo um para cada instância do processo 
                $encontrouCNJ = array_search($monitoramento->numero_cnj, $cnjsPesquisadosNaEscavador);
                if(is_numeric($encontrouCNJ)){
                    Estrutura::gravaLog($nomeArquivoLog, "O cadastro da pesquisa do processo já foi realizado na Escavador em uma execusão anterior, obtendo resultado da pesquisa...");
                    //Se o CNJ já foi pesquisado para essa execução iremos apenas obter os dados dessa pesquisa realizada anteriormente
                    $result = $escavadorController->resultadoEspecíficoDeUmaBuscaAssincrona($idsDasPesquisasNaEscavador[$monitoramento->numero_cnj], $nomeArquivoLog);
//                  Código para teste
//                  $result = '{"code":200,"content":{"id":7796843,"created_at":{"date":"2023-03-02 22:00:00.000000","timezone_type":3,"timezone":"UTC"},"enviar_callback":"SIM","link_api":"https:\/\/api.escavador.com\/api\/v1\/async\/resultados\/7796843","numero_processo":"5013425-86.2023.8.24.0930","resposta":null,"status":"PENDENTE","status_callback":null,"tipo":"BUSCA_PROCESSO","tribunal":{"sigla":"TJSC","nome":"Tribunal de Justi\u00e7a de Santa Catarina","busca_processo":1,"busca_nome":1,"disponivel_autos":1,"busca_documento":1,"quantidade_creditos_busca_processo":5,"quantidade_creditos_busca_nome":7,"quantidade_creditos_busca_documento":10},"valor":"5013425-86.2023.8.24.0930"}}';
                }else{
                    Estrutura::gravaLog($nomeArquivoLog, "Realizando cadastro da pesquisa do processo na Escavador...");
                    //Faz requisição para Escavador para iniciar a pesquisa do CNJ
                    $result = $escavadorController->pesquisarProcessoNoSiteDoTribunalAssincrono($monitoramento->numero_cnj, $monitoramento->abrangencia, $nomeArquivoLog);
//                  Código para teste
//                  $result = '{"code":200,"content":{"id":7796843,"created_at":{"date":"2023-03-02 22:00:00.000000","timezone_type":3,"timezone":"UTC"},"enviar_callback":"SIM","link_api":"https:\/\/api.escavador.com\/api\/v1\/async\/resultados\/7796843","numero_processo":"5013425-86.2023.8.24.0930","resposta":null,"status":"PENDENTE","status_callback":null,"tipo":"BUSCA_PROCESSO","tribunal":{"sigla":"TJSC","nome":"Tribunal de Justi\u00e7a de Santa Catarina","busca_processo":1,"busca_nome":1,"disponivel_autos":1,"busca_documento":1,"quantidade_creditos_busca_processo":5,"quantidade_creditos_busca_nome":7,"quantidade_creditos_busca_documento":10},"valor":"5013425-86.2023.8.24.0930"}}';
                }
                //Define os valores dos atributos
                $result             = json_decode($result);
                $data_hora          = date('Y-m-d H:i:s');
                $status             = 'ERRO';
                $status_msg         = "";
                $id_resultado_busca = null;
                //Verifica status da requisição
                if($result->code === 200){
                    //UTC -3 para horário de Brasília
                    //$timestamp = strtotime($result->content->created_at->date . " -3 hours"); 
                    //$data_hora = date('Y-m-d H:i:s', $timestamp);
                    $status    = $result->content->status;
                    $monitoramentosCadastrados++;
                    //Se tiver '$link_api' extrai o id do resultado da busca no Escavador
                    $id_resultado_busca = $result->content->id;
                    $idsDasPesquisasNaEscavador[$monitoramento->numero_cnj] = $id_resultado_busca;
                }else if ($result->code === 422){
                    $status_msg = $result->message;
                }else{
                    $status_msg = json_encode($result);
                }
                
                //Inicia transação no BD
                DB::beginTransaction();//
                
                $status_menssage = $this->insereRegistroMonitoraProcessoTribunalBuscas($monitoramento->id, $data_hora, $status, $status_msg, null, $id_resultado_busca);
                Estrutura::gravaLog($nomeArquivoLog, $status_menssage);
                Estrutura::gravaLog($nomeArquivoLog, "Fim da pesquisa do processo de CNJ: " . $monitoramento->numero_cnj . ", " . ($indexMonitoramento + 1) . " de " . count($monitoramentos));
                //Comita transação
                DB::commit();
                
            }
            
            unset($escavadorController);
            $menssagemRetorno = "$monitoramentosCadastrados de " . count($monitoramentos) . " monitoramentos iniciados no site do tribunal.";
            
            Estrutura::gravaLog($nomeArquivoLog, "FIM DO PROCESSO DE PESQUISA OS PROCESSOS NOS SITES DOS TRIBUNAIS PELA ESCAVADOR");

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
     * Pesquisa Processo No Site Do Tribunal
     * 
     * @param string $nomeArquivoLog 
     * @return JSON
     */
    public function pesquisarProcessosNoSiteDoTribunal(Request $request, $nomeArquivoLog = 'MonitoramentoTribunalController/pesquisarProcessosNoSiteDoTribunal'){
        try{
            $id                  = $request->input('id');
            $numero_cnj          = $request->input('numero_cnj');
            $abrangencia         = $request->input('abrangencia');
            $escavadorController = new EscavadorController();
            //Faz requisição para Escavador
            $result = $escavadorController->pesquisarProcessoNoSiteDoTribunalAssincrono($numero_cnj, $abrangencia);
            //Define os valores dos atributos
            $result             = json_decode($result);
            $data_hora          = date('Y-m-d H:i:s');
            $status             = 'ERRO';
            $status_msg         = "";
            $id_resultado_busca = null;
            //Verifica status da requisição
            if($result->code === 200){
                //UTC -3 para horário de Brasília
                //$timestamp = strtotime($result->content->created_at->date . " -3 hours"); 
                //$data_hora = date('Y-m-d H:i:s', $timestamp);
                $status    = $result->content->status;
                //Se tiver '$link_api' extrai o id do resultado da busca no Escavador
                $id_resultado_busca = $this->extraiIdLinkApiEscavador($result->content->link_api);
            }else if ($result->code === 422){
                $status_msg = $result->message;
            }else{
                $status_msg = json_encode($result);
            }

            $status_msg = $status_msg . " (BUSCA FORÇADA PELO USUÁRIO)";

            //Inicia transação no BD
            DB::beginTransaction();//

            $this->insereRegistroMonitoraProcessoTribunalBuscas($id, $data_hora, $status, $status_msg, null, $id_resultado_busca);

            //Comita transação
            DB::commit();
            
            unset($escavadorController);
            
            if($result->code == 200){
                //Retorno de sucesso
                return Estrutura::responseNaj("Pesquisa do proceeso cadastrada na Escavador", $result->code);
            }else{
                //Retorno de erro
                return Estrutura::responseNaj($result->message, $result->code);
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
     * Pesquisa Processo Com Erros na Última Busca No Site Do Tribunal
     * 
     * @param string $nomeArquivoLog 
     * @return JSON
     */
    public function pesquisarProcessosComErroNoSiteDoTribunal(Request $request, $nomeArquivoLog = 'MonitoramentoTribunalController/pesquisarProcessosComErroNoSiteDoTribunal'){
        try{
            //SQL para buscar os monitoramentos com erro na última busca
            $monitoramentos = $this->getModel()->monitoramentosComErroNaUltimaBusca();
            $escavadorController = new EscavadorController();
            
            foreach ($monitoramentos as $monitoramento){
                //Faz requisição para Escavador
                $result = $escavadorController->pesquisarProcessoNoSiteDoTribunalAssincrono($monitoramento->numero_cnj, $monitoramento->abrangencia);
                //Define os valores dos atributos
                $result             = json_decode($result);
                $data_hora          = date('Y-m-d H:i:s');
                $status             = 'ERRO';
                $status_msg         = "";
                $id_resultado_busca = null;
                //Verifica status da requisição
                if($result->code === 200){
                    //UTC -3 para horário de Brasília
                    //$timestamp = strtotime($result->content->created_at->date . " -3 hours"); 
                    //$data_hora = date('Y-m-d H:i:s', $timestamp);
                    $status    = $result->content->status;
                    //Se tiver '$link_api' extrai o id do resultado da busca no Escavador
                    $id_resultado_busca = $this->extraiIdLinkApiEscavador($result->content->link_api);
                }else if ($result->code === 422){
                    $status_msg = $result->message;
                }else{
                    $status_msg = json_encode($result);
                }

                //Inicia transação no BD
                DB::beginTransaction();//

                $this->insereRegistroMonitoraProcessoTribunalBuscas($monitoramento->id, $data_hora, $status, $status_msg, null, $id_resultado_busca);

                //Comita transação
                DB::commit();
            }
            
            unset($escavadorController);
            
            if($result->code == 200){
                //Retorno de sucesso
                return Estrutura::responseNaj("Pesquisa dos proceesos cadastrada na Escavador", $result->code);
            }else{
                //Retorno de erro
                return Estrutura::responseNaj($result->message, $result->code);
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
     * Verifica se existe monitoramentos que estão com status "PENDENTES" a mais de 12 horas e 
     * caso econtrar insere um registro com status de "ERRO" em "monitora_processo_tribunal_buscas"  
     * para cada monitoramento deste tipo
     * 
     * @param string $nomeArquivoLog 
     * @return JSON
     */
    public function persisteMonitoramentosObsoletos($nomeArquivoLog = 'MonitoramentoTribunalController/persisteMonitoramentosObsoletos'){
        try{
            
            $monitoramentos = $this->getModel()->buscaMonitoramentosObsoletos(); 
            if(count($monitoramentos) > 0){
                foreach($monitoramentos as $monitoramento){
                    $id_monitora_tribunal = $monitoramento->id;
                    $data_hora            = date("Y-m-d H:i:s");
                    $status               = "ERRO";
                    $status_msg           = "Monitoramento ficou PENDENTE por mais de 12 horas.";

                    //Inicia transação no BD
                    DB::beginTransaction();

                    $this->insereRegistroMonitoraProcessoTribunalBuscas($id_monitora_tribunal, $data_hora, $status, $status_msg, null, $monitoramento->id_resultado_busca);

                    //Comita transação
                    DB::commit();

                }
                //Retorno de sucesso
                return Estrutura::responseNaj('Monitoramentos obsoletos persistidos com sucesso');
            }else{
                //Retorno de sucesso
                return Estrutura::responseNaj('Não foram identificados monitoramentos obsoletos');
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
     * Busca os tribunais na Escavador
     * 
     * @param string $nomeArquivoLog nome do arquivod de log
     * @param bool   $print_r        define se ira imprimir o resultado em tela
     * 
     * @return object
     */
    public function buscaTribunaisEscavador($nomeArquivoLog = 'MonitoramentoTribunalController/buscaTribunaisEscavador', $JSON = true){
        $escavadorController = new EscavadorController();
        $content             = $escavadorController->retornarSistemasDosTribunaisDisponiveis($nomeArquivoLog);
        if($JSON){
            return $content;
        }
        $content = json_decode($content);
        return $content;
    }
    
    /**
     * Persiste os tribunais do Escavados no BD
     * 
     * @return JSON
     */
    public function persisteTribunais($nomeArquivoLog = 'MonitoramentoTribunalController/persisteTribunais'){
        try{
            //Primeiramente vamos persistir os tribunais em 'monitora_tribunais'
            
            //Inicia transação no BD
            DB::beginTransaction();
            
            //Contador de tribunais salvos no BD
            $countTribunais = 0;
            
            //Busca a sigla de todos os tribunais cadastrados no BD
            $tribunaisDeMonitoraTribunais = $this->getModel()->buscaSiglaTribunaisDeMonitoraTribunais();
            
            //Se não hover nehum registro em monitora_tribunais
            if(count($tribunaisDeMonitoraTribunais) == 0){
                //Seta o primeiro registro como 'indefinido' em monitora_tribunais
                $this->getModel()->setaTribunalIndefinido();
            }
            
            //Busca os tribunais na Escavador
            $content = $this->buscaTribunaisEscavador($nomeArquivoLog, false);
            
            //Verifica se requisição para a Escavador foi bem sucedida
            if($content->code != 200){
                return response()->json($content)->content();
            }
            
            
            //Para cada item do conteúdo...
            foreach ($content->content->items as  $tribunal){
                
                //Verifica se tribunal da Escavador já está cadastrado no BD, se naõ estiver iremos cadastra-lo
                if(!in_array($tribunal->sigla, $tribunaisDeMonitoraTribunais)){

                    //Cadastra registro em 'monitora_tribunais'
                    //Seta os valores do 'monitora_tribunais' em seus respectivos campos
                    $monitoraTribunaisModel                          = new MonitoraTribunaisModel();
                    $monitoraTribunaisModel->id                      = MonitoraTribunaisModel::max('id') + 1;
                    $monitoraTribunaisModel->nome                    = $tribunal->nome;
                    $monitoraTribunaisModel->sigla                   = $tribunal->sigla;
                    $monitoraTribunaisModel->busca_nome              = $tribunal->busca_nome;
                    $monitoraTribunaisModel->busca_processo          = $tribunal->busca_processo;
                    $monitoraTribunaisModel->creditos_busca_processo = isset($tribunal->quantidade_creditos_busca_processo) ? $tribunal->quantidade_creditos_busca_processo : null ;
                    $monitoraTribunaisModel->creditos_busca_nome     = $tribunal->quantidade_creditos_busca_nome;
                    $monitoraTribunaisModel->disponivel_autos        = $tribunal->disponivel_autos;

                    $ok = $monitoraTribunaisModel->save();
                    unset($monitoraTribunaisModel);
                    if(!$ok){
                        Throw new NajException('Erro ao salvar registro em monitora_tribunais.');
                    }

                    $countTribunais++;

                }
            }
            
            //Comita transação
            DB::commit();
            
            //Agora que já temos os tribunais atualziados em 'monitora_tribunais' vamos persistir os tribunais de 'monitora_tribunais' para 'prc_orgao'
            
            //Inicia transação no BD
            DB::beginTransaction();
            
            //Busca novamente a sigla de todos os tribunais cadastrados em monitora_tribunais no BD
            $tribunaisDeMonitoraTribunais = $this->getModel()->buscaSiglaTribunaisDeMonitoraTribunais();
            
            //Busca a sigla de todos os tribunais cadastrados em prc_orgao no BD
            $tribunaisDePrcOrgao = $this->getModel()->buscaSiglaTribunaisDePrcOrgao();
            
            //Para cada item de monitora_tribunais...
            foreach ($tribunaisDeMonitoraTribunais as  $tribunal){
                
                //Verifica se tribunal da tabela monitora_tribunais já está cadastrado na tabela prc_orgao do BD, se naõ estiver iremos cadastra-lo
                if(!in_array($tribunal, $tribunaisDePrcOrgao)){

                    //Cadastra registro em 'prc_orgao'
                    //Seta os valores do 'prc_orgao' em seus respectivos campos
                    $prcOrgaoModel        = new PrcOrgaoModel();
                    $prcOrgaoModel->ID    = PrcOrgaoModel::max('ID') + 1;
                    $prcOrgaoModel->ORGAO = $tribunal;
                    $prcOrgaoModel->URL   = null;

                    $ok = $prcOrgaoModel->save();
                    unset($prcOrgaoModel);
                    if(!$ok){
                        Throw new NajException('Erro ao salvar registro em prc_orgao.');
                    }

                }
            }
            
            //Comita transação
            DB::commit();
            
            if($countTribunais > 0){
                //Retorno de sucesso
                return Estrutura::responseNaj($countTribunais . ' Tribunais do Escavador salvos com sucesso!');
            } else {
                //Retorno de sucesso
                return Estrutura::responseNaj('No momento não existe novos tribunais na Escavador.');
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
     * Seta registros de "monitora_processo_movimentacao" como lidos
     */
    public function setaRegistroslidos(Request $request, $nomeArquivoLog = 'MonitoramentoTribunalController/setaRegistroslidos'){
        try{
            //Inicia transação no BD
            DB::beginTransaction();
            $ids     = $request->input('ids');
            foreach($ids as $id){
                $this->getModel()->setaRegistroComoLido($id);
            }
            //Comita transação
            DB::commit();
            //Retorno de sucesso
            return Estrutura::responseNaj('Registros setados como lidos com sucesso!');
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
     * Seta registros de "monitora_processo_movimentacao" como lidos
     */
    public function setaTodosRegistrosComolidos($nomeArquivoLog = 'MonitoramentoTribunalController/setaTodosRegistrosComolidos'){
        try{
            //Inicia transação no BD
            DB::beginTransaction();
            $this->getModel()->setaTodosRegistrosComolidos();
            //Comita transação
            DB::commit();
            //Retorno de sucesso
            return Estrutura::responseNaj('Registros setados como lidos com sucesso!');
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
     * Exclui os registros de "monitora_processo_movimentacao" e seus relacionamentos em "prc_movimento" e "atividade"
     */
    public function excluirMovimentacoes($id, $instancia, $nomeArquivoLog = 'MonitoramentoTribunalController/excluirMovimentacoes'){
        try{
            $result = $this->getModel()->excluirMovimentacao($id, $instancia);
            $code   = $result['code'];
            $msg    = $result['msg_retorno'];        
            //Retorno de sucesso
            return Estrutura::responseNaj($msg, $code);
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
     * Extrai do 'link_api' o identificador numérico do resultado da busca
     * 
     * @param string $url link_api
     * @return string
     */
    public function extraiIdLinkApiEscavador($url){
        $numero_resultado = substr($url, 50);
        $url = substr($url, 0, 50);
        $url_api = "https://api.escavador.com/api/v1/async/resultados/";
        if($url_api == $url){
            if(is_numeric($numero_resultado)){
                return $numero_resultado;
            }else{
                return null;
            }
        }else{
            return null;
        }
    }
    
    /**
     * Busca o total de monitoramentos no sistema
     * 
     * @return JSON
     */
    public function totalDeMonitoramentosNoSistema() {
        $result = $this->getModel()->totalDeMonitoramentosNoSistema();
        return response()->json($result)->content();
    }
 
    /**
     * Busca o total de monitoramentos ativos no sistema
     * 
     * @return JSON
     */
    public function totalDeMonitoramentosAtivosNoSistema() {
        $result = $this->getModel()->totalDeMonitoramentosAtivosNoSistema();
        return response()->json($result)->content();
    }
    
    /**
     * Verifica se CNJ já tem monitoramento com pesquisa realizada na Escavador 
     *  
     * @param Request $request
     * @return JSON
     */
    public function verificaSeCNJjaTemMonitoramento(Request $request){
        $numero_cnj    = $request->input('numero_cnj');
        $abrangencia   = $request->input('abrangencia');
        $monitoramento = $this->getModel()->verificaSeCNJjaTemMonitoramento($numero_cnj, $abrangencia);
        if(count($monitoramento) > 0){
            $monitoramento = $monitoramento[0];
            if(!is_null($monitoramento->id_resultado_busca)){
                
                //Inicia transação no BD
                DB::beginTransaction();
                
                //Pega o id do último monitoramento em "monitora_processo_tribunal";
                $id = DB::table('monitora_processo_tribunal')->max('id');
                
                //Vamos inserir um registro de PENDENTE na tabela de buscas para o novo monitoramento
                $this->insereRegistroMonitoraProcessoTribunalBuscas($id, date("Y-m-d H:i:s"), "PENDENTE", "Busca em andamento", null, $monitoramento->id_resultado_busca);

                //Obtêm as movimentações para o novo monitoramento
                $result                    = $this->persisteMovimentacaoPelaBuscaEspecifica($monitoramento);
                $menssagemRetorno          = $result['menssagemRetorno'];
                $movimentacoesObtidasGeral = $result['movimentacoesObtidasGeral'];
                $monitoramentosPendentes   = $result['monitoramentosPendentes'];

                //Comita transação
                DB::commit();

                if($movimentacoesObtidasGeral > 0){
                    $menssagemRetorno .= $movimentacoesObtidasGeral . ' movimentações obtidas com sucesso!';
                    $code = 200;
                }else{
                    $menssagemRetorno .= "No momento não foram detectadas novas movimentações para o CNJ $numero_cnj.";
                    $code = 200;
                }
                if($monitoramentosPendentes > 0){
                    $menssagemRetorno .= 'O monitoramento ainda está pendente com busca em andamento!';
                }
            }else{
                $menssagemRetorno = "Já existe um monitoramento para este CNJ, no entanto o id_resultado_busca não foi informado, contate o suporte.";
                $code = 400;
            }
        }else{
            $menssagemRetorno = "Ainda não existem monitoramentos registrados no banco de dados para este CNJ.";
            $code = 200;
        }
        return Estrutura::responseNaj($menssagemRetorno, $code);
    }
    
    /**
     * Buscar Movimentacoes Processos Nos Tribunais
     * Engloba todo o fluxo da busca das movimentações dos Processos Nos Tribunais
     * 
     * @param $tipo automacao ou manual
     * @return JSON
     */
    public function buscarMovimentacoesProcessosNosTribunais(Request $request, $nomeArquivoLog = 'MonitoramentoTribunalController/buscarMovimentacoesProcessosNosTribunais'){
        try{
            //Seta o limite de execução do script
            //William Goebel
            //set_time_limit(900); seta 15 minutos de execusão, usei para simular erro do servidor do naj_eustaquio Licenciado: LAUSCHNER E ADVOGADOS ASSOCIADOS
            set_time_limit(0); //0=NOLIMIT
            ini_set('max_execution_time', 0); //0=NOLIMIT
            //Seta o limite da memória
            //ini_set('memory_limit', '4096M'); // Antes era 1024, vamos ver agora se essa porra funciona sem quebrar com mais memória;

            //Seta a Time Zone
            date_default_timezone_set('America/Sao_Paulo');
            
            //Se método for POST significa que a função foi chamada pela automação, se for GET significa que a função foi chamada manualmete
            $method = $request->method();
            //Obtêm os registros de "dias_mes" e "dias_semana" do sys_config
            $sys_config_dias_mes    = DB::select("SELECT VALOR FROM sys_config WHERE SECAO = 'TRIBUNAIS' AND CHAVE = 'DIAS_MES'");
            $sys_config_dias_semana = DB::select("SELECT VALOR FROM sys_config WHERE SECAO = 'TRIBUNAIS' AND CHAVE = 'DIAS_SEMANA'");
            //Verifica se existe os registros de "dias_mes" e "dias_semana" no sys_config
            if(count($sys_config_dias_mes) > 0 && count($sys_config_dias_semana) > 0){
                $sys_config_dias_mes    = $sys_config_dias_mes[0]->VALOR;
                $sys_config_dias_semana = $sys_config_dias_semana[0]->VALOR;
            }else{
                if(!count($sys_config_dias_mes) > 0){
                    throw new NajException("A seção 'TRIBUNAIS' e a chave 'DIAS_MES' não foi definida no banco de dados, contate o suporte!");
                }elseif(!count($sys_config_dias_semana) > 0){
                    throw new NajException("A seção 'TRIBUNAIS' e a chave 'DIAS_SEMANA' não foi definida no banco de dados, contate o suporte!");
                }
            }
            if($method == 'POST'){
                //NELSON: Para a rota de automação da busca por andamentos dos tribunais, 
                //só executar se estiver definido um dia da semana em SYS_CONFIG, senão, 
                //gerar um exception ou um json de resposta acusando a mesma mensagem anterior.
                $tipo = "automacao";
                //Se "dias_mes" e "dias_semana" no sys_config forem nulos significa que a busca automática está desativada
                if(is_null($sys_config_dias_mes) && is_null($sys_config_dias_semana)){
                    return Estrutura::responseNaj([Estrutura::responseNaj("A busca automática está desativada, não é permitido obter movimentações!", 300)]);
                //Se "dias_mes" e "dias_semana" no sys_config forem diferentes de nulos significa que a inconsistência de dados pois somente um pode estar setado
                }elseif(!is_null($sys_config_dias_mes) && !is_null($sys_config_dias_semana)){
                    return Estrutura::responseNaj([Estrutura::responseNaj("Atenção, detectado inconsistência de dados, dias do mês e dias da semana setados ao mesmo tempo no sys_config!", 300)]);
                }
            }else if($method == 'GET'){
                //NELSON: No menu dropdown em BUSCAR MOVIMENTAÇÕES considerar que se não tiver um DIA DA SEMANA definido no SYS_CONFIG
                //então é permitida a busca manual em qualquer dia (não considerar o dia da semana que está marcado no monitoramento, 
                //considerar apenas se já houve uma busca no mesmo dia e não executar novamente), 
                //SE HOUVER um ou mais dias definidos no SYS_CONFIG então ele não permite acionar o clique no menu exibindo uma mensagem
                //para o usuário: "A busca automática está ativada, não é permitido obter movimentações manualmente!".
                $tipo = "manual";
                if(!is_null($sys_config_dias_semana)){
                    return Estrutura::responseNaj([Estrutura::responseNaj("A busca automática está ativada, não é permitido obter movimentações!", 300)]);
                }
            }
            //1°passo, verifica se existe tribunais novos e se encontrar cadastra na tabela monitora_tribunais no DB 
            $result1 = $this->persisteTribunais($nomeArquivoLog);
            //2° passo, busca o resultado das (pesquisas dos processos nos tribunais) através dos callbacks da Escavador dos processos cujo o status da última busca seja igual a pendente e
            //cadastra os resultados obtidos das movimentações e envolvidos dos processoas respectivamente nas tabelas monitora_processo_novimentacao e monitora_processo_envolvidos no DB
            //além disso atualiza o status da última pesquisa de cada processo com SUCESSO OU ERRO na tabela monitora_processo_tribunal_buscas
            $result2 = $this->persisteMovimentacoesPelaBuscaEspecifica($nomeArquivoLog);
            //3° passo, Verifica se existe monitoramentos que estão com status "PENDENTES" a mais de 12 horas e 
            //caso econtrar insere um registro com status de "ERRO" em "monitora_processo_tribunal_buscas"  
            //para cada monitoramento deste tipo
            //$result3 = $this->persisteMonitoramentosObsoletos($nomeArquivoLog);
            //4° passo, Verifica os monitoramentos de processos aptos para pesquisa 
            //e requesita para a Escavador que faça a pesquisa dos mesmos nos sites dos tribunais
            $result4 = $this->pesquisarOsProcessosNoSiteDoTribunal($tipo, $nomeArquivoLog);
            //Retorno de sucesso
            return Estrutura::responseNaj([$result2,$result4]);
            //return Estrutura::responseNaj([$result4]);
        } catch (NajException $e) {
            return Estrutura::responseNajExeption($e, $nomeArquivoLog);
        } catch (Exception $e) {
            return Estrutura::responseNajExeption($e, $nomeArquivoLog);
        } catch (Error $e) {
            return Estrutura::responseNajExeption($e, $nomeArquivoLog);
        } catch (ClientException $e){
            return Estrutura::responseNajExeption($e, $nomeArquivoLog);
        }
    }
    
    /**
     * Buscar Movimentacoes dos Processos Nos Tribunais
     * Persiste Movimentações Pendentes pela busca específica e Verifica as obsoletas
     * 
     * @param $tipo automacao ou manual
     * @return JSON
     */
    public function buscarMovimentacoesPendentesProcessosNosTribunais( $nomeArquivoLog = 'MonitoramentoTribunalController/buscarMovimentacoesPendentesProcessosNosTribunais'){
        try{
            //Seta o limite de execução do script
            set_time_limit(0);
            
            //Seta o limite da memória
            ini_set('memory_limit', '1024M');

            //Seta a Time Zone
            date_default_timezone_set('America/Sao_Paulo');
            
            //1° passo
            $result1 = $this->persisteMovimentacoesPelaBuscaEspecifica($nomeArquivoLog);
            //2° passo
            //$result2 = $this->persisteMonitoramentosObsoletos($nomeArquivoLog);
            
            //Retorno de sucesso
            return $result1;
        } catch (NajException $e) {
            return Estrutura::responseNajExeption($e, $nomeArquivoLog);
        } catch (Exception $e) {
            return Estrutura::responseNajExeption($e, $nomeArquivoLog);
        } catch (Error $e) {
            return Estrutura::responseNajExeption($e, $nomeArquivoLog);
        } catch (ClientException $e){
            return Estrutura::responseNajExeption($e, $nomeArquivoLog);
        }
    }
    
}
