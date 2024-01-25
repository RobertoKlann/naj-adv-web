@extends('najWeb.viewBase')

@section('title', 'Conta Virtual')

@section('css')
@endsection

@section('content')

<div id="datatable-conta-virtual" class="naj-datatable" style="height: 100%;"></div>

@component('najWeb.componentes.modalManutencaoContaVirtual')
@endcomponent

@endsection

@section('scripts')
    <script src="{{ env('APP_URL') }}js/tables/contaVirtualTable.js"></script>
    <script src="{{ env('APP_URL') }}js/contaVirtual.js"></script>
@endsection