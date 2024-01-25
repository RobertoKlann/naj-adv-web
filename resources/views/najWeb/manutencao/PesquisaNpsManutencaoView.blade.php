@extends('najWeb.viewBase')

@section('title', 'Pesquisa Nps')

@section('css')
@endsection

@section('content')

<div class="email-app font-12" style="height: 100%">
    <div class="left-part" style="height: 100%; width:15%">
        <div class="scrollable ps-container ps-theme-default" style="height:100%;">
            <div class="divider"></div>
            <ul class="list-group nav-left-naj">
                <li class="list-group-item cursor-pointer" onclick="window.location.href = '{{ env('APP_URL') }}naj/pesquisa/nps'">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Consulta de todas as pesquisas realizadas">
                        <i class="mr-2 fas fa-search"></i>
                        Pesquisa
                    </a>
                </li>
                <li class="list-group-item cursor-pointer option-selected" onclick="redirectNpsTabCadastro();">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Cadastro de pesquisas">
                        <i class="mr-2 fas fa-plus"></i>
                        Cadastro
                    </a>
                </li>
                <li class="list-group-item cursor-pointer" onclick="redirectNps('usuarios');">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Usuários Participantes da Pesquisa">
                        <i class="mr-2 fas fa-users"></i>
                        Usuários <span class="badge badge-warning badge-rounded badge-pendente-nps float-right" id="badge-pendentes-nps"></span>
                    </a>
                </li>
            </ul>
            <div class="ps-scrollbar-x-rail" style="left: 0px; bottom: 0px;"><div class="ps-scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps-scrollbar-y-rail" style="top: 0px; right: 3px;"><div class="ps-scrollbar-y" tabindex="0" style="top: 0px; height: 0px;"></div>
            </div>
        </div>
    </div>
    <div class="right-part" style="height: 100%; width:85%">
        <div class="card-custom-naj">
            <div id="bloqueio-modal-manutencao-nps" class="loader loader-default"></div>
            <div class="header-custom-naj-card">
                PESQUISA NPS: [INCLUINDO...]
            </div>
            <div class="body-custom-naj-card">
                <form class="form-horizontal needs-validation" novalidate="" id="form-pesquisa-nps">

                    <input type="hidden" class="form-control" id="isUpdate">

                    <div class="form-group row" hidden="">
                        <label for="id" class="col-3 control-label text-right label-center">Código</label>
                        <div class="col-8">
                            <div class="input-group">
                                <input type="text" class="form-control" name="id" id="id" placeholder="Código...">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="descricao" class="col-sm-2 pl-0 control-label label-center">Descrição</label>
                        <div class="col-8">
                            <div class="input-group">
                                <input type="text" class="form-control" name="descricao" id="descricao" required="">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="pergunta" class="col-sm-2 pl-0 text-right label-center">Pergunta</label>
                        <div class="col-8">
                            <div class="input-group">
                                <textarea class="form-control" name="pergunta" id="pergunta" required=""></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="data_hora_inclusao" class="col-sm-2 pl-0 text-right label-center">Data Inclusão</label>
                        <div class="col-sm-3">
                            <div class="input-group">
                                <input type="datetime-local" name="data_hora_inclusao" id="data_hora_inclusao" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="data_hora_inicio" class="col-sm-2 pl-0 text-right label-center">Data Inicio</label>
                        <div class="col-sm-3">
                            <div class="input-group">
                                <input type="datetime-local" name="data_hora_inicio" id="data_hora_inicio" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="range_max" class="col-sm-2 pl-0 text-right label-center">Nota máxima</label>
                        <div class="col-sm-3">
                            <div class="input-group">
                                <select class="form-control" name="range_max" id="range_max" required="">
                                    <option value="" selected="" disabled="">-- Selecionar --</option>
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="range_min_info" class="col-sm-2 pl-0 text-right label-center">Nota miníma informação</label>
                        <div class="col-sm-3">
                            <div class="input-group">
                                <input type="text" name="range_min_info" id="range_min_info" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="range_max_info" class="col-sm-2 pl-0 text-right label-center">Nota maxíma informação</label>
                        <div class="col-sm-3">
                            <div class="input-group">
                                <input type="text" name="range_max_info" id="range_max_info" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="situacao" class="col-sm-2 pl-0 text-right label-center">Situação</label>
                        <div class="col-3">
                            <div class="input-group">
                                <select class="form-control" name="situacao" id="situacao" required="">
                                    <option value="" selected="" disabled="">-- Selecionar --</option>
                                    <option value="A">Ativo</option>
                                    <option value="B">Baixado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                </form>
            </div>
            <div class="footer-custom-naj-card">
                <label class="col-sm-2 pl-0 text-right label-center"></label>
                <button type="button" id="gravarPesquisaNps" class="btn btnLightCustom" title="Gravar">
                    <i class="fas fa-save"></i>
                    Gravar&nbsp;
                </button>
                <button type="submit" class="btn btnLightCustom" onclick="newResearch();" title="Incluir nova pesquisa">
                    <i class="fas fa-plus"></i>
                    Nova&nbsp;&nbsp;&nbsp;
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')

<script src="{{ env('APP_URL') }}js/pesquisaNps.js"></script>

<script>
    addClassCss('selected', '#sidebar-nps');
</script>

@endsection