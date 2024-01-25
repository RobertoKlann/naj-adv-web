@extends('najWeb.viewBase')

@section('title', 'Usuários')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ env('APP_URL') }}css/codigoAcessoUsuario.css">
    <link rel="stylesheet" type="text/css" href="{{ env('APP_URL') }}ampleAdmin/assets/libs/bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.min.css">

    <style>

        #list-actions-default {
            width: 230px !important;
        }

    </style>
@endsection

@section('active-layer', 'usuario')

@section('content')

<div id="datatable-usuarios" class="naj-datatable" style="height: 100%;"></div>

@component('najWeb.componentes.modalCopiarPermissao')
@endcomponent

@component('najWeb.componentes.modalCodigoAcessoUsuario')
@endcomponent

@component('najWeb.componentes.modalExcluir')
@endcomponent

@component('najWeb.componentes.modalNovoRelacionamentoPessoaUsuario')
@endcomponent

@endsection
@section('scripts')
    <script src="{{ env('APP_URL') }}js/tables/usuarioTable.js"></script>
    <script src="{{ env('APP_URL') }}js/usuario.js"></script>
    <script src="{{ env('APP_URL') }}js/codigoAcesso.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/bootstrap-switch/dist/js/bootstrap-switch.min.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/select2/dist/js/select2.full.min.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/select2/dist/js/select2.min.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/dist/js/pages/forms/select2/select2.init.js"></script>
@endsection