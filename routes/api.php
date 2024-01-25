<?php

use Illuminate\Http\Request;

/*
  |--------------------------------------------------------------------------
  | API Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register API routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | is assigned the "api" middleware group. Enjoy building your API
  |
 */


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//Route::group([
//    'prefix' => 'najadvweb'
//        ], function($router) {
//    Route::group([
//        'prefix' => 'v1'
//            ], function($router) {
/*
          | escavador
          |
          | [GET]
          |    - solicitartokendeacesso: solicitar token de acesso
          |    - buscarportermo: buscar por termo
          |    - todososresultadosdasbuscasassincronas: todos os resultados das buscasas sincronas
          |    - resultadoespecificodeumabuscaassincrona: resultado especifico de uma busca assincrona
          |    - retornaroscallbacks: retornar os callbacks
          |    - consultarcreditos: consultar creditos
          |    - retornarorigens: retornar origens
          |    - retornarpaginadodiariooficial: retornar pagina do diario oficial
          |    - downloaddopdfdapaginadodiariooficial: download do pdf da pagina do diario oficial
         */
//        Route::group(options('escavador'), function ($router) {
Route::get('/solicitartokendeacesso', 'EscavadorController@SolicitarTokenDeAcesso')->name('escavador.solicitartokendeacesso');
Route::get('/buscarportermo', 'EscavadorController@BuscarPorTermo')->name('escavador.buscarportermo');
Route::get('/todososresultadosdasbuscasassincronas', 'EscavadorController@TodosOsResultadosDasBuscasAssincronas')->name('escavador.todososresultadosdasbuscasassincronas');
Route::get('/resultadoespecificodeumabuscaassincrona/{id}', 'EscavadorController@ResultadoEspecíficoDeUmaBuscaAssincrona')->name('escavador.resultadoespecificodeumabuscaassincrona');
Route::get('/retornaroscallbacks', 'EscavadorController@RetornarOsCallbacks')->name('escavador.retornaroscallbacks');
Route::get('/consultarcreditos', 'EscavadorController@ConsultarCreditos')->name('escavador.consultarcreditos');
Route::get('/retornarorigens', 'EscavadorController@RetornarOrigens')->name('escavador.retornarorigens');
Route::get('/retornarpaginadodiariooficial/{diario}', 'EscavadorController@RetornarPaginaDoDiarioOficial')->name('escavador.retornarpaginadodiariooficial');
Route::get('/downloaddopdfdapaginadodiariooficial/{id}/{diario}', 'EscavadorController@DownloadDoPDFDaPaginaDoDiarioOficial')->name('escavador.downloaddopdfdapaginadodiariooficial');
//        });
//
//    });
//});

/*
 | Auth
 |
 */
Route::group([
    'namespace' => 'Auth',
    'prefix'    => 'auth'
], function ($router) {
    Route::get('login', 'LoginController@index')->name('auth.index');
    Route::get('validaLogin', 'LoginController@validaLogin')->name('auth.login');
    Route::get('token', 'LoginController@token')->name('auth.token');
    Route::post('login', 'LoginController@login')->name('auth.login');
    Route::get('logout', 'LoginController@logout')->name('auth.logout');
});

/*
 | Sincronização
 |
 */
Route::group([
    'namespace' => 'NajWeb',
    'prefix'    => 'sincronizacao'
], function ($router) {

    //SINCRONIZAÇÃO USUÁRIOS
    Route::post('usuarios', 'SincronizacaoController@sincronizacaoUsuarios')->name('sincronizacao.usuarios');
});

/*
 | Pusher Notifications
 |
 */
