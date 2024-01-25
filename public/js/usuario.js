(isIndex('usuarios')) ? table = new UsuarioTable : table = false;
const naj = new Naj('Usuário', table);
let codigoUsuarioDono;
let codigoUsuarioReceber;
let inputElementCopiarCurrent;
let usuarioVeioDoCpanel = false;
let codigoPessoaRelUsuario = false;

//---------------------- Functions -----------------------//

$(document).ready(function() {
    
    if(isIndex('usuarios')) {
        sessionStorage.removeItem('@NAJ_WEB/usuario_key');
        sessionStorage.removeItem('@NAJ_WEB/usuario');
        table.render();
    }

    $('#icone-nav-menu-usuarios').click(() => {
		$('ul.nav-list-usuarios').addClass('open').slideToggle('200');
	});

    afterLoadTelaUsuario();
    
    $('#gravarUsuario').on('click', function(e) {
        e.preventDefault();
        let is_alterar = $('input[name=is_update_usuario]').val();
        usuario = JSON.parse(sessionStorage.getItem('@NAJ_WEB/usuario'));

        //Apenas usuário do tipo supervisor ou adm pode alterar ou incluir users. OBS: Qualquer coisa fala com Nelson, ele quem falou para validar isso!
        if(tipoUsuarioLogado != '0' && tipoUsuarioLogado != '1') {
            NajAlert.toastWarning('Apenas usuários do tipo Supervisor ou Administrador podem Incluir/Alterar usuários!');
            return;
        }

        if(is_alterar == "0" && !$('[name=password]').val() && !usuarioVeioDoCpanel) {
            NajAlert.toastWarning('Você deve gerar uma senha para o usuário!');
            return;
        }

        if(!validaForm()) {
            return;
        }

        let cpf = $('[name=cpf]').val().replace(/\D+/g, '');

        if(!isValidCPF(cpf) && cpf != '00000000000') {
            NajAlert.toastWarning('O CPF informado é inválido!');
            return;
        }

        if(!validaCampoLogin() && $('select[name=usuario_tipo_id]').val() != 3) {
            NajAlert.toastWarning('Campo LOGIN inválido!');
            return;
        }

        if($('select[name=usuario_tipo_id]').val() != 3 && !$('input[name=email_recuperacao]').val()) {
            NajAlert.toastWarning('Campo email é obrigatório!');
            return;
        }

        let dados = getDadosForm();
        
        createOrUpdateUsuario(dados, is_alterar);
    });

    //Esconde caixa do campo de pesquisa do copiar
    $('#content-outside').on('click', function() {
        $("#content-select-ajax-naj-input-nome-pesquisa-copiar-dono").hide();
        $("#content-select-ajax-naj-input-nome-pesquisa-copiar-receber").hide();
    });

    //Realiza a busca do copiar para o dono
    $('#input-nome-pesquisa-copiar-dono').on('click', function(element) {
        if(element.target.value.length < 3) {
            return;
        }
    
        setTimeout(async function() {
            result =  await searchDataCopiarPermissao(element.target.value);
            updateListaDataCopiarPermissao(result, element.target.id);
            inputElementCopiarCurrent = element.target.id;
        }, 500);
    });

    //Realiza a busca do copiar para o destinatário
    $('#input-nome-pesquisa-copiar-receber').on('click', function(element) {
        if(element.target.value.length < 3) {
            return;
        }
    
        setTimeout(async function() {
            result =  await searchDataCopiarPermissao(element.target.value);
            updateListaDataCopiarPermissao(result, element.target.id);
            inputElementCopiarCurrent = element.target.id;
        }, 500);
    });

    //Add novo relacionamento
    $('#content-select-ajax-naj-relacionamento').on('click', function(el) {
        onClickContentSelectAjaxPessoa(el);
    });

    //Esconde caixa do campo de pesquisa
    $('#content-outside-manutencao-usuario').on('click', function(element) {
        $('#content-select-ajax-naj-relacionamento').hide();
    });

    //RESETAR SENHA
    $('#gerarSenha').on('click', function(el) {
        let senhaProvisoria = getPassword();
        
        $('[name=password]').val(senhaProvisoria);
        NajAlert.toastWarning("Copie a senha gerada, repasse ao usuário e clique em GRAVAR!");
    });

    //COPIAR SENHA
    $('#copy-password').on('click', function(el) {
        $('[name=password]').select();
        document.execCommand('copy');
    });
});

