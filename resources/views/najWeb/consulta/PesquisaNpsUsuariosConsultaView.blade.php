@extends('najWeb.viewBase')

@section('title', 'Pesquisa Nps')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ env('APP_URL') }}ampleAdmin/assets/extra-libs/prism/prism.css">

    <style>
        .option-selected {
            background: #00000040 !important;
        }

        .badge-status-nps {
            font-size: 11px;
            text-shadow: none;
            margin-top: 2px;
            margin-left: 20%;
        }

        .btn-nota {
            border-radius: 100%;
        }

    </style>

@endsection

@section('content')

<div class="email-app font-12" style="height: 100%">
    <div class="left-part" style="height: 100%; width:15%">
        <a class="ti-menu ti-close btn btn-success show-left-part d-block d-md-none"></a>
        <div class="scrollable ps-container ps-theme-default" style="height:100%;">
            <div class="divider"></div>
            <ul class="list-group nav-left-naj">
                <li class="list-group-item cursor-pointer" onclick="window.location.href = '{{ env('APP_URL') }}naj/pesquisa/nps'">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Consulta de todas as pesquisas realizadas">
                        <i class="mr-2 fas fa-search"></i>
                        Pesquisa
                    </a>
                </li>
                <li class="list-group-item cursor-pointer" onclick="redirectNpsTabCadastro();">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Cadastro de pesquisas">
                        <i class="mr-2 fas fa-plus"></i>
                        Cadastro
                    </a>
                </li>
                <li class="list-group-item cursor-pointer option-selected" onclick="redirectNps('usuarios');">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Usuários Participantes da Pesquisa">
                        <i class="mr-2 fas fa-users"></i>
                        Usuários <span class="badge badge-warning badge-rounded badge-pendente-nps float-right" id="badge-pendentes-nps"></span>
                    </a>
                </li>
            </ul>
            <div class="ps-scrollbar-x-rail" style="left: 0px; bottom: 0px;"><div class="ps-scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps-scrollbar-y-rail" style="top: 0px; right: 3px;"><div class="ps-scrollbar-y" tabindex="0" style="top: 0px; height: 0px;"></div>
            </div>
        </div>
    </div>
    <div class="right-part ml-auto" style="height: 100%; width:85%">
        <div id="datatable-pesquisa-nps-usuarios" class="naj-datatable no-margin-datatable" style="height: 100%;"></div>
    </div>
</div>

@component('najWeb.componentes.modalManutencaoPesquisaNpsUsuarios')
@endcomponent

@endsection

@section('scripts')

<script src="{{ env('APP_URL') }}ampleAdmin/assets/extra-libs/prism/prism.js"></script>
<script src="{{ env('APP_URL') }}js/tables/pesquisaNpsUsuariosTable.js"></script>
<script src="{{ env('APP_URL') }}js/tables/npsRelacionamentoUsuariosTable.js"></script>
<script src="{{ env('APP_URL') }}js/pesquisaNpsUsuarios.js"></script>

<script>
    loadTablePesquisaNpsUsuarios();
</script>

@endsection