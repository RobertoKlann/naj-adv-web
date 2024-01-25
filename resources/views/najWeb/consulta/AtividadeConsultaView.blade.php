@extends('najWeb.viewBase')

@section('title', 'Atividades')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ env('APP_URL') }}css/atividade.css">
    <link rel="stylesheet" href="{{ env('APP_URL') }}css/gijgo.min.css">

    <style>
        button {
            background: #fff !important;
        }
        .naj-datatable i {
            color: rgba(47, 50, 62, .75);
            cursor: pointer;
            font-size: 14px;
        }
    </style>

@endsection

@section('active-layer', 'atividade')

@section('content')

<div id="datatable-atividades" class="naj-datatable" style="height: 100%;"></div>

@component('najWeb.componentes.modalNovaAtividade')
@endcomponent

@component('najWeb.componentes.modalConsultaAnexoAtividade')
@endcomponent

@component('najWeb.componentes.modalConsultaObservacao')
@endcomponent

@component('najWeb.componentes.modalManutencaoPessoa')
@endcomponent

@endsection
@section('scripts')
    <script src="{{ env('APP_URL') }}js/gijgo.min.js"></script>
    <script src="{{ env('APP_URL') }}js/messages.pt-br.js"></script>
    <script src="{{ env('APP_URL') }}js/tables/atividadeTable.js"></script>
    <script src="{{ env('APP_URL') }}js/atividade.js"></script>
    <script src="{{ env('APP_URL') }}js/tarefaChat.js"></script>
    <script src="{{ env('APP_URL') }}js/tables/pessoaContatoTable.js"></script>
    <script src="{{ env('APP_URL') }}js/pessoa.js"></script>
    <script src="{{ env('APP_URL') }}js/pessoaContato.js"></script>
@endsection