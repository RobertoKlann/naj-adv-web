//---------------------- Functions -----------------------//
$(document).ready(function() {

    $('#content-select-ajax-naj-nova-tarefa').on('click', function(el) {
        onClickContentSelectAjaxTarefa(el, 'nome_cliente', 'codigo_cliente', 'content-select-ajax-naj-nova-tarefa');
    });

    $('#content-select-ajax-naj-nova-tarefa-supervisor').on('click', function(el) {
        onClickContentSelectAjaxTarefa(el, 'nome_supervisor', 'codigo_supervisor', 'content-select-ajax-naj-nova-tarefa-supervisor');
    });

    $('#content-select-ajax-naj-nova-tarefa-responsavel').on('click', function(el) {
        onClickContentSelectAjaxTarefa(el, 'nome_responsavel', 'codigo_responsavel', 'content-select-ajax-naj-nova-tarefa-responsavel');
    });

    //Esconde caixa do campo de pesquisa
    $('#content-outside-tarefa').on('click', function(el) {
        if(el.target.id == 'nome_cliente' || el.target.id == 'nome_supervisor' || el.target.id == 'nome_responsavel' 
           || el.target.id == 'icon-search-cliente' || el.target.id == 'icon-search-responsavel' || el.target.id == 'icon-search-supervisor') {
            return;
        }

        $("#content-select-ajax-naj-nova-tarefa").hide();
        $("#content-select-ajax-naj-nova-tarefa-responsavel").hide();
        $("#content-select-ajax-naj-nova-tarefa-supervisor").hide();
    });

    //Realiza a busca
    $('#nome_cliente').on('click', function(element) {
        buscaDadosCliente(element);
    });

    //Realiza a busca
    $('#nome_supervisor').on('click', function(element) {
        buscaDadosSupervisor(element);
    });

    //Realiza a busca
    $('#nome_responsavel').on('click', function(element) {
        buscaDadosResponsavel(element);
    });

    $('#modal-novo-tipo-tarefa').on('hidden.bs.modal', function() {
        $('#modal-nova-tarefa-chat').removeClass('z-index-100');
    });

    $('#modal-prioridade-tarefa').on('hidden.bs.modal', function() {
        $('#modal-nova-tarefa-chat').removeClass('z-index-100');
    });

    //Ao esconder o modal de '#modal-manutencao-pessoa' remove a classe 'z-index-100' do modal '#modal-nova-tarefa-chat'
    $('#modal-manutencao-pessoa').on('hidden.bs.modal', function(){
        $('#modal-nova-tarefa-chat').removeClass('z-index-100');
    });

    $('#modal-manutencao-pessoa').on('hidden.bs.modal', function() {
        onChangeCodigosPessoasTarefa('codigo_cliente', 'nome_cliente');
        onChangeCodigosPessoasTarefa('codigo_supervisor', 'nome_supervisor');
        onChangeCodigosPessoasTarefa('codigo_responsavel', 'nome_responsavel');
    });
});

async function storeTarefa(dados) {
    try{
        loadingStart('bloqueio-nova-tarefa-chat');
        if(!validaForm('form-nova-tarefa-chat')) {
            if(!$('#codigo_divisao_tarefa').val()) {
                NajAlert.toastWarning("É necessário informar uma divisão!");
                return;
            }

            if(!$('#id_tipo').val()) {
                NajAlert.toastWarning("É necessário informar um tipo de tarefa!");
                return;
            }

            if(!$('#id_prioridade').val()) {
                NajAlert.toastWarning("É necessário informar a prioridade da tarefa!");
                return;
            }
            return;
        }

        if(!validaDataHoraPrazoInterno()) {
            NajAlert.toastWarning("A data/hora informada para campo Prazo Interno não é válida!");
            return;
        }

        if(!validaDataHoraPrazoFatal()) {
            NajAlert.toastWarning("A data/hora informada para campo Prazo Fatal não é válida!");
            return;
        }

        if(!validaDataHoraPrazoFatalMaiorPrazoInterno()) {
            NajAlert.toastWarning("A data/hora informado para campo Prazo Fatal deve ser maior que a data/hora do campo Prazo Interno!");
            return;
        }

        var dados = await getDadosFormTarefa();

        if(!dados) {
            NajAlert.toastWarning("O usuário de criação da tarefa não tem uma pessoa cadastrada para ele!");
            return;
        }

        let response = await NajApi.postData(`${baseURL}tarefa?XDEBUG_SESSION_START`, dados);

        if(!response) {
            NajAlert.toastWarning("Não é possível cadastrar a tarefa, tente novamente mais tarde!");
        } else if(response.model) {
            NajAlert.toastSuccess("Tarefa cadastrada com sucesso!");
            desabilitaCamposTarefa();
            $('#codigo_tarefa').val(response.model.id);
            $('#gravar-tarefa')[0].disabled = true;
        } else if(response.hasOwnProperty('mensagem')){
            NajAlert.toastWarning(response.mensagem);
        } else {
            NajAlert.toastWarning("Não é possível cadastrar a tarefa, tente novamente mais tarde!");
        }

    }catch(e){
        console.log(e);
    }finally{
        loadingDestroy('bloqueio-nova-tarefa-chat');
    } 
}

