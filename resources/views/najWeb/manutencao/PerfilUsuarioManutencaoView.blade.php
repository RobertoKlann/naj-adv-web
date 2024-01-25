@extends('najWeb.viewBase')

@section('title', 'Perfil Usuário')

@section('css')
@endsection

@section('content')

<div id="bloqueio-atualizar-dados" class="loader loader-default" data-half></div>
<div class="row row-content-perfil">
    <div class="col-5 mr-0 pr-0">
        <div class="row content-pai-perfil position-relative content-alterar-perfil">
            <div class="col-12 content-header-perfil">
                <p>ALTERA DADOS DO USUÁRIO</p>
            </div>
            <div class="col-12 content-body-perfil scrollable">
                <div class="row">
                    <div class="col-12">
                        <form class="form-horizontal needs-validation" id="form-usuario-perfil" novalidate="">
                            <input type="hidden" name="id" id="id">
                            <input type="hidden" name="usuario_tipo_id" id="usuario_tipo_id">

                            <div class="form-group row">
                                <label for="cpf" class="col-sm-3 control-label label-center">CPF</label>
                                <div class="col-sm-6">
                                    <div class="input-group">
                                        <input type="text" name="cpf" class="form-control mascaracpf" onkeypress="return onlynumber();" required="" disabled>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="nome" class="col-sm-3 control-label label-center">Nome</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <input type="text" name="nome" class="form-control" required="">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="apelido" class="col-sm-3 control-label label-center">Apelido</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <input type="text" name="apelido" class="form-control"  required="">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="login" class="col-sm-3 control-label label-center">Login</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <input type="text" name="login" class="form-control"  required="">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="email_recuperacao" class="col-sm-3 control-label label-center">E-mail</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <input type="email" name="email_recuperacao" class="form-control" placeholder="example@gmail.com" required="">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="mobile_recuperacao" class="col-sm-3 control-label label-center pr-0">Número Móvel</label>
                                <div class="col-sm-5">
                                    <div class="input-group">
                                        <input type="text" name="mobile_recuperacao" class="form-control mascaracelular" maxlength="16" onkeypress="return onlynumber();" required="">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="custom-control custom-checkbox content-permissions-dados-naj">
                                    <input class="custom-control-input" type="checkbox" id="check-permissao" name="check-permissao" checked="true">
                                    &emsp;
                                    <label class="custom-control-label" for="check-permissao" id="label-permissao"></label>
                                </div>
                            </div>
                        </form>
                        <div class="form-group row">
                            <label for="mobile_recuperacao" class="col-sm-3 control-label label-center"></label>
                            <div class="col-sm-5">
                                <button type="submit" id="submitEditarPerfil" class="btn btn-md btn-info" title="Gravar" style="margin-bottom: 5px;">
                                    Confirmar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-5 ml-0 pl-0">
        <div class="row content-pai-perfil position-relative content-alterar-senha">
            <div class="col-12 content-header-perfil">
                <p>ALTERAR SENHA</p>
            </div>
            <div class="col-12 content-body-perfil scrollable content-body-senha">
                <div class="row">
                    <div class="col-12 box-alterar-senha-perfil">
                        <form action="">
                            <div class="form-group row">
                                <label for="password" class="col-sm-3 control-label label-center pl-3 pr-0">Senha Antiga</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="password" name="senhaAntiga" class="form-control" required="">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="password" class="col-sm-3 control-label label-center pl-3 pr-0">Nova Senha</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="password" name="novaSenha" class="form-control" required="">
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="form-group row">
                            <label for="password" class="col-sm-3 control-label label-center pl-3 pr-0"></label>
                            <div class="col-sm-8">
                                <button type="submit" id="submitUpdateSenha" class="btn btn-md btn-info" title="Confirmar" style="margin-bottom: 5px;">
                                    Confirmar
                                </button>    
                            </div>
                        </div>
                        <div style="font-size: 11px; margin-top: 10% !important;">
                            <h5 style="font-weight: 500 !important;">Dicas para criar uma senha mais segura:</h5>
                            <p class="m-0">* Combine letras maiúsculas e minúsculas, símbolos e números.</p>
                            <p class="m-0">* Não use informações pessoais como data de nascimento ou seu nome.</p>
                            <p class="m-0">* Para maior segurança, é obrigatório informar uma senha diferente da anterior.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script src="{{ env('APP_URL') }}js/perfilUsuario.js"></script>
@endsection