let tableChat;
let loadDataJuridica = false;
let loadDataClasse = false;
let loadDataComarca = false;
let loadDataPessoaGrupo = false;
let loadDataPessoaAniversariante = false;

let usersWithDevice = [];
let usersWithoutDevice = [];
let usersWithoutLogin = [];

$(window).ready(function() {

    //ação do click no item nova mensagem
    $('#action-novo-atendimento-chat').on('click', function() {
        usersNewAtendimento = [];
        $('#previews-file-novo-atendimento')[0].innerHTML = '';
        $('#modal-consulta-novo-atendimento-chat').modal('show');
        $('.steps').addClass('ajuste-steps-chat');
        $(".tab-wizard").steps('setStep');
        $('.actions .disabled').addClass('d-none')
        $('.actions .disabled').addClass('first-step');
        $('.actions').addClass('footer-steps-naj');
        $('.actions').addClass('footer-steps-naj-atendimento');
        $('.actions ul').addClass('ul-footer-steps-naj-atendimento');
        $('.content')[0].style.height = '62vh';

        tableChat = null;

        tableChat = new AtendimentoChatTable;
        tableChat.render();

        //Cria os filtros personalizados
        getCustomFiltersNovaMensagem();

        $('#nome-consulta-avancada').keypress(function(event) {
            var keycode = (event.keyCode ? event.keyCode : event.which);
            
            if(keycode != 13) return;

            event.preventDefault();
            buscaDadosFilterCustom();
        });

        
    });

    //Ao esconder o modal de '#modal-manutencao-pessoa' remove a classe 'z-index-100' do modal '#modal-upload-anexo-ficha-pessoa-chat'
    $('#modal-consulta-avancada-nova-mensagem-chat').on('hidden.bs.modal', function(){
        $('#modal-consulta-novo-atendimento-chat').removeClass('z-index-100');
    });


    //Quando clica no botão de "ações" abre o drop das ações 
    $(document).on('click', '.btn-action-default', function() {
        if($('#list-actions-default')[0].attributes.class.value.search("action-in-open") > 0){
            removeClassCss('action-in-open', '#list-actions-default');
        } else {
            addClassCss('action-in-open', '#list-actions-default');
        }
    });
    
    //Fecha o drop down das ações ao clicar fora do drop down das ações
    $(document).on('click', function (e) {
        if(e.target.attributes['class'] != undefined){
            if(e.target.attributes.class.value.search('btn btnCustom action-in-button btn-action-default') == -1 && e.target.attributes.class.value.search('fas fa-ellipsis-v btn-icon') == -1 ){
                removeClassCss('action-in-open', '#list-actions-default');
            }
        }
    });
});

/**
 * Carrega os filtros personalizados da tabela
 */
async function getCustomFiltersNovaMensagem() {
    let content = `
        <div style="display: flex;" class="font-12">
            <div style="display: flex; align-items: center;" class="m-1">
                <span>Pesquisar por Nome: </span>
            </div>
            <div class="input-group" style="width: 50%; display: flex; align-items: center;">
                <input type="text" name="nome" id="nome-consulta-avancada" class="form-control" id="input-nome-pesquisa">
            </div>
            <div style="display: flex; align-items: center;" class="m-1">
                <span>Situação :</span>
            </div>
            <select id="status-consulta-avancada" name="status" width="200" class="mt-1 mr-1 mb-1">
                <option value="A">Ativo</option>
                <option value="B">Baixado</option>
                <option value="2">Todos</option>
            </select>
            <button id="search-button" class="btn btnCustom action-in-button m-1" onclick="buscaDadosFilterCustom()">
                <i class="fas fa-search btn-icon"></i>&nbsp;&nbsp;
                Pesquisar
            </button>
        </div>
    `;

    $('.data-table-filter').append(content);
}

function buscaDadosFilterCustom() {
    const filterNome   = $('#nome-consulta-avancada').val();
    const filterStatus = $('#status-consulta-avancada').val();

    //limpando os filtros anteriores
    tableChat.filtersForSearch = [];

    if(filterNome) {
        filter1        = {}; 
        filter1.val    = filterNome;
        filter1.op     = "C";
        filter1.col    = "nome";
        filter1.origin = btoa(filter1);
        tableChat.filtersForSearch.push(filter1);
    }

    if(filterStatus && filterStatus != 2) {
        filter2        = {}; 
        filter2.val    = filterStatus;
        filter2.op     = "I";
        filter2.col    = "status";
        filter2.origin = btoa(filter2);
        tableChat.filtersForSearch.push(filter2);
    }

    tableChat.load();
}

