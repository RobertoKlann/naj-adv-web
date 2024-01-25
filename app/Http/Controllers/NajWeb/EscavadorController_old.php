<?php

//ESCAVADOR rotas
//Route::get('/escavador/solicitartokendeacesso'                                  , 'EscavadorController@SolicitarTokenDeAcesso')->name('escavador.solicitartokendeacesso');
//Route::post('/escavador/buscarportermo'                                         , 'EscavadorController@BuscarPorTermo')->name('escavador.buscarportermo');
//Route::get('/escavador/todososresultadosdasbuscasassincronas'                 , 'EscavadorController@TodosOsResultadosDasBuscasAssincronas')->name('escavador.todososresultadosdasbuscasassincronas');
//Route::get('/escavador/resultadoespecificodeumabuscaassincrona/{id}'          , 'EscavadorController@ResultadoEspecíficoDeUmaBuscaAssincrona')->name('escavador.resultadoespecificodeumabuscaassincrona');
//Route::post('/escavador/retornaroscallbacks'                                    , 'EscavadorController@RetornarOsCallbacks')->name('escavador.retornaroscallbacks');
//Route::get('/escavador/consultarcreditos/'                                      , 'EscavadorController@ConsultarCreditos')->name('escavador.consultarcreditos');
//Route::get('/escavador/retornarorigens'                                       , 'EscavadorController@RetornarOrigens')->name('escavador.retornarorigens');
//Route::get('/escavador/retornarpaginadodiariooficial/{diario}'                , 'EscavadorController@RetornarPaginaDoDiarioOficial')->name('escavador.retornarpaginadodiariooficial');
//Route::get('/escavador/downloaddopdfdapaginadodiariooficial/{id}/{diario}'    , 'EscavadorController@DownloadDoPDFDaPaginaDoDiarioOficial')->name('escavador.downloaddopdfdapaginadodiariooficial');
//Route::get('/escavador/obterinstituicoes'                                     , 'EscavadorController@obterInstituicoes')->name('escavador.obterinstituicoes');
//Route::get('/escavador/processosdemainstituicao'                              , 'EscavadorController@processosDeUmaInstituicao')->name('escavador.processosdemainstituicao');
//Route::get('/escavador/retornarosdiariosoficiaismonitorados/{$monitoramentoId}' , 'EscavadorController@retornarOsDiariosOficiaisMonitorados')->name('escavador.retornarosdiariosoficiaismonitorados');
//Route::get('/escavador/registrarnovomonitoramento'                              , 'EscavadorController@registrarNovoMonitoramento')->name('escavador.registrarnovomonitoramento');
//Route::get('/escavador/retornarsistemasDostribunaisdisponiveis'                 , 'EscavadorController@retornarSistemasDosTribunaisDisponiveis')->name('escavador.retornarsistemasDostribunaisdisponiveis');
//Route::get('/escavador/teste'     

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Http\Controllers\NajWeb\SysConfigController;
use App\Http\Controllers\Api\NajApiController;
use Exception;
use Error;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

/**
 * Controller da ingegração com a API da ESCAVADOR.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      16/03/2020
 */
class EscavadorController_old extends NajController {
    
    /** @var SysConfigController */
    protected $sysConfigController;
    
    /** @var NajApiController */
    protected $najApiController;
    