Route::group([
    'namespace' => 'NajWeb',
    'prefix'    => 'notificacao'
], function ($router) {

    //Financeiro
    Route::post('financeiro/pagar', 'FinanceiroPagarNotificacaoController@callSendManyNotifications')->name('notificacao.financeiro.pagar-call-send-many');
    Route::post('financeiro/receber', 'FinanceiroReceberNotificacaoController@callSendManyNotifications')->name('notificacao.financeiro.receber-call-send-many');

    //Pessoa
    Route::post('pessoa/aniversariantes', 'PessoaAniversarianteNotificacaoController@callSendManyNotifications')->name('notificacao.pessoa.aniversariante-call-send-many');

    //Atualizações Andamentos/Atividades/Ociosidade
    Route::post('novos/andamentoAtividade', 'AtividadeAndamentoNotificacaoController@callSendManyNotifications')->name('notificacao.novos.atividade-andamento-call-send-many');

    //Eventos da agenda
    Route::post('agenda/eventos', 'AgendaEventoNotificacaoController@callSendManyNotifications')->name('notificacao.agenda.evento-call-send-many');
});

/*
 | Automação monitoramentos do diário e do tribunal
 |
 */
Route::group([
    'namespace' => 'NajWeb',
    'prefix'    => 'monitoramento'
], function ($router) {

    Route::post('diarios/obtermovimentacoesdiario', 'MonitoramentoDiarioController@persistePublicacoes')->name('monitoramento.diarios.obtermovimentacoesdiario');
    Route::post('tribunais/obtermovimentacoestribunal', 'MonitoramentoTribunalController@buscarMovimentacoesProcessosNosTribunais')->name('monitoramento.tribunais.obtermovimentacoestribunal');
    Route::post('tribunais/obterpendentestribunal', 'MonitoramentoTribunalController@buscarMovimentacoesPendentesProcessosNosTribunais')->name('monitoramento.tribunais.obterpendentestribunal');
    
});