function onClickCheckAllUsuariosMensagem() {
    let rows = $('#datatable-novo-atendimento-chat-modal .data-table-content .data-table-row');

    for(var i = 0; i < rows.length; i++) {
        tableChat.onClickRow(rows[i], true);
    }
}

async function onClickSelecaoAvancada() {
    loadingStart('loading-novo-atendimento');

    //carregando todos os dados da seleção avançada
    await loadDataSelecaoAvancada();

    $('#modal-consulta-avancada-nova-mensagem-chat').modal('show');
    $('#modal-consulta-novo-atendimento-chat').addClass('z-index-100');
    loadingDestroy('loading-novo-atendimento');
}

async function loadDataSelecaoAvancada() {
    await loadDataTabPessoaAniversariante();

    onClickTabPessoasAniversariantes();
}

function onClickTabAreaJuridica() {
    $('#link-classe').removeClass('active');
    $('#link-comarca').removeClass('active');
    $('#link-pessoas-grupos').removeClass('active');
    $('#link-pessoas-aniversariantes').removeClass('active');
    $('#link-areajuridica').addClass('active');

    $('#comarca').hide();
    $('#classe').hide();
    $('#pessoas_grupos').hide();
    $('#pessoas_aniversariantes').hide();
    $('#areajuridica').show();

    clearDataUsersSelect();

    loadDataTabJuridica();
}

function onClickTabComarca() {
    $('#link-classe').removeClass('active');
    $('#link-areajuridica').removeClass('active');
    $('#link-pessoas-grupos').removeClass('active');
    $('#link-pessoas-aniversariantes').removeClass('active');
    $('#link-comarca').addClass('active');

    $('#areajuridica').hide();
    $('#classe').hide();
    $('#pessoas_grupos').hide();
    $('#pessoas_aniversariantes').hide();
    $('#comarca').show();

    clearDataUsersSelect();

    loadDataTabComarca();
}

function onClickTabClasse() {
    $('#link-areajuridica').removeClass('active');
    $('#link-comarca').removeClass('active');
    $('#link-pessoas-grupos').removeClass('active');
    $('#link-pessoas-aniversariantes').removeClass('active');
    $('#link-classe').addClass('active');

    $('#comarca').hide();
    $('#areajuridica').hide();
    $('#pessoas_grupos').hide();
    $('#pessoas_aniversariantes').hide();
    $('#classe').show();

    clearDataUsersSelect();

    loadDataTabClasse();
}