function getRandomNum(lbound, ubound) {
    return (Math.floor(Math.random() * (ubound - lbound)) + lbound);
}

function getRandomChar() {
    var charSet = "012LMNOPQRS3abcde4567opq89fghnvwxyzABijklmCrstuDEVZ@WXY#KTU&-FGHIJ";

    return charSet.charAt(getRandomNum(0, charSet.length));
}

function getPassword() {
    var rc = "";
    if (length > 0) rc = rc + getRandomChar();

    for (var idx = 1; idx < 10; ++idx) {
        rc = rc + getRandomChar();
    }

    return rc;
}

async function afterLoadTelaUsuario() {
    limpaFormulario('#form-usuario');

    let hasPermission = perm('SenhaServicosAoCliente')

    if(tipoUsuarioLogado != '0' && tipoUsuarioLogado != '1' && !hasPermission) {
        $($('.nav-left-naj .list-group-item')[3]).hide()
    } else {
        $($('.nav-left-naj .list-group-item')[3]).show()
    }

    if(isCreate()) {
        sessionStorage.removeItem('@NAJ_WEB/usuario_key');
        sessionStorage.removeItem('@NAJ_WEB/usuario');
        $('input[name=is_update_usuario]').val(0);
        $('input[name=data_inclusao]').val(getDataAtual());
        $('[name=status]').val('A');
        $('#resetarSenha').remove();

        $('#headerUsuario')[0].innerHTML = `CADASTRO DE USUÁRIOS: INCLUINDO...`;
    } else if(isEdit()) {
        $('input[name=is_update_usuario]').val(1);
        usuario = JSON.parse(sessionStorage.getItem('@NAJ_WEB/usuario_key'));
        id      = (usuario.id) ? usuario.id : usuario;
        $('input[name=cpf]')[0].disabled = true;
        $('input[name=data_inclusao]')[0].disabled = true;        
        response = await naj.getData(`${baseURL}usuarios/show/${btoa(JSON.stringify({id: id}))}`);

        let codigo_pessoa_usuario = await naj.getData(`${baseURL}pessoas/cpf/${response.cpf}`);
        if(codigo_pessoa_usuario[0]) {
            codigoPessoaRelUsuario = codigo_pessoa_usuario[0].CODIGO;
            $('#row-cpf').append(`
                <i id="icon-ficha-pessoa-usuario" class="font-18 mdi mdi-open-in-new cursor-pointer text-dark mr-1" title="Código: ${codigo_pessoa_usuario[0].CODIGO}" data-toggle="tooltip" onclick="onClickFichaPessoa(${codigo_pessoa_usuario[0].CODIGO});" style="margin-top: 3px;"></i>
            `);

            $('#icon-ficha-pessoa-usuario').tooltip('update');
        }

        //Se for SUPERVISOR faz uns tratamento diferenciado
        if(response.usuario_tipo_id == 0) {
            desabilitaAllCampos();
            var option = document.createElement("option");
            option.appendChild(document.createTextNode('Supervisor'));
            option.value = 0;
            $('[name=usuario_tipo_id]').append(option);
        }

        //Se for CLIENTE faz uns tratamento diferenciado
        if(response.usuario_tipo_id == 3) {
            $('[name=login]')[0].disabled = true;
        }

        naj.loadData('#form-usuario', response);
        $('.mascaracelular').val(response.mobile_recuperacao).trigger('input');
        onChangeStatusUsuario();
        sessionStorage.setItem('@NAJ_WEB/usuario', JSON.stringify(response));
        $('#headerUsuario')[0].innerHTML = `CADASTRO DE: ${response.nome}`;

        $('input[name=senha_alteracao_cadastro]').val(response.senha_alteracao_cadastro);

        //Setando o ultimo acesso
        let dataHora = response.ultimo_acesso;
        if(dataHora) {
            let data     = dataHora.split(' ')[0],
                hora     = dataHora.split(' ')[1],
                hour     = hora.split(':')[0],
                minutes  = hora.split(':')[1];

            $('#utlimo_acesso').val(`${data}T${hour}:${minutes}`);
        }
    }

    addClassCss('selected', '#sidebar-usuario');
    $('.nav-list-usuarios').children().removeClass('tabActiveNaj');
    addClassCss('tabActiveNaj', '#tabCadastroUsuario');
}

