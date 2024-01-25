@extends('najWeb.viewBase')

@section('title', 'Usuario - Estatísticas')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ env('APP_URL') }}ampleAdmin/assets/extra-libs/c3/c3.min.css">

    <style>
        .body-dashboard {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 79% !important;
        }
    </style>
@endsection

@section('active-layer', 'usuarios')

@section('content')

<div class="email-app font-12" style="height: 100%">
    <a id="icone-nav-menu-usuarios" class="icone-nav-menu-usuarios"><i class="fas fa-bars mr-2"></i> Menu</a>
    <div class="left-part content-pai-nav-left-naj">
        <div class="nav-menu-usuarios scrollable ps-container ps-theme-default" style="height:100%;">
            <ul class="nav-list-usuarios list-group nav-left-naj">
                <li class="list-group-item cursor-pointer" onclick="window.location.href = '{{ env('APP_URL') }}naj/usuarios'">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Consulta de usuários">
                        <i class="mr-2 fas fa-search"></i>
                        Pesquisa
                    </a>
                </li>
                <li class="list-group-item cursor-pointer" onclick="onClickMenuUsuario('edit');">
                    <a class="link-nav-left-naj link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Cadastro de Usuários">
                        <i class="mr-2 fas fa-plus"></i>
                        Cadastro
                    </a>
                </li>
                <li class="list-group-item cursor-pointer" onclick="onClickMenuUsuario('permissoes');">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Permissões do Usuário">
                        <i class="mr-2 fas fa-lock"></i>
                        Permissões
                    </a>
                </li>
                <li class="list-group-item cursor-pointer" onclick="onClickMenuUsuario('relacionamentos');">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Pessoas Relacionadas ao Usuário">
                        <i class="mr-2 fas fa-users"></i>
                        Pessoas
                    </a>
                </li>
                <li class="list-group-item cursor-pointer" onclick="onClickMenuUsuario('dispositivos');">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Dispositivos do Usuário">
                        <i class="mr-2 fas fa-mobile-alt"></i>
                        Dispositivos
                    </a>
                </li>
                <li class="list-group-item cursor-pointer" onclick="onClickMenuUsuario('smtp');">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Configuração do E-mail do Usuário">
                        <i class="mr-2 fas fa-envelope"></i>
                        Configuração E-mail
                    </a>
                </li>
                <li class="list-group-item cursor-pointer option-selected" onclick="onClickMenuUsuario('estatisticas');">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Estatísticas do Usuário">
                        <i class="mr-2 fas fa-chart-bar"></i>
                        Estatísticas
                    </a>
                </li>
            </ul>
            <div class="ps-scrollbar-x-rail" style="left: 0px; bottom: 0px;"><div class="ps-scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps-scrollbar-y-rail" style="top: 0px; right: 3px;"><div class="ps-scrollbar-y" tabindex="0" style="top: 0px; height: 0px;"></div>
            </div>
        </div>
    </div>
    <div id="loading-usuario" class="loader loader-default" data-half></div>
    <div class="right-part content-pai-nav-right-naj" id="content-outside-manutencao-usuario">
        <div class="card-custom-naj">
            <div class="header-custom-naj-card" id="headerUsuario"></div>
            <div class="body-custom-naj-card naj-scrollable body-dashboard">
                <div id="dashboardUser"></div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('scripts')
    <script src="{{ env('APP_URL') }}js/usuario.js"></script>
    <script src="{{ env('APP_URL') }}js/estatisticasUsuario.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/assets/extra-libs/c3/d3.min.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/assets/extra-libs/c3/c3.min.js"></script>
@endsection