async function onClickSelecionarPessoaConsultaAvancada(key) {
    loadingStart('loading-consulta-avancada');
    let usuarios;
    let parametro = JSON.parse(atob(key));
    let actionRemove = false;

    if(parametro.nome_filter == 'CODIGO_GRUPO') {
        usuarios = await NajApi.getData(`clientes/pessoas/grupo/${key}`);
    } else if(parametro.nome_filter == 'CODIGO_ANIVERSARIANTE') {
        usuarios = await NajApi.getData(`clientes/pessoas/aniversariante/${key}?XDEBUG_SESSION_START`);
    } else {
        usuarios = await NajApi.getData(`clientes/pessoas/${key}`);
    }

    if(parametro.nome_filter == 'codigo_classe') {
        if($(`#buttonSelecionarClasse-${parametro.key}`).hasClass('btn-success')) {
            actionRemove = !actionRemove;
            $(`#buttonSelecionarClasse-${parametro.key}`).removeClass('btn-success');
            $(`#buttonSelecionarClasse-${parametro.key}`).addClass('btn-info');
        } else {
            $(`#buttonSelecionarClasse-${parametro.key}`).addClass('btn-success');
            $(`#buttonSelecionarClasse-${parametro.key}`).removeClass('btn-info');
        }
    } else if(parametro.nome_filter == 'codigo_comarca') {
        if($(`#buttonSelecionarComarca-${parametro.key}`).hasClass('btn-success')) {
            actionRemove = !actionRemove;
            $(`#buttonSelecionarComarca-${parametro.key}`).removeClass('btn-success');
            $(`#buttonSelecionarComarca-${parametro.key}`).addClass('btn-info');
        } else {
            $(`#buttonSelecionarComarca-${parametro.key}`).addClass('btn-success');
            $(`#buttonSelecionarComarca-${parametro.key}`).removeClass('btn-info');
        }
    } else if(parametro.nome_filter == 'id_area_juridica') {
        if($(`#buttonSelecionarJuridica-${parametro.key}`).hasClass('btn-success')) {
            actionRemove = !actionRemove;
            $(`#buttonSelecionarJuridica-${parametro.key}`).removeClass('btn-success');
            $(`#buttonSelecionarJuridica-${parametro.key}`).addClass('btn-info');
        } else {
            $(`#buttonSelecionarJuridica-${parametro.key}`).addClass('btn-success');
            $(`#buttonSelecionarJuridica-${parametro.key}`).removeClass('btn-info');
        }
    } else if(parametro.nome_filter == 'CODIGO_ANIVERSARIANTE') {
        if($(`#buttonSelecionarPessoaAniversariante-${parametro.key}`).hasClass('btn-success')) {
            actionRemove = !actionRemove;
            $(`#buttonSelecionarPessoaAniversariante-${parametro.key}`).removeClass('btn-success');
            $(`#buttonSelecionarPessoaAniversariante-${parametro.key}`).addClass('btn-info');
        } else {
            $(`#buttonSelecionarPessoaAniversariante-${parametro.key}`).addClass('btn-success');
            $(`#buttonSelecionarPessoaAniversariante-${parametro.key}`).removeClass('btn-info');
        }
    } else {
        if($(`#buttonSelecionarPessoaGrupo-${parametro.key}`).hasClass('btn-success')) {
            actionRemove = !actionRemove;
            $(`#buttonSelecionarPessoaGrupo-${parametro.key}`).removeClass('btn-success');
            $(`#buttonSelecionarPessoaGrupo-${parametro.key}`).addClass('btn-info');
        } else {
            $(`#buttonSelecionarPessoaGrupo-${parametro.key}`).addClass('btn-success');
            $(`#buttonSelecionarPessoaGrupo-${parametro.key}`).removeClass('btn-info');
        }
    }

    usuarios.naoHabilitados.forEach((item) => {
        if(actionRemove) {
            usersWithoutLogin = usersWithoutLogin.filter((codigo) => {
                return codigo != item;
            });
        } else {
            usersWithoutLogin.push(item);
        }
    });

    usuarios.habilitadosSemDevice.forEach((item) => {
        if(actionRemove) {
            usersWithoutDevice = usersWithoutDevice.filter((codigo) => {
                return codigo != item;
            });
        } else {
            usersWithoutDevice.push(item);
        }
    });

    usuarios.habilitadosComDevice.forEach((item) => {
        if(actionRemove) {
            usersWithDevice = usersWithDevice.filter((codigo) => {
                return codigo != item;
            });
        } else {
            usersWithDevice.push(item);
        }
    });

    $('#total-nao-habilitados-app')[0].innerHTML = usersWithoutLogin.length;
    $('#total-habilitados-app')[0].innerHTML = usersWithDevice.length;
    $('#total-habilitados-sem-device-app')[0].innerHTML = usersWithoutDevice.length;

    loadingDestroy('loading-consulta-avancada');
}

