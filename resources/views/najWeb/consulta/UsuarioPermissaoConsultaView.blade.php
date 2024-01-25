@extends('najWeb.viewBase')

@section('title', 'Usuario - Permissões')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ env('APP_URL') }}ampleAdmin/assets/libs/select2/dist/css/select2.min.css">
@endsection

@section('active-layer', 'usuario')

@section('content')

<div class="email-app font-12" style="height: 100%">
    <a id="icone-nav-menu-usuarios" class="icone-nav-menu-usuarios"><i class="fas fa-bars mr-2"></i> Menu</a>
    <div class="left-part content-pai-nav-left-naj">
        <div class="nav-menu-usuarios scrollable ps-container ps-theme-default" style="height:100%;">
            <div class="divider"></div>            
            <ul class="nav-list-usuarios list-group nav-left-naj">
                <li class="list-group-item cursor-pointer" onclick="window.location.href = '{{ env('APP_URL') }}naj/usuarios'">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Consulta de usuários">
                        <i class="mr-2 fas fa-search"></i>
                        Pesquisa
                    </a>
                </li>
                <li class="list-group-item cursor-pointer" onclick="onClickMenuUsuario('edit');">
                    <a class="link-nav-left-naj link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Cadastro de Usuários">
                        <i class="mr-2 fas fa-plus"></i>
                        Cadastro
                    </a>
                </li>
                <li class="list-group-item cursor-pointer option-selected" onclick="onClickMenuUsuario('permissoes');">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Permissões do Usuário">
                        <i class="mr-2 fas fa-lock"></i>
                        Permissões
                    </a>
                </li>
                <li class="list-group-item cursor-pointer" onclick="onClickMenuUsuario('relacionamentos');">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Pessoas Relacionadas ao Usuário">
                        <i class="mr-2 fas fa-users"></i>
                        Pessoas
                    </a>
                </li>
                <li class="list-group-item cursor-pointer" onclick="onClickMenuUsuario('dispositivos');">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Dispositivos do Usuário">
                        <i class="mr-2 fas fa-mobile-alt"></i>
                        Dispositivos
                    </a>
                </li>
                <li class="list-group-item cursor-pointer" onclick="onClickMenuUsuario('smtp');">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Configuração do E-mail do Usuário">
                        <i class="mr-2 fas fa-envelope"></i>
                        Configuração E-mail
                    </a>
                </li>
                <li class="list-group-item cursor-pointer" onclick="onClickMenuUsuario('estatisticas');">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Estatísticas do Usuário">
                        <i class="mr-2 fas fa-chart-bar"></i>
                        Estatísticas
                    </a>
                </li>
            </ul>
            <div class="ps-scrollbar-x-rail" style="left: 0px; bottom: 0px;"><div class="ps-scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps-scrollbar-y-rail" style="top: 0px; right: 3px;"><div class="ps-scrollbar-y" tabindex="0" style="top: 0px; height: 0px;"></div>
            </div>
        </div>
    </div>
    <div class="right-part ml-auto" id="content-table-permissao">
        <div id="div-bloqueio" class="loader loader-default" data-half></div>
        <div class="card" id="card-permissao-usuario">
            <div class="card-header-naj" id="header-card-permissao-usuario">
                <div class="row ml-1">
                    <button type="button" class="btn" data-toggle="dropdown" style="max-height: 40px; box-shadow: none;">
                        <i class="fas fa-ellipsis-v" act="1"></i>
                    </button>
                    <div class="dropdown-menu pt-0 pb-0">
                        <a class="dropdown-item text-capitalize" href="javascript:void(0)" onclick="onClickCopiarPerfil();"><i class="far fa-copy"  style="margin-left: -5px;"></i>&nbsp; Copiar permissões</a>
                        <div class="dropdown-item text-capitalize custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input ml-2" id="all-modulos-matriz">
                            <label class="custom-control-label ml-2" for="all-modulos-matriz">Todos Módulos</label>
                        </div>
                        <div class="dropdown-item text-capitalize custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input ml-2" id="modulos-especiais">
                            <label class="custom-control-label ml-2" for="modulos-especiais">Módulos Especiais</label>
                        </div>
                    </div>
                    <p id="headerPermissao" style="margin-top: 8px;"></p>
                </div>
            </div>
            <div class="content-pai-permissao page-content container-fluid note-has-grid mb-2">
                <ul class="nav nav-pills p-3 bg-white rounded-pill align-items-center" id="content-divisao">
                    
                </ul>
                <div class="tab-content bg-transparent div-pai-bg-note-full">
                    <div id="note-full-container" class="note-has-grid row content-all-divisao">
                        
                        <!-- Aqui vai as outras divisões -->
                    </div>
                    <hr class="m-0 p-0">
                    <div class="d-flex align-items-center p-2 content-footer-permissao-custom">
                        <div class="ml-auto">
                            <button class="btn btn-info text-white btn-rounded py-2 px-3" onclick="onClickGravarPermissao();"><i class="fas fa-save"></i>  Gravar</button>
                        </div>
                    </div>
				</div>
            </div>
        </div>
    </div>
</div>

@component('najWeb.componentes.modalCopiarPermissaoUsuario')
@endcomponent

@endsection

@section('scripts')
    <script src="{{ env('APP_URL') }}js/usuario.js"></script>
    <script src="{{ env('APP_URL') }}js/usuarioPermissao.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/select2/dist/js/select2.full.min.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/select2/dist/js/select2.min.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/dist/js/pages/forms/select2/select2.init.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/assets/extra-libs/prism/prism.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/dist/js/pages/notes/notes.js"></script>
@endsection