async function createOrUpdateUsuario(dados, is_alterar) {
    loadingStart('loading-usuario');
    if(is_alterar == "1") {
        await naj.update(`${baseURL}usuarios/${btoa(JSON.stringify({id: $('input[name=id]').val()}))}?XDEBUG_SESSION_START`, dados);
        $('[name=password]').val('');
    } else {
        response = await naj.store(`${baseURL}usuarios`, dados);

        loadingDestroy('loading-usuario');
        if(!response) return;

        sessionStorage.setItem('@NAJ_WEB/usuario_key', JSON.stringify(response));
        sessionStorage.setItem('@NAJ_WEB/usuario'    , JSON.stringify(response));
        window.location.href = `${baseURL}usuarios/edit`;
    }
    loadingDestroy('loading-usuario');
}

async function onClickNovoUsuario() {
    window.location.href = `${baseURL}usuarios/create`;
}

function getDadosForm() {
    let empresa = sessionStorage.getItem('@NAJ_WEB/identificadorEmpresa');
    let valueDataBaixa = $('input[name=data_baixa]').val();

    if($('select[name=status]').val() == 'A')
        valueDataBaixa = '0001-01-01';

    let dados = {
        'id'                 : $('input[name=id]').val(),
        'usuario_tipo_id'    : $('select[name=usuario_tipo_id]').val(),
        'login'              : $('input[name=login]').val(),
        'password'           : $('input[name=password]').val(),
        'status'             : $('select[name=status]').val(),
        'data_inclusao'      : $('input[name=data_inclusao]').val(),
        'data_baixa'         : valueDataBaixa,
        'email_recuperacao'  : $('input[name=email_recuperacao]').val(),
        'mobile_recuperacao' : $('input[name=mobile_recuperacao]').val().replace(/\D+/g, ''),
        'nome'               : $('input[name=nome]').val(),
        'apelido'            : $('input[name=apelido]').val(),
        'data_nascimento'    : $('input[name=data_nascimento]').val(),
        'cpf'                : $('input[name=cpf]').val(),
        'ultimo_acesso'      : `${$('input[name=data_inclusao]').val()} 00:00:01`,
        'senha_alteracao_cadastro' : $('input[name=senha_alteracao_cadastro]').val(),
        'codigo_pessoa_rel_usuario': codigoPessoaRelUsuario,
        'codigo_pessoa'      : empresa,
        'pessoa_codigo'      : empresa,
        'najWeb'             : 1,
        'usuarioVeioDoCpanel': usuarioVeioDoCpanel,
        'items' : [
            {
                "pessoa_codigo": empresa,
                "usuario_id"   : 0
            }
        ]
    };

    return dados;
}

function onChangeTipoUsuario() {
    let tipo_usuario = $('#usuario_tipo_id').val();

    if(!tipo_usuario) return;

    if(tipo_usuario == '3') {
        $('[name=login]')[0].disabled = true;
        $('[name=login]').val($('[name=cpf]').val());
    } else {
        $('[name=login]')[0].disabled = false;
    }
}