async function onClickNovaTarefa() {
    loadingStart('bloqueio-nova-tarefa-chat');
    limpaFormulario('#form-nova-tarefa-chat');
    removeClassCss('was-validated', '#form-nova-tarefa-processo');
    onClickButtonLimparPrazoInterno();
    onClickButtonLimparPrazoFatal();
    
    await carregaOptionsSelect('divisoes/paginate','codigo_divisao_tarefa',false,'data', false, null);
    await carregaOptionsSelect('tarefa/tipos/paginate','id_tipo',false,'data', false, null);
    await carregaOptionsSelect('tarefa/prioridade/paginate','id_prioridade',false,'data', false, null);
    await loadInputDataHoraCadastro();
    
    //Carrega campos do Usuário de criação
    $('#codigo_usuario_criacao').val(`${idUsuarioLogado}`);
    $('#nome_usuario_criacao').val(`${nomeUsuarioLogado}`);
    
    //Carrega o campo da situação
    $('[name=id_situacao]').val('PENDENTE DE ANALISE');
    
    //Esconde a linha do campo código da tarefa
    $('#row_codigo_tarefa').hide();

    $('#gravar-tarefa')[0].disabled = false;
    loadingDestroy('bloqueio-nova-tarefa-chat');
    $('#modal-nova-tarefa-chat').modal('show');
}

async function loadInputDivisao() {
    let dados = await NajApi.getData(`divisoes/paginate`);
    let bOptionSelecione = true;
    $('#codigo_divisao_tarefa')[0].innerHTML = '';

    if(dados.resultado.length == 1) {
        bOptionSelecione = false;
    }

    for(i = 0; i < dados.resultado.length; i++) {
        if(bOptionSelecione) {
            bOptionSelecione = false;
            $('#codigo_divisao_tarefa').append($('<option>', {
                value: 0,
                text: '* Selecione a Divisão *',
                selected: true,
                disabled: true
            }));
        }
        $('#codigo_divisao_tarefa').append($('<option>', {
            value: dados.resultado[i].CODIGO,
            text: dados.resultado[i].DIVISAO,
            selected: bOptionSelecione
        }));
    }
}

async function loadInputTipo() {
    let dados = await NajApi.getData(`tarefa/tipos/paginate`);
    let bOptionSelecione = true;

    $('select[name=id_tipo]')[0].innerHTML = '';

    if(dados.resultado.length == 1) {
        bOptionSelecione = false;
    }

    for(i = 0; i < dados.resultado.length; i++) {
        if(bOptionSelecione) {
            bOptionSelecione = false;
            $('select[name=id_tipo]').append($('<option>', {
                value: 0,
                text: '* Selecione o Tipo *',
                selected: true,
                disabled: true
            }));
        }

        $('select[name=id_tipo]').append($('<option>', {
            value: dados.resultado[i].ID,
            text: dados.resultado[i].TIPO
        }));
    }
}

async function loadInputSituacao() {
    $('[name=id_situacao]').val('PENDENTE DE ANALISE');
}

async function loadInputPrioridade() {
    let dados = await NajApi.getData(`tarefa/prioridade/paginate`);
    let bOptionSelecione = true;

    $('select[name=id_prioridade]')[0].innerHTML = '';

    if(dados.resultado.length == 1) {
        bOptionSelecione = false;
    }

    for(i = 0; i < dados.resultado.length; i++) {
        if(bOptionSelecione) {
            bOptionSelecione = false;
            $('select[name=id_prioridade]').append($('<option>', {
                value: 0,
                text: '* Selecione a Prioridade *',
                selected: true,
                disabled: true
            }));
        }

        $('select[name=id_prioridade]').append($('<option>', {
            value: dados.resultado[i].ID,
            text: dados.resultado[i].PRIORIDADE
        }));
    }
}

