@extends('najWeb.viewBase')

@section('title', 'Cadastro Processo')

@section('css')
    <link href="{{ env('APP_URL') }}css/processo.css" rel="stylesheet">
@endsection

@section('content')

<div id="" class="m-2" style="height: 100%;">
    <button id="" class="btnCadastrarProcesso" style="width: 20%">
        Click aqui!
    </button>
</div>

@component('najWeb.componentes.modalManutencaoProcesso')
@endcomponent

@endsection

@section('scripts')
    <script src="{{ env('APP_URL') }}js/processo.js"></script>
@endsection