//---------------------- Functions -----------------------//

const naj = new Naj();
$(document).ready(function() {

    sessionStorage.removeItem('@NAJWEB/tokenUser');
    $('#form-login').submit(function(e) {
        e.preventDefault();
        loadingStart();

        let dados = {
            'login'           : $('#login').val(),
            'password'        : $('#password').val(),
            'usuario_tipo_id' : [5,6]
        };

        if(!dados.login || !dados.password) $('#divResultadoLogin')[0].innerHTML = '<p class="font-weight-bold"><i class="fas fa-ban"></i>&nbsp;&nbsp;&nbsp;Informe o login e senha!</p>';
        
        axios({
            method : 'post',
            url    : `${cpanelUrl}auth/login`,
            data   : dados,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Access-Control-Allow-Origin': '*',
                'Access-Control-Allow-Credentials': true
            }
        })
        .then(response => {
            if(response.data.naj.user && response.data.naj.accessToken) {
                sessionStorage.setItem('@NAJWEB/tokenUser', response.data.naj.accessToken);
                loadingDestroy();

                //Troca de page
                $('#cadastro').addClass('active');
                $('a[id="a-cadastro"]').addClass('active');
                $('#recuperacao').removeClass('active');
                $('a[id="a-recuperacao"]').removeClass('active');
            } else {
                NajAlert.toastError('Usuário invalido! Deve ser utilizado um usuário do tipo Administrador/Usuário CPANEL!');
                loadingDestroy();
            }
        }).catch(error => {
            if(error.message == "Request failed with status code 401") {
                console.clear();
                NajAlert.toastError('Usuário invalido! Deve ser utilizado um usuário do tipo Administrador CPANEL!');
                loadingDestroy();
            } else {
                NajAlert.toastError('Não foi possível fazer a requisição solicitada! Algo de errado com o servidor aconteceu!');
                loadingDestroy();
            }
        });
    });

    $('#form-empresa').submit(function(e) {
        e.preventDefault();
        loadingStart();

        //Valida se foi informado o código da empresa
        if(!$('#codigoEmpresa').val()) {
            NajAlert.toastError('Você deve informar o código da empresa!');
            loadingDestroy();
            return;
        }

        token = sessionStorage.getItem('@NAJWEB/tokenUser');
        if(!token) {
            loadingDestroy();
            NajAlert.toastError('Token inválido!');
            return;
        }

        axios({
            method  : 'get',
            url     : `${cpanelUrl}pessoa/${btoa(JSON.stringify({'codigo' : $('#codigoEmpresa').val()}))}`,
            headers: {
                "Authorization"    : "Bearer " + token,
                "X-Requested-With" : "XMLHttpRequest",
                "Content-Type"     : "application/json"
            }
        })
        .then(response => {
            if(response.data.naj) {
                $('#name-empresa')[0].innerHTML = `Dados cadastrais da empresa: ${response.data.naj.nome}`;
                //Carrega os dados da empresa
                $('#codigo').val(response.data.naj.codigo);
                $('#nome').val(response.data.naj.nome);
                $('#cep').val(response.data.naj.cep);
                $('#cidade').val(response.data.naj.cidade);
                $('#estado').val(response.data.naj.uf);
                $('#bairro').val(response.data.naj.bairro);
                $('#complemento').val(response.data.naj.complemento);
                $('#numero').val(response.data.naj.numero);
                $('#endereco').val(response.data.naj.endereco);

                if(!response.data.naj.cnpj) {
                    $('#cpf').val(response.data.naj.cpf);
                    $('#divcpf').show();
                    $('#divcnpj').hide();
                } else {
                    $('#cnpj').val(response.data.naj.cnpj);
                    $('#divcpf').hide();
                    $('#divcnpj').show();
                }
                
                loadingDestroy();
            } else {
                if(response.data.naj == null) {
                    NajAlert.toastError('O código do cliente informado não existe!');
                } else if(response.data.naj.mensagem) {
                    NajAlert.toastError(`${response.data.naj.mensagem}!`);
                    $('#divResultadoEmpresa')[0].innerHTML = `<p class="font-weight-bold"><i class="fas fa-ban"></i>&nbsp;&nbsp;&nbsp;!</p>`;
                }
                loadingDestroy();
            }
        });
    });

    $('#form-cadastro-empresa').submit(function(e) {
        e.preventDefault();
        loadingStart();

        token = sessionStorage.getItem('@NAJWEB/tokenUser');

        if(!token) {
            NajAlert.toastError('Token inválido!');
            loadingDestroy();
            return;
        }

        let dados = {
            "codigo"         : $('#codigo').val(),
            "nome"           : $('#nome').val(),
            "cpf"            : ($('#cpf').val()) ? $('#cpf').val() : null,
            "cnpj"           : ($('#cnpj').val()) ? $('#cnpj').val() : null,
            "codigo_divisao" : 1,
            "cep"            : $('#cep').val(),
            "cidade"         : $('#cidade').val(),
            "uf"             : $('#estado').val(),
            "bairro"         : $('#bairro').val(),
            "numero"         : $('#numero').val(),
            "complemento"    : $('#complemento').val(),
            "endereco"       : $('#endereco').val(),
            "SECAO"          : 'CPANEL',
            "CHAVE"          : 'CLIENTE_ID',
            "VALOR"          : $('#codigo').val()
        };

        //Valida se foi informado os dados da empresa
        if(!dados.codigo || !dados.nome) {
            NajAlert.toastError('Você deve informar todos os campos acima!');
            loadingDestroy();
            return;
        }

        axios({
            method  : 'post',
            url     : 'install/empresas',
            data    : dados,
            headers: {
                "Authorization"    : "Bearer " + token,
                "X-Requested-With" : "XMLHttpRequest",
                "Content-Type"     : "application/json"
            }
        })
        .then(response => {
            if(response.data && response.data.model) {
                sessionStorage.setItem('@NAJWEB/empresa', JSON.stringify($('#codigo').val()));
                loadingDestroy();
                //Troca de page
                $('#finalizar').addClass('active');
                $('#cadastro').removeClass('active');
                $('a[id="a-finalizar"]').addClass('active');
                $('a[id="a-cadastro"]').removeClass('active');
            } else {
                NajAlert.toastError(`Não foi possível incluir a empresa! Verifique se já não existe uma empresa com o mesmo código ou se existe a informação do CPANEL no sys_config!`);
                loadingDestroy();
            }
        });
    });

    $('#form-usuario').submit(function(e) {
        e.preventDefault();
        loadingStart();
        empresa = JSON.parse(sessionStorage.getItem('@NAJWEB/empresa'));

        if(!empresa) {
            loadingDestroy();
            NajAlert.toastError('É necessário cadastrar a empresa primeiro!');
            return;
        }
        
        let dados = {
            "login"              : $('#login-user').val(),
            "password"           : $('#password-user').val(),
            "nome"               : $('#nome-user').val(),
            "apelido"            : $('#apelido-user').val(),
            "email_recuperacao"  : $('#email_recuperacao-user').val(),
            "status"             : 'A',
            "cpf"                : $('#cpf-user').val(),
            "data_inclusao"      : getDataAtual(),
            "usuario_tipo_id"    : 0,
            "tokenInstall"       : sessionStorage.getItem('@NAJWEB/tokenUser'),
            "codigo_pessoa"      : empresa,
            "pessoa_codigo"      : empresa,
            "najWeb"             : 1,
            "items" : [
                {
                    "pessoa_codigo": empresa,
                    "usuario_id"   : 0
                }
            ]
        };

        //Valida se foi informado os dados da empresa
        if(!dados.login || !dados.nome || !dados.password || !dados.apelido || !dados.cpf || !dados.email_recuperacao) {
            NajAlert.toastError(`Você deve informar todos os campos acima!`);
            loadingDestroy();
            return;
        }

        axios({
            method  : 'post',
            url     : 'install/usuarios?XDEBUG_SESSION_START',
            data    : dados
        })
        .then(response => {
            if(response.data && response.data.model) {
                if(!response || !response.data.model) return;

                sessionStorage.setItem('@NAJ_WEB/usuario_key', JSON.stringify(response.data.model));
                token = sessionStorage.getItem('@NAJWEB/tokenUser');
                loadingDestroy();

                //Faz a autenticação local, para entrar no sistema
                axios({
                    method  : 'post',
                    url     : 'auth/login',
                    data    : {'login' : response.data.model.login, 'password': $('#password-user').val(), 'status': 'A'},
                    headers: {
                        "Authorization"    : "Bearer " + token,
                        "X-Requested-With" : "XMLHttpRequest",
                        "Content-Type"     : "application/json"
                    }
                })
                .then(response => {
                    sessionStorage.removeItem('@NAJWEB/empresa');
                    window.location.href = 'naj/home';
                }).catch(error => {
                    NajAlert.toastError('A senha informada não confere com a senha desse mesmo usuário no CPANEL.');
                });
            } else {
                if(response.data) {
                    if(response.data.mensagem) {
                        NajAlert.toastError(response.data.mensagem);
                        loadingDestroy();
                        return;
                    }
                } else {
                    NajAlert.toastError(`Não foi possível incluir o usuário, verifique se existe uma licença para o tipo Supervisor ou se já não existe um usuário com o cpf informado!`);
                    loadingDestroy();
                }
            }
        });
    });
});