async function loadInputUsuarioCadastro() {
    $('#codigo_usuario_criacao').val(`${idUsuarioLogado}`);
    $('#nome_usuario_criacao').val(`${nomeUsuarioLogado}`);
}

async function loadInputDataHoraCadastro() {
    let dataHora = getDataHoraAtual(),
        data     = dataHora.split(' ')[0],
        hora     = dataHora.split(' ')[1],
        hour     = hora.split(':')[0],
        minutes  = hora.split(':')[1];

    $('#data_hora_criacao').val(`${data}T${hour}:${minutes}`);
}

function getClienteTarefa(element) {
    if(element.value.length < 3) {
        return;
    }

    setTimeout(async function() {
        result =  await searchDataTarefa(element.value, false);
        updateListaDataTarefa(result, 'content-select-ajax-naj-nova-tarefa');
    }, 500);
}

function getSupervisorTarefa(element) {
    if(element.value.length < 3) {
        return;
    }

    setTimeout(async function() {
        result =  await searchDataTarefa(element.value);
        updateListaDataTarefa(result, 'content-select-ajax-naj-nova-tarefa-supervisor');
    }, 500);
}

function getResponsavelTarefa(element) {
    if(element.value.length < 3) {
        return;
    }

    setTimeout(async function() {
        result =  await searchDataTarefa(element.value);
        updateListaDataTarefa(result, 'content-select-ajax-naj-nova-tarefa-responsavel');
    }, 500);
}

async function searchDataTarefa(value, filtra_pessoa_usuario = true) {
    let urlSearchDataTarefa = (filtra_pessoa_usuario) ? `${baseURL}pessoas/getPessoasUsuarioInFilter/${value}` : `${baseURL}pessoas/getPessoasFilter/${value}`;
    let response            = await NajApi.getData(urlSearchDataTarefa);
    let content             = '';
    let rows                = '';

    if(response.data.length > 0) {
        for(var i = 0; i < response.data.length; i++) {
            let cpfCnpj = (response.data[i].cpf) ? response.data[i].cpf : response.data[i].cnpj;
            rows += `
                <div class="row row-full content-rows-permissao" style="flex-wrap: nowrap !important;">
                    <div class="col-sm-1 col-icon"><i class="far fa-hand-pointer"></i></div>
                    <div class="col-sm-0 col-codigo" style="display: none;">${response.data[i].pessoa_codigo}</div>
                    <div class="col-sm-5 col-name">${response.data[i].nome}</div>
                    <div class="col-sm-3 col-cpf">${cpfCnpj}</div>
                    <div class="col-sm-3 col-cidade-tarefa">${(response.data[i].cidade == null) ? '' : response.data[i].cidade}</div>
                </div>
            `;
        }

        content += `
            <div class="input-group col-12 p-0 m-0 naj-scrollable content-table-input-ajax">
                ${rows}
            </div>
            <div class="input-group col-12 content-footer-table-input-ajax">
                <div class="row">
                    <button type="button" class="btn btnLightCustom" title="Gravar" style="margin: 4px 0 5px 5px;" onclick="onClickNovaPessoaTarefa();"><i class="fas fa-plus"></i> Nova Pessoa</button>
                    <span class="span-contador-registros-table-input-ajax">Exibindo ${response.data.length} registro(s)</span>
                </div>
            </div>
        `;
    } else {
        content += '<p class="text-center">Nenhum registro encontrado...</p>';
    }

    return content;
}

function updateListaDataTarefa(data, id_content)  {
    $(`#${id_content}`)[0].innerHTML = "";
    $(`#${id_content}`).append(data);
    $(`#${id_content}`).show();
}

async function onClickContentSelectAjaxTarefa(el, input_nome, input_id, id_content) {
    var pai = el.target.parentElement;
    if(!pai.getElementsByClassName('col-codigo')[0]) return;
    let codigoPessoa = pai.getElementsByClassName('col-codigo')[0].textContent;
    let nomePessoa = pai.getElementsByClassName('col-name')[0].textContent;

    $(`#${input_id}`).val(codigoPessoa);
    $(`#${input_nome}`).val(nomePessoa);

    $(`#${id_content}`).hide();
}

