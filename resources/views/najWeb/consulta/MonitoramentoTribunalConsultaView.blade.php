@extends('najWeb.viewBase')

@section('title', 'Monitoramento Tribunais')

@section('css')
<link rel="stylesheet" href="{{ env('APP_URL') }}ampleAdmin/assets/libs/bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.min.css">
<link rel="stylesheet" href="{{ env('APP_URL') }}ampleAdmin/assets/libs/sweetalert2/dist/sweetalert2.min.css">
<link rel="stylesheet" href="{{ env('APP_URL') }}css/gijgo.min.css">
<link rel="stylesheet" href="{{ env('APP_URL') }}css/monitoramento.css">
<link rel="stylesheet" href="{{ env('APP_URL') }}css/processo.css">
<link rel="stylesheet" href="{{ env('APP_URL') }}css/tarefaChat.css">
@endsection

@section('content')

<div class="email-app font-12" style="height: 100%">
    <div class="left-part" style="height: 100%; width:15%">
        <a class="ti-menu ti-close btn btn-success show-left-part d-block d-md-none"></a>
        <div class="scrollable ps-container ps-theme-default" style="height:100%;" data-ps-id="1f7af6ce-24b4-acc8-2f77-8972d68dc1b6">
            <ul class="list-group nav-left-naj" id="sideMenuMT">
                <li id="optionTodos" class="list-group-item cursor-pointer" onclick="setSelectedOptionMenuMD(this); buscaTodosMonitoramentos();">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Todos os monitoramentos">
                        <i class="mr-2 fas fa-folder"></i>
                        Todos
                    </a>
                </li>
                <li id="optionNovas" class="list-group-item cursor-pointer option-selected" onclick="setSelectedOptionMenuMD(this); buscaNovasMovimetacoes();">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Monitoramentos com novas movimentações">
                        <i class="mr-2 fas fa-newspaper"></i>
                        Novas <span id ="badgeNovasMovimentacoes" class="badge badge-warning text-white font-normal badge-pill float-right">0</span>
                    </a>
                </li>
                <li id="optionPendentes" class="list-group-item cursor-pointer" onclick="setSelectedOptionMenuMD(this); buscaBuscasEmAndamentos();">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Monitoramentos com busca em andamento">
                        <i class="mr-2 fas fa-hourglass-half"></i>
                        Pendentes <span id ="badgeBuscasEmAndamento" class="badge badge-warning text-white font-normal badge-pill float-right">0</span>
                    </a>
                </li>
                <li id="optionComErro" class="list-group-item cursor-pointer" onclick="setSelectedOptionMenuMD(this); buscaErroUltimaBusca();">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Monitoramentos com erro na última busca">
                        <i class="mr-2 fas fa-bug"></i>
                        Com Erro <span id ="badgeErroNaUltimaBusca" class="badge badge-danger text-white font-normal badge-pill float-right">0</span>
                    </a>
                </li>
                <li id="optionBaixados" class="list-group-item cursor-pointer" onclick="setSelectedOptionMenuMD(this); buscaMonitoramentosBaixados();">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Monitoramentos baixados">
                        <i class="mr-2 fas fa-trash"></i>
                        Baixados <span id ="badgeMonitoramentosBaixados" class="badge badge-secondary text-white font-normal badge-pill float-right">0</span>
                    </a>
                </li>
            </ul>
            <div class="ps-scrollbar-x-rail" style="left: 0px; bottom: 0px;"><div class="ps-scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps-scrollbar-y-rail" style="top: 0px; right: 3px;"><div class="ps-scrollbar-y" tabindex="0" style="top: 0px; height: 0px;"></div>
            </div>
        </div>
    </div>
    <div class="right-part ml-auto" style="height: 100%; width:85%">
        <div id="datatable-monitoramento-tribunal" class="naj-datatable no-margin-datatable" style="height: 100%;"></div>
    </div>
</div>

@component('najWeb.componentes.modalAreaTransferencia')
@endcomponent

@component('najWeb.componentes.modalManutencaoMonitoramentoProcessoTribunal')
@endcomponent

@component('najWeb.componentes.modalConteudoMovimentacaoProcesso')
@endcomponent

@component('najWeb.componentes.modalManutencaoComentarioMovimentacaoProcesso')
@endcomponent

@component('najWeb.componentes.modalManutencaoConsultaMonitoraProcessoTribunalBuscas')
@endcomponent

@component('najWeb.componentes.modalConfirmacaoExclusaoAndamentos')
@endcomponent

@component('najWeb.componentes.modalManutencaoBuscasSemanaisPadrao')
@endcomponent

@component('najWeb.componentes.modalNovaTarefaProcesso')
@endcomponent

@component('najWeb.componentes.modalTipoTarefa')
@endcomponent

@component('najWeb.componentes.modalPrioridadeTarefa')
@endcomponent

@component('najWeb.componentes.modalManutencaoQuotasDeBusca')
@endcomponent

@component('najWeb.componentes.modalConsultaValidacaoProcessos')
@endcomponent

@component('najWeb.componentes.modalManutencaoPessoa')
@endcomponent

@component('najWeb.componentes.modalManutencaoPessoaContato')
@endcomponent

@endsection

@section('scripts')
<script src="{{ env('APP_URL') }}js/gijgo.min.js"></script>
<script src="{{ env('APP_URL') }}js/messages.pt-br.js"></script>
<script src="{{ env('APP_URL') }}js/messages.pt-br.js"></script>
<script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/bootstrap-switch/dist/js/bootstrap-switch.min.js"></script>
<script src="{{ env('APP_URL') }}js/tables/monitoramentoTribunalTable.js"></script>
<script src="{{ env('APP_URL') }}js/tables/monitoraProcessoTribunaBuscasTable.js"></script>
<script src="{{ env('APP_URL') }}js/tables/conteudoMovimentacoesProcessoTable.js"></script>
<script src="{{ env('APP_URL') }}js/tables/validacaoProcessosTable.js"></script>
<script src="{{ env('APP_URL') }}js/tables/pessoaContatoTable.js"></script>
<script src="{{ env('APP_URL') }}js/monitoramentoTribunal.js"></script>
<script src="{{ env('APP_URL') }}js/monitoramentoProcessoTribunal.js"></script>
<script src="{{ env('APP_URL') }}js/comentarioMovimentacaoProcesso.js"></script>
<script src="{{ env('APP_URL') }}js/monitoraProcessoTribunaBuscas.js"></script>
<script src="{{ env('APP_URL') }}js/conteudoMovimentacoesProcesso.js"></script>
<script src="{{ env('APP_URL') }}js/buscasSemanaisPadrao.js"></script>
<script src="{{ env('APP_URL') }}js/tarefaProcesso.js"></script>
<script src="{{ env('APP_URL') }}js/quotaDeBuscas.js"></script>
<script src="{{ env('APP_URL') }}js/validacaoProcessos.js"></script>
<script src="{{ env('APP_URL') }}js/pessoa.js"></script>
<script src="{{ env('APP_URL') }}js/pessoaContato.js"></script>

@isset($codigo)
    @isset($cnj)
        <script>
            let codido_processo = {{$codigo}};
            let numero_cnj      = "{{$cnj}}";
            //Chama o método "carregaModalManutencaoMPTinclussao" passando o "codido_processo" e o "numero_cnj" como parâmetro
            carregaModalManutencaoMPTinclussao(codido_processo, numero_cnj);
        </script>
    @endisset
@endisset

@endsection