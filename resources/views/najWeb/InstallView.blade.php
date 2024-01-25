<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="{{ env('APP_URL') }}ampleAdmin/dist/css/style.min.css" rel="stylesheet">
        <link href="{{ env('APP_URL') }}imagens/logo-naj-2020_N - Cópia.png" rel="shortcut icon" type="image/vnd.microsoft.icon" />
        <link rel="stylesheet" href="{{ env('APP_URL') }}naj-datatable/styles/loading.css">
        <link href="{{ env('APP_URL') }}css/install.css" rel="stylesheet">
        <link rel="stylesheet" href="{{ env('APP_URL') }}naj-datatable/styles/alert.css">
        <title>Naj - Install</title>
    </head>
    <body class="hold-transition login-page" style="background-color: #0d5aa5;">
        <div class="main-wrapper">
            <div class="preloader">
                <div class="lds-ripple">
                    <div class="lds-pos"></div>
                    <div class="lds-pos"></div>
                </div>
            </div>

            <div id="loading" class="loader loader-default" data-half></div>
            <div class="page-content container-fluid content-install">
                <ul class="nav nav-tabs manage-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#recuperacao" role="tab" id="a-recuperacao">
                            <span class="hidden-sm-up">
                                <h4><i class="fas fa-lock"></i></h4>
                            </span>
                            <span class="d-none d-md-block">Login</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#cadastro" role="tab" id="a-cadastro">
                            <span class="hidden-sm-up">
                                <h4><i class="fas fa-book"></i></h4>
                            </span>
                            <span class="d-none d-md-block">Cadastro Empresa</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#finalizar" role="tab" id="a-finalizar">
                            <span class="hidden-sm-up">
                                <h4><i class="fas fa-book"></i></h4>
                            </span>
                            <span class="d-none d-md-block">Cadastro Usuário</span>
                        </a>
                    </li>
                </ul>
                <!-- Tab panes -->
                <div class="tab-content tab-content-full">
                    <div class="tab-pane active content-full" id="recuperacao" role="tabpanel">
                        <div class="bg-light content-full">
                            <div class="user-box-wrapper p-4 d-flex no-block justify-content-center align-items-center">
                                <div class="user-box" style="margin-top: 5%; max-width: 300px !important;">
                                    <div id="loginform">
                                        <div class="logo">
                                            <h5 class="font-medium mb-3">Informe o login e senha há serem validados</h5>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <form class="form-horizontal mt-3" id="form-login">
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i id="iconCodigoAcesso" class="fas fa-user"></i></span>
                                                        </div>
                                                        <input type="text" name="login" id="login" class="form-control form-control-lg" aria-describedby="basic-addon1" placeholder="Informe seu login">
                                                    </div>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i id="iconCodigoAcesso" class="fas fa-lock"></i></span>
                                                        </div>
                                                        <input type="password" name="password" id="password" class="form-control form-control-lg" aria-describedby="basic-addon1" placeholder="Informe sua senha">
                                                    </div>
                                                    <div class="form-group text-center">
                                                        <div class="col-xs-12 pb-3">
                                                            <button class="btn btn-block btn-lg btn-info" type="submit">Validar</button>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="_method" value="POST">
                                                    <input type="hidden" id="token-login" name="_token" value="{{ csrf_token() }}">
                                                </form>
                                            </div>
                                            <div class="col-12" id="divResultadoLogin"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane content-full" id="cadastro" role="tabpanel">
                        <div class="bg-light content-full">
                            <div class="row ">
                                <div class="col-sm-12 col-md-5 col-lg-5 p-4" style="padding-right: 0px !important; padding-bottom: 0px !important">
                                    <div class="user-box-wrapper p-4 d-flex no-block justify-content-center align-items-center">
                                        <div class="user-box" style="margin-top: 10%; max-width: 300px !important;">
                                            <div id="loginform">
                                                <div class="logo">
                                                    <h5 class="font-medium mb-3">Informe o ID do cliente</h5>
                                                </div>
                                                <div class="row">
                                                    <div class="col-12">
                                                        <form class="form-horizontal mt-3" id="form-empresa">
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon1"><i id="iconCodigoAcesso" class="fas fa-user"></i></span>
                                                                </div>
                                                                <input type="text" name="codigoEmpresa" id="codigoEmpresa" class="form-control form-control-lg" aria-describedby="basic-addon1" placeholder="Informe o ID">
                                                            </div>
                                                            <div class="form-group text-center">
                                                                <div class="col-xs-12 pb-3">
                                                                    <button class="btn btn-block btn-lg btn-info" type="submit">Buscar</button>
                                                                </div>
                                                            </div>
                                                            <input type="hidden" name="_method" value="POST">
                                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                        </form>
                                                    </div>
                                                    <div class="col-12" id="divResultadoEmpresa"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-7 col-lg-7 p-4" style="padding-left: 0px !important; padding-bottom: 0px !important">
                                    <div class="user-box-wrapper p-4 d-flex no-block justify-content-center align-items-center">
                                        <div class="user-box col-sm-12 col-md-12 col-lg-12" style="margin-top: 10%;">
                                            <div class="logo">
                                                <h5 class="font-medium mb-3" id="name-empresa"></h5>
                                            </div>
                                            <div class="row">
                                                <div class="col-12" style="margin-top: -5px;">
                                                    <form class="mt-3" id="form-cadastro-empresa">
                                                        <div class="form-group row">
                                                            <label for="codigo" class="col-sm-2 control-label label-center">Código</label>
                                                            <div class="col-sm-4">
                                                                <div class="input-group">
                                                                    <input type="text" class="form-control" id="codigo" name="codigo">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="nome" class="col-sm-2 control-label label-center">Nome</label>
                                                            <div class="col-sm-10">
                                                                <div class="input-group">
                                                                    <input type="text" class="form-control" id="nome" name="nome" placeholder="Informe seu nome">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row" style="display: none;" id="divcpf">
                                                            <label for="cnpj" class="col-sm-2 control-label label-center">CPF</label>
                                                            <div class="col-sm-5">
                                                                <div class="input-group">
                                                                    <input type="text" class="form-control mascaracpf" name="cpf" id="cpf">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row" id="divcnpj">
                                                            <label for="cnpj" class="col-sm-2 control-label label-center">CNPJ</label>
                                                            <div class="col-sm-5">
                                                                <div class="input-group">
                                                                    <input type="text" class="form-control mascaracnpj" name="cnpj" id="cnpj">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <input type="text" name="cep" id="cep" class="form-control d-none">
                                                        <input type="text" name="cidade" id="cidade" class="form-control d-none">
                                                        <input type="text" name="estado" id="estado" class="form-control d-none">
                                                        <input type="text" name="bairro" id="bairro" class="form-control d-none">
                                                        <input type="text" name="complemento" id="complemento" class="form-control d-none">
                                                        <input type="text" name="numero" id="numero" class="form-control d-none">
                                                        <input type="text" name="endereco" id="endereco" class="form-control d-none">

                                                        <input type="hidden" name="_method" value="POST">
                                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                        <div class="form-group text-center">
                                                            <div class="col-lg-8 pb-3">
                                                                <button class="btn btn-block btn-lg btn-info" type="submit" style="margin-left: -15px !important;">Cadastrar</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                                <div class="col-12" id="divResultadoCadastroEmpresa"></div>
                                            </div>
                                        </div>
                                    </div>                                        
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tab-pane content-full" id="finalizar" role="tabpanel">
                        <div class="bg-light content-full">
                            <div class="p-3 tab-content-custom-finalizar">
                            <div class="user-box-wrapper d-flex no-block justify-content-center align-items-center">
                                <div class="user-box" style="min-width: 50%;">
                                    <div class="logo text-center">
                                        <h5 class="font-medium mb-3">Informe os dados do primeiro usuário</h5>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <form class="form-horizontal mt-3" id="form-usuario">
                                                <div class="form-group row">
                                                    <label for="login" class="col-sm-2 control-label label-center">Login</label>
                                                    <div class="col-sm-8">
                                                        <div class="input-group">
                                                            <input type="text" name="login-user" id="login-user" class="form-control" onchange="onChangeLogin();">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="password" class="col-sm-2 control-label label-center">Senha</label>
                                                    <div class="col-sm-8">
                                                        <div class="input-group">
                                                            <input type="password" name="password-user" id="password-user" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="nome" class="col-sm-2 control-label label-center">Nome</label>
                                                    <div class="col-sm-8">
                                                        <div class="input-group">
                                                            <input type="text" name="nome" id="nome-user" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="apelido" class="col-sm-2 control-label label-center">Apelido</label>
                                                    <div class="col-sm-8">
                                                        <div class="input-group">
                                                        <input type="text" id="apelido-user" name="apelido" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="cpf" class="col-sm-2 control-label label-center">CPF</label>
                                                    <div class="col-sm-8">
                                                        <div class="input-group">
                                                            <input type="text" name="cpf" id="cpf-user" class="form-control mascaracpf">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="email_recuperacao" class="col-sm-2 control-label label-center">Email Rec.</label>
                                                    <div class="col-sm-8">
                                                        <div class="input-group">
                                                            <input type="email" name="email_recuperacao" id="email_recuperacao-user" class="form-control" placeholder="example@gmail.com">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group text-center">
                                                    <div class="col-lg-8 pb-3">
                                                        <button class="btn btn-block btn-lg btn-info" type="submit" style="margin-left: 27%;">Cadastrar</button>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="_method" value="POST">
                                                <input type="hidden" id="token_cadastro_user" name="_token" value="{{ csrf_token() }}">
                                            </form>
                                        </div>
                                        <div class="col-12" id="divResultadoCadastroUsuario"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="{{ env('APP_URL') }}js/jquery.js"></script>
        <script src="{{ env('APP_URL') }}js/input-mask/jquery.inputmask.js"></script>
        <script src="{{ env('APP_URL') }}js/input-mask/jquery.inputmask.date.extensions.js"></script>
        <script src="{{ env('APP_URL') }}js/input-mask/jquery.inputmask.extensions.js"></script>
        <script src="{{ env('APP_URL') }}js/jQuery-Mask-Plugin/jquery.mask.min.js"></script>
        <script src="{{ env('APP_URL') }}js/Naj.js"></script>
        <script src="{{ env('APP_URL') }}js/NajFunctions.js"></script>
        <script src="{{ env('APP_URL') }}js/axios.js"></script>
        <script src="{{ env('APP_URL') }}naj-datatable/src/functions.js"></script>
        <script src="{{ env('APP_URL') }}js/install.js"></script>
        <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/jquery/dist/jquery.min.js"></script>
        <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/popper.js/dist/umd/popper.min.js"></script>
        <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="{{ env('APP_URL') }}naj-datatable/src/sweetalert2.min.js"></script>
        <script src="{{ env('APP_URL') }}naj-datatable/src/alerts.js"></script>
        <script>
            const cpanelUrl = "{{ env('CPANEL_URL') }}";
            $('[data-toggle="tooltip"]').tooltip();
            $(".preloader").fadeOut();
            $('#to-recover').on("click", function() {
                $("#loginform").slideUp();
                $("#recoverform").fadeIn();
            });
        </script>
    </body>
</html>