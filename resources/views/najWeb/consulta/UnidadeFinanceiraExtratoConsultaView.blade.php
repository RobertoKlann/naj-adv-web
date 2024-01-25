@extends('najWeb.viewBase')

@section('title', 'Unidade Financeira Extrato')

@section('css')
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

@section('content')

<div id="datatable-unidade-financeira-extrato" class="naj-datatable" style="height: 91%;"></div>

<div id='saldoAnterior' class="row datatable-body mt-0" style="height: 7%;">
    <div class="col-9 p-0 text-left">
        <span id="saldoContaVirtual">
            <span class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Saldo atual na plataforma IUGU."></span>
            <b>Saldo Atual:</b> R$ <span id='saldoAtualValor'>0.00</span>&emsp;
            <span class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Saldo disponível para saque na plataforma IUGU."></span>
            <b>Saldo Disponível:</b> R$ <span id='saldoDisponivelValor'>0.00</span>&emsp;
            <span class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Data e Hora da última consulta do Saldo Disponível na plataforma IUGU."></span>
            <b>Data:</b> <span id='saldoDisponivelData'></span>&emsp;
            <b>Hora:</b> <span id='saldoDisponivelHora'></span>&emsp;
        </span>
    </div>
    <div class="col-3 p-0 text-right">
        <span>
            <span class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Saldo Anterior do período."></span>
            <b>Saldo Anterior:</b> R$ <span id='saldoAnteriorValor'>0,00</span>&emsp;
        </span>
    </div>
</div>

@component('najWeb.componentes.modalRealizarSaque')
@endcomponent

@component('najWeb.componentes.modalManutencaoUnidadeFinanceiraData')
@endcomponent

@endsection

@section('scripts') 
    <script src="{{ env('APP_URL') }}js/gijgo.min.js"></script>
    <script src="{{ env('APP_URL') }}js/messages.pt-br.js"></script>
    <script src="{{ env('APP_URL') }}js/tables/unidadeFinanceiraExtratoTable.js"></script>
    <script src="{{ env('APP_URL') }}js/unidadeFinanceiraExtrato.js"></script>
@endsection