async function onChangeCpf() {
    let cpf        = $('[name=cpf]').val();
    let cpfValidar = $('[name=cpf]').val().replace(/\D+/g, '');

    if(!isValidCPF(cpfValidar) && cpfValidar != '00000000000') {
        NajAlert.toastWarning('O CPF informado é inválido!');
        return;
    }

    if(!cpf) return;

    loadingStart('loading-usuario');
    response = await naj.getData(`${baseURL}usuarios/cpf/${cpf}`);

    if(response.status_code == 401) {
        loadingDestroy('loading-usuario');
        NajAlert.toastWarning("Token vazio ou inválido!");
        return;
    }

    if(!response[0]) {
        response = await naj.getData(`${baseURL}pessoas/cpf/${cpf}`);

        if(response[0]) {
            $('[name=nome]').val(response[0].NOME);
            let apelido = response[0].NOME.split(' ');
            $('[name=apelido]').val(apelido[0]);
        }

        onChangeTipoUsuario();

        $('#gerarSenha')[0].disabled = false;
        loadingDestroy('loading-usuario');
        return;
    }

    //SE CHEGOU ATÉ AQUI ENTÃO É PQ VEIO DO CPANEL
    if(response[0].usuario_tipo_id == 5 || response[0].usuario_tipo_id == 6) {        
        loadingDestroy('loading-usuario');
        NajAlert.toastWarning("Não é possível cadastrar o usuário que possue esse cpf! Tipo de usuário inválido!");
        $('[name=cpf]').val('');
        $('[name=cpf]').focus();
        return;
    }

    let tipo_usuario_selected = $('[name=usuario_tipo_id]').val();
    naj.loadData('#form-usuario', response[0]);

    $('input[name=data_inclusao]').val(getDataAtual());
    $('[name=usuario_tipo_id]').val(tipo_usuario_selected);
    $('.mascaracelular').val(response[0].mobile_recuperacao).trigger('input');

    onChangeTipoUsuario();

    //Limpando a senha
    $('[name=password]').val('');
    $('#gerarSenha')[0].disabled = true;
    usuarioVeioDoCpanel = true;
    loadingDestroy('loading-usuario');
}

function onChangeNome() {
    let nome = $('input[name=nome]').val().split(' ');

    $('[name=apelido]').val(nome[0]);
}

function onClickMenuUsuario(rotina) {
    (sessionStorage.getItem('@NAJ_WEB/usuario_key') == null ) ? null : window.location.href = `${baseURL}usuarios/${rotina}`;
}

function exibeModalPermissaoUsuario(el) {
    $('#modal-incluir-permissao-usuario').modal('show');
    $(el).tooltip('hide');
}

/**
 * Esconde/Mostra o campo data de baixa conforme seleção.
 */
function onChangeStatusUsuario() {
    let oDivDataBaixa = $("#div-databaixa"),
        oSelectStatus = $("#statusUser");

    if (oSelectStatus.val() === "A") {
        oDivDataBaixa.hide();
        $('input[name=data_baixa]').val("");
    } else {
        oDivDataBaixa.show();
        if(!$('input[name=data_baixa]').val())
            $('input[name=data_baixa]').val(getDataAtual());
    }
}

function desabilitaAllCampos() {
    $('[name=nome]')[0].disabled = true;
    $('[name=apelido]')[0].disabled = true;
    $('[name=cpf]')[0].disabled = true;
    $('[name=login]')[0].disabled = true;
    $('[name=data_inclusao]')[0].disabled = true;
    $('[name=data_baixa]')[0].disabled = true;
    $('[name=email_recuperacao]')[0].disabled = true;
    $('[name=mobile_recuperacao]')[0].disabled = true;
    $('[name=usuario_tipo_id]')[0].disabled = true;
    $('[name=status]')[0].disabled = true;
}

//////////////////////Funções utilizadas para o copiar perfil ///////////////////////////////

function exibeModalCopiarPermissao(el) {
    $('#modal-copiar-permissao-usuario').modal('show');
    $(el).tooltip('hide');

    $('#input-nome-pesquisa-copiar-receber').val('');
    $('#input-nome-pesquisa-copiar-dono').val('');
    $("#content-select-ajax-naj").hide();
    codigoUsuarioDono    = 0;
    codigoUsuarioReceber = 0;
}

function getUsuarios(element) {
    if(element.value.length < 3) {
        return;
    }

    setTimeout(async function() {
        result =  await searchDataCopiarPermissao(element.value);
        updateListaDataCopiarPermissao(result, element.id);
        inputElementCopiarCurrent = element.id;
    }, 500);
}