async function loadDataTabClasse() {

    if(!loadDataClasse) {
        loadingStart('loading-consulta-avancada');
        let sHtmlClasse = '';
        //carrega os dados da tab CLASSE
        const dataClasse = await NajApi.getData(`processos/classe/classeFromChat`);

        for(var i = 0; i < dataClasse.length; i++) {
            const dataClasseClientes = await NajApi.getData(`clientes/quantidadeByCard/${btoa(JSON.stringify({'key': dataClasse[i].codigo_classe, 'nome_filter' : 'codigo_classe'}))}`);

            sHtmlClasse += `
                <div clas="w-50" style="width: 49%; margin-left: 2px; margin-right: 2px;">
                    <div class="card">
                        <div class="card-body pr-1">
                            <h5 class="card-title text-uppercase">${dataClasse[i].classe}</h5>
                            <div class="d-flex align-items-center mb-2 mt-4">
                                <h2 class="mb-0 display-7"><i class="fas fa-users text-info"></i></h2>
                                <div class="d-flex" style="margin-left: 5% !important;">
                                    <h3 class="ml-3 font-medium">${dataClasseClientes[0].qtde_clientes}</h3>
                                    <button class="btn btn-info ml-3" id="buttonSelecionarClasse-${dataClasse[i].codigo_classe}" onclick="onClickSelecionarPessoaConsultaAvancada('${btoa(JSON.stringify({'key': dataClasse[i].codigo_classe, 'nome_filter' : 'codigo_classe'}))}')"><i class="fas fa-check"></i> Selecionar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        if(dataClasse.length < 1) {
            sHtmlClasse = `
                <div class="d-flex col-12" style="align-items: center; justify-content: center; margin-top: 23%;">
                    <h4 style="">Nenhuma informação...</h4>
                </div>
            `;
        }
    
        loadingDestroy('loading-consulta-avancada');
        $('#content-classe').append(sHtmlClasse);
    }
    
    loadDataClasse = true;
}

async function loadDataTabComarca() {

    if(!loadDataComarca) {
        loadingStart('loading-consulta-avancada');
        let sHtmlComarca = '';
        //carrega os dados da tab COMARCA
        const dataComarca = await NajApi.getData(`processos/comarca/comarcaFromChat`);

        for(var i = 0; i < dataComarca.length; i++) {
            const dataComarClientes = await NajApi.getData(`clientes/quantidadeByCard/${btoa(JSON.stringify({'key': dataComarca[i].codigo_comarca, 'nome_filter' : 'codigo_comarca'}))}`);

            sHtmlComarca += `
                <div clas="w-50" style="width: 49%; margin-left: 2px; margin-right: 2px;">
                    <div class="card">
                        <div class="card-body pr-1">
                            <h5 class="card-title text-uppercase">${dataComarca[i].comarca}</h5>
                            <div class="d-flex align-items-center mb-2 mt-4">
                                <h2 class="mb-0 display-7"><i class="fas fa-users text-info"></i></h2>
                                <div class="d-flex" style="margin-left: 5% !important;">
                                    <h3 class="ml-3 font-medium">${dataComarClientes[0].qtde_clientes}</h3>
                                    <button class="btn btn-info ml-3" id="buttonSelecionarComarca-${dataComarca[i].codigo_comarca}" onclick="onClickSelecionarPessoaConsultaAvancada('${btoa(JSON.stringify({'key': dataComarca[i].codigo_comarca, 'nome_filter' : 'codigo_comarca'}))}')"><i class="fas fa-check"></i> Selecionar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        if(dataComarca.length < 1) {
            sHtmlComarca = `
                <div class="d-flex col-12" style="align-items: center; justify-content: center; margin-top: 23%;">
                    <h4 style="">Nenhuma informação...</h4>
                </div>
            `;
        }
        
        loadingDestroy('loading-consulta-avancada');
        $('#content-comarca').append(sHtmlComarca);
    }

    loadDataComarca = true;
}

async function loadDataTabJuridica() {
    if(!loadDataJuridica) {
        loadingStart('loading-consulta-avancada');
        let sHtmlAreaJuridica = '';
        //carrega os dados da tab ÁREA JURICA
        const dataAJ = await NajApi.getData(`processos/areajuridica/areasFromChat`);

        for(var i = 0; i < dataAJ.length; i++) {
            const dataAJClientes = await NajApi.getData(`clientes/quantidadeByCard/${btoa(JSON.stringify({'key': dataAJ[i].id_area_juridica, 'nome_filter' : 'id_area_juridica'}))}`);

            sHtmlAreaJuridica += `
                <div clas="w-50" style="width: 49%; margin-left: 2px; margin-right: 2px;">
                    <div class="card">
                        <div class="card-body pr-1">
                            <h5 class="card-title text-uppercase">${dataAJ[i].area}</h5>
                            <div class="d-flex align-items-center mb-2 mt-4">
                                <h2 class="mb-0 display-7"><i class="fas fa-users text-info"></i></h2>
                                <div class="d-flex" style="margin-left: 5% !important;">
                                    <h3 class="ml-3 font-medium">${dataAJClientes[0].qtde_clientes}</h3>
                                    <button class="btn btn-info ml-3" id="buttonSelecionarJuridica-${dataAJ[i].id_area_juridica}" onclick="onClickSelecionarPessoaConsultaAvancada('${btoa(JSON.stringify({'key': dataAJ[i].id_area_juridica, 'nome_filter' : 'id_area_juridica'}))}')"><i class="fas fa-check"></i> Selecionar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        if(dataAJ.length < 1) {
            sHtmlAreaJuridica = `
                <div class="d-flex col-12" style="align-items: center; justify-content: center; margin-top: 23%;">
                    <h4 style="">Nenhuma informação...</h4>
                </div>
            `;
        }
    
        $('#content-areajuridica').append(sHtmlAreaJuridica);
        loadingDestroy('loading-consulta-avancada');
    }

    loadDataJuridica = true;
}

function onClickTabPessoasGrupos() {
    $('#link-classe').removeClass('active');
    $('#link-comarca').removeClass('active');
    $('#link-areajuridica').removeClass('active');
    $('#link-pessoas-aniversariantes').removeClass('active');
    $('#link-pessoas-grupos').addClass('active');

    $('#comarca').hide();
    $('#classe').hide();
    $('#areajuridica').hide();
    $('#pessoas_aniversariantes').hide();
    $('#pessoas_grupos').show();

    clearDataUsersSelect();

    loadDataTabPessoaGrupo();
}

async function loadDataTabPessoaGrupo() {
    if(!loadDataPessoaGrupo) {
        loadingStart('loading-consulta-avancada');
        let sHtmlPG = `
                
        `;

        //carrega os dados da tab PESSOA GRUPO
        const dataPG = await NajApi.getData(`pessoas/pessoaGrupoFromChat`);

        for(var i = 0; i < dataPG.length; i++) {
            const dataPGClientes = await NajApi.getData(`clientes/quantidadeByCardPesoaGrupo/${btoa(JSON.stringify({'key': dataPG[i].CODIGO}))}`);

            sHtmlPG += `
                <div clas="w-50" style="width: 49%; margin-left: 2px; margin-right: 2px;">
                    <div class="card">
                        <div class="card-body pr-1">
                            <h5 class="card-title text-uppercase">${dataPG[i].GRUPO}</h5>
                            <div class="d-flex align-items-center mb-2 mt-4">
                                <h2 class="mb-0 display-7"><i class="fas fa-users text-info"></i></h2>
                                <div class="d-flex" style="margin-left: 5% !important;">
                                    <h3 class="ml-3 font-medium">${dataPGClientes[0].qtde_clientes}</h3>
                                    <button class="btn btn-info ml-3" id="buttonSelecionarPessoaGrupo-${dataPG[i].CODIGO}" onclick="onClickSelecionarPessoaConsultaAvancada('${btoa(JSON.stringify({'key': dataPG[i].CODIGO, 'nome_filter' : 'CODIGO_GRUPO'}))}')"><i class="fas fa-check"></i> Selecionar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        if(dataPG.length < 1) {
            sHtmlPG = `
                <div class="d-flex col-12" style="align-items: center; justify-content: center; margin-top: 23%;">
                    <h4 style="">Nenhuma informação...</h4>
                </div>
            `;
        }
    
        $('#content-pessoas-grupos').append(sHtmlPG);
        loadingDestroy('loading-consulta-avancada');
    }

    loadDataPessoaGrupo = true;
}

function onClickTabPessoasAniversariantes() {
    $('#link-classe').removeClass('active');
    $('#link-comarca').removeClass('active');
    $('#link-areajuridica').removeClass('active');
    $('#link-pessoas-grupos').removeClass('active');
    $('#link-pessoas-aniversariantes').addClass('active');

    $('#comarca').hide();
    $('#classe').hide();
    $('#areajuridica').hide();
    $('#pessoas_grupos').hide();
    $('#pessoas_aniversariantes').show();

    clearDataUsersSelect();

    loadDataTabPessoaAniversariante();
}

async function loadDataTabPessoaAniversariante() {
    if(!loadDataPessoaAniversariante) {
        loadingStart('loading-consulta-avancada');
        let sHtmlPA = ``;

        //carrega os dados da tab PESSOA ANIVERSARIANTE
        const dataPA = [
            {key: 1, descricao: 'Janeiro'},
            {key: 2, descricao: 'Fevereiro'},
            {key: 3, descricao: 'Março'},
            {key: 4, descricao: 'Abril'},
            {key: 5, descricao: 'Maio'},
            {key: 6, descricao: 'Junho'},
            {key: 7, descricao: 'Julho'},
            {key: 8, descricao: 'Agosto'},
            {key: 9, descricao: 'Setembro'},
            {key: 10, descricao: 'Outubro'},
            {key: 11, descricao: 'Novembro'},
            {key: 12, descricao: 'Dezembro'},
        ];

        const dataPAClientes = await NajApi.getData(`pessoas/quantidadeByCardPessoaAniversariantes`);

        for(var i = 0; i < dataPA.length; i++) {
            let amount = 0

            if (dataPAClientes[i])
                amount = dataPAClientes[i].quantidade_cliente

            sHtmlPA += `
                <div clas="w-50" style="width: 49%; margin-left: 2px; margin-right: 2px;">
                    <div class="card">
                        <div class="card-body pr-1">
                            <h5 class="card-title text-uppercase">${dataPA[i].descricao}</h5>
                            <div class="d-flex align-items-center mb-2 mt-4">
                                <h2 class="mb-0 display-7"><i class="fas fa-users text-info"></i></h2>
                                <div class="d-flex" style="margin-left: 5% !important;">
                                    <h3 class="ml-3 font-medium">${amount}</h3>
                                    <button class="btn btn-info ml-3" id="buttonSelecionarPessoaAniversariante-${dataPA[i].key}" onclick="onClickSelecionarPessoaConsultaAvancada('${btoa(JSON.stringify({'key': dataPA[i].key, 'nome_filter' : 'CODIGO_ANIVERSARIANTE'}))}')"><i class="fas fa-check"></i> Selecionar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        if(dataPA.length < 1) {
            sHtmlPA = `
                <div class="d-flex col-12" style="align-items: center; justify-content: center; margin-top: 23%;">
                    <h4 style="">Nenhuma informação...</h4>
                </div>
            `;
        }
    
        $('#content-pessoas-aniversariantes').append(sHtmlPA);
        loadingDestroy('loading-consulta-avancada');
    }

    loadDataPessoaAniversariante = true;
}

function clearDataUsersSelect() {
    usersWithDevice = [];
    usersWithoutDevice = [];
    usersWithoutLogin = [];

    $(`#modal-consulta-avancada-nova-mensagem-chat .btn-success`).addClass('btn-info');
    $(`#modal-consulta-avancada-nova-mensagem-chat .btn-success`).removeClass('btn-success');

    $('#total-nao-habilitados-app')[0].innerHTML = usersWithoutLogin.length;
    $('#total-habilitados-app')[0].innerHTML = usersWithDevice.length;
    $('#total-habilitados-sem-device-app')[0].innerHTML = usersWithoutDevice.length;
}

async function onClickFilterUserConsultaAvancadaChat() {
    const usersFilter = usersWithDevice;
    // const usersFilter = usersWithDevice.concat(usersWithoutDevice); // Nelson definiu que não era para juntar, ficar apenas com os que tem device

    //Se não tem usuário selecionado
    if(usersFilter.length < 1) return;

    usersNewAtendimento = usersFilter;

    tableChat.filtersForSearch = [];

    const newFilter = {
        val: usersNewAtendimento,
        op: 'IN',
        col: 'usuarios.id'
    };

    tableChat.addFilterForSearch({
        val: usersNewAtendimento,
        op: 'IN',
        col: 'usuarios.id',
        origin: tableChat.toBase64(newFilter),
    });

    await tableChat.load();
    onClickCheckAllUsuariosMensagem();

    $('#modal-consulta-avancada-nova-mensagem-chat').modal('hide');
    $('#modal-consulta-novo-atendimento-chat').removeClass('z-index-100');
}