async function onChangeLogin() {
    loadingStart();
    token = sessionStorage.getItem('@NAJWEB/tokenUser');
    login = $('#login-user').val();

    if(!token || !login) {
        NajAlert.toastError(`Token/Login inválidos!`);
        loadingDestroy();
        return;
    } 

    result = await axios({
        method : 'get',
        url    : `${cpanelUrl}usuarios/login/${login}`,
        headers: {
            "Authorization"    : "Bearer " + token,
            "X-Requested-With" : "XMLHttpRequest",
            "Content-Type"     : "application/json"
        }
    })
    .then(response => {
        if(response.data && response.data.length >= 1) {
            $('#nome-user').val(response.data[0].nome);
            $('#apelido-user').val(response.data[0].apelido);
            $('#email_recuperacao-user').val(response.data[0].email_recuperacao);
            $('#cpf-user').val(response.data[0].cpf);
            $('#cpf-user')[0].disabled = true;
        }
        loadingDestroy();
    });
}

/**
 * Permite apenas caracteres númericos.
 */
function onlynumber(evt) {
    let theEvent = evt || window.event,
        key      = theEvent.keyCode || theEvent.which;

    key = String.fromCharCode(key);
    let regex = /^[0-9.]+$/;
    if (!regex.test(key)) {
        theEvent.returnValue = false;
        if (theEvent.preventDefault)
            theEvent.preventDefault();
    }
}

$('.mascaracpf').mask('000.000.000-00', {placeholder: "___.___.___-__"});
$('.mascaracep').mask('00.000-000', {placeholder: "__.___-___"});
$('.mascaracelular').mask('(00) 0 0000-0000', {placeholder: "(00) 0 0000-0000"});
$('.mascaracnpj').mask('00.000.000/0000-00', {placeholder: "__.___.___/____-__"});