async function onChangeCodigosPessoasTarefa(input_codigo, input_name) {
    let codigoCliente = $(`#${input_codigo}`).val();
    if(!codigoCliente) {
        $(`#${input_name}`).val('');
        return;
    }
    
    let dados = await NajApi.getData(`pessoas/show/${btoa(JSON.stringify({"CODIGO" : codigoCliente}))}`);

    if(!dados.NOME) return;
    $(`#${input_name}`).val(dados.NOME);
}

async function onClickButtonCadastroPessoa(id_input) {
    let codigoCliente = $(`#${id_input}`).val();

    if(!codigoCliente) {
        let descriçãoInput = 'Responsável';
        if(id_input == 'codigo_cliente') {
            descriçãoInput = 'Cliente';
        } else if(id_input == 'codigo_supervisor') {
            descriçãoInput = 'Supervisor';
        }

        NajAlert.toastWarning(`Você deve informar o código do ${descriçãoInput} para utilizar essa ação!`);
        return;
    }

    sessionStorage.setItem('@NAJ_WEB/pessoa_action', 'edit')
    let response = await NajApi.getData(`${baseURL}pessoas/show/${btoa(JSON.stringify({CODIGO: codigoCliente}))}`);

    await carregaOptionsSelect(`pessoas/divisao`, 'codigo_divisao', false, 'data', false, 1);
    await carregaOptionsSelect(`pessoas/grupopessoa`, 'codigo_grupo', false, 'data', false);

    sessionStorage.setItem('@NAJ_WEB/codigo_pessoa', codigoCliente);

    if(response.CNPJ == "" || response.CNPJ == null){
        //Esconde o label e o campo do CNPJ
        $('#form-pessoa #label_cnpj').hide();
        $('#form-pessoa #cnpj').hide();
    } else if(response.CEP == "" || response.CEP == null){
        //Esconde o label e o campo do CEP
        $('#form-pessoa #label_cpf').hide();
        $('#form-pessoa #cpf').hide();
    }

    NajApi.loadData('#form-pessoa', response);

    $('#modal-manutencao-pessoa').modal('show');
    $('#modal-nova-tarefa-chat').addClass('z-index-100');
}

async function onClickButtonTipoTarefa() {
    let id_tipo = $('#id_tipo').val();

    if(id_tipo) {
        $('#headerTipoTarefa')[0].innerHTML = "Alterar Tipo da Tarefa";
        let response = await NajApi.getData(`tarefa/tipos/show/${btoa(JSON.stringify({"ID" : id_tipo}))}`);

        if(response) {
            NajApi.loadData('#form-novo-tipo-tarefa', response);
            $('#is-alterar-tipo-tarefa').val("1");
            $('#excluir-tipo-tarefa').show();
        }
    } else {
        $('#headerTipoTarefa')[0].innerHTML = "Novo Tipo de Tarefa";
        $('#is-alterar-tipo-tarefa').val("0");
        limpaFormulario('#form-novo-tipo-tarefa');
        $('#excluir-tipo-tarefa').hide();
    }

    $('#modal-novo-tipo-tarefa').modal('show');
    $('#modal-nova-tarefa-chat').addClass('z-index-100');
}

async function storeUpdateTipoTarefa() {
    let tipoTarefa = $('#TIPO').val();

    if(!tipoTarefa) {
        NajAlert.toastWarning("Você deve informar o tipo da tarefa!");
        return;
    }

    let dados = {
        "TIPO": tipoTarefa
    };

    let is_update = $('#is-alterar-tipo-tarefa').val(),
        response,
        message;

    loadingStart('bloqueio-novo-tipo-tarefa');

    if(is_update == "0") {
        response = await NajApi.postData(`${baseURL}tarefa/tipos?XDEBUG_SESSION_START`, dados);
        message  = 'cadastrado';
    } else {
        response = await NajApi.updateData(`${baseURL}tarefa/tipos/${btoa(JSON.stringify({id: $('[name=id_tipo]').val()}))}?XDEBUG_SESSION_START`, dados);
        message  = 'alterado';
    }

    if(!response) {
        NajAlert.toastWarning(`Não é possível ${message} o tipo de tarefa, tente novamente mais tarde!`);
    } else if(response.model) {
        NajAlert.toastSuccess(`Tipo de tarefa ${message} com sucesso!`);
    } else {
        NajAlert.toastWarning(response.mensagem);
    }

    await loadInputTipo();
    $('#modal-novo-tipo-tarefa').modal('hide');
    $('#modal-nova-tarefa-chat').removeClass('z-index-100');
    loadingDestroy('bloqueio-novo-tipo-tarefa');
}

