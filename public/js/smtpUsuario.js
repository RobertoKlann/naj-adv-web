//---------------------- Functions -----------------------//

$(document).ready(function() {

    loadDadosSmtpUsuario();

    $('#gravarSmtpUsuario').on('click', function(e) {
        e.preventDefault();

        //Apenas usuário do tipo supervisor ou adm pode usar essa rotina. OBS: Qualquer coisa fala com Nelson, ele quem falou para validar isso!
        if(tipoUsuarioLogado != '0' && tipoUsuarioLogado != '1') {
            NajAlert.toastWarning('Apenas usuários do tipo Supervisor ou Administrador podem Incluir/Alterar as Configurações do E-mail!');
            return;
        }

        if(!validaForm()) {
            return;
        }

        var dados = {
            'smtp_host' : $('[name=smtp_host]').val(),
            'smtp_login': $('[name=smtp_login]').val(),
            'smtp_senha': $('[name=smtp_senha]').val(),
            'smtp_porta': $('[name=smtp_porta]').val(),
            'smtp_ssl'  : $('[name=smtp_ssl]').val()
        };

        updateSmtpUsuario(dados);
    });

    $('.nav-list-usuarios').children().removeClass('tabActiveNaj');
    addClassCss('tabActiveNaj', '#tabUsuarioSmtp');
});

async function loadDadosSmtpUsuario() {    
    let usuario = JSON.parse(sessionStorage.getItem('@NAJ_WEB/usuario'));
    let response = await naj.getData(`${baseURL}usuarios/show/${btoa(JSON.stringify({id: usuario.id}))}`);    
    naj.loadData('#form-smtp-usuario', response);
    $('#headerSmtpUsuario')[0].innerHTML = `CONFIGURAÇÃO E-MAIL DE: ${response.nome}`;
}

async function updateSmtpUsuario(dados) {
    loadingStart('loading-smtp-usuario');
    let usuario = JSON.parse(sessionStorage.getItem('@NAJ_WEB/usuario'));

    //Se for cliente não deixa dar permissão!
    if(usuario.usuario_tipo_id != '1' && usuario.usuario_tipo_id != '2') {
        NajAlert.toastWarning("Apenas usuários do tipo Administrador e Usuário podem receber configuração de E-mail!");
        loadingDestroy('loading-smtp-usuario');
        return;
    }

    let response = await naj.updateData(`${baseURL}usuarios/smtp/${btoa(JSON.stringify({id: usuario.id}))}?XDEBUG_SESSION_START`, dados);

    if(response.status_code == 200) {
        NajAlert.toastSuccess(response.mensagem);
    } else {
        NajAlert.toastWarning(response.mensagem);
    }

    loadingDestroy('loading-smtp-usuario');
}