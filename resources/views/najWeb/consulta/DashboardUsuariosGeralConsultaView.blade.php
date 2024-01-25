@extends('najWeb.viewBase')

@section('title', 'Dashboards Usuários')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ env('APP_URL') }}ampleAdmin/assets/extra-libs/c3/c3.min.css">

    <style>
        .btn-light-dropdown {
            border: 1px solid #d2e3ee;
            border-radius: 2px;
            box-shadow: none;
            background-color: #ffffffe3 !important;
            width: 10px !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
@endsection

@section('active-layer', 'usuarios')

@section('content')

<div class="page-content container-fluid scrollable" style="height: 100%; overflow-y: auto; padding: 5px;">
    <div class="row m-0">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-center">Todas as operações (Últimos 12 meses)</h4>
                    <div id="dashboardGeral"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('scripts')
    <script src="{{ env('APP_URL') }}js/dashboardUsuarioGeral.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/assets/extra-libs/c3/d3.min.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/assets/extra-libs/c3/c3.min.js"></script>
@endsection