async function onClickButtonPrioridadeTarefa() {
    let id_prioridade = $('#id_prioridade').val();

    if(id_prioridade) {
        $('#headerPrioridadeTarefa')[0].innerHTML = "Alterar Prioridade da Tarefa";
        let response = await NajApi.getData(`tarefa/prioridade/show/${btoa(JSON.stringify({"ID" : id_prioridade}))}`);

        if(response) {
            NajApi.loadData('#form-prioridade-tarefa', response);
            $('#is-alterar-prioridade-tarefa').val("1");
            $('#excluir-prioridade-tarefa').show();
        }
    } else {
        $('#headerPrioridadeTarefa')[0].innerHTML = "Nova Prioridade de Tarefa";
        $('#is-alterar-prioridade-tarefa').val("0");
        limpaFormulario('#form-prioridade-tarefa');
        $('#excluir-prioridade-tarefa').hide();
    }

    $('#modal-prioridade-tarefa').modal('show');
    $('#modal-nova-tarefa-chat').addClass('z-index-100');
}

async function storeUpdatePrioridadeTarefa() {
    let prioridadeTarefa = $('#PRIORIDADE').val();

    if(!prioridadeTarefa) {
        NajAlert.toastWarning("Você deve informar a prioridade da tarefa!");
        return;
    }

    let dados = {
        "PRIORIDADE": prioridadeTarefa
    };

    let is_update = $('#is-alterar-prioridade-tarefa').val(),
        response,
        message;

    loadingStart('bloqueio-prioridade-tarefa');

    if(is_update == "0") {
        response = await NajApi.postData(`${baseURL}tarefa/prioridade`, dados);
        message  = 'cadastrada';
    } else {
        response = await NajApi.updateData(`${baseURL}tarefa/prioridade/${btoa(JSON.stringify({id: $('[name=id_prioridade]').val()}))}`, dados);
        message  = 'alterada';
    }

    if(!response) {
        NajAlert.toastWarning(`Não é possível ${message} a prioridade da tarefa, tente novamente mais tarde!`);
    } else if(response.model) {
        NajAlert.toastSuccess(`Prioridade da tarefa ${message} com sucesso!`);
    } else {
        NajAlert.toastWarning(response.mensagem);
    }

    await loadInputPrioridade();
    $('#modal-prioridade-tarefa').modal('hide');
    $('#modal-nova-tarefa-chat').removeClass('z-index-100');
    loadingDestroy('bloqueio-prioridade-tarefa');
}

async function onClickNovaPessoaTarefa() {
    let codigo_pessoa = $('#codigo_cliente').val();

    if(codigo_pessoa) {
        sessionStorage.setItem('@NAJ_WEB/pessoa_action', 'edit');        
    } else {
        sessionStorage.setItem('@NAJ_WEB/pessoa_action', 'create');
    }
    
    await carregaModalManutencaoPessoa();
}

async function getDadosFormTarefa() {
    let horaPrazoInterno = $('#data_prazo_interno').val().split('T')[1],
        dataPrazoInterno = $('#data_prazo_interno').val().split('T')[0],
        horaPrazoFatal   = $('#data_prazo_fatal').val().split('T')[1],
        dataPrazoFatal   = $('#data_prazo_fatal').val().split('T')[0];

    const data_hora_compromisso_fatal   = `${dataPrazoFatal} ${horaPrazoFatal}:00`;
    const data_hora_compromisso_interno = `${dataPrazoInterno} ${horaPrazoInterno}:00`;

    let pessoa = await NajApi.getData(`${baseURL}pessoa/usuario/${$('#codigo_usuario_criacao').val()}`);

    if(!pessoa[0]) return false;

    if(!pessoa[0].pessoa_codigo && (pessoa[0].pessoa_codigo != 0)) {
        return false;
    }

    return {
        "codigo_divisao"        : $('#codigo_divisao_tarefa').val(),
        "codigo_cliente"        : $('#codigo_cliente').val(),
        "codigo_usuario_criacao": pessoa[0].pessoa_codigo,
        "codigo_responsavel"    : $('#codigo_responsavel').val(),
        "codigo_supervisor"     : $('#codigo_supervisor').val(),
        "descricao"             : $('#descricao').val(),
        "id_tipo"               : $('#id_tipo').val(),
        "id_situacao"           : 1,
        "id_prioridade"         : $('#id_prioridade').val(),
        "data_hora_criacao"     : $('#data_hora_criacao').val(),
        "data_prazo_interno"    : dataPrazoInterno,
        "data_prazo_fatal"      : dataPrazoFatal,
        "hora_prazo_fatal"      : horaPrazoFatal,
        "hora_prazo_interno"    : horaPrazoInterno,
        "data_hora_compromisso_interno" : data_hora_compromisso_interno,
        "data_hora_compromisso_fatal" : data_hora_compromisso_fatal
    };
}

