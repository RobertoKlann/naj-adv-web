@extends('najWeb.viewBase')

@section('title', 'Espaço em Disco')

@section('css')

    <style>
    </style>

@endsection

@section('active-layer', 'espaco-disco')

@section('content')

<div class="page-content container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-center" id="titleChart">Utilização de espaço em disco dos anexos</h4>
                    <div>
                        <canvas id="espacoDisco" width="400" height="130"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('scripts')
    <script src="{{ env('APP_URL') }}js/dashboardEspacoDisco.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/Chart.js/dist/Chart.min.js"></script>
@endsection