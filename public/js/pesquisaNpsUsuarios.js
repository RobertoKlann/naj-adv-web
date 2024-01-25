(isIndex('pesquisa/nps/usuarios')) ? table = new PesquisaNpsUsuariosTable : table = false;
const NajApi = new Naj('Pesquisa Nps', table);

let isUpdate;
let npsRelUsuariosTable;
let usersRelNps = [];

$(document).ready(() => {    
    addClassCss('selected', '#sidebar-nps');
    table.render();

    loadPesquisaNpsUsuarios();

    $('#storeUserRelNps').on('click', () => {
        if(usersRelNps.length == 0)
            return NajAlert.toastWarning('Você deve selecionar ao menos um usuário para gravar os dados!');

        const idPesquisa = sessionStorage.getItem('@NAJ_WEB/nps');

        if(!idPesquisa)
            return NajAlert.toastWarning('Não foi possível identificar a pesquisa selecionada, retorne a consulta e selecione a pesquisa novamnete!');

        const data = {
            'id_pesquisa': idPesquisa,
            'users': usersRelNps
        };

        storeUpdateUserRelNps(data);
    });
});

async function loadTablePesquisaNpsUsuarios() {
    await table.render();

    setTimeout(() => {
        let rows = $('#datatable-pesquisa-nps-usuarios .data-table-content .data-table-row');
        let npsUserRelUpdate = [];
    
        for(var i = 0; i < rows.length; i++) {
            npsUserRelUpdate.push(JSON.parse(atob(rows[i].getAttribute('key'))));
        }
    
        if(npsUserRelUpdate.length > 0)
            update = NajApi.postData(`${baseURL}pesquisa/nps/usuarios/lido`, {keys: npsUserRelUpdate});
    }, 3000);
}

function redirectNps(route = '') {
    return window.location.href = `${baseURL}pesquisa/nps/${route}`;
}

function redirectNpsTabCadastro() {
    if(isCreate())
        return window.location.href = `${baseURL}pesquisa/nps/create`;

    return window.location.href = `${baseURL}pesquisa/nps/edit`;
}

async function openModalNewNpsUsuarios() {
    npsRelUsuariosTable = new NpsRelacionamentoUsuariosTable();
    npsRelUsuariosTable.render();

    usersRelNps = [];
    isUpdate = false;

    $('#modal-manutencao-nps-usuarios').modal('show');
}

async function openModalNpsUsuarioUpdate() {
    //Tem que buscar os existentes e selecionar na consulta

    $('#modal-manutencao-nps-usuarios').modal('show');
}

function onClickCheckAllUsersNps() {
    let rows = $('#datatable-nps-relacionamento-usuarios .data-table-content .data-table-row');

    for(var i = 0; i < rows.length; i++)
        npsRelUsuariosTable.onClickRow(rows[i], true);
}

async function storeUpdateUserRelNps(data) {
    loadingStart('bloqueio-modal-manutencao-nps-usuarios');
    if(isUpdate) {
        const response = await NajApi.update(`${baseURL}pesquisa/nps/usuarios/${btoa(JSON.stringify({id: $('#id').val()}))}`, data);

        loadingDestroy('bloqueio-modal-manutencao-nps-usuarios');

        if(!response) return NajAlert.toastWarning('Não foi possível alterar os usuários!');

        NajAlert.toastSuccess('Relacionamento alterado com sucesso!');
    } else {
        const response = await NajApi.postData(`${baseURL}pesquisa/nps/usuarios`, data);

        loadingDestroy('bloqueio-modal-manutencao-nps-usuarios');

        if(!response) return NajAlert.toastWarning('Não foi possível incluir os usuários!');

        if(response.user_invalid.length > 0) {
            NajAlert.toastError(`
                Relacionamento incluido com sucesso, porém o(s) usuário(s) a baixo não foram relacionados por já terem relacionamento:
                ${response.user_invalid.join(', ')}
            `);
        } else {
            NajAlert.toastSuccess('Relacionamento incluido com sucesso!');
        }

        $('#modal-manutencao-nps-usuarios').modal('hide');
        npsRelUsuariosTable.load();
    }
}

async function loadPesquisaNpsUsuarios() {
    const key = JSON.parse(sessionStorage.getItem('@NAJ_WEB/nps'));

    const notRead = await NajApi.getData(`${baseURL}pesquisa/nps/usuarios/pendentes/${btoa(JSON.stringify({pesquisa: key}))}`);

    if(!notRead) return;

    if(notRead[0].quantidade > 0) {
        if(notRead[0].quantidade > 99)
            return $('#badge-pendentes-nps')[0].innerHTML = '+99';

        $('#badge-pendentes-nps')[0].innerHTML = notRead[0].quantidade;
    }
}

$('.mascaracelular').mask("(00) 0 0000-0000", {placeholder: "(00) 0 0000-0000"});