@extends('najWeb.viewBase')

@section('title', 'Monitoramento Diário')

@section('css')
<link rel="stylesheet" href="{{ env('APP_URL') }}ampleAdmin/assets/libs/bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.min.css">
<link rel="stylesheet" href="{{ env('APP_URL') }}ampleAdmin/assets/libs/select2/dist/css/select2.min.css">
<link rel="stylesheet" href="{{ env('APP_URL') }}css/gijgo.min.css">
<link rel="stylesheet" href="{{ env('APP_URL') }}css/monitoramento.css">
<link rel="stylesheet" href="{{ env('APP_URL') }}css/processo.css">
<link rel="stylesheet" href="{{ env('APP_URL') }}css/tarefaChat.css">
@endsection

@section('content')

<div class="email-app font-12" style="height: 100%">
    <div class="left-part" style="height: 100%; width:15%">
        <div class="scrollable ps-container ps-theme-default" style="height:100%;" data-ps-id="1f7af6ce-24b4-acc8-2f77-8972d68dc1b6">
            <ul class="list-group nav-left-naj" id="sideMenuMD">
                <li class="list-group-item cursor-pointer" onclick="setSelectedOptionMenuMD(this); buscaTodasPublicacoesMD();">
                    <a class="link-nav-left-naj list-group-item-action">
                        <i class="mr-2 fas fa-folder"></i>
                        Todas
                    </a>
                </li>
                <li class="list-group-item cursor-pointer option-selected" onclick="setSelectedOptionMenuMD(this); buscaNaoLidos();">
                    <a class="link-nav-left-naj list-group-item-action">
                        <i class="mr-2 fas fa-newspaper"></i>
                        Novas Publicações <span id ="badgeNovasPublicacoes" class="badge badge-warning text-white font-normal badge-pill float-right">0</span>
                    </a>
                </li>
                <li class="list-group-item cursor-pointer" onclick="setSelectedOptionMenuMD(this); buscaPendentes();">
                    <a class="link-nav-left-naj list-group-item-action">
                        <i class="mr-2 fas fa-flag"></i>
                        Pendentes <span id ="badgePendentes" class="badge badge-danger text-white font-normal badge-pill float-right">0</span>
                    </a>
                </li>
                <li class="list-group-item cursor-pointer" onclick="setSelectedOptionMenuMD(this); buscaNaoMonitorados();">
                    <a class="link-nav-left-naj list-group-item-action">
                        <i class="mr-2 fas fa-flag"></i>
                        Não Monitorados
                    </a>
                </li>
                <li class="list-group-item cursor-pointer" onclick="setSelectedOptionMenuMD(this); buscaSemPrazoDefinido();">
                    <a class="link-nav-left-naj list-group-item-action">
                        <i class="mr-2 fas fa-calendar"></i>
                        Sem Prazo Definido
                    </a>
                </li>
                <li class="list-group-item cursor-pointer" onclick="setSelectedOptionMenuMD(this); buscaDescartados();">
                    <a class="link-nav-left-naj list-group-item-action">
                        <i class="mr-2 fas fa-trash"></i>
                        Descartados <span id ="badgeDescartados" class="badge badge-secondary text-white font-normal badge-pill float-right">0</span>
                    </a>
                </li>
                <hr>
            </ul>
            <div class="ps-scrollbar-x-rail" style="left: 0px; bottom: 0px;"><div class="ps-scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps-scrollbar-y-rail" style="top: 0px; right: 3px;"><div class="ps-scrollbar-y" tabindex="0" style="top: 0px; height: 0px;"></div>
            </div>
        </div>
    </div>
    <div class="right-part ml-auto" style="height: 100%; width:85%">
        <div id="datatable-monitoramento-diario" class="naj-datatable no-margin-datatable" style="height: 100%;"></div>
    </div>
</div>

@component('najWeb.componentes.modalConsultaTermoMonitorado')
@endcomponent

@component('najWeb.componentes.modalManutencaoTermoMonitorado')
@endcomponent

@component('najWeb.componentes.modalConteudoPublicacao')
@endcomponent

@component('najWeb.componentes.modalNovaTarefaProcesso')
@endcomponent

@component('najWeb.componentes.modalTipoTarefa')
@endcomponent

@component('najWeb.componentes.modalPrioridadeTarefa')
@endcomponent

@component('najWeb.componentes.modalManutencaoMonitoramentoProcessoTribunal')
@endcomponent

@component('najWeb.componentes.modalManutencaoProcesso')
@endcomponent

@component('najWeb.componentes.modalManutencaoProcessoClasse')
@endcomponent

@component('najWeb.componentes.modalManutencaoProcessoCartorio')
@endcomponent

@component('najWeb.componentes.modalManutencaoProcessoAreaJuridica')
@endcomponent

@component('najWeb.componentes.modalManutencaoProcessoComarca')
@endcomponent

@component('najWeb.componentes.modalManutencaoPessoa')
@endcomponent

@component('najWeb.componentes.modalManutencaoPessoaContato')
@endcomponent

@component('najWeb.componentes.modalManutencaoComentarioPublicacaoProcesso')
@endcomponent

@endsection

@section('scripts')
<script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/bootstrap-switch/dist/js/bootstrap-switch.min.js"></script>
<script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/select2/dist/js/select2.full.min.js"></script>
<script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/select2/dist/js/select2.init.js"></script>
<script src="{{ env('APP_URL') }}js/gijgo.min.js"></script>
<script src="{{ env('APP_URL') }}js/messages.pt-br.js"></script>
<script src="{{ env('APP_URL') }}js/tables/monitoramentoDiarioTable.js"></script>
<script src="{{ env('APP_URL') }}js/monitoramentoDiario.js"></script>
<script src="{{ env('APP_URL') }}js/tables/termoMonitoradoTable.js"></script>
<script src="{{ env('APP_URL') }}js/termoMonitorado.js"></script>
<script src="{{ env('APP_URL') }}js/pessoa.js"></script>
<script src="{{ env('APP_URL') }}js/tables/pessoaContatoTable.js"></script>
<script src="{{ env('APP_URL') }}js/pessoaContato.js"></script>
<script src="{{ env('APP_URL') }}js/processoClasse.js"></script>
<script src="{{ env('APP_URL') }}js/processoCartorio.js"></script>
<script src="{{ env('APP_URL') }}js/processoAreaJuridica.js"></script>
<script src="{{ env('APP_URL') }}js/processoComarca.js"></script>
<script src="{{ env('APP_URL') }}js/processo.js"></script>
<script src="{{ env('APP_URL') }}js/monitoramentoProcessoTribunal.js"></script>
<script src="{{ env('APP_URL') }}js/tarefaProcesso.js"></script>
<script src="{{ env('APP_URL') }}js/comentarioPublicacaoProcesso.js"></script>
@endsection