// app
Route::prefix('v1')->group(function ($router) {
    /*
    | auth
    |
    | [POST]
    |    - login: geração do token
    |    - me: informações do payload
    |    - logout: adição do token na blacklist
    |    - refresh: geração de um novo token
    */
    // Route::prefix('auth')->group(function($router) {
    //     Route::post('/login'  , 'Auth\LoginController@login')->name('auth.login');
    //     Route::post('/signup' , 'AuthController@signUp')->name('auth.signup');
    //     Route::post('/logout' , 'AuthController@logout')->name('auth.logout');
    //     Route::post('/me'     , 'AuthController@me')->name('auth.me');
    //     Route::post('/refresh', 'AuthController@refresh')->name('auth.refresh');
    // });
    Route::get('app/dashboard', 'Api\AppDashboardController@dashboard')->name('api.dashboard');
    Route::post('app/home', 'Api\AppDashboardController@home')->name('api.dashboard-home');
    Route::get('app/monitora', 'Api\AppDashboardController@monitoracao')->name('api.dashboard-monitoracao');
    Route::get('app/monitoraHome', 'Api\AppDashboardController@monitoracaoHome')->name('api.dashboard-monitoracao-home');
    Route::get('app/pesquisas', 'Api\AppPesquisasController@pesquisas')->name('api.pesquisas');
    Route::get('app/pesquisas/refresh/{id}', 'Api\AppPesquisasController@refreshVisualizacao')->name('api.pesquisas-refresh');
    Route::post('app/pesquisas/recusado/{id}', 'Api\AppPesquisasController@recusado')->name('api.pesquisas-recusado');
    Route::post('app/pesquisas/aceito/{id}', 'Api\AppPesquisasController@aceito')->name('api.pesquisas-aceito');


    Route::prefix('app/processos')->group(function ($router) {
        //Route::get('paginate', 'Api\AppProcessoController@paginate')->name('api.processos-paginate');
        Route::get('monitora', 'Api\AppProcessoController@monitoracao')->name('api.processos-monitoracao');
        Route::get('todos/paginate', 'Api\AppProcessoController@paginate')->name('api.processos-todos-paginate');
        Route::get('ativos/paginate', 'Api\AppProcessoController@paginate')->name('api.processos-ativos-paginate');
        Route::get('encerrados/paginate', 'Api\AppProcessoController@paginate')->name('api.processos-encerrados-paginate');
        Route::get('{key}', 'Api\AppProcessoController@show')->name('api.processos-show');
        Route::get('{key}/partes', 'Api\AppProcessoController@getPartes')->name('api.processos-partes');
        Route::get('{key}/movimentacao', 'Api\AppProcessoController@getMovimentacao')->name('api.processos-movimentacao');
        Route::get('{key}/atividades', 'Api\AppProcessoController@getAtividades')->name('api.processos-atividades');
        Route::get('{key}/attachment', 'Api\AppProcessoController@getAttachments')->name('api.processos-attachment');
        Route::post('attachment/download', 'Api\AppProcessoController@getFileDownload')->name('api.processos-attachment-download');
    });

    Route::prefix('app/agenda')->group(function ($router) {
        Route::get('todos', 'Api\AppAgendaController@getAll')->name('api.agenda-all');
        Route::get('eventos', 'Api\AppAgendaController@getAllEvents')->name('api.evento-all');
        Route::get('monitora', 'Api\AppAgendaController@monitoracao')->name('api.agenda-monitoracao');
        Route::get('{tipo}/monitora', 'Api\AppAgendaController@monitoracaoTipo')->name('api.agenda-monitoracao-tipo');
    });

    Route::prefix('app/atividades')->group(function ($router) {
        Route::get('paginate', 'Api\AppAtividadesController@paginate')->name('api.atividades-paginate');
        Route::get('monitora', 'Api\AppAtividadesController@monitoracao')->name('api.atividades-monitoracao');
        Route::get('{key}/attachment', 'Api\AppAtividadesController@getAttachments')->name('api.atividades-attachment');
        Route::post('attachment/download', 'Api\AppAtividadesController@getFileDownload')->name('api.atividades-attachment-download');
    });

    Route::prefix('app/financeiro')->group(function ($router) {
        //Route::get('paginate', 'Api\AppFinanceiroController@paginate')->name('api.financeiro-paginate');
        Route::get('pagar/paginate', 'Api\AppFinanceiroController@paginate')->name('api.financeiro-pagar-paginate');
        Route::get('receber/paginate', 'Api\AppFinanceiroController@paginate')->name('api.financeiro-receber-paginate');
        Route::get('monitora', 'Api\AppFinanceiroController@monitoracao')->name('api.financeiro-monitoracao');
    });

    Route::prefix('app/chat')->group(function ($router) {
        Route::get('monitora', 'Api\AppChatController@monitoracao')->name('api.chat-monitoracao');
        //Route::get('paginate', 'Api\AppChatController@paginate')->name('api.chat-paginate');
        Route::get('mensagens/paginate', 'Api\AppAtendimentoMensagemController@paginate')->name('api.chat-mensagens-paginate');
        Route::post('mensagens/check', 'Api\AppAtendimentoMensagemController@check')->name('api.chat-mensagens-check');
        Route::post('mensagens/checkNotReadForCurrentUser', 'Api\AppAtendimentoMensagemController@checkNotReadForCurrentUser')->name('api.chat-mensagens-checkNotReadForCurrentUser');
        Route::post('mensagens', 'Api\AppAtendimentoMensagemController@store')->name('api.chat-mensagens-store');
        Route::post('mensagens/getFile', 'Api\AppAtendimentoMensagemController@getFile')->name('api.chat-mensagens-get-file');
    });

    /*Route::prefix('app/chat/atendimentos')->group(function($router) {
        Route::get('paginate', 'Api\AppAtendimentoController@paginate')->name('api.atendimento-paginate');
        Route::post('/', 'Api\AppAtendimentoController@store')->name('api.atendimento-store');

        // chat
        Route::get('{key}/mensagens/paginate', 'Api\AppAtendimentoMensagemController@paginate')->name('api.atendimento-mensagem-paginate');
        Route::post('{key}/mensagens', 'Api\AppAtendimentoMensagemController@store')->name('api.atendimento-mensagem-store');
    });*/
});