function onClickNovoTipoTarefa() {
    $('#TIPO').val('');
    $('#is-alterar-tipo-tarefa').val("0");
    $('#headerTipoTarefa')[0].innerHTML = "Novo Tipo de Tarefa";
    $('#excluir-tipo-tarefa').hide();
}

function onClickNovaPrioridadeTarefa() {
    $('#PRIORIDADE').val('');
    $('#is-alterar-prioridade-tarefa').val("0");
    $('#headerPrioridadeTarefa')[0].innerHTML = "Nova Prioridade de Tarefa";
    $('#excluir-prioridade-tarefa').hide();
}

async function buscaDadosCliente(element) {
    if(!element.target || element.target.value == 0) {
        let content = `
            <div class="input-group col-12 p-0 m-0 content-table-input-ajax"></div>
            <div class="input-group col-12 content-footer-table-input-ajax">
                <div class="row">
                    <button type="button" class="btn btnLightCustom" title="Gravar" style="margin: 4px 0 5px 5px;" onclick="onClickNovaPessoaTarefa();"><i class="fas fa-plus"></i> Nova Pessoa</button>
                    <span class="span-contador-registros-table-input-ajax">Exibindo 0 registro(s)</span>
                </div>
            </div>
        `;

        updateListaDataTarefa(content, 'content-select-ajax-naj-nova-tarefa');
    } else {
        if(element.target.value.length < 3) {
            return;
        }
    
        setTimeout(async function() {
            result =  await searchDataTarefa(element.target.value, false);
            updateListaDataTarefa(result, 'content-select-ajax-naj-nova-tarefa');
        }, 500);
    }
}

async function buscaDadosSupervisor(element) {
    if(!element.target || element.target.value == 0) {
        let content = `
            <div class="input-group col-12 p-0 m-0 content-table-input-ajax"></div>
            <div class="input-group col-12 content-footer-table-input-ajax">
                <div class="row">
                    <button type="button" class="btn btnLightCustom" title="Gravar" style="margin: 4px 0 5px 5px;" onclick="onClickNovaPessoaTarefa();"><i class="fas fa-plus"></i> Nova Pessoa</button>
                    <span class="span-contador-registros-table-input-ajax">Exibindo 0 registro(s)</span>
                </div>
            </div>
        `;

        updateListaDataTarefa(content, 'content-select-ajax-naj-nova-tarefa-supervisor');
    } else {
        if(element.target.value.length < 3) {
            return;
        }
    
        setTimeout(async function() {
            result =  await searchDataTarefa(element.target.value);
            updateListaDataTarefa(result, 'content-select-ajax-naj-nova-tarefa-supervisor');
        }, 500);
    }
}

async function buscaDadosResponsavel(element) {
    if(!element.target || element.target.value == 0) {
        let content = `
            <div class="input-group col-12 p-0 m-0 content-table-input-ajax"></div>
            <div class="input-group col-12 content-footer-table-input-ajax">
                <div class="row">
                    <button type="button" class="btn btnLightCustom" title="Gravar" style="margin: 4px 0 5px 5px;" onclick="onClickNovaPessoaTarefa();"><i class="fas fa-plus"></i> Nova Pessoa</button>
                    <span class="span-contador-registros-table-input-ajax">Exibindo 0 registro(s)</span>
                </div>
            </div>
        `;

        updateListaDataTarefa(content, 'content-select-ajax-naj-nova-tarefa-responsavel');
    } else {
        if(element.target.value.length < 3) {
            return;
        }
    
        setTimeout(async function() {
            result =  await searchDataTarefa(element.target.value);
            updateListaDataTarefa(result, 'content-select-ajax-naj-nova-tarefa-responsavel');
        }, 500);
    }
}

