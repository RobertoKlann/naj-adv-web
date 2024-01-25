const tableDispositivos          = new DispositivoTable;

//---------------------- Functions -----------------------//

$(document).ready(function() {
    
    tableDispositivos.render();
    addClassCss('selected', '#sidebar-usuario');
    
    $('#form-usuario-permissao').submit(function(e) {
        e.preventDefault();
        
        let dados      = getDadosForm();
            is_alterar = $('input[name=is_update_usuario]').val();
        
            createOrUpdateUsuarioDispositivo(dados, is_alterar);
    });
});

async function exibeModalUsuarioDispositivo(el, alterar, codigo) {
    $('#modal-incluir-usuarios').modal('show');
    $(el).tooltip('hide');
    limpaFormulario('#form-usuario');

    if(!alterar) {
        response = await naj.getData(`${urlBaseUsuario}proximo`);
        $('input[name=id]').val(response + 1);
        $('input[name=is_update_usuario]').val(0);     
    } else {
        $('input[name=is_update_usuario]').val(1);
        response = await naj.getData(`${urlBaseUsuario}show/${btoa(JSON.stringify({id: codigo}))}`);
        naj.loadData('#modal-incluir-usuarios', response);
    }
}

async function createOrUpdateUsuarioDispositivo(dados, is_alterar) {
    if(is_alterar == "1") {
        await naj.update(`${urlBaseUsuario}${btoa(JSON.stringify({id: $('input[name=id]').val()}))}`, dados);
    } else {
        await naj.store(`${urlBaseUsuario}`, dados);
    }
    $('#modal-incluir-usuarios').modal('hide');
}

async function onClickNovoDispositivo() {
    limpaFormulario('#form-usuario');
    response = await naj.getData(urlUsuarioProximo);
    $('input[name=id]').val(response + 1);
    $('input[name=is_update_usuario]').val(0);
}