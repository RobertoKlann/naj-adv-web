<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="height: 100%;">

    <head>    
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <title>Naj - @yield('title')</title>

        <link href="{{ env('APP_URL') }}imagens/logo-naj-2020_N - Cópia.png" rel="shortcut icon" type="image/vnd.microsoft.icon" />

        <link rel="stylesheet" href="{{ env('APP_URL') }}ampleAdmin/dist/css/style.min.css">
        @isset($ignora_css_datatable)
            <!--<script>console.log('app.css ignorados para esta rotina')</script>-->
        @else
            <link href="{{ env('APP_URL') }}css/app.css" rel="stylesheet">
        @endisset

        <!-- CSS NAJ -->
        <link rel="stylesheet" href="{{ env('APP_URL') }}naj-datatable/styles/alert.css">
        <link rel="stylesheet" href="{{ env('APP_URL') }}naj-datatable/styles/loading.css">
        <link rel="stylesheet" href="{{ env('APP_URL') }}naj-datatable/styles/modal.css">
        @isset($ignora_css_datatable)
            <!--<script>console.log('index.css ignorados para esta rotina')</script>-->
        @else
            <link rel="stylesheet" href="{{ env('APP_URL') }}naj-datatable/styles/index.css">
        @endisset
        <link rel="stylesheet" href="{{ env('APP_URL') }}naj-datatable/styles/scrollbar.css">

        @yield('css')
    </head>

    <body style="height: 100%;">    
        <div id="loading" class="loader loader-default"></div>
        <div id="main-wrapper" style="height: 100%;">
            <header class="topbar">
                <nav class="navbar top-navbar navbar-expand-md navbar-dark">
                    <div class="navbar-header">                    
                        <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)">
                            <i class="ti-menu ti-close"></i>
                        </a>                        
                        <b class="logo-icon ml-5">
                            <img src="{{ env('APP_URL') }}imagens/logo-naj-125x50px.png" alt="homepage" class="dark-logo" />
                        </b>
                        <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i class="ti-more"></i></a>
                    </div>
                    <div class="navbar-collapse collapse mb-0 ">
                        &emsp;<span class="font-weight-bolder">Licenciado:</span>&nbsp;
                        <span id="nomeEmpresaLicenciada"></span>
                    </div>
                    <div class="navbar-collapse collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav float-right mr-auto"></ul>
                        <ul class="navbar-nav float-right">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle waves-effect waves-dark pro-pic" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    @if (Auth::check())
                                    <span class="ml-2 user-text font-medium">{{ Auth::user()->apelido }}</span><span class="fas fa-angle-down ml-2 user-text"></span>
                                    @else
                                    <span class="ml-2 user-text font-medium">Usuário</span><span class="fas fa-angle-down ml-2 user-text"></span>
                                    @endif
                                </a>
                                <div class="dropdown-menu dropdown-menu-right user-dd animated flipInY">
                                    <div class="d-flex no-block align-items-center p-3 mb-2 border-bottom">
                                        @if (Auth::check())
                                        <div class="ml-2">
                                            <h4 class="mb-0">{{ Auth::user()->nome }}</h4>
                                            <p class=" mb-0 text-muted">{{ Auth::user()->email_recuperacao }}</p>
                                        </div>
                                        @else
                                        <div class="ml-2">
                                            <h4 class="mb-0">USUÁRIO NÃO AUTENTICADO</h4>
                                        </div>
                                        @endif
                                    </div>
                                    <a class="dropdown-item" href="{{ url(env('APP_ALIAS') . '/usuarios/perfil') }}"><i class="ti-user mr-1 ml-1"></i> Alterar dados do Usuário</a>
                                    @if(env('URL_LOGIN_NAJ_ANTIGO'))
                                    @else
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="javascript:void(0)" id="logout"><i class="fa fa-power-off mr-1 ml-1"></i> Logout</a>
                                    @endif
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>
            <aside class="left-sidebar">
                <div class="scroll-sidebar">
                    <nav class="sidebar-nav">
                        <ul id="sidebarnav">
                            <li class="sidebar-item" id="sidebar-home" title="Voltar ao inicio">
                                <a class="sidebar-link" href="{{ url(env('URL_NAJ_ANTIGO')) }}" aria-expanded="false">
                                    <i class="fas fa-arrow-circle-left"></i>
                                    <span class="hide-menu">VOLTAR</span>
                                </a>
                            </li>
                            @if(isset($is_home) && env('URL_LOGIN_NAJ_ANTIGO'))
                            <li class="sidebar-item" id="sidebar-atendimento" title="Atendimento"> 
                                <a class="sidebar-link" href="{{ url(env('APP_ALIAS') . '/home') }}" aria-expanded="false">
                                    <i class="far fa-comments"></i>
                                    <span class="hide-menu">Atendimento</span>
                                </a>
                            </li>
                            @elseif(!env('URL_LOGIN_NAJ_ANTIGO'))
                            <li class="sidebar-item" id="sidebar-atendimento" title="Atendimento"> 
                                <a class="sidebar-link" href="{{ url(env('APP_ALIAS') . '/home') }}" aria-expanded="false">
                                    <i class="far fa-comments"></i>
                                    <span class="hide-menu">Atendimento</span>
                                </a>
                            </li>
                            @endif
                            @if(isset($is_usuarios) && env('URL_LOGIN_NAJ_ANTIGO'))
                            <li class="sidebar-item" id="sidebar-usuario" title="Usuários"> 
                                <a class="sidebar-link" href="{{ url(env('APP_ALIAS') . '/usuarios') }}" aria-expanded="false">
                                    <i class="fas fa-users"></i>
                                    <span class="hide-menu">Usuários</span>
                                </a>
                            </li>
                            @elseif(!env('URL_LOGIN_NAJ_ANTIGO'))
                            <li class="sidebar-item" id="sidebar-usuario" title="Usuários"> 
                                <a class="sidebar-link" href="{{ url(env('APP_ALIAS') . '/usuarios') }}" aria-expanded="false">
                                    <i class="fas fa-users"></i>
                                    <span class="hide-menu">Usuários</span>
                                </a>
                            </li>
                            @endif

                            @if(isset($is_atividades) && env('URL_LOGIN_NAJ_ANTIGO'))
                            <li class="sidebar-item" id="sidebar-atividades" title="Atividades">
                                <a class="sidebar-link" href="{{ url(env('APP_ALIAS') . '/atividades') }}" aria-expanded="false">
                                    <i class="fas fa-tasks"></i>
                                    <span class="hide-menu">Atividades</span>
                                </a>
                            </li>
                            @elseif(!env('URL_LOGIN_NAJ_ANTIGO'))
                            <li class="sidebar-item" id="sidebar-atividades" title="Atividades"> 
                                <a class="sidebar-link" href="{{ url(env('APP_ALIAS') . '/atividades') }}" aria-expanded="false">
                                    <i class="fas fa-tasks"></i>
                                    <span class="hide-menu">Atividades</span>
                                </a>
                            </li>
                            @endif

                            @if(isset($is_dashboard) && env('URL_LOGIN_NAJ_ANTIGO'))
                            <li class="sidebar-item" id="sidebar-dashboard">
                                <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fas fa-chart-bar"></i><span class="hide-menu">Gráficos</span></a> 
                                <ul aria-expanded="false" class="collapse first-level">
                                    <li class="sidebar-item"> <a class="sidebar-link has-arrow arrow-left waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fas fa-users"></i><span class="hide-menu">Usuários</span></a>
                                        <ul aria-expanded="false" class="collapse second-level">
                                            <li class="sidebar-item">
                                                <a href="{{ url(env('APP_ALIAS') . '/usuarios/dashboards/geral') }}" class="sidebar-link"><i class="fas fa-chart-line"></i><span class="hide-menu">Estatísticas em Geral</span></a>
                                            </li>
                                            <li class="sidebar-item">
                                                <a href="{{ url(env('APP_ALIAS') . '/usuarios/dashboards/usuarios') }}" class="sidebar-link"><i class="fas fa-chart-line"></i><span class="hide-menu">Estatísticas por Usuário</span></a>
                                            </li>
                                            <li class="sidebar-item">
                                                <a href="{{ url(env('APP_ALIAS') . '/usuarios/dashboards/cliente') }}" class="sidebar-link"><i class="fas fa-chart-line"></i><span class="hide-menu">Estatísticas por Cliente</span></a>
                                            </li>
                                            <li class="sidebar-item">
                                                <a href="{{ url(env('APP_ALIAS') . '/usuarios/dashboards/dispositivo') }}" class="sidebar-link"><i class="fas fa-chart-line"></i><span class="hide-menu">Estatísticas por Dispositivo</span></a>
                                            </li>
                                        </ul>
                                        <li class="sidebar-item">
                                            <a href="{{ url(env('APP_ALIAS') . '/dashboard/espaco/disco') }}" class="sidebar-link"><i class="fas fa-chart-bar"></i><span class="hide-menu"> Espaço em Disco </span></a>
                                        </li>
                                    </li>
                                </ul>
                            </li>
                            <!-- GAMBIARRA PARA O MENU DROPDOWN ABRIR PARA O LADO CERTO, NÃO ENTENDI PQ MAS ASSIM FUNCIONA -->
                            <li class="sidebar-item hide" title="Conta Virtual IUGU"> 
                                <a class="sidebar-link" href="{{ url(env('APP_ALIAS') . '/contavirtual') }}" aria-expanded="false">
                                    <i class="fas fa-barcode"></i>
                                    <span class="hide-menu">Conta Virtual IUGU</span>
                                </a>
                            </li>
                            @elseif(!env('URL_LOGIN_NAJ_ANTIGO'))
                            <li class="sidebar-item" id="sidebar-dashboard">
                                <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fas fa-chart-bar"></i><span class="hide-menu">Gráficos</span></a> 
                                <ul aria-expanded="false" class="collapse first-level">
                                    <li class="sidebar-item"> <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fas fa-users"></i><span class="hide-menu">Usuários</span></a>
                                        <ul aria-expanded="false" class="collapse second-level">
                                            <li class="sidebar-item">
                                                <a href="{{ url(env('APP_ALIAS') . '/usuarios/dashboards/geral') }}" class="sidebar-link"><i class="fas fa-chart-line"></i><span class="hide-menu">Estatísticas em Geral</span></a>
                                            </li>
                                            <li class="sidebar-item">
                                                <a href="{{ url(env('APP_ALIAS') . '/usuarios/dashboards/usuarios') }}" class="sidebar-link"><i class="fas fa-chart-line"></i><span class="hide-menu">Estatísticas por Usuário</span></a>
                                            </li>
                                            <li class="sidebar-item">
                                                <a href="{{ url(env('APP_ALIAS') . '/usuarios/dashboards/cliente') }}" class="sidebar-link"><i class="fas fa-chart-line"></i><span class="hide-menu">Estatísticas por Cliente</span></a>
                                            </li>
                                            <li class="sidebar-item">
                                                <a href="{{ url(env('APP_ALIAS') . '/usuarios/dashboards/dispositivo') }}" class="sidebar-link"><i class="fas fa-chart-line"></i><span class="hide-menu">Estatísticas por Dispositivo</span></a>
                                            </li>
                                        </ul>
                                        <li class="sidebar-item">
                                            <a href="{{ url(env('APP_ALIAS') . '/dashboard/espaco/disco') }}" class="sidebar-link"><i class="fas fa-chart-bar"></i><span class="hide-menu"> Espaço em Disco </span></a>
                                        </li>
                                    </li>
                                </ul>
                            </li>
                            @endif

                            @if(isset($is_conta_virtual) && env('URL_LOGIN_NAJ_ANTIGO'))
                            <li class="sidebar-item" title="Conta Virtual IUGU"> 
                                <a class="sidebar-link" href="{{ url(env('APP_ALIAS') . '/contavirtual') }}" aria-expanded="false">
                                    <i class="fas fa-barcode"></i>
                                    <span class="hide-menu">Conta Virtual IUGU</span>
                                </a>
                            </li>
                            @elseif(!env('URL_LOGIN_NAJ_ANTIGO'))
                            <li class="sidebar-item" title="Conta Virtual IUGU"> 
                                <a class="sidebar-link" href="{{ url(env('APP_ALIAS') . '/contavirtual') }}" aria-expanded="false">
                                    <i class="fas fa-barcode"></i>
                                    <span class="hide-menu">Conta Virtual IUGU</span>
                                </a>
                            </li>
                            @endif
                            @if(isset($is_extrato_financeiro) && env('URL_LOGIN_NAJ_ANTIGO'))
                            <li class="sidebar-item" title="Extrato U. F."> 
                                <a class="sidebar-link" href="{{ url(env('APP_ALIAS') . '/unidadefinanceiraextrato') }}" aria-expanded="false">
                                    <i class="fas fa-file-alt"></i>
                                    <span class="hide-menu">Extrato U. F.</span>
                                </a>
                            </li>
                            @elseif(!env('URL_LOGIN_NAJ_ANTIGO'))
                            <li class="sidebar-item" title="Extrato U. F."> 
                                <a class="sidebar-link" href="{{ url(env('APP_ALIAS') . '/unidadefinanceiraextrato') }}" aria-expanded="false">
                                    <i class="fas fa-file-alt"></i>
                                    <span class="hide-menu">Extrato U. F.</span>
                                </a>
                            </li>
                            @endif

                            @if(isset($is_monitoramento_diarios) && env('URL_LOGIN_NAJ_ANTIGO'))
                            <li class="sidebar-item" id="sidebar-monitoramento-diario" title="Monitoramento dos Diários"> 
                                <a class="sidebar-link" href="{{ url(env('APP_ALIAS') . '/monitoramento/diarios') }}" aria-expanded="false">
                                    <i class="fas fa-book"></i>
                                    <span class="hide-menu">Diários</span>
                                </a>
                            </li>
                            @endif

                            @if(isset($is_monitoramento_tribunais) && env('URL_LOGIN_NAJ_ANTIGO'))
                            <li class="sidebar-item" title="Monitoramento dos Tribunais"> 
                                <a class="sidebar-link" href="{{ url(env('APP_ALIAS') . '/monitoramento/tribunais') }}" aria-expanded="false">
                                    <i class="fas fa-home"></i>
                                    <span class="hide-menu">Tribunais</span>
                                </a>
                            </li>
                            @endif

                            @if(!env('URL_LOGIN_NAJ_ANTIGO'))
                            <li class="sidebar-item"> <a class="sidebar-link two-column has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fas fa-search"></i><span class="hide-menu">Monitoramentos </span></a>
                                <ul aria-expanded="false" class="collapse first-level">
                                    <li class="sidebar-item" id="sidebar-monitoramento-diario" title="Monitoramento dos Diários"> 
                                        <a class="sidebar-link" href="{{ url(env('APP_ALIAS') . '/monitoramento/diarios') }}" aria-expanded="false">
                                            <i class="fas fa-book"></i>
                                            <span class="hide-menu">Diários</span>
                                        </a>
                                    </li>
                                    <li class="sidebar-item" title="Monitoramento dos Tribunais"> 
                                        <a class="sidebar-link" href="{{ url(env('APP_ALIAS') . '/monitoramento/tribunais') }}" aria-expanded="false">
                                            <i class="fas fa-home"></i>
                                            <span class="hide-menu">Tribunais</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>                            
                            @endif

                            @if(isset($is_nps) && env('URL_LOGIN_NAJ_ANTIGO'))
                            <li class="sidebar-item" id="sidebar-nps" title="Pesquisa NPS"> 
                                <a class="sidebar-link" href="{{ url(env('APP_ALIAS') . '/pesquisa/nps') }}" aria-expanded="false">
                                    <i class="fas fa-home"></i>
                                    <span class="hide-menu">Pesquisa NPS</span>
                                </a>
                            </li>
                            @elseif(!env('URL_LOGIN_NAJ_ANTIGO'))
                            <li class="sidebar-item" id="sidebar-nps" title="Pesquisa NPS"> 
                                <a class="sidebar-link" href="{{ url(env('APP_ALIAS') . '/pesquisa/nps') }}" aria-expanded="false">
                                    <i class="fas fa-chart-bar"></i>
                                    <span class="hide-menu">Pesquisa NPS</span>
                                </a>
                            </li>
                            @endif

                            @if(isset($is_process_parado) && env('URL_LOGIN_NAJ_ANTIGO'))
                            <li class="sidebar-item" id="sidebar-processos-parado" title="Pesquisa NPS"> 
                                <a class="sidebar-link" href="{{ url(env('APP_ALIAS') . '/processos/parado') }}" aria-expanded="false">
                                    <i class="fas fa-balance-scale"></i>
                                    <span class="hide-menu">Processos Parado</span>
                                </a>
                            </li>
                            @elseif(!env('URL_LOGIN_NAJ_ANTIGO'))
                            <li class="sidebar-item" id="sidebar-processos-parado" title="Pesquisa NPS"> 
                                <a class="sidebar-link" href="{{ url(env('APP_ALIAS') . '/processos/parado') }}" aria-expanded="false">
                                    <i class="fas fa-balance-scale"></i>
                                    <span class="hide-menu">Processos Parado</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </nav>
                </div>
            </aside>
            <!--loader do najFunctions é aplicado sobre o "page-wrapper" por default-->
            <div class="page-wrapper" style="display: block; height: 100%;">
                @yield('content')
            </div>
        </div>

        <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/jquery/dist/jquery.min.js"></script>
        <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/popper.js/dist/umd/popper.min.js"></script>
        <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="{{ env('APP_URL') }}ampleAdmin/dist/js/app.js"></script>	
        <script src="{{ env('APP_URL') }}ampleAdmin/dist/js/app.init.horizontal-fullwidth.js"></script>	
        <script src="{{ env('APP_URL') }}ampleAdmin/dist/js/app-style-switcher.js"></script>
        <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
        <script src="{{ env('APP_URL') }}ampleAdmin/assets/extra-libs/sparkline/sparkline.js"></script>
        <script src="{{ env('APP_URL') }}ampleAdmin/dist/js/waves.js"></script>
        <script src="{{ env('APP_URL') }}ampleAdmin/dist/js/sidebarmenu.js"></script>
        <script src="{{ env('APP_URL') }}ampleAdmin/dist/js/custom.min.js"></script>
        <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/block-ui/jquery.blockUI.js"></script>
        <script src="{{ env('APP_URL') }}/js/input-mask/jquery.inputmask.js"></script>
        <script src="{{ env('APP_URL') }}js/input-mask/jquery.inputmask.date.extensions.js"></script>
        <script src="{{ env('APP_URL') }}js/input-mask/jquery.inputmask.extensions.js"></script>
        <script src="{{ env('APP_URL') }}js/jQuery-Mask-Plugin/jquery.mask.min.js"></script>
        <script src="{{ env('APP_URL') }}js/axios.js"></script>
        <script src="{{ env('APP_URL') }}js/Naj.js"></script>
        <script src="{{ env('APP_URL') }}js/NajFunctions.js"></script>
        <script src="{{ env('APP_URL') }}js/plugins/download.js"></script>

        <script>
            const baseURL           = "{{ env('APP_URL') }}" + "{{ env('APP_ALIAS') }}" + "/";
            const baseUrlApiBoletos = "{{ env('APP_URL_API_BOLETOS') }}";
            const baseURLCpanel     = "{{ env('CPANEL_URL') }}";
            const appAlias          = "{{ env('APP_ALIAS') }}";
            const appUrl            = "{{ env('APP_URL') }}";
            const najAntigoUrl      = "{{ env('URL_NAJ_ANTIGO') }}";
            const nomeUsuarioLogado = "{{ Auth::user()->nome }}";
            const tipoUsuarioLogado = "{{ Auth::user()->usuario_tipo_id }}";
            const idUsuarioLogado   = "{{ Auth::user()->id }}";
            const apelidoUsuarioLogado = "{{ Auth::user()->apelido }}";
            const nomeEmpresa       = '';
            let CONFIG = null;

            $(window).on('load', () => {
                identificador = sessionStorage.getItem('@NAJ_WEB/identificadorEmpresa');
                permissions = sessionStorage.getItem('@NAJ_WEB/permissions');

                // if (!permissions) {
                    axios({ //Busca as permissões do usuário
                        method: 'get',
                        url: `${baseURL}usuarios/allPermissions`
                    }).then(response => {
                        if(!response.data.permissions) return;

                        sessionStorage.setItem('@NAJ_WEB/permissions', JSON.stringify({perm: response.data.permissions}));
                    });
                // }


                if(!identificador) {
                    //Busca o identificador
                    axios({
                        method: 'get',
                        url: `${baseURL}empresas/identificador`
                    }).then(response => {
                        if(!response.data) return;

                        sessionStorage.setItem('@NAJ_WEB/identificadorEmpresa', response.data);
                    });

                    //Busca os dados da empresa
                    axios({
                        method: 'get',
                        url: `${baseURL}empresas/getNomeFirstEmpresa`
                    }).then(response => {
                        if (!response.data) return;
                        sessionStorage.setItem('@NAJ_WEB/nomeEmpresa', response.data);
                        $('#nomeEmpresaLicenciada')[0].innerHTML = `${response.data}`;
                    });
                }

                $('#nomeEmpresaLicenciada')[0].innerHTML = `${sessionStorage.getItem('@NAJ_WEB/nomeEmpresa')}`;
                $('#logout').on('click', onClickLogout);
            });

            function onClickLogout() {
                localStorage.clear();
                sessionStorage.clear();
                window.location.href = "{{ url('auth/logout') }}";
            }
        </script>

        <script src="{{ env('APP_URL') }}js/datatable/api.js"></script>
        <!--<script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/sweetalert2/dist/sweetalert2.min.js"></script>-->
        <script src="{{ env('APP_URL') }}naj-datatable/src/sweetalert2.min.js"></script>
        <script src="{{ env('APP_URL') }}naj-datatable/src/functions.js"></script>
        <script src="{{ env('APP_URL') }}naj-datatable/src/TableModel.js"></script>
        <script src="{{ env('APP_URL') }}naj-datatable/src/Table.js"></script>
        <script src="{{ env('APP_URL') }}naj-datatable/src/alerts.js"></script>
        <script src="{{ env('APP_URL') }}naj-datatable/src/masks.js"></script>
        <script src='https://momentjs.com/downloads/moment.min.js'></script>

        @yield('scripts')

    </body>
</html>