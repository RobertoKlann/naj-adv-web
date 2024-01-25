@extends('najWeb.viewBase')

@section('title', 'Usuario - Manutenção')

@section('css')
@endsection

@section('content')

<div class="email-app font-12" style="height: 100%">
    <a id="icone-nav-menu-usuarios" class="icone-nav-menu-usuarios"><i class="fas fa-bars mr-2"></i> Menu</a>
    <div class="left-part content-pai-nav-left-naj">
        <div class="nav-menu-usuarios scrollable ps-container ps-theme-default" style="height:100%;">
            <ul class="nav-list-usuarios list-group nav-left-naj">
                <li class="list-group-item cursor-pointer" onclick="window.location.href = '{{ env('APP_URL') }}naj/usuarios'">
                    <a class="link-nav-left-naj list-group-item-action tooltip-naj" data-toggle="tooltip" data-placement="right" title="Consulta de usuários">
                        <i class="mr-2 fas fa-search"></i>
                        Pesquisa
                    </a>
                </li>
                <li class="list-group-item cursor-pointer option-selected" onclick="onClickMenuUsuario('edit');">
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
    <div id="loading-usuario" class="loader loader-default" data-half></div>
    <div class="right-part content-pai-nav-right-naj" id="content-outside-manutencao-usuario">
        <div class="card-custom-naj">
            <div class="header-custom-naj-card" id="headerUsuario">
                Cadastro de Usuários
            </div>
            <div class="body-custom-naj-card naj-scrollable" id="body-card-usuarios">
                <form class="form-horizontal needs-validation" id="form-usuario" novalidate="">
                    <input type="hidden" name="is_update_usuario">
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="senha_alteracao_cadastro" id="senha_alteracao_cadastro">

                    <div class="form-group row">
                        <label for="usuario_tipo_id" class="col-sm-2 pl-0 control-label label-center">Tipo de Usuário</label>
                        <div class="col-sm-4">
                            <div class="input-group">
                                <select class="form-control" id="usuario_tipo_id" name="usuario_tipo_id" required="" onchange="onChangeTipoUsuario();">
                                    <option value="1">Administrador</option>
                                    <option value="2">Usuário</option>
                                    <option value="3">Cliente</option>
                                    <option value="4">Parceiro</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row" id="row-cpf">
                        <label for="cpf" class="col-sm-2 pl-0 control-label label-center">CPF</label>
                        <div class="col-sm-4">
                            <div class="input-group">
                                <input type="text" name="cpf" class="form-control mascaracpf" onkeypress="return onlynumber();" onchange="onChangeCpf();"  required="">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="nome" class="col-sm-2 pl-0 control-label label-center">Nome</label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <input type="text" name="nome" id="input-nome-pesquisa" class="form-control" onchange="onChangeNome();"  required="" onkeypress="getPessoaFromNomeUsuario(this);">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row p-0 m-0">
                        <label for="select" class="col-sm-2 pl-0 control-label label-center p-0 m-0"></label>
                        <div class="col-sm-8">
                            <div class="input-group content-select-ajax-rel" id="content-select-ajax-naj-relacionamento" style="width: 98.6%; margin-top: -17px; margin-left: -10px;">
                                
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="apelido" class="col-sm-2 pl-0 control-label label-center">Apelido</label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <input type="text" name="apelido" class="form-control"  required="">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="login" class="col-sm-2 pl-0 control-label label-center">Login</label>
                        <div class="col-sm-4">
                            <div class="input-group">
                                <input type="text" name="login" class="form-control"  required="">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row rowPassword">
                        <label for="password" class="col-sm-2 pl-0 control-label label-center">Senha</label>
                        <div class="col-sm-4">
                            <div class="input-group-prepend">
                                <input type="text" name="password" class="form-control" placeholder="Clique em Gerar Senha" readonly>
                                <i class="far fa-clone icon-search-input-naj cursor-pointer" data-toggle="tooltip" data-placement="top" title="Clique para copiar senha" id="copy-password"></i>
                            </div>
                        </div>
                        <button type="button" id="gerarSenha" class="btn btn-info" title="Gerar Senha">
                            Gerar Senha
                        </button>
                    </div>

                    <div class="form-group row">
                        <label for="email_recuperacao" class="col-sm-2 pl-0 label-center control-label">E-mail</label>
                        <div class="col-sm-4">
                            <div class="input-group">
                                <input type="email" name="email_recuperacao" class="form-control" placeholder="example@gmail.com">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="mobile_recuperacao" class="col-sm-2 pl-0 label-center control-label">Número Móvel</label>
                        <div class="col-sm-4">
                            <div class="input-group">
                                <input type="text" name="mobile_recuperacao" class="form-control mascaracelular" maxlength="16" onkeypress="return onlynumber();" required="">
                            </div>
                        </div>
                    </div>

                    

                    <div class="form-group row">
                        <label for="status" class="col-sm-2 pl-0 control-label label-center">Status</label>
                        <div class="col-sm-4">
                            <div class="input-group">
                                <select id="statusUser" name="status" class="form-control" onchange="onChangeStatusUsuario();">
                                    <option value="A">Ativo</option>
                                    <option value="B">Baixado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="data_inclusao" class="col-sm-2 pl-0 control-label label-center">Data Inclusão</label>
                        <div class="col-sm-3">
                            <div class="input-group">
                                <input type="date" name="data_inclusao" class="form-control" value="{{date('d-m-Y')}}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="ultimo_acesso" class="col-sm-2 pl-0 control-label label-center">Ultimo Acesso</label>
                        <div class="col-sm-3">
                            <div class="input-group">
                                <input type="datetime-local" id="utlimo_acesso" name="ultimo_acesso" class="form-control" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row" style="display: none;" id="div-databaixa">
                        <label for="data_baixa" class="col-sm-2 pl-0 control-label label-center">Data Baixa</label>
                        <div class="col-sm-3">
                            <div class="input-group">
                                <input type="date" name="data_baixa" class="form-control" value="{{date('d-m-Y')}}">
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="_method" value="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                </form>
            </div>
            <div class="footer-custom-naj-card">
                <label for="usuario_tipo_id" class="col-sm-2 pl-0 control-label label-center" style="margin-left: 6px;"></label>
                <button type="button" id="gravarUsuario" class="btn btnLightCustom" title="Gravar">
                    <i class="fas fa-save"></i>
                    Gravar&nbsp;
                </button>
                <button type="submit" class="btn btnLightCustom" onclick="onClickNovoUsuario();" title="Incluir novo registro">
                    <i class="fas fa-plus"></i>
                    Novo&nbsp;&nbsp;&nbsp;
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script src="{{ env('APP_URL') }}js/usuario.js"></script>
@endsection