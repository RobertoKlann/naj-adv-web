(isIndex('pesquisa/nps')) ? table = new PesquisaNpsTable : table = false;
const NajApi = new Naj('Pesquisa Nps', table);

$(document).ready(() => {
    if(isIndex('pesquisa/nps')) {
        sessionStorage.removeItem('@NAJ_WEB/nps_key');
        sessionStorage.removeItem('@NAJ_WEB/nps');
        table.render();
    } else {
        loadFormNps();
    }

    $('#gravarPesquisaNps').on('click', (event) => {
        event.preventDefault();
        loadingStart('bloqueio-modal-manutencao-nps');

        const data = {
            'descricao': $('#descricao').val(),
            'pergunta': $('#pergunta').val(),
            'data_hora_inclusao': $('#data_hora_inclusao').val(),
            'data_hora_inicio': $('#data_hora_inicio').val(),
            'range_max': $('#range_max').val(),
            'range_min_info': $('#range_min_info').val(),
            'range_max_info': $('#range_max_info').val(),
            'situacao': $('#situacao').val()
        }

        let isUpdate = $('#isUpdate').val();

        if(!validaForm()) {
            return loadingDestroy('bloqueio-modal-manutencao-nps');
        }

        savePesquisaNps(data, isUpdate);
    });
});

function loadTablePesquisaNps() {
    table.render();
}

function newResearch() {
    sessionStorage.removeItem('@NAJ_WEB/nps');
    sessionStorage.removeItem('@NAJ_WEB/nps/descricao');
    return window.location.href = `${baseURL}pesquisa/nps/create`;
}

function redirectNps(route = '') {
    return window.location.href = `${baseURL}pesquisa/nps/${route}`;
}

function redirectNpsTabCadastro() {
    if(isCreate())
        return window.location.href = `${baseURL}pesquisa/nps/create`;

    return window.location.href = `${baseURL}pesquisa/nps/edit`;
}

async function loadFormNps() {
    loadingStart('bloqueio-modal-manutencao-nps');

    if(isCreate()) {
        $('#isUpdate').val(0);        
        $('#situacao').val('A');

        loadInputsDataHora(getDataHoraAtual(), 'data_hora_inclusao');
        loadInputsDataHora(getDataHoraAtual(), 'data_hora_inicio');

        return loadingDestroy('bloqueio-modal-manutencao-nps');
    }

    const key = JSON.parse(sessionStorage.getItem('@NAJ_WEB/nps'));
    const pesquisa = await NajApi.getData(`${baseURL}pesquisa/nps/show/${btoa(JSON.stringify({id: key}))}`);
    const notRead = await NajApi.getData(`${baseURL}pesquisa/nps/usuarios/pendentes/${btoa(JSON.stringify({pesquisa: key}))}`);

    if(notRead.length > 0) {
        if(notRead[0].quantidade > 99)
            $('#badge-pendentes-nps')[0].innerHTML = '+99';

        if(notRead[0].quantidade > 0)
            $('#badge-pendentes-nps')[0].innerHTML = notRead[0].quantidade;
    }

    NajApi.loadData('#form-pesquisa-nps', pesquisa);

    $('#isUpdate').val(1);
    loadInputsDataHora(pesquisa.data_hora_inclusao, 'data_hora_inclusao');
    loadInputsDataHora(pesquisa.data_hora_inclusao, 'data_hora_inicio');

    $('.header-custom-naj-card')[0].innerHTML = `Alterando a pesquisa: ${pesquisa.descricao}`;
    $('.header-custom-naj-card')[0].innerHTML = `PESQUISA NPS: ${key} - ${pesquisa.descricao} [ALTERANDO...]`;

    loadingDestroy('bloqueio-modal-manutencao-nps');
}

async function savePesquisaNps(data, isUpdate) {
    if(isUpdate == "1") {
        await NajApi.update(`${baseURL}pesquisa/nps/${btoa(JSON.stringify({id: $('#id').val()}))}`, data);

        loadingDestroy('bloqueio-modal-manutencao-nps');
    } else {
        const response = await NajApi.store(`${baseURL}pesquisa/nps?XDEBUG_SESSION_START`, data);

        loadingDestroy('bloqueio-modal-manutencao-nps');

        if(!response) return;

        sessionStorage.setItem('@NAJ_WEB/nps', JSON.stringify(response.id));
        sessionStorage.setItem('@NAJ_WEB/nps/descricao', JSON.stringify(response.descricao));
        window.location.href = `${baseURL}pesquisa/nps/edit`;
    }
}

async function loadInputsDataHora(dateHour, input) {
    let dataHora = dateHour,
        data     = dataHora.split(' ')[0],
        hora     = dataHora.split(' ')[1],
        hour     = hora.split(':')[0],
        minutes  = hora.split(':')[1];

    $(`#${input}`).val(`${data}T${hour}:${minutes}`);
}