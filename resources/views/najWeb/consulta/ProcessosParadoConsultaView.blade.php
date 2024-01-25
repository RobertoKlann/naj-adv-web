@extends('najWeb.viewBase')

@section('title', 'Processos Parado')

@section('css')
    <style>
        .td-nome-parte-cliente {
            font-weight: 500;
            color: #000;
        }

        .icone-informaçoes-processo {
            font-size: 15px !important;
            margin-top: 2px;
            position: absolute;
        }

        .badge-status-processo, .badge-informacoes-processo, .badge-nome-partes-processo {
            font-size: 11px !important;
            text-shadow: none !important;
        }

        .row-atividade-andamento-processo, .row-informacoes-processo {
            margin-left: 5% !important;
            width: 100%;
            word-break: break-word;
        }

        .title-andamento-atividade-processo-parado {
            font-weight: 500;
            color: #000;
        }

        .icone-partes-processo-expanded {
            font-size: 15px !important;
            margin-top: 2px;
        }

    </style>
@endsection

@section('active-layer', 'processos-parado')

@section('content')

<div id="datatable-processos-parado" class="naj-datatable" style="height: 100%;"></div>

@endsection
@section('scripts')
    <script src="{{ env('APP_URL') }}js/tables/processosParadoTable.js"></script>
    <script src="{{ env('APP_URL') }}js/processosParado.js"></script>
@endsection