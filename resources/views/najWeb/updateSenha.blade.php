<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        <link href="{{ env('APP_URL') }}ampleAdmin/dist/css/style.min.css" rel="stylesheet">
        <link href="{{ env('APP_URL') }}ampleAdmin/assets/libs/jquery-steps/jquery.steps.css" rel="stylesheet">
        <link href="{{ env('APP_URL') }}ampleAdmin/assets/libs/jquery-steps/steps.css" rel="stylesheet">
        <link href="{{ env('APP_URL') }}imagens/logo-naj-2020_N - Cópia.png" rel="shortcut icon" type="image/vnd.microsoft.icon" />        
        <link rel="stylesheet" href="{{ env('APP_URL') }}naj-datatable/styles/alert.css">
        <link rel="stylesheet" href="{{ env('APP_URL') }}naj-datatable/styles/loading.css">
        <link rel="stylesheet" href="{{ env('APP_URL') }}naj-datatable/styles/scrollbar.css">
        <link href="{{ env('APP_URL') }}css/app.css" rel="stylesheet">

        <script src="{{ env('APP_URL') }}naj-datatable/src/sweetalert2.min.js"></script>
        <script src="{{ env('APP_URL') }}naj-datatable/src/alerts.js"></script>
        <title>Atualização de dados</title>

        <style>
            .round, body {
                font-weight: 400;
            }
        </style>
    </head>
    <body>
        <div class="main-wrapper">
            <div class="preloader">
                <div class="lds-ripple">
                    <div class="lds-pos"></div>
                    <div class="lds-pos"></div>
                </div>
            </div>

            <div id="main-atualizar-dados" class="auth-wrapper d-flex no-block justify-content-center align-items-center" style="background:url({{ env('APP_URL') }}ampleAdmin/assets/images/big/auth-bg.jpg) no-repeat center center;">
                <div class="card-atualizar-dados shadow-lg">
                    <div class="p-0 wizard-content" style="height: 100%;">
                        <div id="bloqueio-atualizar-dados" class="loader loader-default" data-half></div>
                        <div class="tab-wizard wizard-circle naj-scrollable" style="height: 100%;">
                            <!-- Step 1 -->
                            <h6>Atualizar Dados</h6>
                            <section>
                                <div class="col-12">
                                    <div class="row pl-4">
                                        <div class="col-12">
                                            <form class="form-horizontal needs-validation" id="form-usuario-perfil" novalidate="">
                                                <input class="d-none" type="text" name="id" value="{{ Auth::user()->id }}">
                                                <input class="d-none" type="text" name="usuario_tipo_id" id="usuario_tipo_id"  value="{{ Auth::user()->usuario_tipo_id }}">
                                                <input class="d-none" type="text" name="status" id="status"  value="{{ Auth::user()->status }}">
                                                <input class="d-none" type="text" name="data_inclusao" id="data_inclusao"  value="{{ Auth::user()->data_inclusao }}">

                                                <div class="form-group row">
                                                    <label for="cpf" class="col-sm-2 control-label label-center pr-2">CPF</label>
                                                    <div class="col-sm-3 input-alterar-dados">
                                                        <div class="input-group">
                                                            <input type="text" id="cpf" name="cpf" class="form-control mascaracpf" onkeypress="return onlynumber();" required="" value="{{ Auth::user()->cpf }}" disabled>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="nome" class="col-sm-2 control-label label-center pr-2">Nome</label>
                                                    <div class="col-sm-9 input-alterar-dados">
                                                        <div class="input-group">
                                                            <input type="text" name="nome" class="form-control" required=""  value="{{ Auth::user()->nome }}">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="apelido" class="col-sm-2 control-label label-center pr-2">Apelido</label>
                                                    <div class="col-sm-9 input-alterar-dados">
                                                        <div class="input-group">
                                                            <input type="text" name="apelido" class="form-control" required="" value="{{ Auth::user()->apelido }}">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="email_recuperacao" class="col-sm-2 control-label label-center pr-2">E-mail</label>
                                                    <div class="col-sm-9 input-alterar-dados">
                                                        <div class="input-group">
                                                            <input type="email" name="email_recuperacao" class="form-control" placeholder="example@gmail.com" required="" value="{{ Auth::user()->email_recuperacao }}">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="mobile_recuperacao" class="col-sm-2 control-label label-center pr-2">Número Móvel</label>
                                                    <div class="col-sm-3 input-alterar-dados">
                                                        <div class="input-group">
                                                            <input type="text" name="mobile_recuperacao" class="form-control mascaracelular" maxlength="16" onkeypress="return onlynumber();" required="" value="{{ Auth::user()->mobile_recuperacao }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Step 2 -->
                            <h6>Redefinir Senha</h6>
                            <section>
                                <div class="row mt-4" id="content-update-senha-atualizacao-dados">
                                    <form class="form-horizontal mt-3" id="loginform" style="width: 65%; margin-top: -4% !important;">
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fas fa-key"></i></span>
                                            </div>
                                            <input type="password" id="password_nova" name="password_nova" class="form-control form-control-lg" placeholder="Digite a Nova Senha" aria-describedby="basic-addon1">
                                        </div>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon2"><i class="fas fa-key"></i></span>
                                            </div>
                                            <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control form-control-lg" placeholder="Confirme a Nova Senha" aria-describedby="basic-addon1">
                                        </div>
                                        <input class="d-none" type="text" name="id" value="{{ Auth::user()->id }}">

                                        <div id="content-avisos-atualizar-dados">
                                            <h5 style="font-weight: 500 !important;">Dicas para criar uma senha mais segura:</h5>
                                            <p class="m-0">* Combine letras maiúsculas e minúsculas, símbolos e números.</p>
                                            <p class="m-0">* Não use informações pessoais como data de nascimento ou seu nome.</p>
                                            <p class="m-0">* Para maior segurança, é obrigatório informar uma senha diferente da anterior.</p>
                                            <div class="custom-control custom-checkbox" style="margin-left: -5px;">
                                                <input class="custom-control-input" type="checkbox" id="check-permissao" name="check-permissao" checked="true">
                                                &emsp;
                                                <label class="custom-control-label" for="check-permissao" id="label-permissao"></label>
                                            </div>
                                        </div>

                                        <input type="hidden" name="_method" value="POST">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    </form>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="{{ env('APP_URL') }}js/jquery.js"></script>
        <script src="{{ env('APP_URL') }}js/axios.js"></script>
        <script src="{{ env('APP_URL') }}js/NajFunctions.js"></script>
        <script src="{{ env('APP_URL') }}naj-datatable/src/functions.js"></script>
        <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/jquery/dist/jquery.min.js"></script>
        <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/popper.js/dist/umd/popper.min.js"></script>
        <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/jquery-steps/build/jquery.steps.js"></script>
        <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/jquery-validation/dist/jquery.validate.min.js"></script>
        <script src="{{ env('APP_URL') }}/js/input-mask/jquery.inputmask.js"></script>
        <script src="{{ env('APP_URL') }}js/input-mask/jquery.inputmask.date.extensions.js"></script>
        <script src="{{ env('APP_URL') }}js/input-mask/jquery.inputmask.extensions.js"></script>
        <script src="{{ env('APP_URL') }}js/jQuery-Mask-Plugin/jquery.mask.min.js"></script>
        <script>
            const baseURL      = "{{ env('APP_URL') }}" + "{{ env('APP_ALIAS') }}" + "/";
            const urlNajAntigo = "{{ env('URL_NAJ_ANTIGO') }}";
            const urlLoginNajAntigo = "{{ env('URL_LOGIN_NAJ_ANTIGO') }}";

            $(window).on('load', () => {
                $('#label-permissao')[0].innerHTML = `Autorizo a NAJ SISTEMAS EM INFORMÁTICA a entrar em contato utilizando minhas informações como: E-MAIL e TELEFONE MÓVEL para tratar de assuntos pertinentes ao sistema NAJ.`;

                identificador = sessionStorage.getItem('@NAJ_WEB/identificadorEmpresa');
                if(!identificador) {
                    //Busca o identificador
                    axios({
                        method: 'get',
                        url: `${baseURL}empresas/identificador`
                    }).then(response => {
                        if(!response.data) return;

                        sessionStorage.setItem('@NAJ_WEB/identificadorEmpresa', response.data);
                    });

                    //Busca os dados da empresa
                    axios({
                        method: 'get',
                        url: `${baseURL}empresas/getNomeFirstEmpresa`
                    }).then(response => {
                        if (!response.data) return;
                    });
                }
            });

            $('[data-toggle="tooltip"]').tooltip();
            $(".preloader").fadeOut();
            $('#to-recover').on("click", function() {
                $("#loginform").slideUp();
                $("#recoverform").fadeIn();
            });

            //Configurando os steps do novo atendimento
            $(".tab-wizard").steps({
                headerTag: "h6",
                bodyTag: "section",
                transitionEffect: "fade",
                titleTemplate: '<span class="step">#index#</span> #title#',
                labels: {
                    previous: "Voltar",
                    next: "Próximo",
                    finish: "Confirmar"
                },
                onFinished: async function(event, currentIndex) {
                    let checkedPermissao = $('#check-permissao')[0].checked;

                    if(!checkedPermissao)
                        return NajAlert.toastWarning("Você deve MARCAR a caixa que AUTORIZA a NAJ SISTEMAS EM INFORMÁTICA fazer contato com você!");

                    loadingStart('bloqueio-atualizar-dados');

                    let empresa = sessionStorage.getItem('@NAJ_WEB/identificadorEmpresa');
                    let dados = {
                        'novaSenha'          : $('[name=password_nova]').val(),
                        'password'           : $('[name=password_nova]').val(),
                        'najWeb'             : 1,
                        'codigo_pessoa'      : empresa,
                        'pessoa_codigo'      : empresa,
                        'senha_provisoria'   : 'N',
                        'id'                 : $('[name=id]').val(),
                        'usuario_tipo_id'    : $('[name=usuario_tipo_id]').val(),
                        'nome'               : $('[name=nome]').val(),
                        'cpf'                : $('[name=cpf]').val(),
                        'apelido'            : $('[name=apelido]').val(),
                        'login'              : $('[name=email_recuperacao]').val(),
                        'email_recuperacao'  : $('[name=email_recuperacao]').val(),
                        'mobile_recuperacao' : $('[name=mobile_recuperacao]').val().replace(/\D+/g, ''),
                        'status'             : $('[name=status]').val(),
                        'data_inclusao'      : $('[name=data_inclusao]').val(),
                        'usuarioVeioDoCpanel': true,
                        'updateSenhaProvisoria': true,
                        'items' : [
                            {
                                "pessoa_codigo": empresa,
                                "usuario_id"   : 0
                            }
                        ]
                    };

                    if(!dados.nome || !dados.cpf || !dados.apelido || !dados.email_recuperacao || !dados.mobile_recuperacao) {
                        NajAlert.toastWarning("Clique em VOLTAR e informe todos os dados!");
                        loadingDestroy('bloqueio-atualizar-dados');
                        return;
                    }

                    //Validando o formulário
                    if(!$('#confirmar_senha').val() || !dados.novaSenha)  {
                        NajAlert.toastWarning("É necessário informar as duas senhas para realizar alteração!");
                        loadingDestroy('bloqueio-atualizar-dados');
                        return;
                    }

                    //Validando se as senhas são diferentes
                    if($('#confirmar_senha').val() != dados.novaSenha)  {
                        NajAlert.toastWarning("Confirme a senha corretamente!");
                        loadingDestroy('bloqueio-atualizar-dados');
                        return;
                    }

                    if(dados.novaSenha.length < 4) {
                        NajAlert.toastWarning("A nova senha deve conter no minimo 4 digitos, sendo números e letras!");
                        loadingDestroy('bloqueio-atualizar-dados');
                        return;
                    }

                    if(!/\d/.test(dados.novaSenha)) {
                        NajAlert.toastWarning("A nova senha deve conter no minimo 4 digitos, sendo números e letras!");
                        loadingDestroy('bloqueio-atualizar-dados');
                        return;
                    }

                    if(!/[a-z]/.test(dados.novaSenha)) {
                        NajAlert.toastWarning("A nova senha deve conter no minimo 4 digitos, sendo números e letras!");
                        loadingDestroy('bloqueio-atualizar-dados');
                        return;
                    }

                    if(!/(?=(?:.*?[0-9]){1})/.test(dados.novaSenha)) {
                        NajAlert.toastWarning("A nova senha deve conter no minimo 4 digitos, sendo números e letras!");
                        loadingDestroy('bloqueio-atualizar-dados');
                        return;
                    }

                    if(!validaCampoEmail()) {
                        loadingDestroy('bloqueio-atualizar-dados');
                        NajAlert.toastWarning('O campo E-mail deve ser um email válido!');
                        return;
                    }

                    axios({
                        method: 'put',
                        url   : `${baseURL}usuarios/atualizarDados/${btoa(JSON.stringify({id: $('input[name=id]').val()}))}`,
                        data  : dados
                    })
                    .then(response => {
                        loadingDestroy('bloqueio-atualizar-dados');
                        if(response.data.mensagem == "Senha antiga incorreta!") {
                            NajAlert.toastWarning(response.data.mensagem);
                            return;
                        }

                        if(response.data.mensagem == "Login já existente!") {
                            NajAlert.toastWarning(response.data.mensagem);
                            loadingDestroy('bloqueio-atualizar-dados');
                            return;
                        }

                        if(response.data.mensagem == "Não foi possível alterar o usuário! O limite de usuários do tipo informado foi atingido!") {
                            NajAlert.toastWarning(response.data.mensagem);
                            loadingDestroy('bloqueio-atualizar-dados');
                            return;
                        }

                        if(response.data.mensagem && response.data.mensagem.mensagem) {
                            NajAlert.toastWarning(response.data.mensagem.mensagem);
                            loadingDestroy('bloqueio-atualizar-dados');
                            return;
                        }

                        sessionStorage.removeItem('@NAJ_WEB/identificadorEmpresa');

                        //Verificando se é para jogar para o naj antigo
                        if(urlLoginNajAntigo) {
                            window.location.href = `${urlNajAntigo}`;
                        } else {
                            window.location.href = `${baseURL}home`;
                        }
                        
                    }).catch(e => {
                        if(e.response.data.mensagem == "Senha antiga incorreta") {
                            NajAlert.toastWarning("Senha antiga incorreta!");
                            loadingDestroy('bloqueio-atualizar-dados');
                            return;
                        }

                        if(e.response.data.mensagem == "Login já existente!") {
                            NajAlert.toastWarning("Login já existente!");
                            loadingDestroy('bloqueio-atualizar-dados');
                            return;
                        }

                        loadingDestroy('bloqueio-atualizar-dados');
                        NajAlert.toastWarning("Erro ao alterar a senha, tente novamente mais tarde!");
                    });
                }
            });

            $('.actions .disabled').addClass('d-none')
            $('.actions .disabled').addClass('first-step');
            $('.actions').addClass('footer-steps-naj');
            $('.actions ul').addClass('ul-footer-steps-naj');

            if(navigator.userAgent.match(/iPhone/i) || navigator.userAgent.match(/Android/i) || navigator.userAgent.match(/Windows Phone/i)) {
                $('.content')[0].style.height = '47vh';
                $('.content')[0].style.overflowY = 'auto';
                $('.content').addClass('naj-scrollable ');
            } else {
                $('.content')[0].style.maxHeight = '62vh';
                $('.content')[0].style.paddingTop = '10px';
                $('.content')[0].style.overflowY = 'auto';

                $('.content').addClass('naj-scrollable');
            }

            if($('[name=nome]').val())
                    $('[name=apelido]').val($('[name=nome]').val().split(' ')[0]);

            $('[name=nome]').on('change', () => {
                if($('[name=nome]').val())
                    $('[name=apelido]').val($('[name=nome]').val().split(' ')[0]);
            });

            $('.mascaracelular').mask("(00) 0 0000-0000", {placeholder: "(00) 0 0000-0000"});
            $('.mascaracpf').mask('000.000.000-00', {placeholder: "___.___.___-__"});
        </script>
        <script src="{{ env('APP_URL') }}js/updateSenha.js"></script>
    </body>
</html>