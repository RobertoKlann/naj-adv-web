$(document).ready(function() {
    if(!$('#cpf').val()) {
        $('#cpf')[0].disabled = false;
    }
});

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