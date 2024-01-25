const naj = new Naj('Perfil', null);

//---------------------- Functions -----------------------//
$(document).ready(function() {
    onLoadPerfil();

    $('#label-permissao')[0].innerHTML = `Autorizo a NAJ SISTEMAS EM INFORMÁTICA a entrar em contato utilizando minhas informações como: E-MAIL e TELEFONE MÓVEL para tratar de assuntos pertinentes ao sistema NAJ.`;

    $('#submitEditarPerfil').on('click', function(e) {
        e.preventDefault();

        let checkedPermissao = $('#check-permissao')[0].checked;

        if(!checkedPermissao)
            return NajAlert.toastWarning("Você deve MARCAR a caixa que AUTORIZA A NAJ SISTEMAS EM INFORMÁTICA fazer contato com você!");

        if(!validaCampoLogin()) {
            loadingDestroy('bloqueio-atualizar-dados');
            NajAlert.toastWarning('O campo login deve ser um email válido!');
            return;
        }

        if(!validaCampoEmail()) {
            loadingDestroy('bloqueio-atualizar-dados');
            NajAlert.toastWarning('O campo E-mail deve ser um email válido!');
            return;
        }

        updateUsuario();
    });

    $('#submitUpdateSenha').on('click', function(e) {
        e.preventDefault();

        updatePasswordUsuario();
    });

    if($('[name=nome]').val())
            $('[name=apelido]').val($('[name=nome]').val().split(' ')[0]);

    $('[name=nome]').on('change', () => {
        if($('[name=nome]').val())
            $('[name=apelido]').val($('[name=nome]').val().split(' ')[0]);
    });
});

async function onLoadPerfil() {
    let response = await naj.getData(`${baseURL}usuarios/show/${btoa(JSON.stringify({id: idUsuarioLogado}))}`);
    naj.loadData('#form-usuario-perfil', response);
    $('.mascaracelular').val(response.mobile_recuperacao).trigger('input');
    $('input[name=id]').val(idUsuarioLogado);
}

async function updateUsuario() {
    loadingStart('bloqueio-atualizar-dados');
    empresa = sessionStorage.getItem('@NAJ_WEB/identificadorEmpresa');
    let dados = {
        'id'                 : $('input[name=id]').val(),
        'usuario_tipo_id'    : $('input[name=usuario_tipo_id]').val(),
        'login'              : $('input[name=login]').val(),
        'email_recuperacao'  : $('input[name=email_recuperacao]').val(),
        'mobile_recuperacao' : $('input[name=mobile_recuperacao]').val().replace(/\D+/g, ''),
        'nome'               : $('input[name=nome]').val(),
        'apelido'            : $('input[name=apelido]').val(),
        'cpf'                : $('input[name=cpf]').val(),
        'codigo_pessoa'      : empresa,
        'pessoa_codigo'      : empresa,
        "najWeb"             : 1,
        "items" : [
            {
                "pessoa_codigo": empresa,
                "usuario_id"   : 0
            }
        ]
    };

    let response = await naj.updateData(`${baseURL}usuarios/${btoa(JSON.stringify({id: $('input[name=id]').val()}))}`, dados);

    if(!response) {
        loadingDestroy('bloqueio-atualizar-dados');
        NajAlert.toastWarning("Não foi possível atualizar os dados, tente novamente mais tarde!");
    } else {
        loadingDestroy('bloqueio-atualizar-dados');
        if(response.mensagem == 'Login já existente!') {
            NajAlert.toastWarning(response.mensagem);
            return;
        }

        NajAlert.toastSuccess("Dados atualizados com sucesso!");
    }

}

async function updatePasswordUsuario() {
    let dados = {
        'senhaAntiga': $('[name=senhaAntiga]').val(),
        'novaSenha'  : $('[name=novaSenha]').val(),
        'najWeb'     : 1
    };

    loadingStart('bloqueio-atualizar-dados');

    //Validando o formulário
    if(!dados.senhaAntiga || !dados.novaSenha)  {
        NajAlert.toastWarning("É necessário informar as duas senhas para realizar alteração!");
        loadingDestroy('bloqueio-atualizar-dados');
        return;
    }

    //Validando se as senhas são diferentes
    if(dados.senhaAntiga == dados.novaSenha)  {
        NajAlert.toastWarning("A nova senha deve ser diferente da antiga!");
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

    await axios({
        method: 'put',
        url   : `${baseURL}usuarios/password/${btoa(JSON.stringify({id: $('input[name=id]').val()}))}`,
        data  : dados
    })
    .then(response => {
        NajAlert.toast(response.data.mensagem);
        loadingDestroy('bloqueio-atualizar-dados');
    }).catch(e => {
        NajAlert.toast("Erro ao alterar a senha, tente novamente mais tarde!");
        loadingDestroy('bloqueio-atualizar-dados');
    });
}

function validaCampoEmail() {
    let usuario = $('[name=email_recuperacao]').val().substring(0, $('[name=email_recuperacao]').val().indexOf("@")),
        dominio = $('[name=email_recuperacao]').val().substring($('[name=email_recuperacao]').val().indexOf("@") + 1, $('[name=email_recuperacao]').val().length);

    if ((usuario.length >=1) && (dominio.length >=3) && (usuario.search("@")==-1) && (dominio.search("@")==-1) 
            && (usuario.search(" ")==-1) && (dominio.search(" ")==-1) && (dominio.search(".")!=-1) && (dominio.indexOf(".") >=1)
            && (dominio.lastIndexOf(".") < dominio.length - 1))
    {
        return true;
    }

    return false;
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

//Mascaras
$('.mascaracelular').mask("(00) 0 0000-0000", {placeholder: "(00) 0 0000-0000"});
$('.mascaracpf').mask('000.000.000-00', {placeholder: "___.___.___-__"});