async function searchDataCopiarPermissao(value) {
    let content = '';

    filterUser = {val: value, op: "C", col: "nome", origin: btoa({val: value, op: "C", col: "nome"})};
    response   = await naj.getData(`${baseURL}usuarios/paginate?f=${btoa(JSON.stringify(filterUser))}`);
    if(response.resultado.length > 0) {
        for(var i = 0; i < response.resultado.length; i++) {
            content+= `
                <div class="row row-full content-rows-copiar" style="flex-wrap: nowrap !important;" onclick="onClickRowUsuarioSearch(this);">
                    <div class="col-sm-0 col-codigo" style="display: none;">${response.resultado[i].id}</div>
                    <div class="col-sm-8 col-name">${response.resultado[i].nome}</div>
                    <div class="col-sm-4 col-cpf">${response.resultado[i].cpf}</div>
                </div>
            `;
        }
    } else {
        content += '<p class="text-center">Nenhum registro encontrado...</p>';
    }

    return content;
}

function updateListaDataCopiarPermissao(data, id) {
    $('#content-select-ajax-naj-' + id)[0].innerHTML = "";
    $('#content-select-ajax-naj-' + id).append(data);
    $('#content-select-ajax-naj-' + id).show();
}

function onClickRowUsuarioSearch(el) {
    if(inputElementCopiarCurrent == 'input-nome-pesquisa-copiar-receber') {
        codigoUsuarioReceber = el.firstElementChild.textContent;
    } else {
        codigoUsuarioDono = el.firstElementChild.textContent;
    }

    $(`#${inputElementCopiarCurrent}`).val(el.childNodes[3].textContent);
}

async function onClickCopiarPermissao() {
    loadingStart('bloqueio-copiar-permissao');
    let usuario = await naj.getData(`${baseURL}usuarios/show/${btoa(JSON.stringify({id: codigoUsuarioReceber}))}`);

    //Se for cliente não deixa dar permissão!
    if(usuario.usuario_tipo_id == '3') {
        NajAlert.toastSuccess("Não é possível dar permissão para usuário tipo cliente!");
        loadingDestroy('bloqueio-copiar-permissao');
        return;
    } else if(usuario.mensagem) {
        NajAlert.toastSuccess(usuario.mensagem);
        loadingDestroy('bloqueio-copiar-permissao');
        return;
    }

    //Apenas usuário do tipo supervisor ou adm podem copiar. OBS: Qualquer coisa fala com Nelson, ele quem falou para validar isso!
    if(tipoUsuarioLogado != '0' && tipoUsuarioLogado != '1') {
        NajAlert.toastWarning('Apenas usuários do tipo Supervisor ou Administrador podem Copiar as permissões');
        return;
    }
    
    //Se não tiver os usuários
    if(!codigoUsuarioReceber || !codigoUsuarioDono) {
        NajAlert.toastWarning('Informe os usuários corretamente!');
        loadingDestroy('bloqueio-copiar-permissao');
        return;
    }

    pessoaDono    = await naj.getData(`${baseURL}pessoa/usuario/${codigoUsuarioDono}`);
    pessoaReceber = await naj.getData(`${baseURL}pessoa/usuario/${codigoUsuarioReceber}`);

    if(!pessoaDono[0] || !pessoaReceber[0]) {
        NajAlert.toastError('Não foi possível copiar as permissões, tente novamente mais tarde!');
        loadingDestroy('bloqueio-copiar-permissao');
        return;
    }

    response = await naj.postData(`${baseURL}usuarios/permissoes/copiar`, {"pessoa_codigo": pessoaDono[0].pessoa_codigo, "pessoa_codigo_destino" : pessoaReceber[0].pessoa_codigo});

    if(response.mensagem) {
        NajAlert.toastSuccess("Permissões copiadas com sucesso!");
        loadingDestroy('bloqueio-copiar-permissao');
    } else {
        NajAlert.toastError("Não foi possível copiar as permissões!");

        loadingDestroy('bloqueio-copiar-permissao');
    }
}

////////////////FUNCTIONS PARA O INPUT DE PESQUISA DA PESSOA NO CAMPO NOME////////////////
function getPessoaFromNomeUsuario(element) {
    if(element.value.length < 3) {
        return;
    }

    setTimeout(async function() {
        result =  await searchDataPessoa(element.value);
        updateListaData(result);
    }, 500);
}

