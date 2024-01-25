@extends('najWeb.viewBase')

@section('title', 'Configuração E-mail')

@section('css')
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
                <li class="list-group-item cursor-pointer" onclick="onClickMenuUsuario('permissoes');">
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
                <li class="list-group-item cursor-pointer option-selected" onclick="onClickMenuUsuario('smtp');">
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
    <div id="loading-smtp-usuario" class="loader loader-default" data-half></div>
    <div class="right-part content-pai-nav-right-naj" id="content-table-smtp">
        <div class="card-custom-naj">
            <div class="header-custom-naj-card" id="headerSmtpUsuario">
                Configuração do E-mail
            </div>
            <div class="body-custom-naj-card naj-scrollable" id="body-card-smtp">
                <form class="form-horizontal needs-validation" id="form-smtp-usuario" novalidate="">
                    <div class="form-group row">
                        <label for="smtp_host" class="col-sm-1 pl-0 control-label label-center">Host SMTP</label>
                        <div class="col-sm-6">
                            <div class="input-group">
                                <input type="text" name="smtp_host" id="smtp_host" class="form-control" required="">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="smtp_login" class="col-sm-1 pl-0 control-label label-center">Login</label>
                        <div class="col-sm-6">
                            <div class="input-group">
                                <input type="text" name="smtp_login" id="smtp_login" class="form-control" required="">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="smtp_senha" class="col-sm-1 pl-0 control-label label-center">Senha</label>
                        <div class="col-sm-6">
                            <div class="input-group">
                                <input type="password" name="smtp_senha" id="smtp_senha" class="form-control"  required="">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="smtp_porta" class="col-sm-1 pl-0 control-label label-center">Porta</label>
                        <div class="col-sm-2">
                            <div class="input-group">
                                <input type="text" onkeypress="return onlynumber();" name="smtp_porta" id="smtp_porta" class="form-control"  required="">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="smtp_ssl" class="col-sm-1 pl-0 control-label label-center">SSL/TLS</label>
                        <div class="col-sm-2">
                            <div class="input-group">
                                <select id="smtp_ssl" name="smtp_ssl" class="form-control">
                                    <option value="S">Sim</option>
                                    <option value="N">Não</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="_method" value="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                </form>
            </div>
            <div class="footer-custom-naj-card">
                <label for="usuario_tipo_id" class="col-sm-2 pl-0 control-label label-center" style="margin-left: 6px;"></label>
                <button type="button" id="gravarSmtpUsuario" class="btn btnLightCustom" title="Gravar">
                    <i class="fas fa-save"></i>
                    Gravar&nbsp;
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script src="{{ env('APP_URL') }}js/usuario.js"></script>
    <script src="{{ env('APP_URL') }}js/smtpUsuario.js"></script>
@endsection