    /**
     * Seta o controller do SysConfig ao carregar o controller
     */
    public function onLoad() {
        $this->sysConfigController = new SysConfigController();
        $this->najApiController    = new NajApiController();
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
     */
    public function solicitarTokenDeAcesso(){
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
        $body = $response->getBody();
        print_r(json_decode((string) $body));
    }
    
    //Busca
    
    /**
     * Pesquisa um termo no escavador. 
     * @ticket Consome 1 CRÉDITO por requisição.
     * @param string $q O termo a ser pesquisado. Você pode pesquisar entre aspas dupla para match perfeito (obrigatório).
     * @param string $qo Tipo da entidade a ser pesquisada (obrigatório). 
     * @param integer $page Número da página, respeitando o limite informado (opcional).
     * @ticket Para $qo os valores podem ser:
     * @example t:  Para pesquisar todos os tipos de entidades.
     * @example p:  Para pesquisar apenas as pessoas.
     * @example i:  Para pesquisar apenas as instituições.
     * @example pa: Para pesquisar apenas as patentes.
     * @example d:  Para pesquisar apenas os Diários Oficiais.
     * @example en: Para pesquisar as pessoas e instituições que são partes em processos.
     * @example a:  Para pesquisar apenas os artigos. (obrigatório).
     */
    public function buscarPorTermo($q, $qo, $page){
        $client = new \GuzzleHttp\Client();
        $response = $client->get("https://www.escavador.com/api/v1/busca", [
            'headers' =>  [
                "Authorization" => "Bearer " . $this->getToken(),
                "X-Requested-With" => "XMLHttpRequest",
            ],
            'query' => [
                    "q"    => $q    /* Ex: "João Silva"*/,
                    "qo"   => $qo   /* Ex: "t"*/,
                    "page" => $page /* Ex: "1"*/,
                ],
        ]);
        $body = $response->getBody();
        print_r(json_decode((string) $body));
    }

    //Busca Assíncrona
    
    /**
     * Consultar todos os resultados das buscas assíncronas (Busca de processos, 
     * documentos e nomes em sistemas de tribunais).
     * @ticket GRÁTIS por requisição.
     * @ticket Tipos de busca assíncrona:
     * @example BUSCA_POR_NOME:	     Busca processos pelo nome em tribunais.
     * @example BUSCA_POR_DOCUMENTO: Busca processos pelo documento (CPF ou CNPJ) em tribunais.
     * @example BUSCA_PROCESSO:	     Busca processo pela numeração CNJ.
     */
    public function todosOsResultadosDasBuscasAssincronas(){
        $client = new \GuzzleHttp\Client();
        $response = $client->get("https://www.escavador.com/api/v1/async/resultados", [
            'headers' => [
                "Authorization" => "Bearer " . $this->getToken(),
                "X-Requested-With" => "XMLHttpRequest",
            ],
        ]);
        $body = $response->getBody();
        print_r(json_decode((string) $body));
    }
    
    public function todosOsResultadosDasBuscasAssincronas_old(){
        $this->najApiController->setToken($this->getToken());
        $this->najApiController->setUrl("https://www.escavador.com/api/v1/async/resultados");
        $response = $this->najApiController->get();
        $body = $response->getBody();
        print_r(json_decode((string) $body));
    }
    
    /**
     * Consultar um resultado específico de uma busca assíncrona (Busca de processos,
     * documentos e nomes em sistemas de tribunais).
     * @ticket GRÁTIS por requisição.
     * @param integer $id Identificador numérico do resultado da busca.
     */
    public function resultadoEspecíficoDeUmaBuscaAssincrona($id){
        $client = new \GuzzleHttp\Client();
        $response = $client->get("https://www.escavador.com/api/v1/async/resultados/" . $id, [
            'headers' => [
                "Authorization" => "Bearer " . $this->getToken(),
                "X-Requested-With" => "XMLHttpRequest",
            ],
        ]);
        $body = $response->getBody();
        print_r(json_decode((string) $body));
    }
    
    //Callback
    
    /**
     * Consultar todos os callbacks recebidos pela API.
     * @ticket GRÁTIS por requisição.
     * @param date $data_maxima Data e hora (em UTC) máxima dos callbacks listados (opcional).
     */
    public function retornarOsCallbacks($data_maxima){
        $data_maxima = isset($data_maxima) ? $data_maxima : date("Y-m-d H:i:s");
        $client = new Client();
        $response = $client->get("https://www.escavador.com/api/v1/callbacks", [
            'headers' => [
                "Authorization" => "Bearer " . $this->getToken(),
                "X-Requested-With" => "XMLHttpRequest",
            ],
            'query' => [
                    "data_maxima" => $data_maxima,
                ],
        ]);
        $body = $response->getBody();
        print_r(json_decode((string) $body));
    }
    
    //Créditos
     
    /**
     * Retorna o saldo atual dos créditos.  
     * @ticket GRÁTIS por requisição.
     */
    public function consultarCreditos(){
        $client = new \GuzzleHttp\Client();
        $response = $client->get("https://www.escavador.com/api/v1/quantidade-creditos", [
            'headers' => [
                "Authorization" => "Bearer " . $this->getToken(),
                "X-Requested-With" => "XMLHttpRequest",
            ],
        ]);
        $body = $response->getBody();
        print_r(json_decode((string) $body));
    }
    
    //Diários Oficiais
    
    /**
     * Retorna as origens de todos os diários disponiveis no Escavador.
     * @ticket GRÁTIS por requisição.
     */
    public function retornarOrigens(){
        $client = new \GuzzleHttp\Client();
        $response = $client->get("https://www.escavador.com/api/v1/origens", [
            'headers' => [
                "Authorization" => "Bearer " . $this->getToken(),
                "X-Requested-With" => "XMLHttpRequest",
            ],
        ]);
        $body = $response->getBody();
        print_r(json_decode((string) $body));
    }
    
    /**
     * Retorna uma página específica do Diário Oficial pelo seu identificador no Escavador.
     * @ticket Consome 1 CRÉDITO por requisição.
     * @param integer $diario Identificador numérico de um Diario Oficial.
     * @param integer $page   Número da página do Diário Oficial, respeitando o limite informado. Valor padrão: 1 (opcional).
     */
    public function retornarPaginaDoDiarioOficial($diario, $page = 1){
        $client = new \GuzzleHttp\Client();
        $response = $client->get("https://www.escavador.com/api/v1/diarios/" . $diario, [
            'headers' => [
                "Authorization" => "Bearer " . $this->getToken(),
                "X-Requested-With" => "XMLHttpRequest",
            ],
            'query' => [
                    //page opcional, número da página do Diário Oficial, respeitando o limite informado. Valor padrão: 1.
                    "page" => "1",
                ],
        ]);
        $body = $response->getBody();
        print_r(json_decode((string) $body));
    }
    
    /**
     * Retorna em formato PDF, uma página do Diário Oficial pelo seu identificador no Escavador.
     * @ticket Consome 1 CRÉDITO por requisição
     * @param integer $id Identificador numérico de um Diario Oficial.
     * @param integer $pagina Número da página do Diário Oficial, respeitando o limite informado. Valor padrão: 1.
     */
    public function downloadDoPDFDaPaginaDoDiarioOficial($id, $pagina){
        $client = new \GuzzleHttp\Client();
        $response = $client->get("https://www.escavador.com/api/v1/diarios/{$id}/pdf/pagina/{$pagina}/baixar", [
            'headers' => [
                "Authorization" => "Bearer " . $this->getToken(),
                "X-Requested-With" => "XMLHttpRequest",
            ],
        ]);
        $body = $response->getBody();
        print_r(json_decode((string) $body));
    }
    
    //Instituições
    
    /**
     * Retorna dados relacionados a uma instituição pelo seu identificador.
     * @ticket Consome 2 CRÉDITOS por requisição
     * @param integer $instituicaoId Identificador numérico de uma Instituição.
     */
    public function obterInstituicoes($instituicaoId){
        $client = new \GuzzleHttp\Client();
        $response = $client->get("https://api.escavador.com/api/v1/instituicoes/" . $instituicaoId, [
            'headers' => [
                "Authorization" => "Bearer " . $this->getToken(),
                "X-Requested-With" => "XMLHttpRequest",
            ],
        ]);
        $body = $response->getBody();
        print_r(json_decode((string) $body));
    }

    /**
     * Retorna os processos de uma instituição que saíram em Diários Oficiais e estão no Escavador.
     * @ticket Consome 1 CRÉDITO por requisição
     * @param integer $instituicaoId Identificador numérico de uma Instituição.
     * @param integer $limit         Limita o número dos registros listados. Caso não seja enviado, aplica-se o limite padrão de 20 registros. Limite máximo: 60 (opcional).
     * @param integer $page          Número da página, respeitando o limite informado (opcional).
     */
    public function processosDeUmaInstituicao($instituicaoId, $limit = 20, $page = 1){
        $client = new \GuzzleHttp\Client();
        $response = $client->get("https://api.escavador.com/api/v1/instituicoes/" . $instituicaoId . "/processos", [
            'headers' => [
                "Authorization" => "Bearer " . $this->getToken(),
                "X-Requested-With" => "XMLHttpRequest",
            ],
            'query' => [
                    "limit" => $limit,
                    "page" => $page,
                ],
        ]);
        $body = $response->getBody();
        print_r(json_decode((string) $body));
    }
    
    //Monitoramento de Diários Oficiais
    
    /**
     * Retorna os diários oficiais de um monitoramento pelo identificador do monitoramento.
     * @ticket GRÁTIS por requisição.
     * @param integer $monitoramentoId Identificador numérico de um monitoramento de diários.
     */
    public function retornarOsDiariosOficiaisMonitorados($monitoramentoId){
        $client = new \GuzzleHttp\Client();
        $response = $client->get("https://api.escavador.com/api/v1/monitoramentos/" . $monitoramentoId . "/origens", [
            'headers' => [
                "Authorization" => "Bearer " . $this->getToken(),
                "X-Requested-With" => "XMLHttpRequest",
            ],
        ]);
        $body = $response->getBody();
        print_r(json_decode((string) $body));
    }
    
    /**
     * Cadastra um termo ou processo para ser monitorado nos diários.
     * @ticket 2 CRÉDITOS/DIA * O valor dos créditos se referem a cada dia em que o monitoramento estiver ativo.
     * @param Request $request
     * @param string  $tipo              O tipo do valor a ser monitorado. Valores permitidos: termo, processo. (obrigatório). 
     * @param string  $termo             O termo a ser monitorado nos diários. Obrigatório se tipo = termo. (opcional). 
     * @param array   $origens_ids       Array de ids dos diarios que deseja monitorar. Saiba como encontrar esses ids em Retornar origens. Obrigatório se tipo = termo. (opcional).
     * @param int     $processo_id       O id do processo a ser monitorado nos diários. Saiba como encontrar esse id em Buscar processos dos Diários Oficiais por número. Obrigatório se tipo = processo. (opcional).
     * @param array   $variacoes         Array de strings com as variações do termo monitorado. O array deve ter no máximo 3 variações. (opcional).
     * @param array   $termos_auxiliares Array de array de strings com termos e condições para o alerta do monitoramento. As condições que podem ser utilizadas são as seguintes: CONTEM: apenas irá alertar se na página conter todos os nomes informados. NAO_CONTEM: apenas irá alertar se não tiver nenhum dos termos informados. CONTEM_ALGUMA: apenas irá alertar, se tiver pelo menos 1 dos termos informados. (opcional).
     */
    public function registrarNovoMonitoramento(Request $request, $tipo, $termo, $origens_ids, $processo_id, $variacoes, $termos_auxiliares){
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
                    "processo_id" => $processo_id, //ex: 3,
                    "variacoes" => $variacoes,     //ex: ['mansão', 'casebre'],
                    "termos_auxiliares" => $termos_auxiliares //ex: [['termo' => 'nao', 'condicao' => 'NAO_CONTEM'], ['termo' => 'contem isso', 'condicao' => 'CONTEM'], ['termo' => 'e isso', 'condicao' => 'CONTEM'], ['termo' => 'alguma', 'condicao' => 'CONTEM_ALGUMA']],
                ],
        ]);
        $body = $response->getBody();
        print_r(json_decode((string) $body));
    }

    /**
     * Retorna os sistemas de tribunais disponíveis no Escavador.
     * @ticket GRÁTIS por requisição
     */
    public function retornarSistemasDosTribunaisDisponiveis(){
        $client = new \GuzzleHttp\Client();
        $response = $client->get("https://api.escavador.com/api/v1/tribunal/origens", [
            'headers' => $this->getHeaders(),
        ]);
        echo response()->json($response->getBody())->content();
    }
    
    ///////////////////////Parou aqui (verificar a documentação dos métodos seguintes)!
    
    /**
     * Obtêm o user name
     * @return string
     */
    function getUserName() {
        return $this->sysConfigController->searchSysConfig('ESCAVADOR', 'USERNAME')->VALOR;
    }
    
    /**
     * Obtêm o password
     * @return string
     */
    function getPassword() {
        return $this->sysConfigController->searchSysConfig('ESCAVADOR', 'PASSWORD')->VALOR;
    }
    
    /**
     * Obtêm o token
     * @return string
     */
    function getToken() {
        return $this->sysConfigController->searchSysConfig('ESCAVADOR', 'TOKEN')->VALOR;
    }
    
    /**
     * Retorna o headers da requisição
     * @return array
     */
    function getHeaders(){
        return ["Authorization" => "Bearer " . $this->getToken(), "X-Requested-With" => "XMLHttpRequest", "Content-Type" => "application/json"];
    }

    function teste(){
        echo $this->getUserName();
        echo "<br>";
        echo $this->getPassword();
        //echo response()->json($result)->content();
    }
    
}
