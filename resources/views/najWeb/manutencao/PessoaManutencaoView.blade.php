@extends('najWeb.viewBase')

@section('title', 'Cadastro Pessoa')

@section('css')
@endsection

@section('content')

<div id="" class="m-2" style="height: 100%;">
    <button id="chamaModalManutencaoPessoa" class="" style="width: 20%">
        Click aqui!
    </button>
</div>

@component('najWeb.componentes.modalManutencaoPessoa')
@endcomponent

@component('najWeb.componentes.modalManutencaoPessoaContato')
@endcomponent

@endsection

@section('scripts')
    <script src="{{ env('APP_URL') }}js/tables/pessoaContatoTable.js"></script>
    <script src="{{ env('APP_URL') }}js/pessoa.js"></script>
    <script src="{{ env('APP_URL') }}js/pessoaContato.js"></script>
@endsection