async function searchDataPessoa(value) {
    let content = '';
    response = await naj.getData(`${baseURL}pessoas/getPessoasFisicaByNome/${value}`);
    if(response.data.length > 0) {
        for(var i = 0; i < response.data.length; i++) {
            content+= `
                <div class="row row-full content-rows-permissao" style="flex-wrap: nowrap !important;">
                    <div class="col-sm-0 col-codigo" style="display: none;">${response.data[i].pessoa_codigo}</div>
                    <div class="col-sm-6 col-name">${response.data[i].nome}</div>
                    <div class="col-sm-3 col-cpf">${(response.data[i].cpf) ? response.data[i].cpf : response.data[i].cnpj}</div>
                    <div class="col-sm-3 col-cidade">${(response.data[i].cidade == null) ? '' : response.data[i].cidade}</div>
                </div>
            `;
        }
    } else {
        content += '<p class="text-center">Nenhum registro encontrado...</p>';
    }

    return content;
}

function updateListaData(data) {
    $('#content-select-ajax-naj-relacionamento')[0].innerHTML = "";
    $('#content-select-ajax-naj-relacionamento').append(data);
    $('#content-select-ajax-naj-relacionamento').show();
}

async function onClickContentSelectAjaxPessoa(el) {
    var pai = el.target.parentElement;
    if(!pai.getElementsByClassName('col-codigo')[0]) return;

    $('[name=cpf]').val(pai.getElementsByClassName('col-cpf')[0].innerText);
    $('[name=nome]').val(pai.getElementsByClassName('col-name')[0].innerText);
    onChangeNome();
    onChangeCpf();

    $("#content-select-ajax-naj-relacionamento").hide();
}

//////////////////FIM FUNCTIONS BUSCA PESSOA CAMPO NOME/////////////////////

/**
 * Valida o cpf passado por parâmetro.
 * 
 * @param {*} number
 * @returns boolean
 */
function isValidCPF(number) {
    var sum;
    var rest;
    sum = 0;
    if (number == "00000000000") return false;

    for (i=1; i<=9; i++) sum = sum + parseInt(number.substring(i-1, i)) * (11 - i);
    rest = (sum * 10) % 11;

    if ((rest == 10) || (rest == 11))  rest = 0;
    if (rest != parseInt(number.substring(9, 10)) ) return false;

    sum = 0;
    for (i = 1; i <= 10; i++) sum = sum + parseInt(number.substring(i-1, i)) * (12 - i);
    rest = (sum * 10) % 11;

    if ((rest == 10) || (rest == 11))  rest = 0;
    if (rest != parseInt(number.substring(10, 11) ) ) return false;
    return true;
}

function onClickRestarSenha() {    
    let senhaProvisoria = Math.random().toString(36).slice(-8);
    $('[name=nova-senha-provisoria]').val(senhaProvisoria);
    
    $('#modal-resetar-senha-usuario').modal('show');
}

function validaCampoLogin() {
    let usuario = $('[name=login]').val().substring(0, $('[name=login]').val().indexOf("@")),
        dominio = $('[name=login]').val().substring($('[name=login]').val().indexOf("@") + 1, $('[name=login]').val().length);

        if ((usuario.length >=1) && (dominio.length >=3) && (usuario.search("@")==-1) && (dominio.search("@")==-1) 
              && (usuario.search(" ")==-1) && (dominio.search(" ")==-1) && (dominio.search(".")!=-1) && (dominio.indexOf(".") >=1)
              && (dominio.lastIndexOf(".") < dominio.length - 1))
        {
            return true;
        }

        return false;
}

function onClickFichaPessoa(codigo) {
    window.open(`${najAntigoUrl}?idform=pessoas&pessoaid=${codigo}`);
}

//Mascaras
$('.mascaracelular').mask("(00) 0 0000-0000", {placeholder: "(00) 0 0000-0000"});
$('.mascaracpf').mask('000.000.000-00', {placeholder: "___.___.___-__"});