async function onClickExcluirPrioridadeTarefa() {
    await NajApi.destroy(`${baseURL}tarefa/prioridade/many/${btoa(JSON.stringify({"ID": $('[name=id_prioridade]').val()}))}`);
    await loadInputPrioridade();
}

async function onClickExcluirTipoTarefa() {
    await NajApi.destroy(`${baseURL}tarefa/tipos/many/${btoa(JSON.stringify({"ID": $('#id_tipo').val()}))}`);
    await loadInputTipo();
}

function desabilitaCamposTarefa() {
    $('#codigo_divisao_tarefa')[0].disabled = true;
    $('#codigo_cliente')[0].disabled = true;
    $('#nome_cliente')[0].disabled = true;
    $('#id_tipo')[0].disabled = true;
    $('#descricao')[0].disabled = true;
    $('#codigo_supervisor')[0].disabled = true;
    $('#nome_supervisor')[0].disabled = true;
    $('#codigo_responsavel')[0].disabled = true;
    $('#nome_responsavel')[0].disabled = true;
    $('#id_situacao')[0].disabled = true;
    $('#id_prioridade')[0].disabled = true;
    $('#data_prazo_interno')[0].disabled = true;
    $('#data_prazo_fatal')[0].disabled = true;
}

function validaDataHoraPrazoInterno() {
    //VALIDA SE A DATA INFORMADA É VALIDA
    if(!$('#data_prazo_interno')[0].validity.valid) {
        return false;
    }

    return true;
}

function validaDataHoraPrazoFatal() {
    //VALIDA SE A DATA INFORMADA É VALIDA
    if(!$('#data_prazo_fatal')[0].validity.valid) {
        return false;
    }

    return true;
}

function validaDataHoraPrazoFatalMaiorPrazoInterno() {
    let data_hora_prazo_interno = $('#data_prazo_interno').val(),
        data_hora_prazo_fatal   = $('#data_prazo_fatal').val();

    //SE NÃO FOI INFORMADO A DATA TA TUDO CERTO, AFINAL NÃO É OBRIGATORIO INFORMAR
    if(!data_hora_prazo_fatal || !data_hora_prazo_interno) {
        return true;
    }

    let ano_interno  = data_hora_prazo_interno.split('T')[0].split('-')[0],
        mes_interno  = data_hora_prazo_interno.split('T')[0].split('-')[1],
        dia_interno  = data_hora_prazo_interno.split('T')[0].split('-')[2],
        hora_interno = data_hora_prazo_interno.split('T')[1].split(':')[0],
        min_interno  = data_hora_prazo_interno.split('T')[1].split(':')[1],
        ano_fatal    = data_hora_prazo_fatal.split('T')[0].split('-')[0],
        mes_fatal    = data_hora_prazo_fatal.split('T')[0].split('-')[1],
        dia_fatal    = data_hora_prazo_fatal.split('T')[0].split('-')[2],
        hora_fatal   = data_hora_prazo_fatal.split('T')[1].split(':')[0],
        min_fatal    = data_hora_prazo_fatal.split('T')[1].split(':')[1];

    if(ano_fatal < ano_interno) {
        return false;
    }

    if(ano_fatal == ano_interno && mes_fatal < mes_interno) {
        return false;
    }

    if(ano_fatal == ano_interno && mes_fatal == mes_interno && dia_fatal < dia_interno) {
        return false;
    }

    if(ano_fatal == ano_interno && mes_fatal == mes_interno && dia_fatal == dia_interno && hora_fatal < hora_interno) {
        return false;
    }

    if(ano_fatal == ano_interno && mes_fatal == mes_interno && dia_fatal == dia_interno && hora_fatal == hora_interno && min_fatal < min_interno) {
        return false;
    }

    return true;
}

function onClickButtonLimparPrazoInterno() {
    $('#data_prazo_interno').val('');
}

function onClickButtonLimparPrazoFatal() {
    $('#data_prazo_fatal').val('');
}

function onClickButtonAlterarTarefa() {
    let codigo_tarefa = $('#codigo_tarefa').val();

    if(!codigo_tarefa) {
        return;
    }

    window.open(`${najAntigoUrl}?idform=tarefas&tarefaid=${codigo_tarefa}`);
}