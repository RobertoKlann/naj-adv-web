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
                    <div class="m-0 pt-1 pb-2 mb-dropdown-item-divider d-flex">
                        <div class="btn-group dropright show pl-0">
                            <button type="button" class="btn btn-light btn-light-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-v" act="1"></i></button>
                            <div class="dropdown-menu pb-0" id="dropdown-chart">
                                <a class="dropdown-item mb-dropdown-item-divider item-filter-data-chat-selected" href="#" onclick="loadDataChart(10, this);" style="margin-top: -10px;">Top 10</a>
                                <a class="dropdown-item mb-dropdown-item-divider" href="#" onclick="loadDataChart(20, this);">Top 20</a>
                                <a class="dropdown-item mb-dropdown-item-divider" href="#" onclick="loadDataChart(30, this);">Top 30</a>
                                <a class="dropdown-item mb-dropdown-item-divider" href="#" onclick="loadDataChart(40, this);">Top 40</a>
                                <a class="dropdown-item mb-dropdown-item-divider" href="#" onclick="loadDataChart(50, this);">Top 50</a>
                                <a class="dropdown-item" href="#" onclick="loadDataChart(100, this);">Todos</a>
                            </div>
                        </div>
                        <h4 class="card-title text-center" style="left: 35%;">Operações por usuário (Últimos 12 meses)</h4>
                    </div>
                    <div id="operationByUser"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('scripts')
    <script src="{{ env('APP_URL') }}js/dashboardByUsuario.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/assets/extra-libs/c3/d3.min.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/assets/extra-libs/c3/c3.min.js"></script>
@endsection