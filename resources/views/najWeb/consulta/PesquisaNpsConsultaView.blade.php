@extends('najWeb.viewBase')

@section('title', 'Pesquisa Nps')

@section('css')

    <style>
        .option-selected {
            background: #00000040 !important;
        }
    </style>

@endsection

@section('content')

<div style="height: 100%; ">
    <div id="datatable-pesquisa-nps" class="naj-datatable" style="height: 100%;"></div>
</div>

@endsection

@section('scripts')

<script src="{{ env('APP_URL') }}js/tables/pesquisaNpsTable.js"></script>
<script src="{{ env('APP_URL') }}js/pesquisaNps.js"></script>

<script>
    loadTablePesquisaNps();
</script>

@endsection