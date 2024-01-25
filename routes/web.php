<?php

/**
 * Rotas da Aplicação.
 *
 * @package routes
 * @author  Roberto Klann
 * @author  William Tiago
 * @since   09/01/2020
 */

Route::get('/', function() {
    return redirect('auth/login');
});

/*
 | Auth
 |
 */
Route::group([
    'namespace' => 'Auth',
    'prefix'    => 'auth'
], function($router) {
    Route::get('login' , 'LoginController@index')->name('auth.index');
    Route::post('login', 'LoginController@login')->name('auth.login');
    Route::get('logout', 'LoginController@logout')->name('auth.logout');
});

/*
 | Install
 |
 */
Route::group([
    'namespace' => 'NajWeb',
    'prefix'    => 'install'
], function($router) {

    Route::get('/', function() {
        return view('najWeb/InstallView');
    });

    //EMPRESA
    Route::post('empresas', 'EmpresaController@store')->name('empresa.store');

    //USUÁRIO
    Route::post('usuarios', 'UsuarioController@store')->name('usuario.store');
});

/*
 | Naj
 |
 */
Route::group([
    'namespace'  => 'NajWeb',
    'prefix'     => 'naj',
    'middleware' => 'auth:web'
], function($router) {
    Route::get('{route}', 'HomeController@index')->where('route', 'index|home');

    //SYS CONFIG
    Route::get('sysconfig/searchsysconfig/{secao}/{chave}', 'SysConfigController@searchSysConfig')->name('sysconfig.searchsysconfig');
    Route::get('sysconfig/searchsysconfigall/{secao}/{chave}', 'SysConfigController@searchSysConfigAll')->name('sysconfig.searchsysconfigall');
    Route::put('sysconfig/{secao}/{chave}/{valor}', 'SysConfigController@updateSysConfig')->name('sysconfig.updateSysConfig');
    Route::post('sysconfig/{secao}/{chave}/{valor}', 'SysConfigController@createSysConfig')->name('sysconfig.createSysConfig');
    
    //ATUALIZAR SENHA USUARIO
    Route::get('password/update', 'HomeController@indexUpdateSenha')->name('password.update');

    //BOLETOS
    Route::get('boletos/contavirtualmanutencao', 'BoletoController@contaVirtualManutencao')->name('boleto.contavirtualmanutencao');

    //USUÁRIOS
    Route::get('usuarios'                     , 'UsuarioController@index')->name('usuario.index');
    Route::get('usuarios/paginate'            , 'UsuarioController@paginate')->name('usuario.paginate');
    Route::get('usuarios/proximo'             , 'UsuarioController@proximo')->name('usuario.proximo');
    Route::get('usuarios/show/{id}'           , 'UsuarioController@show')->name('usuario.show');
    Route::get('usuarios/create'              , 'UsuarioController@create')->name('usuario.create');
    Route::get('usuarios/edit'                , 'UsuarioController@edit')->name('usuario.edit');
    Route::get('usuarios/cpf/{cpf}'           , 'UsuarioController@getUserByCpfInCpanel')->name('usuario.show');
    Route::get('usuarios/smtp'                , 'UsuarioController@smtp')->name('usuario.smtp');
    Route::put('usuarios/{id}'                , 'UsuarioController@update')->name('usuario.update');
    Route::put('usuarios/password/{id}'       , 'UsuarioController@updatePassword')->name('usuario.update-password');
    Route::put('usuarios/senhaProvisoria/{id}', 'UsuarioController@updateSenhaProvisora')->name('usuario.update-senha-provisoria');
    Route::put('usuarios/atualizarDados/{id}' , 'UsuarioController@atualizarDados')->name('usuario.atualizar-dados');
    Route::put('usuarios/smtp/{id}'           , 'UsuarioController@smtpUpdate')->name('usuario.update-smtp');
    Route::post('usuarios'                    , 'UsuarioController@store')->name('usuario.store');

    //USUÁRIO ESTATISTICAS
    Route::get('usuarios/estatisticas'     , 'UsuarioController@estatisticasView')->name('usuario.estatisticas-view');
    Route::get('usuarios/estatisticas/data/{parameters}', 'UsuarioController@dataEstatisticasUser')->name('usuario.estatisticas-data');
    Route::get('usuarios/estatisticas/data/user/{user}', 'UsuarioController@dataByUserTypeClient')->name('usuario.estatisticas-admin-data');

    //USUÁRIO PERFIL
    Route::get('usuarios/perfil', 'UsuarioController@perfil')->name('usuario.perfil');

    //CÓDIGO ACESSOS
    Route::get('usuarios/codigoAcesso/{codigo}', 'CodigoAcessoController@validaCodigoAcesso')->name('codigo.acesso.index');
    Route::put('usuarios/codigoAcesso/{codigo}', 'CodigoAcessoController@update')->name('codigo.acesso.update');
    Route::post('usuarios/codigoAcesso'        , 'CodigoAcessoController@store')->name('codigo.acesso.store');

    //USUÁRIOS ACESSO
    Route::get('usuarios/acessos'  , 'UsuarioAcessoController@index')->name('usuario.acesso.index');

    //USUÁRIOS PERMISSÕES
    Route::get('usuarios/allPermissions'     , 'UsuarioPermissaoController@permissions')->name('usuario.permissao.permissions');
    Route::get('usuarios/permissoes'         , 'UsuarioPermissaoController@index')->name('usuario.permissao.index');
    Route::get('usuarios/permissoes/paginate', 'UsuarioPermissaoController@paginate')->name('usuario.permissao.paginate');
    Route::post('usuarios/permissoes'        , 'UsuarioPermissaoController@store')->name('usuario.permissao.store');
    Route::post('usuarios/permissoes/copiar' , 'UsuarioPermissaoController@copiar')->name('usuario.permissao.copiar');

    //USUÁRIOS DISPOSITIVOS
    Route::get('usuarios/dispositivos'           , 'UsuarioDispositivoController@index')->name('usuario.dispositivo.index');
    Route::get('usuarios/dispositivos/paginate'  , 'UsuarioDispositivoController@paginate')->name('usuario.dispositivo.paginate');
    Route::get('usuarios/dispositivos/{id}'      , 'UsuarioDispositivoController@allDispositivosUsuario')->name('usuario.dispositivo.index');
    Route::get('usuarios/dispositivos/in/{users}', 'UsuarioDispositivoController@allDispositivosUsuarios')->name('usuario.dispositivo.index');
    Route::put('usuarios/dispositivos/{id}'      , 'UsuarioDispositivoController@update')->name('usuario.dispositivo.update');

    //USUÁRIOS RELACIONAMENTOS
    Route::get('usuarios/relacionamentos'                , 'UsuarioRelacionamentoController@index')->name('usuario.relacionamento.index');
    Route::get('usuarios/relacionamentos/paginate'       , 'UsuarioRelacionamentoController@paginate')->name('usuario.relacionamento.paginate');
    Route::get('usuarios/relacionamentos/show/{id}'      , 'UsuarioRelacionamentoController@show')->name('usuario.relacionamento.show');
    Route::put('usuarios/relacionamentos/{id}'           , 'UsuarioRelacionamentoController@update')->name('usuario.relacionamento.update');
    Route::post('usuarios/relacionamentos'               , 'UsuarioRelacionamentoController@store')->name('usuario.relacionamento.store');
    Route::delete('usuarios/relacionamentos/many/{list}' , 'UsuarioRelacionamentoController@destroyMany')->name('usuario.relacionamento.destroy-many');

    //MÓDULOS
    Route::get('modulos/index/{modulo}'           , 'ModuloController@allModulos')->name('modulos.index');
    Route::get('modulos/grupos/index/{parametros}', 'ModuloController@allGrupos')->name('modulos.grupos.index');

    //PESSOAS
    Route::get('pessoas/getPessoasFilter/{filter}'         , 'PessoaController@getPessoasFilter')->name('pessoas.index');
    Route::get('pessoas/getPessoaFilter/{filter}'          , 'PessoaController@getPessoaFilter')->name('pessoas.index');
    Route::get('pessoas/getPessoasFisicaByNome/{filter}'   , 'PessoaController@getPessoasFisicaByNome')->name('pessoas.fisica-by-nome');
    Route::get('pessoas/getPessoasUsuarioInFilter/{filter}', 'PessoaController@getPessoasUsuarioInFilter')->name('pessoas-usuario-filter.show');
    Route::get('pessoas/cpf/{cpf}'                         , 'PessoaController@getPessoaByCpf')->name('pessoas.cpf');
    Route::get('pessoas/paginate'                          , 'PessoaController@paginate')->name('pessoas.paginate');
    Route::get('pessoas/proximo'                           , 'PessoaController@proximo')->name('pessoas.proximo');
    Route::get('pessoas/show/{id}'                         , 'PessoaController@show')->name('pessoa.show');
    Route::get('pessoas/create'                            , 'PessoaController@create')->name('pessoas.create');
    Route::post('pessoas'                                  , 'PessoaController@store')->name('pessoas.store');
    Route::put('pessoas/{id}'                              , 'PessoaController@update')->name('pessoa.update');
    Route::get('pessoas/divisao'                           , 'PessoaController@getAllDivisao')->name('pessoas.divisao');
    Route::get('pessoas/grupopessoa'                       , 'PessoaController@getAllGrupoPessoa')->name('pessoas.grupopessoa');
    Route::get('pessoas/pessoaGrupoFromChat'               , 'PessoaController@allPessoaGrupoFromChat')->name('pessoas.pessoa-grupo-chat');
    Route::get('pessoas/grupoClienteRelacionadas/{filter}' , 'PessoaController@getGrupoClienteRelacionadas')->name('pessoas.grupo-cliente');
    Route::get('pessoas/grupoClienteRelacionadas/codigo/{filter}' , 'PessoaController@getGrupoClienteRelacionadasByCodigo')->name('pessoas.grupo-cliente-by-codigo');

    //ANIVERSARIANTE
    Route::get('pessoas/quantidadeByCardPessoaAniversariantes', 'PessoaController@getQuantidadeByCardPessoaAniversariantes')->name('pessoas.pessoa-aniversariantes-chat');

    //PESSOA ANEXOS
    Route::post('pessoas/anexos', 'PessoaAnexoController@store')->name('pessoas.anexo.store');

    //PESSOA X CONTATO
    Route::get('pessoa/contato/paginate'          , 'PessoaContatoController@paginate')->name('pessoa.contato.paginate');
    Route::get('pessoa/contato/proximo'           , 'PessoaContatoController@proximo')->name('pessoa.contato.proximo');
    Route::get('pessoa/contato/show/{id}'         , 'PessoaContatoController@show')->name('pessoa.contato.show');
    Route::post('pessoa/contato'                  , 'PessoaContatoController@store')->name('pessoa.contato.store');
    Route::put('pessoa/contato/{id}'              , 'PessoaContatoController@update')->name('pessoa.contato.update');
    Route::delete('pessoa/contato/many/{list}'    , 'PessoaContatoController@destroyMany')->name('pessoa.contato.destroy-many');
    
    //PESSOA X USUARIO
    Route::get('pessoa/usuario/{filter}', 'PessoaRelacionamentoUsuarioController@getRelacionamentosUsuario')->name('pessoa.usuario.relacionamento');

    //PESSOA X CLIENTE
    Route::get('pessoa/cliente/isPessoaCliente/{filter}', 'PessoaClienteController@isPessoaCliente')->name('pessoa.cliente');

    //DIVISOES
    Route::get('divisoes/paginate', 'DivisaoController@paginate')->name('pessoa.index');

    //EMPRESA
    Route::get('empresas/identificador'      , 'EmpresaController@getIdentificadorEmpresa')->name('empresa.identificador-empresa');
    Route::get('empresas/getNomeFirstEmpresa', 'EmpresaController@getNomeFirstEmpresa')->name('empresa.first-empresa');
    Route::get('empresas/logo/index'         , 'EmpresaController@indexLogo')->name('empresa.index-logo');

    //CONFIGURAÇÃO SISTEMA
    Route::get('configuracao/padrao', 'SysConfigController@configuracaoPadrao')->name('sysconfig.configuracaoPadrao');
    
    //CONTA VIRTUAL
    Route::get('contavirtual'                            , 'ContaVirtualController@index')->name('boleto.contavirtual.index');
    Route::get('contavirtual/paginate'                   , 'ContaVirtualController@paginate')->name('boleto.contavirtual.paginate');
    Route::get('contavirtual/proximo'                    , 'ContaVirtualController@proximo')->name('boleto.contavirtual.proximo');
    Route::get('contavirtual/show/{id}'                  , 'ContaVirtualController@show')->name('boleto.contavirtual.show');
    Route::put('contavirtual/{id}'                       , 'ContaVirtualController@update')->name('boleto.contavirtual.update');
    Route::post('contavirtual'                           , 'ContaVirtualController@store')->name('boleto.contavirtual.store');
    Route::delete('contavirtual/many/{list}'             , 'ContaVirtualController@destroyMany')->name('boleto.contavirtual.destroy-many');
    Route::get('contavirtual/verificanaturezafinanceira' , 'ContaVirtualController@verificaNaturezaFinanceira')->name('boleto.contavirtual.verificaNaturezaFinanceira');

    //UNIDADE FINANCEIRA EXTRATO
    Route::get('unidadefinanceiraextrato'                                 , 'UnidadeFinanceiraExtratoController@index')->name('unidadefinanceiraextrato.index');
    Route::get('unidadefinanceiraextrato/paginate'                        , 'UnidadeFinanceiraExtratoController@paginate')->name('unidadefinanceiraextrato.paginate');
    Route::get('unidadefinanceiraextrato/proximo'                         , 'UnidadeFinanceiraExtratoController@proximo')->name('unidadefinanceiraextrato.proximo');
    Route::get('unidadefinanceiraextrato/unidades'                        , 'UnidadeFinanceiraExtratoController@unidades')->name('unidadefinanceiraextrato.unidades');
    Route::get('unidadefinanceiraextrato/saldocontavirtual/{account_id}'  , 'UnidadeFinanceiraExtratoController@saldoContaVirtual')->name('unidadefinanceiraextrato.saldocontavirtual');
    Route::post('unidadefinanceiraextrato/saldoanterior'                  , 'UnidadeFinanceiraExtratoController@saldoAnterior')->name('unidadefinanceiraextrato.saldoanterior');
    Route::post('unidadefinanceiraextrato/editadata'                      , 'UnidadeFinanceiraExtratoController@editaData')->name('unidadefinanceiraextrato.editadata');
    Route::get('unidadefinanceiraextrato/maxdata/{tipo_data}'             , 'UnidadeFinanceiraExtratoController@maxData')->name('unidadefinanceiraextrato.maxdata');
 
    //UNIDADE FINANCEIRA
    Route::get('unidadefinanceira/unidades' , 'UnidadeFinanceiraController@unidades')->name('unidadefinanceira.unidades');
    
    //PAGAMENTO ESPÉCIE
    Route::get('pagamentoespecie' , 'PagamentoEspecieController@pagamentoEspecieUnidadeFinaceira')->name('pagamentoespecie.pagamentoEspecieUnidadeFinaceira');
    
    //MONITORAMENTO
    
    //MONITORAMENTO CONTROLLER
    Route::get('monitoramento/buscacallbacksescavador' , 'MonitoramentoController@buscaCallbacksEscavador')->name('monitoramento.buscacallbacksescavador');
    Route::get('monitoramento/automacao'               , 'MonitoramentoController@automacao')->name('monitoramento.automacao');
   
    //DIÀRIOS
    
    //MONITORA TERMO MOVIMENTAÇÃO / MONITORAMENTO DIÁRIO
    Route::get('monitoramento/diarios'                             , 'MonitoramentoDiarioController@index')->name('monitoramento.diarios.index');
    Route::put('monitoramento/diarios/update/{id}'                 , 'MonitoramentoDiarioController@update')->name('monitoramento.diarios.update');
    Route::get('monitoramento/diarios/paginate'                    , 'MonitoramentoDiarioController@paginate')->name('monitoramento.diarios.paginate');
    Route::get('monitoramento/diarios/proximo'                     , 'MonitoramentoDiarioController@proximo')->name('monitoramento.diarios.proximo');
    Route::get('monitoramento/diarios/buscanomedostermos'          , 'MonitoramentoDiarioController@buscaNomeDosTermos')->name('monitoramento.diarios.buscanomedostermos');
    Route::get('monitoramento/diarios/setaregistrolido/{id}'       , 'MonitoramentoDiarioController@setaRegistroLido')->name('monitoramento.diarios.setaregistrolido');
    Route::get('monitoramento/diarios/obtermovimentacoesdiario'    , 'MonitoramentoDiarioController@persistePublicacoes')->name('monitoramento.diarios.obtermovimentacoesdiario');
    Route::get('monitoramento/diarios/buscadiariosescavador'       , 'MonitoramentoDiarioController@buscaDiariosEscavador')->name('monitoramento.diarios.buscadiariosescavador');
    Route::get('monitoramento/diarios/persistediarios'             , 'MonitoramentoDiarioController@persisteDiarios')->name('monitoramento.diarios.persistediarios');
    Route::post('monitoramento/diarios/atualizaenvolvido'          , 'MonitoramentoDiarioController@atualizaEnvolvido')->name('monitoramento.diarios.atualizaenvolvido');
    Route::get('monitoramento/diarios/totalpublicacoesnovas'       , 'MonitoramentoDiarioController@totalPublicacoesNovas')->name('monitoramento.diarios.totalpublicacoesnovas');
    Route::get('monitoramento/diarios/totalpublicacoespendentes'   , 'MonitoramentoDiarioController@totalPublicacoesPendentes')->name('monitoramento.diarios.totalpublicacoespendentes');
    Route::get('monitoramento/diarios/totalpublicacoesdescartados' , 'MonitoramentoDiarioController@totalPublicacoesDescartados')->name('monitoramento.diarios.totalpublicacoesdescartados');
    Route::get('monitoramento/diarios/descartarpublicacao/{id}'    , 'MonitoramentoDiarioController@descartarPublicacao')->name('monitoramento.diarios.descartarpublicacao');
    
    //MONITORA TERMO DIARIO / TERMOS MONITORADOS
    Route::get('monitoramento/diarios/termos/proximo'         , 'TermoMonitoradoController@proximo')->name('monitoramento.diarios.termos.proximo');
    Route::get('monitoramento/diarios/termos/paginate'        , 'TermoMonitoradoController@paginate')->name('monitoramento.diarios.termos.paginate');
    Route::get('monitoramento/diarios/termos/show/{id}'       , 'TermoMonitoradoController@show')->name('monitoramento.diarios.termos.show');
    Route::put('monitoramento/diarios/termos/{id}'            , 'TermoMonitoradoController@update')->name('monitoramento.diarios.termos.update');
    Route::post('monitoramento/diarios/termos'                , 'TermoMonitoradoController@store')->name('monitoramento.diarios.termos.store');
    Route::delete('monitoramento/diarios/termos/many/{list}'  , 'TermoMonitoradoController@destroyMany')->name('monitoramento.diarios.termos.destroy-many');
    
    //MONITORA TERMO PROCESSO
    Route::put('monitoramento/diarios/processo/{id}'            , 'MonitoraTermoProcessoController@update')->name('monitoramento.diarios.processo.update');
    Route::post('monitoramento/diarios/desvinculaprocesso/{id}' , 'MonitoraTermoProcessoController@desvinculaProcesso')->name('monitoramento.diarios.processo.desvinculaprocesso');
    
    //TRIBUNAIS
    
    //MONITORAMENTO TRIBUNAL
    Route::get('monitoramento/tribunais'                                      , 'MonitoramentoTribunalController@index')->name('monitoramento.tribunais.monitoramentotribunal.index');
    Route::get('monitoramento/tribunais/paginate'                             , 'MonitoramentoTribunalController@paginate')->name('monitoramento.tribunais.paginate');
    Route::get('monitoramento/tribunais/proximo'                              , 'MonitoramentoTribunalController@proximo')->name('monitoramento.tribunais.proximo');
    Route::get('monitoramento/tribunais/buscatribunaisescavador'              , 'MonitoramentoTribunalController@buscaTribunaisEscavador')->name('monitoramento.tribunais.buscatribunaisescavador');
    Route::get('monitoramento/tribunais/persistetribunais'                    , 'MonitoramentoTribunalController@persisteTribunais')->name('monitoramento.tribunais.persistetribunais');
    Route::get('monitoramento/tribunais/obtermovimentacoestribunal'           , 'MonitoramentoTribunalController@buscarMovimentacoesProcessosNosTribunais')->name('monitoramento.tribunais.obtermovimentacoestribunal');
    Route::get('monitoramento/tribunais/obterpendentestribunal'               , 'MonitoramentoTribunalController@buscarMovimentacoesPendentesProcessosNosTribunais')->name('monitoramento.tribunais.obterpendentestribunal');
    Route::post('monitoramento/tribunais/pesquisaprocesso'                    , 'MonitoramentoTribunalController@pesquisarProcessosNoSiteDoTribunal')->name('monitoramento.tribunais.pesquisaprocesso');
    Route::get('monitoramento/tribunais/pesquisaprocessoscomerros'            , 'MonitoramentoTribunalController@pesquisarProcessosComErroNoSiteDoTribunal')->name('monitoramento.tribunais.pesquisaprocessoscomerros');
    Route::get('monitoramento/tribunais/totalmonitoramentos'                  , 'MonitoramentoTribunalController@totalDeMonitoramentosNoSistema')->name('monitoramento.tribunais.totalmonitoramentos');
    Route::get('monitoramento/tribunais/totalmonitoramentosativos'            , 'MonitoramentoTribunalController@totalDeMonitoramentosAtivosNoSistema')->name('monitoramento.tribunais.totalmonitoramentosativos');
    Route::post('monitoramento/tribunais/verificasecnjjatemmonitoramento'     , 'MonitoramentoTribunalController@verificaSeCNJjaTemMonitoramento')->name('monitoramento.tribunais.verificasecnjjatemmonitoramento');
    
    //MONITORAMENTO TRIBUNAL / MOVIMENTACOES
    Route::post('monitoramento/tribunais/movimentacoes/setaregistroslidos'          , 'MonitoramentoTribunalController@setaRegistroslidos')->name('monitoramento.tribunais.movimentacoes.setaregistroslido');
    Route::get('monitoramento/tribunais/movimentacoes/setatodosregistroscomolidos'  , 'MonitoramentoTribunalController@setaTodosRegistrosComolidos')->name('monitoramento.tribunais.movimentacoes.setatodosregistroscomolidos');
    Route::get('monitoramento/tribunais/movimentacoes/excluir/{id_mpt}/{instancia}' , 'MonitoramentoTribunalController@excluirMovimentacoes')->name('monitoramento.tribunais.movimentacoes.excluir');
    
    //MONITORA PROCESSO MOVIMENTACAO
    Route::put('monitoraprocessomovimentacao/{id}' , 'MonitoraProcessoMovimentacaoController@update')->name('monitoraprocessomovimentacao.update');
    Route::get('monitoraprocessomovimentacao/desvincularatividade/{id}' , 'MonitoraProcessoMovimentacaoController@desvincularAtividade')->name('monitoraprocessomovimentacao.desvincularatividade');
    
    //PRC MOVIMENTO
    Route::get('processomovimento/proximo' , 'PrcMovimentoController@proximo')->name('processomovimento.proximo');
    Route::put('processomovimento/{id}'    , 'PrcMovimentoController@update')->name('processomovimento.update');
    Route::post('processomovimento'        , 'PrcMovimentoController@store')->name('processomovimento.store');
    
    //MONITORA PROCESSO TRIBUNAL
    Route::get('monitoraprocessotribunal/proximo'  , 'MonitoraProcessoTribunalController@proximo')->name('monitoraprocessotribunal.proximo');
    Route::post('monitoraprocessotribunal'         , 'MonitoraProcessoTribunalController@insere')->name('monitoraprocessotribunal.insere');
    Route::put('monitoraprocessotribunal'          , 'MonitoraProcessoTribunalController@atualiza')->name('monitoraprocessotribunal.atualiza');
    Route::put('monitoraprocessotribunalfrequenica', 'MonitoraProcessoTribunalController@atualizaFrequencia')->name('monitoraprocessotribunal.monitoraprocessotribunalfrequenica');
    Route::get('monitoraprocessotribunal/show/{id}'     , 'MonitoraProcessoTribunalController@show')->name('monitoraprocessotribunal.show');
    
    //MONITORA PROCESSO TRIBUNAL REL PRC
    Route::get('monitoraprocessotribunalrelprc/proximo' , 'MonitoraProcessoTribunalRelPrcController@proximo')->name('monitoraprocessotribunalrelprc.proximo');
    Route::post('monitoraprocessotribunalrelprc'        , 'MonitoraProcessoTribunalRelPrcController@store')->name('monitoraprocessotribunalrelprc.store');
    
    //MONITORA PROCESSO TRIBUNAL BUSCAS
    Route::get('monitoraprocessotribunalbusca/paginate' , 'MonitoraProcessoTribunalBuscasController@paginate')->name('monitoraprocessotribunalbusca.paginate');
    
    //PROCESSOS
    Route::get('processos/create'                           , 'ProcessoController@create')->name('processo.create');
    Route::get('processos/paginate'                         , 'ProcessoController@paginate')->name('processos.paginate');
    Route::get('processos/proximo'                          , 'ProcessoController@proximo')->name('processos.proximo');
    Route::get('processos/show/{key}'                       , 'ProcessoController@show')->name('processos.show');
    Route::post('processos'                                 , 'ProcessoController@store')->name('processos.store');
    Route::put('processos/{id}'                             , 'ProcessoController@update')->name('processos.update');    
    Route::get('processos/partes/{key}'                     , 'ProcessoController@getPartes')->name('processos.partes');
    Route::get('processos/partes/cliente/{key}'             , 'ProcessoController@getParteCliente')->name('processos.partes.clinte');
    Route::get('processos/partes/adversaria/{key}'          , 'ProcessoController@getParteAdversaria')->name('processos.partes.adversaria');
    Route::get('processos/prcqualificacao'                  , 'ProcessoController@getPrcQualificacao')->name('processos.prcqualificacao');
    Route::get('processos/prcorgao'                         , 'ProcessoController@getPrcOrgao')->name('processos.prcorgao');
    Route::get('processos/prcsituacao'                      , 'ProcessoController@getPrcSituacao')->name('processos.prcsituacao');
    Route::get('processos/idareajuridica/{codigo_processo}' , 'ProcessoController@getIdAreaJuridica')->name('processos.idareajuridica');

    //PROCESSOS PARADO
    Route::get('processos/parado'          , 'ProcessoParadoController@index')->name('processos.parado-index');
    Route::get('processos/parado/paginate' , 'ProcessoParadoController@paginate')->name('processos.parado-paginate');

    //PROCESSOS VALIDACAO
    Route::get('validacao/processos/paginate'                      ,'ValidacaoProcessosController@paginate')->name('processos.validacao.paginate');
    Route::get('validacao/processos/monitorarprocessos/{situacao}' ,'ValidacaoProcessosController@monitorarTodosOsProcessosValidosAtivos')->name('processos.validacao.monitorarprocessos');
    
    //PROCESSOS ANEXOS
    Route::get('processos/anexos/{key}'           , 'ProcessoController@anexos')->name('processos.anexos');
    Route::get('processos/anexos/download/{key}'  , 'ProcessoAnexoController@download')->name('processos.anexos.download');

    //PROCESSOS ATIVIDADES
    Route::get('processos/atividades/paginate'      , 'AtividadeProcessoController@paginate')->name('processos.atividade.paginate');
    Route::get('processos/atividades/proximo'       , 'AtividadeProcessoController@proximo')->name('processos.atividades.proximo');
    Route::get('processos/atividades/{key}'         , 'AtividadeProcessoController@show')->name('processos.atividades.show');
    Route::put('processos/atividades/{key}'         , 'AtividadeProcessoController@update')->name('processos.atividades.update');
    Route::post('processos/atividades'              , 'AtividadeProcessoController@store')->name('processos.atividades.store');
    Route::delete('processos/atividades/many/{list}', 'AtividadeProcessoController@destroyMany')->name('processos.atividades.many.destroy-many');

    //PROCESSOS ATIVIDADES TIPO
    Route::get('processos/atividades/tipos/getallatividadestipos' , 'AtividadeTipoController@getAllAtividadesTipos')->name('pessoas.atividades.getallatividadestipos');
    Route::get('processos/atividades/tipos/proximo'               , 'AtividadeTipoController@proximo')->name('pessoas.atividades.proximo');
     
    //PROCESSOS ANDAMENTO
    Route::get('processos/andamento/paginate', 'AndamentoProcessoController@paginate')->name('processos.andamento.paginate');

    //PROCESSO CLASSE 
    Route::get('processos/classe/filter/{filter}'          , 'ProcessoClasseController@getProcessoClasseInFilter')->name('processos.classe.filter');
    Route::get('processos/classe/getclassebyname/{filter}' , 'ProcessoClasseController@getProcessoClasseByName')->name('processos.classe.getclassebyname');
    Route::get('processos/classe/show/{key}'               , 'ProcessoClasseController@show')->name('processos.classe.show');
    Route::get('processos/classe/proximo'                  , 'ProcessoClasseController@proximo')->name('processos.classe.proximo');
    Route::post('processos/classe'                         , 'ProcessoClasseController@store')->name('processos.classe.store');
    Route::put('processos/classe/{id}'                     , 'ProcessoClasseController@update')->name('processos.classe.update');
    Route::get('processos/classe/classeFromChat'           , 'ProcessoClasseController@classeFromChat')->name('processos.areajuridica.classeFromChat');
   
    //PROCESSO CARTÓRIO
    Route::get('processos/cartorio/filter/{filter}'           , 'ProcessoCartorioController@getProcessoCartorioInFilter')->name('processos.cartorio.filter');
    Route::get('processos/comarca/getcartoriobyname/{filter}' , 'ProcessoCartorioController@getProcessoCartorioByName')->name('processos.comarca.getcartoriobyname');
    Route::get('processos/cartorio/show/{key}'                , 'ProcessoCartorioController@show')->name('processos.cartorio.show');
    Route::get('processos/cartorio/proximo'                   , 'ProcessoCartorioController@proximo')->name('processos.cartorio.proximo');
    Route::post('processos/cartorio'                          , 'ProcessoCartorioController@store')->name('processos.cartorio.store');
    Route::put('processos/cartorio/{id}'                      , 'ProcessoCartorioController@update')->name('processos.cartorio.update');
   
    //PROCESSO ÁREA JURÍDICA
    Route::get('processos/areajuridica'                 , 'ProcessoAreaJuridicaController@getProcessoAreaJuridica')->name('processos.areajuridica');
    Route::get('processos/areajuridica/filter/{filter}' , 'ProcessoAreaJuridicaController@getProcessoAreaJuridicaInFilter')->name('processos.areajuridica.filter');
    Route::get('processos/areajuridica/show/{key}'      , 'ProcessoAreaJuridicaController@show')->name('processos.areajuridica.show');
    Route::get('processos/areajuridica/proximo'         , 'ProcessoAreaJuridicaController@proximo')->name('processos.areajuridica.proximo');
    Route::post('processos/areajuridica'                , 'ProcessoAreaJuridicaController@store')->name('processos.areajuridica.store');
    Route::put('processos/areajuridica/{id}'            , 'ProcessoAreaJuridicaController@update')->name('processos.areajuridica.update');
    Route::get('processos/areajuridica/areasFromChat'   , 'ProcessoAreaJuridicaController@areasFromChat')->name('processos.areajuridica.areasFromChat');
   
    //PROCESSO COMARCA
    Route::get('processos/comarca/filter/{filter}'           , 'ProcessoComarcaController@getProcessoComarcaInFilter')->name('processos.comarca.filter');
    Route::get('processos/comarca/getcomarcabyname/{filter}' , 'ProcessoComarcaController@getProcessoComarcaByName')->name('processos.comarca.getcomarcabyname');
    Route::get('processos/comarca/show/{key}'                , 'ProcessoComarcaController@show')->name('processos.comarca.show');
    Route::get('processos/comarca/proximo'                   , 'ProcessoComarcaController@proximo')->name('processos.comarca.proximo');
    Route::post('processos/comarca'                          , 'ProcessoComarcaController@store')->name('processos.comarca.store');
    Route::put('processos/comarca/{id}'                      , 'ProcessoComarcaController@update')->name('processos.comarca.update');
    Route::get('processos/comarca/comarcaFromChat'           , 'ProcessoComarcaController@comarcaFromChat')->name('processos.areajuridica.comarcaFromChat');
    
    //ESCAVADOR
    
    //ESCAVADOR Autenticação
    Route::get('/escavador/solicitartokendeacesso'                               , 'EscavadorController@solicitarTokenDeAcesso')->name('escavador.solicitartokendeacesso');
    //ESCAVADOR Busca
    Route::post('/escavador/buscarportermo'                                      , 'EscavadorController@buscarPorTermo')->name('escavador.buscarportermo');
    //ESCAVADOR Busca Assíncrona
    Route::get('/escavador/todososresultadosdasbuscasassincronas'                , 'EscavadorController@todosOsResultadosDasBuscasAssincronas')->name('escavador.todososresultadosdasbuscasassincronas');
    Route::get('/escavador/resultadoespecificodeumabuscaassíncrona/{id}'         , 'EscavadorController@resultadoEspecíficoDeUmaBuscaAssincrona')->name('escavador.resultadoespecificodeumabuscaassíncrona');
    //ESCAVADOR Callback
    Route::get('/escavador/retornaroscallbacks'                                  , 'EscavadorController@retornarOsCallbacks')->name('escavador.retornaroscallbacks');
    //ESCAVADOR Créditos
    Route::get('/escavador/consultarcreditos/'                                   , 'EscavadorController@consultarCreditos')->name('escavador.consultarcreditos');
    //ESCAVADOR Diários Oficiais
    Route::get('/escavador/retornarorigens/'                                     , 'EscavadorController@retornarOrigens')->name('escavador.retornarorigens');
    Route::get('/escavador/retornaridsorigens/'                                  , 'EscavadorController@retornarIdsOrigens')->name('escavador.retornaridsorigens');
    //ESCAVADOR Monitoramento de Diários Oficiais
    Route::get('/escavador/retornarosdiariosoficiaismonitorados/{id}'            , 'EscavadorController@retornarOsDiariosOficiaisMonitorados')->name('escavador.retornarosdiariosoficiaismonitorados');
    Route::post('/escavador/registrarnovomonitoramentodiarios'                   , 'EscavadorController@registrarNovoMonitoramentoDiarios')->name('escavador.registrarnovomonitoramentodiarios');
    Route::get('/escavador/retornarmonitoramentosdiarios'                        , 'EscavadorController@retornarMonitoramentosDiarios')->name('escavador.retornarMonitoramentosdiarios');
    Route::get('/escavador/retornarmonitoramentodiarios/{id}'                    , 'EscavadorController@retornarMonitoramentoDiarios')->name('escavador.retornarMonitoramentodiarios');
    Route::put('/escavador/editarmonitoramentodiarios'                          , 'EscavadorController@editarMonitoramentoDiarios')->name('escavador.editarMonitoramentodiarios');
    Route::get('/escavador/removermonitoramentodiarios/{id}'                     , 'EscavadorController@removerMonitoramentoDiarios')->name('escavador.removerMonitoramentodiarios');
    //ESCAVADOR Monitoramento no site do Tribunal
    Route::get('/escavador/registrarnovomonitoramentotribunais'                  , 'EscavadorController@registrarNovoMonitoramentoTribunais')->name('escavador.registrarnovomonitoramentotribunais');
    Route::get('/escavador/retornarmonitoramentostribunais'                      , 'EscavadorController@retornarMonitoramentoTribunais')->name('escavador.retornarMonitoramentotribunais');
    Route::get('/escavador/retornarmonitoramentotribunais/{id}'                  , 'EscavadorController@retornarMonitoramentoTribunais')->name('escavador.retornarMonitoramentotribunais');
    Route::post('/escavador/editarmonitoramentotribunais'                        , 'EscavadorController@editarMonitoramentoTribunais')->name('escavador.editarMonitoramentotribunais');
    Route::get('/escavador/removermonitoramentotribunais/{id}'                   , 'EscavadorController@removerMonitoramentoTribunais')->name('escavador.removerMonitoramentotribunais');
    //ESCAVADOR Processos
    Route::get('/escavador/pesquisarprocessonositedotribunalassincrono/{numero}' , 'EscavadorController@pesquisarProcessoNoSiteDoTribunalAssincrono')->name('escavador.pesquisarprocessonositedotribunalassincrono');
    //ESCAVADOR Tribunais
    Route::get('/escavador/retornarsistemasdostribunaisdisponiveis'              , 'EscavadorController@retornarSistemasDosTribunaisDisponiveis')->name('escavador.retornarsistemasDostribunaisdisponiveis');
    //ESCAVADOR model
    Route::get('/escavador/verificatokenescavador'                               , 'EscavadorController@verificaTokenEscavador')->name('escavador.verificatokenescavador');
    //ESCAVADOR Teste
    Route::post('/escavador/teste'                                               , 'EscavadorController@teste')->name('escavador.teste');
    
    //CHAT - MENSAGEM
    Route::get('chat/mensagens'            , 'AtendimentoController@allMessages')->name('atendimento.all-messages');
    Route::get('chat/mensagens/finish'     , 'AtendimentoController@allMessagesFinish')->name('atendimento.all-messages-finish');
    Route::get('chat/mensagem/publico/{id}', 'ChatMensagemController@getAllMensagensChatPublico')->name('atendimento.all-messages-chat-publico');
    Route::get('chat/mensagem/new/{id}'    , 'ChatMensagemController@newMessagesFromChat')->name('atendimento.all-new-messages');
    Route::get('chat/mensagem/old/{id}'    , 'ChatMensagemController@oldMessagesFromChat')->name('atendimento.all-new-messages');
    Route::post('chat/mensagem'            , 'ChatMensagemController@store')->name('atendimento.chat.mensagem.store');

    //CHAT - ANEXO
    Route::post('chat/mensagem/anexo'               , 'AnexoChatStorageController@uploadAnexoChat')->name('anexo-chat.upload');
    Route::post('chat/mensagem/shareAnexo'          , 'AnexoChatStorageController@shareAnexoChat')->name('anexo-chat.share');
    Route::get('chat/mensagem/download/{parameters}', 'AnexoChatStorageController@downloadAnexoChat')->name('anexo-chat.download');

    //CHAT - ATENDIMENTO
    Route::post('chat/atendimento'     , 'ChatAtendimentoController@store')->name('chat.atendimento.store');
    Route::post('chat/novo/atendimento', 'ChatAtendimentoController@novoAtendimento')->name('chat.novo-atendimento.store');
    Route::put('chat/atendimento/{id}' , 'ChatAtendimentoController@update')->name('chat.atendimento.update');

    Route::get('financeiro/paginate', 'FinanceiroController@paginate')->name('financeiro-paginate');

    //CHAT DOCUMENTOS
    Route::get('documentos/show/{key}'    , 'DocumentosChatController@documentos')->name('documentos-show');
    Route::get('documentos/download/{key}', 'DocumentosChatController@download')->name('documentos-download');

    //TAREFA
    Route::post('tarefa', 'TarefaController@store')->name('tarefa.store');

    //TAREFA PRIORIDADE
    Route::get('tarefa/prioridade/paginate'       , 'TarefaPrioridadeController@paginate')->name('tarefa-prioridade.paginate');
    Route::get('tarefa/prioridade/show/{id}'      , 'TarefaPrioridadeController@show')->name('tarefa-prioridade.show');
    Route::put('tarefa/prioridade/{id}'           , 'TarefaPrioridadeController@update')->name('tarefa-prioridade.update');
    Route::post('tarefa/prioridade'               , 'TarefaPrioridadeController@store')->name('tarefa-prioridade.store');
    Route::delete('tarefa/prioridade/many/{list}' , 'TarefaPrioridadeController@destroyMany')->name('tarefa-prioridade.destroy-many');

    //TAREFA SITUAÇÃO
    Route::get('tarefa/situacao/paginate'  , 'TarefaSituacaoController@paginate')->name('tarefa-situacao-paginate');

    //TAREFA TIPOS
    Route::get('tarefa/tipos/paginate'       , 'TarefaTipoController@paginate')->name('tarefa-tipos.paginate');
    Route::get('tarefa/tipos/show/{id}'      , 'TarefaTipoController@show')->name('tarefa-tipos.show');
    Route::put('tarefa/tipos/{id}'           , 'TarefaTipoController@update')->name('tarefa-tipos.update');
    Route::post('tarefa/tipos'               , 'TarefaTipoController@store')->name('tarefa-tipos.store');
    Route::delete('tarefa/tipos/many/{list}' , 'TarefaTipoController@destroyMany')->name('tarefa-tipos.destroy-many');

    Route::get('clientes/quantidadeByCard/{id}'          , 'AtendimentoController@quantidadeClienteByCard')->name('clientes.qtdbycard');
    Route::get('clientes/quantidadeByCardPesoaGrupo/{id}', 'AtendimentoController@getQuantidadeClientePessoaGrupoByCard')->name('clientes.qtdbycard-pessoa-grupo');
    Route::get('clientes/pessoas/{id}'                   , 'AtendimentoController@getPessoasConsultaAvancada')->name('clientes.pessoas.consulta-avancada');
    Route::get('clientes/pessoas/grupo/{id}'             , 'AtendimentoController@getPessoasGrupoConsultaAvancada')->name('clientes-pessoas-grupo-consulta-avancada');
    Route::get('clientes/pessoas/aniversariante/{id}'    , 'AtendimentoController@getPessoasAniversarianteConsultaAvancada')->name('clientes-pessoas-aniversariante-consulta-avancada');
    
    //PESQUISA NPS
    Route::get('pesquisa/nps'          , 'PesquisaNpsController@index')->name('pesquisa-nps.index');
    Route::get('pesquisa/nps/show/{id}', 'PesquisaNpsController@show')->name('pesquisa-nps.show');
    Route::get('pesquisa/nps/paginate' , 'PesquisaNpsController@paginate')->name('pesquisa-nps.paginate'); 
    Route::get('pesquisa/nps/create'   , 'PesquisaNpsController@create')->name('pesquisa-nps.create');
    Route::get('pesquisa/nps/edit'     , 'PesquisaNpsController@edit')->name('pesquisa-nps.edit');
    Route::get('pesquisa/nps/usuarios' , 'PesquisaNpsController@usuarios')->name('pesquisa-nps.usuarios');
    Route::post('pesquisa/nps'         , 'PesquisaNpsController@store')->name('pesquisa-nps.store');
    Route::put('pesquisa/nps/{id}'     , 'PesquisaNpsController@update')->name('pesquisa-nps.update');

    //PESQUISA NPS USUÁRIOS
    Route::get('pesquisa/nps/usuarios/paginate'      , 'PesquisaNpsUsuarioController@paginate')->name('pesquisa-nps-usuarios.paginate');
    Route::get('pesquisa/usuarios/paginate'          , 'PesquisaNpsUsuarioController@paginateUsuarios')->name('pesquisa-nps-usuarios.paginateUsuarios');
    Route::get('pesquisa/nps/usuarios/show/{id}'     , 'PesquisaNpsUsuarioController@show')->name('pesquisa-nps-usuarios.show');
    Route::get('pesquisa/nps/usuarios/pendentes/{id}', 'PesquisaNpsUsuarioController@pendentesNotRead')->name('pesquisa-nps-usuarios.pendentesNotRead');
    Route::post('pesquisa/nps/usuarios'              , 'PesquisaNpsUsuarioController@store')->name('pesquisa-nps-usuarios.store');
    Route::post('pesquisa/nps/usuarios/lido'         , 'PesquisaNpsUsuarioController@updateLido')->name('pesquisa-nps-usuarios.updateLido');
    Route::put('pesquisa/nps/usuarios/{id}'          , 'PesquisaNpsUsuarioController@update')->name('pesquisa-nps-usuarios.update');

    //DASHBOARDS
    Route::get('dashboard/espaco/disco', 'DashboardEspacoDiscoController@index')->name('dashboard-espaco-disco.index');
    Route::get('dashboard/espaco/disco/data', 'DashboardEspacoDiscoController@loadData')->name('dashboard-espaco-disco.loadData');

    Route::get('usuarios/dashboards/geral'   , 'DashboardUsuarioController@indexGeral')->name('usuario.dashboardsViewGeral');
    Route::get('usuarios/dashboards/usuarios', 'DashboardUsuarioController@indexUser')->name('usuario.dashboardsViewUser');
    Route::get('usuarios/dashboards/cliente' , 'DashboardUsuarioController@indexClient')->name('usuario.dashboardsViewClient');
    Route::get('usuarios/dashboards/dispositivo' , 'DashboardUsuarioController@indexDispositivo')->name('usuario.dashboardsViewDispositivo');
    Route::get('usuarios/dashboards/data/geral'  , 'DashboardUsuarioController@dataByGeral')->name('usuario.data-geral');
    Route::get('usuarios/dashboards/data/usuario/{parameters}', 'DashboardUsuarioController@dataByUserTypeUser')->name('usuario.data-user');
    Route::get('usuarios/dashboards/data/cliente/{parameters}', 'DashboardUsuarioController@dataByUserTypeClient')->name('usuario.data-client');
    Route::get('usuarios/dashboards/data/system'      , 'DashboardUsuarioController@dataByTypeSystem')->name('usuario.data-system');

    //ATIVIDADES
    Route::get('atividades'               , 'AtividadeController@index')->name('atividade.index');
    Route::get('atividades/paginate'      , 'AtividadeController@paginate')->name('atividade.paginate');
    Route::get('atividades/buscanomedonoatividade', 'AtividadeController@buscaNomeDonoAtividade')->name('atividade.buscanomeatividade');
    Route::get('atividades/proximo'       , 'AtividadeController@proximo')->name('atividades.proximo');
    Route::get('atividades/{key}'         , 'AtividadeController@show')->name('atividades.show');
    Route::put('atividades/{key}'         , 'AtividadeController@update')->name('atividades.update');
    Route::post('atividades'              , 'AtividadeController@store')->name('atividades.store');
    Route::delete('atividades/many/{list}', 'AtividadeController@destroyMany')->name('atividades.many.destroy-many');

}); 