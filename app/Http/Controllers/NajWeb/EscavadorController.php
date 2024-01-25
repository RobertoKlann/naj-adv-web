<?php

namespace App\Http\Controllers\NajWeb;

use Exception;
use Error;
use App\Exceptions\NajException;
use App\Http\Controllers\NajController;
use App\Http\Controllers\NajWeb\SysConfigController;
use App\Http\Controllers\Api\NajApiController;
use App\Estrutura;
use App\Models\EscavadorModel;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

/**
 * Controller da integração com a API da ESCAVADOR.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      16/03/2020
 */
class EscavadorController extends NajController {
    
    /** @var SysConfigController */
    protected $sysConfigController;
    
    /**
     * Seta o controller do SysConfig ao carregar o controller
     */
    public function onLoad() {
        $this->sysConfigController = new SysConfigController();
        $this->setModel(new EscavadorModel);
    }

    //Autenticação
    
    /**
     * A API do Escavador utiliza o protocolo OAuth 2.0 para autenticação e autorização, 
     * permitindo que aplicações enviem solicitações autenticadas em nome de usuários individuais do Escavador. 
     * Para isso, é necessário ter uma conta na plataforma. Você pode fazer isso acessando aqui.
     * O access_token recebido deve ser utilizado no cabeçalho das outras requisições, 
     * para que a API identifique o usuário. 
     * O token de acesso tem vida útil limitada (valor retornado no campo expires_in) e caso expire, 
     * será necessário obter um novo token repetindo esta requisição.
     * 
     * @param string $nomeArquivoLog Nome do arquivo de log 'escavador/solicitarTokenDeAcesso' por default
     * @return JSON
     */
    public function solicitarTokenDeAcesso($nomeArquivoLog = 'escavador/solicitarTokenDeAcesso'){
        try{
            $client = new \GuzzleHttp\Client();
            $response = $client->post("https://www.escavador.com/api/v1/request-token", [
                'headers' => [
                    "X-Requested-With" => "XMLHttpRequest",
                    "Content-Type" => "application/json",
                ],
                'json' => [
                        //username string obrigatório E-mail do usuário do escavador.
                        "username" => $this->getUserName(),
                        //password string obrigatório Senha do usuário do escavador.
                        "password" => $this->getPassword(),
                    ],
            ]);
            unset($client);
            return Estrutura::responseNajEscavador($response->getBody()->getContents());
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
    
    //Busca
    
    /**
     * Pesquisa um termo no escavador. 
     * 
     * @param Request $request        Parâmetros da requisição
     * @param string  $nomeArquivoLog Nome do arquivo de log 'escavador/buscarPorTermo' por default
     * @ticket Consome 1 CRÉDITO por requisição.
     * @return JSON
     */
    public function buscarPorTermo(Request $request, $nomeArquivoLog = 'escavador/buscarPorTermo'){
        try{
            /**
             * $termo o termo a ser pesquisado. Você pode pesquisar entre aspas dupla para match perfeito (obrigatório).
             */
            $termo  = (string) json_decode($request->input('termo'));    
            /**
             * $qo tipo da entidade a ser pesquisada (obrigatório). 
             *
             * Para $qo os valores podem ser:
             * t:  Para pesquisar todos os tipos de entidades.
             * p:  Para pesquisar apenas as pessoas.
             * i:  Para pesquisar apenas as instituições.
             * pa: Para pesquisar apenas as patentes.
             * d:  Para pesquisar apenas os Diários Oficiais.
             * en: Para pesquisar as pessoas e instituições que são partes em processos.
             * a:  Para pesquisar apenas os artigos. (obrigatório).
             */
            $tipo   = (string) json_decode($request->input('tipo'));
            /**
             * $page Número da página, respeitando o limite informado (opcional).
             */
            $pagina = (int)    json_decode($request->input('pagina'));

            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://www.escavador.com/api/v1/busca", [
                'headers' =>  [
                    "Authorization" => "Bearer " . $this->getToken(),
                    "X-Requested-With" => "XMLHttpRequest",
                ],
                'query' => [
                        "q"    => $q,    //Ex: "João Silva"
                        "qo"   => $qo,   //Ex: "t"
                        "page" => $page, //Ex: "1"
                    ],
            ]);
            unset($client);
            return Estrutura::responseNajEscavador($response->getBody()->getContents());
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
    
    //Busca Assíncrona
    
    /**
     * Consultar todos os resultados das buscas assíncronas (Busca de processos, documentos e nomes em sistemas de tribunais). 
     * 
     * @param string  $nomeArquivoLog Nome do arquivo de log 'escavador/todosOsResultadosDasBuscasAssincronas' por default
     * @ticket GRÁTIS por requisição.
     * @return JSON
     */
    public function todosOsResultadosDasBuscasAssincronas($nomeArquivoLog = 'escavador/todosOsResultadosDasBuscasAssincronas'){
        try{
            
            /**
             * Tipos de busca assíncrona
             * 
             * BUSCA_POR_NOME: Busca processos pelo nome em tribunais.
             * BUSCA_POR_DOCUMENTO: Busca processos pelo documento (CPF ou CNPJ) em tribunais.
             * BUSCA_PROCESSO: Busca processo pela numeração CNJ.
             * 
             */
            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://api.escavador.com/api/v1/async/resultados", [
                'headers' => [
                        "Authorization" => "Bearer " . $this->getToken(),
                        "X-Requested-With" => "XMLHttpRequest",
                    ],
            ]);
            unset($client);
            return Estrutura::responseNajEscavador($response->getBody()->getContents());
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
     * Consultar um resultado específico de uma busca assíncrona (Busca de processos, documentos e nomes em sistemas de tribunais).
     * 
     * @param int     $id             Identificador numérico do resultado da busca.
     * @param string  $nomeArquivoLog Nome do arquivo de log 'escavador/resultadoEspecificoDeUmaBuscaAssíncrona' por default
     * @ticket GRÁTIS por requisição.
     * @return JSON
     */
    public function resultadoEspecíficoDeUmaBuscaAssincrona($id, $nomeArquivoLog = 'escavador/resultadoEspecíficoDeUmaBuscaAssincrona'){
        try{
            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://api.escavador.com/api/v1/async/resultados/" . $id, [
                'headers' => [
                        "Authorization" => "Bearer " . $this->getToken(),
                        "X-Requested-With" => "XMLHttpRequest",
                    ],
            ]);
            unset($client);
            $response = $response->getBody()->getContents();
            $response = Estrutura::responseNajEscavador($response);
            return $response;
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
    
    //Callback
    
    /**
     * Consultar todos os callbacks recebidos pela API.
     * 
     * @param Request $request        Parâmetros da requisição
     * @param string  $nomeArquivoLog Nome do arquivo de log 'escavador/retornarOsCallbacks' por default
     * @ticket GRÁTIS por requisição.
     * @return JSON
     */
    public function retornarOsCallbacks(Request $request, $nomeArquivoLog = 'retornarOsCallbacks'){
        try{
            //Data e hora (em UTC) máxima dos callbacks listados (opcional).
            $data_maxima = $request->input('data_maxima');  
            $data_maxima = (string) isset($data_maxima) ? $data_maxima : date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'). " +3 hours +1 second"));
            $data_minima = $request->input('data_minima');  
            $data_minima = (string) isset($data_minima) ? $data_minima : date('Y-m-d H:i:s', strtotime(date('Y-m-d') . ' -360 days'));
            $page        = $request->input('page');  
            $page        = (int) isset($page) ? $page : "";
            $client      = new Client();
            $url         = "https://www.escavador.com/api/v1/callbacks";
            $response = $client->get($url, [
                'headers' => [
                    "Authorization" => "Bearer " . $this->getToken(),
                    "X-Requested-With" => "XMLHttpRequest",
                ],
                'query' => [
                        "data_maxima" => $data_maxima,
                        "data_minima" => $data_minima,
                        "page" => $page,
                    ],
            ]);
            unset($client);
            return Estrutura::responseNajEscavador($response->getBody()->getContents());
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
    
    //Créditos
     
    /**
     * Retorna o saldo atual dos créditos.  
     * 
     * @param string $nomeArquivoLog Nome do arquivo de log 'escavador/consultarCreditos' por default
     * @ticket GRÁTIS por requisição.
     * @return JSON
     */
    public function consultarCreditos($nomeArquivoLog = 'escavador/consultarCreditos'){
        try{
            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://www.escavador.com/api/v1/quantidade-creditos", [
                'headers' => [
                    "Authorization" => "Bearer " . $this->getToken(),
                    "X-Requested-With" => "XMLHttpRequest",
                ],
            ]);
            unset($client);
            return Estrutura::responseNajEscavador($response->getBody()->getContents());
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
    
    //Diários Oficiais
    
    /**
     * Retorna as origens de todos os diários disponiveis no Escavador.
     * 
     * @param string $nomeArquivoLog Nome do arquivo de log 'escavador/retornarOrigens' por default 
     * @ticket GRÁTIS por requisição.
     * @return JSON
     */
    public function retornarOrigens($nomeArquivoLog = 'escavador/retornarOrigens'){
        try{
            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://www.escavador.com/api/v1/origens", [
                'headers' => [
                    "Authorization" => "Bearer " . $this->getToken(),
                    "X-Requested-With" => "XMLHttpRequest",
                ],
            ]);
            unset($client);
            return Estrutura::responseNajEscavador($response->getBody()->getContents());
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
     * Retorna os Ids de todas as origens
     * 
     * @param string $nomeArquivoLog Nome do arquivo de log 'escavador/retornarIdsOrigens' por default 
     * @return JSON
     */
    public function retornarIdsOrigens($nomeArquivoLog = 'escavador/retornarIdsOrigens'){
        try{
            $idsOrigens = [];
            $origens = json_decode($this->retornarOrigens());
            if($origens->code = 200){
                foreach ($origens->content as $origen){
                    foreach($origen->diarios as $diario){
                        $idsOrigens[] = $diario->id;
                    }
                }
                return Estrutura::responseNajEscavador(json_encode($idsOrigens));
            } else {
                throw new NajException("Erro ao obter os ids das origens.", 500); 
            }
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

    //Monitoramento de Diários Oficiais
    
    /**
     * Retorna os diários oficiais de um monitoramento pelo identificador do monitoramento.
     * 
     * @param int    $monitoramentoId Identificador numérico de um monitoramento de diários.
     * @param string $nomeArquivoLog  Nome do arquivo de log 'escavador/retornarOsDiariosOficiaisMonitorados' por default
     * @ticket GRÁTIS por requisição.
     * @return JSON
     */
    public function retornarOsDiariosOficiaisMonitorados($id, $nomeArquivoLog = 'escavador/retornarOsDiariosOficiaisMonitorados'){
        try{
            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://api.escavador.com/api/v1/monitoramentos/" . $id . "/origens", [
                'headers' => [
                    "Authorization" => "Bearer " . $this->getToken(),
                    "X-Requested-With" => "XMLHttpRequest",
                ],
            ]);
            unset($client);
            return Estrutura::responseNajEscavador($response->getBody()->getContents());
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
     * Cadastra um termo ou processo para ser monitorado nos diários.
     * 
     * @param Request $request        Parâmetros da requisição
     * @param string  $nomeArquivoLog Nome do arquivo de log 'escavador/registrarNovoMonitoramento' por default
     * @ticket 2 CRÉDITOS/DIA * O valor dos créditos se referem a cada dia em que o monitoramento estiver ativo.
     * @return JSON
     */
    public function registrarNovoMonitoramentoDiarios(Request $request, $nomeArquivoLog = 'escavador/registrarNovoMonitoramentoDiarios'){
        try{
            /**
             * O tipo do valor a ser monitorado. 
             * Valores permitidos: termo, processo. (obrigatório).
             */
            $tipo        = (string) $request->input('tipo');
            /**
             * O termo a ser monitorado nos diários. 
             * Obrigatório se tipo = termo. (opcional).
             */
            $termo       = (string) $request->input('termo'); 
            /**
             * Array de ids dos diarios que deseja monitorar. 
             * Saiba como encontrar esses 
             * ids em Retornar origens. 
             * Obrigatório se tipo = termo. (opcional).
             */
            $origens_ids = json_decode($this->retornarIdsOrigens());
            if($origens_ids->code = 200){
                $origens_ids = (array) $origens_ids->content;
            } else {
                throw new NajException("Erro ao obter os ids das origens.", 500); 
            }
            /**
             * Array de strings com as variações do termo monitorado. 
             * O array deve ter no máximo 3 variações. (opcional).
             */
            $variacoes   = (array) $request->input('variacoes');
            /**
             * Array de array de strings com termos e condições para o alerta do monitoramento.
             * As condições que podem ser utilizadas são as seguintes: 
             * CONTEM: apenas irá alertar se na página conter todos os nomes informados. 
             * NAO_CONTEM: apenas irá alertar se não tiver nenhum dos termos informados. 
             * CONTEM_ALGUMA: apenas irá alertar, se tiver pelo menos 1 dos termos informados. (opcional).
             */
            $termos_auxiliares = (array) $request->input('termos_auxiliares');

            $client = new \GuzzleHttp\Client();
            $response = $client->post("https://api.escavador.com/api/v1/monitoramentos", [
                'headers' => [
                        "Authorization" => "Bearer " . $this->getToken(),
                        "X-Requested-With" => "XMLHttpRequest",
                        "Content-Type" => "application/json",
                    ],
                'json' => [
                        "tipo" => $tipo,               //ex: "termo",
                        "termo" => $termo,             //ex: "casa",
                        "origens_ids" => $origens_ids, //ex: [1, 2, 3],
                        "variacoes" => $variacoes,     //ex: ['mansão', 'casebre'],
                        "termos_auxiliares" => $termos_auxiliares //ex: [['termo' => 'nao', 'condicao' => 'NAO_CONTEM'], ['termo' => 'contem isso', 'condicao' => 'CONTEM'], ['termo' => 'e isso', 'condicao' => 'CONTEM'], ['termo' => 'alguma', 'condicao' => 'CONTEM_ALGUMA']],
                    ],
            ]);
            unset($client);
            return Estrutura::responseNajEscavador($response->getBody()->getContents());
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
     * Retorna todos os monitoramentos de diarios do usuário.
     * 
     * @param string $nomeArquivoLog Nome do arquivo de log 'escavador/retornarMonitoramentosDiarios' por default
     * @ticket GRÁTIS por requisição.
     * @return JSON
     */
    public function retornarMonitoramentosDiarios($nomeArquivoLog = 'escavador/retornarMonitoramentosDiarios'){
        try{
            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://api.escavador.com/api/v1/monitoramentos", [
                'headers' => [
                        "Authorization" => "Bearer " . $this->getToken(),
                        "X-Requested-With" => "XMLHttpRequest",
                    ],
            ]);
            unset($client);
            return Estrutura::responseNajEscavador($response->getBody()->getContents());
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
     * Retorna um monitoramento pelo seu identificador.
     * 
     * @param int    $monitoramentoId Identificador numérico de um monitoramento de diários.
     * @param string $nomeArquivoLog  Nome do arquivo de log 'escavador/retornarMonitoramentoDiarios' por default
     * @ticket GRÁTIS por requisição.
     * @return JSON
     */
    public function retornarMonitoramentoDiarios($monitoramentoId, $nomeArquivoLog = 'escavador/retornarMonitoramentoDiarios'){
        try{
            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://api.escavador.com/api/v1/monitoramentos/" . $monitoramentoId, [
                'headers' => [
                        "Authorization" => "Bearer " . $this->getToken(),
                        "X-Requested-With" => "XMLHttpRequest",
                    ],
            ]);
            unset($client);
            return Estrutura::responseNajEscavador($response->getBody()->getContents());
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
     * Edita um monitoramento de diário oficial. É possível alterar os Diários monitorados, ou as variações do monitoramento.
     * 
     * @param string $nomeArquivoLog Nome do arquivo de log 'escavador/editarMonitoramentoDiarios' por default
     * @ticket GRÁTIS por requisição.
     * @return JSON
     */
    public function editarMonitoramentoDiarios(Request $request, $nomeArquivoLog = 'escavador/editarMonitoramentoDiarios'){
        try{
            /**
             * Array de ids dos diarios que deseja monitorar. 
             * Saiba como encontrar esses ids em Retornar origens. 
             * Obrigatório se tipo = termo. (opcional).
             * 
             */
            $origens_ids = json_decode($this->retornarIdsOrigens());
            if($origens_ids->code = 200){
                $origens_ids = (array) $origens_ids->content;
            } else {
                throw new NajException("Erro ao obter os ids das origens.", 500); 
            }
            /**
             * Array de strings com as variações do termo monitorado. 
             * O array deve ter no máximo 3 variações. (opcional).
             */
            $variacoes   = (array) $request->input('variacoes');
            
            /**
             * Identificador numérico de um monitoramento de diários.
             */
            $monitoramentoId = (int) $request->input('id_monitoramento');
            
            $client = new \GuzzleHttp\Client();
            $response = $client->put("https://api.escavador.com/api/v1/monitoramentos/" . $monitoramentoId, [
                'headers' => [
                        "Authorization" => "Bearer " . $this->getToken(),
                        "X-Requested-With" => "XMLHttpRequest",
                        "Content-Type" => "application/json",
                    ],
                'json' => [
                    "origens_ids" => $origens_ids,
                    "variacoes" => $variacoes
                ]
            ]);
            unset($client);
            return Estrutura::responseNajEscavador($response->getBody()->getContents());
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
     * Remove um monitoramento de diario cadastrado pelo usuário baseado no seu identificador.
     * 
     * @param int    $monitoramentoId Identificador numérico de um monitoramento de diários.
     * @param string $nomeArquivoLog  Nome do arquivo de log 'escavador/removerMonitoramentoDiarios' por default
     * @ticket GRÁTIS por requisição.
     * @return JSON
     */
    public function removerMonitoramentoDiarios($monitoramentoId, $nomeArquivoLog = 'escavador/removerMonitoramentoDiarios'){
        try{
            $client = new \GuzzleHttp\Client();
            $response = $client->delete("https://api.escavador.com/api/v1/monitoramentos/" . $monitoramentoId, [
                'headers' => [
                        "Authorization" => "Bearer " . $this->getToken(),
                        "X-Requested-With" => "XMLHttpRequest",
                    ],
            ]);
            unset($client);
            return Estrutura::responseNajEscavador($response->getBody()->getContents());
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
    
    //Monitoramento no site do Tribunal
    
    /**
     * Cadastra o numero de um processo para ser monitorado nos tribunais. 
     * Ao encontrar algo novo, um callback é enviado via para a sua url definida. 
     * Você pode consultar todos os callbacks enviados e os status.
     * 
     * O monitoramento do tipo UNICO tem o custo fixo de 5 créditos/dia pesquisado, e os monitoramentos dos tipo NUMDOC e NOME dependem do tribunal. 
     * O custo pode ser consultado na rota que lista os sistemas dos tribunais verificando os atributos quantidade_creditos_busca_documento e quantidade_creditos_busca_nome respectivamente. 
     * Os créditos serão cobrados todas as vezes que o robô acessar com sucesso o site do tribunal, definido pela frequência cadastrada.
     * 
     * @param Request $request        Parâmetros da requisição
     * @param string  $nomeArquivoLog Nome do arquivo de log 'escavador/registrarNovoMonitoramentoTribunais' por default
     * @ticket CUSTO DINÂMICO/DIA*
     * @return JSON
     */
    public function registrarNovoMonitoramentoTribunais(Request $request, $nomeArquivoLog = 'escavador/registrarNovoMonitoramentoTribunais'){
        try{
            /**
             * O tipo do valor a ser monitorado.
             * Valores permitidos:
             * UNICO:Númeração CNJ do processo. O monitoramento vai procurar por andamentos novos.
             * NUMDOC: CPF ou CNPJ. O monitoramento vai procurar processos novos relacionados a esse documento.
             * NOME: Nome do envolvido no processo. O monitoramento vai procurar processos novos relacionados a esse nome.
             * (obrigatório).
             */
            $tipo = (string) $request->input('tipo') ? $request->input('tipo') : "UNICO";
            /**
             * O número de processo, nome ou documento a ser monitorado. (obrigatório).
             */
            $valor = (string) $request->input('valor') ? $request->input('valor') : null;
            /**
             * Tribunal a ser pesquisado, sendo ignorado e opcional para tipo=UNICO. 
             * Consulte os Tribunais disponíveis. (obrigatório).
             */
            $tribunal = (string) $request->input('tribunal') ? $request->input('tribunal') : "";
            /**
             * Quantidade de dias que o robô vai buscar por atualizações.
             * Valores permitidos:
             * DIARIA: De segunda a sexta.
             * SEMANAL: 1 vez na semana (O dia é escolhido pelo Escavador).
             * Default: DIARIA.
             * (opcional).
             */
            $frequencia = (string) $request->input('frequencia') ? $request->input('frequencia') : "DIARIA";

            $client = new \GuzzleHttp\Client();
            $response = $client->post("https://api.escavador.com/api/v1/monitoramento-tribunal", [
                'headers' => [
                        "Authorization" => "Bearer " . $this->getToken(),
                        "X-Requested-With" => "XMLHttpRequest",
                        "Content-Type" => "application/json",
                    ],
                    'json' => [
                        "tipo" => $tipo,             //ex: "UNICO"
                        "valor" => $valor,           //ex: "0011119-72.2019.8.26.0050"
                        "tribunal" => $tribunal,     //ex: "TJSP"
                        "frequencia" => $frequencia, //ex: "DIARIA"
                    ],
            ]);
            unset($client);
            return Estrutura::responseNajEscavador($response->getBody()->getContents());
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
     * Retorna todos os monitoramentos criados pelo usuário ou os com o identificadores expecificados na requisição.
     * 
     * @param int    $monitoramentoIds Um array de identificadores de monitoramento separados por "vírgula", (opcional).
     * @param string $nomeArquivoLog   Nome do arquivo de log 'escavador/retornarMonitoramentosTribunais' por default
     * @ticket GRÁTIS por requisição.
     * @return JSON
     */
    public function retornarMonitoramentosTribunais($monitoramentoIds, $nomeArquivoLog = 'escavador/retornarMonitoramentosTribunais'){
        try{
            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://api.escavador.com/api/v1/monitoramento-tribunal", [
                'headers' => [
                        "Authorization" => "Bearer " . $this->getToken(),
                        "X-Requested-With" => "XMLHttpRequest",
                    ],
                    'query' => [
                        "ids" => $monitoramentoIds,
                    ],
            ]);
            unset($client);
            return Estrutura::responseNajEscavador($response->getBody()->getContents());
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
     * Retorna um monitoramento pelo identificador.
     * 
     * @param int    $monitoramentoId Identificador numérico do monitoramento.
     * @param string $nomeArquivoLog  Nome do arquivo de log 'escavador/retornarMonitoramentoTribunais' por default
     * @ticket GRÁTIS por requisição.
     * @return JSON
     */
    public function retornarMonitoramentoTribunais($monitoramentoId, $nomeArquivoLog = 'escavador/retornarMonitoramentoTribunais'){
        try{
            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://api.escavador.com/api/v1/monitoramento-tribunal/" . $monitoramentoId, [
                'headers' => [
                        "Authorization" => "Bearer " . $this->getToken(),
                        "X-Requested-With" => "XMLHttpRequest",
                    ],
            ]);
            unset($client);
            return Estrutura::responseNajEscavador($response->getBody()->getContents());
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
     * Edita um monitoramento de sistema do tribunal. É possível alterar apenas a frequência do monitoramento.
     * 
     * @param string $nomeArquivoLog Nome do arquivo de log 'escavador/editarMonitoramentoTribunais' por default
     * @ticket GRÁTIS por requisição.
     * @return JSON
     */
    public function editarMonitoramentoTribunais(Request $request, $nomeArquivoLog = 'escavador/editarMonitoramentoTribunais'){
        try{
            
            /**
             * Quantidade de dias que o robô vai buscar por atualizações.
             * Valores permitidos:
             * DIARIA: De segunda a sexta.
             * SEMANAL: 1 vez na semana (O dia é escolhido pelo Escavador).
             * Default: DIARIA.
             */
            $frequencias = ['DIARIA','SEMANAL','DIARIA'];
            $frequencia  = (int) $request->input('frequencia') ? $request->input('frequencia') : $frequencias[0];
            
            $client = new \GuzzleHttp\Client();
            $response = $client->put("https://api.escavador.com/api/v1/monitoramento-tribunal/1", [
                'headers' => [
                        "Authorization" => "Bearer " . $this->getToken(),
                        "X-Requested-With" => "XMLHttpRequest",
                        "Content-Type" => "application/json",
                    ],
                'json' => [
                    "frequencia" => $frequencia,
                ],
            ]);
            unset($client);
            return Estrutura::responseNajEscavador($response->getBody()->getContents());
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
     * Remove um monitoramento pelo identificador cadastrado pelo usuário.
     * 
     * @param int    $monitoramentoId Identificador numérico do monitoramento.
     * @param string $nomeArquivoLog  Nome do arquivo de log 'escavador/removerMonitoramentoTribunais' por default
     * @ticket GRÁTIS por requisição.
     * @return JSON
     */
    public function removerMonitoramentoTribunais($monitoramentoId, $nomeArquivoLog = 'escavador/removerMonitoramentoTribunais'){
        try{
            $client = new \GuzzleHttp\Client();
            $response = $client->delete("https://api.escavador.com/api/v1/monitoramento-tribunal/" . $monitoramentoId, [
                'headers' => [
                        "Authorization" => "Bearer " . $this->getToken(),
                        "X-Requested-With" => "XMLHttpRequest",
                    ],
            ]);
            unset($client);
            return Estrutura::responseNajEscavador($response->getBody()->getContents());
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
    
    //Processos
     
    /**
     * A busca é feita diretamente nos sites dos tribunais, pelos robôs do Escavador. 
     * O tempo de busca é afetado pelo tempo de resposta dos sites dos tribunais, pela presença de captchas e outros fatores. 
     * Como há possibilidade do tempo de resposta ser longo, essa rota funciona de maneira assíncrona. 
     * Após solicitar as informações do processo, caso tenha informado, você irá receber um callback (POST) como resultado e também um link para consultar o resultado através do campo link_api.
     *
     * @param string $numero
     * @param int    $origem
     * @param string $nomeArquivoLog
     * @return JSON
     */
    public function pesquisarProcessoNoSiteDoTribunalAssincrono($numero, $origem = 0, $nomeArquivoLog = 'escavador/pesquisarProcessoNoSiteDoTribunalAssincrono'){
        try{
            switch ($origem){
                case 0:
                    $origem = "";
                    break;
                case 1:
                    $origem = "STJ";
                    break;
                case 2:
                    $origem = "STF";
                    break;
                case 3:
                    $origem = "TST";
                    break;
                case 4:
                    $origem = "TSE";
                    break;
            }
            
            /*
             * Se send_callback == 1, a resposta será enviada para a url de callback do usuário, 
             * uma alternativa caso não queira ficar consultando o resultado. Default: 0. (opcional).
             */
            $send_callback = 1;
            /*
             * Se wait == 1, a requisição irá durar até 2 minutos e caso consiga as informações do processo nesse tempo, 
             * a resposta vem de forma síncrona. Caso passe 2 minutos, se não tiver resposta do processo, 
             * o fluxo ocorrerá da forma assíncrona. Default: 0. (opcional).
             */
            $wait = 0;
            /*
             * Sigla da origem do processo (Ex: STJ, STF, ...). 
             * Esse parâmetro serve para forçar a consulta em uma origem diferente do processo.
             * Atenção: Ao utilizar esse parâmetro a consulta será cobrada mesmo que o processo não seja encontrado. (opcional).
             */
            $origem = $origem;
            
            $client = new \GuzzleHttp\Client();
            $response = $client->post("https://api.escavador.com/api/v1/processo-tribunal/" . $numero . "/async", [
                'headers' => [
                        "Authorization" => "Bearer " . $this->getToken(),
                        "X-Requested-With" => "XMLHttpRequest",
                        "Content-Type" => "application/json",
                    ],
                'json' => [
                        "send_callback" => $send_callback,
                        "wait" => $wait,
                        "origem" => $origem,
                        "origem_dados" => "WEB",
                        "tentativas" => 8
                    ],
            ]);
            unset($client);
            return Estrutura::responseNajEscavador($response->getBody()->getContents());
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
    
    //Tribunais
    
    /**
     * Retorna os sistemas de tribunais disponíveis no Escavador.
     * 
     * @param string $nomeArquivoLog Nome do arquivo de log 'escavador/retornarSistemasDosTribunaisDisponiveis' por default
     * @ticket GRÁTIS por requisição
     * @return JSON
     */
    public function retornarSistemasDosTribunaisDisponiveis($nomeArquivoLog = 'escavador/retornarSistemasDosTribunaisDisponiveis'){
        try{
            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://api.escavador.com/api/v1/tribunal/origens", [
                'headers' => $this->getHeaders(),
            ]);
            unset($client);
            return Estrutura::responseNajEscavador($response->getBody()->getContents());
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
    
    //Métodos auxiliares
    
    /**
     * Obtêm o user name
     * @return string
     */
    function getUserName() {
        return json_decode($this->sysConfigController->searchSysConfig('ESCAVADOR', 'USERNAME'));
    }
    
    /**
     * Obtêm o password
     * @return string
     */
    function getPassword() {
        return json_decode($this->sysConfigController->searchSysConfig('ESCAVADOR', 'PASSWORD'));
    }
    
    /**
     * Obtêm o token
     * @return string
     */
    function getToken() {
        $token = json_decode($this->sysConfigController->searchSysConfig('ESCAVADOR', 'TOKEN'));
        $checkToken = (array) $token;
        if(empty($checkToken)){
            throw new NajException("O token da Escavador não foi definido no banco de dados, contate o suporte!");
        }
        return $token;
    }
    
    /**
     * Retorna o headers da requisição
     * @return array
     */
    function getHeaders(){
        return ["Authorization" => "Bearer " . $this->getToken(), "X-Requested-With" => "XMLHttpRequest", "Content-Type" => "application/json"];
    }

    /**
     * Verifica se o token do Escavador foi informado no BD
     * 
     * @return JSON
     */
    public function verificaTokenEscavador(){
        $return = $this->getModel()->verificaTokenEscavador();
        if(is_string($return)){
            return Estrutura::responseNaj($return, 400);
        }
        return Estrutura::responseNaj("Token definido no BD.");
    }
    
    /**
     * Teste
     */
    function teste(){
        try{
            
            $registros = DB::select('SELECT * FROM monitora_termo_processo;');
            foreach ($registros as $registro){
                DB::update('UPDATE monitora_termo_processo SET numero_novo where id = ' . $registro->id);
            }
            
            $nomeArquivoLog = 'teste';
            //throw new NajException("Erro ao obter os ids das origens.", 200); 
            return response()->json("Oi")->content(); 
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
