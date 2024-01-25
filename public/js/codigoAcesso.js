let codigoUsuario;
let codigoPessoaPermissao;
let keyExcluir;
//---------------------- Functions -----------------------//

$(document).ready(function() {
    
    $('#formCodigoAcesso').submit(function(e) {
        e.preventDefault();

        loadingStart('loading-codigo');
        $('#iconCodigoAcesso').removeClass();
        codigoAcesso = $('#codigo_acesso').val();

        if(!codigoAcesso) return;
        validaCodigoAcesso(codigoAcesso);
    });

    $('#content-select-ajax-naj').click(function(el) {
        let pai = el.target.parentElement;
        if(!pai.getElementsByClassName('col-codigo')[0]) return;

        addRowTable(
            {
                'pessoa_codigo': pai.getElementsByClassName('col-codigo')[0].textContent,
                'nome'         : pai.getElementsByClassName('col-name')[0].textContent,
                'cpf'          : (pai.getElementsByClassName('col-cpf')[0].textContent == null) ? '' : pai.getElementsByClassName('col-cpf')[0].textContent,
                'cidade'       : (pai.getElementsByClassName('col-cidade')[0].textContent == null) ? '' : pai.getElementsByClassName('col-cidade')[0].textContent 
            },
            true
        );
        $("#content-select-ajax-naj").hide();
    });

    $('#modal-codigo-acesso-usuario').click(function() {
        $("#content-select-ajax-naj").hide();
    });

    $('#input-nome-pesquisa').click(function(element) {
        if (element.target.value.length < 3) return;
    
        setTimeout(async function() {
            result =  await searchData(element.target.value);
            updateListaData(result);
        }, 500);
    });

    $('#modal-novo-relacionamento-usuario-pessoa').on('hidden.bs.modal', function() {
        $('#modal-codigo-acesso-usuario').removeClass('z-index-100');
    });

    $('#modal-codigo-acesso-usuario').on('hidden.bs.modal', function() {
        limpaFormulario('#formCadastroPessoa');
        $('#codigo_acesso').val('').trigger('change').trigger('input');

        $(`#recuperacao`).addClass('active');
        $('a.recuperacao').addClass('active');

        $(`#cadastro`).removeClass('active');
        $('a.cadastro').removeClass('active');
        $(`#finalizar`).removeClass('active');
        $('a.finalizar').removeClass('active');
        $(`#dispositivos`).removeClass('active');
        $('a.dispositivos').removeClass('active');

        $('#divResultadoUsuario')[0].innerHTML = ``;
        $('#iconCodigoAcesso').addClass('fas fa-check');
        $('#iconCodigoAcesso').removeClass('iconSuccess');
        $('#proximoAcesso')[0].disabled = true;
    });
});

async function onClickAvancarUsuario() {
    loadingStart('loading-codigo');

    empresa = sessionStorage.getItem('@NAJ_WEB/identificadorEmpresa');

    let data = {
        'id'                : codigoUsuario,
        'nome'              : $('input[name=nome]').val(),
        'apelido'           : $('input[name=apelido]').val(),
        'cpf'               : $('input[name=cpf]').val(),
        'login'             : $('input[name=login]').val(),
        'password'          : $('input[name=password]').val(),
        'email_recuperacao' : $('input[name=email_recuperacao]').val(),
        'mobile_recuperacao': $('input[name=mobile_recuperacao]').val().replace(/\D+/g, ''),
        'usuario_tipo_id'   : $('#usuario_tipo_id').val(),
        'status'            : 'A',
        'data_inclusao'     : $('input[name=data_inclusao]').val(),
        'codigo_pessoa'     : empresa,
        'pessoa_codigo'     : empresa,
        'codigoAcesso'      : true,
        "najWeb"            : 1,
            "items" : [
            {
                "pessoa_codigo": empresa,
                "usuario_id"   : 0
            }
        ]
    };

    //Se não tiver o código do usuário significa que não foi validado o código de acesso.
    // if(!codigoUsuario) {
    //     NajAlert.toastError('É necessário validar o código de acesso primeiramente!');
    //     loadingDestroy('loading-codigo');
    //     return;
    // }

    if(!$('input[name=data_inclusao]').val()) {
        NajAlert.toastError('É necessário informar a data de inclusão!');
        loadingDestroy('loading-codigo');
        return;
    }

    if (codigoUsuario)
        hasUsuario = await naj.getData(`${baseURL}usuarios/show/${btoa(JSON.stringify({id: codigoUsuario}))}`);

    //Se já existe o usuário não cadastra.
    if(codigoUsuario && hasUsuario && hasUsuario.id) {
        
        response = await naj.store(`${baseURL}usuarios/codigoAcesso?XDEBUG_SESSION_START`, data);
        
        loadTableRelacionamento(codigoUsuario);
        //Dispara o push do OneSignal
        shootNotificationOneSignal(codigoUsuario);
        loadingDestroy('loading-codigo');
        onClickAvancar('cadastro', 'finalizar');
        return;
    }

    response = await naj.store(`${baseURL}usuarios/codigoAcesso?XDEBUG_SESSION_START`, data);
    codigoUsuario = response.id;
    
    //Dispara o push do OneSignal
    shootNotificationOneSignal(codigoUsuario);

    //Carrega a tabela de relacionamentos
    loadTableRelacionamento(codigoUsuario);
    loadingDestroy('loading-codigo');
    onClickAvancar('cadastro', 'finalizar');
}

function onClickAvancar(remove, active) {
    $(`#${active}`).addClass('active');
    $('a.'+active+'').addClass('active');
    $(`#${remove}`).removeClass('active');
    $('a.'+remove+'').removeClass('active');
}

function onClickVoltar(remove, active) {
    $(`#${active}`).addClass('active');
    $('a.'+active+'').addClass('active');
    $(`#${remove}`).removeClass('active');
    $('a.'+remove+'').removeClass('active');
}

/**
 * Esconde os campos CPF/CNPJ conforme seleção.
 */
function onChangeTipoPessoa() {
    let oTipoPessoa = $("#tipopessoa"),
        oDivCpf     = $("#divcpf"),
        oDivCnpj    = $("#divcnpj"),
        oInputCpf   = $("#cpf"),
        oInputCnpj  = $("#cnpj");

    if (oTipoPessoa.val() === "J") {
        oInputCpf.val("");
        oDivCpf.hide();
        oDivCnpj.show();
    } else {
        oInputCnpj.val("");
        oDivCnpj.hide();
        oDivCpf.show();
    }
}

async function validaCodigoAcesso(cpf) {
    let response = await naj.getData(`${baseURL}usuarios/codigoAcesso/${cpf}?XDEBUG_SESSION_START`);

    if(response.usuario[0]) {
        $('#iconCodigoAcesso').addClass('fas fa-check');
        $('#iconCodigoAcesso').addClass('iconSuccess');
        $('#divResultadoUsuario')[0].innerHTML = `Usuário: ${response.usuario[0].nome}<br>CPF&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: ${response.usuario[0].cpf}`;
        $('#recuperacao #proximoAcesso')[0].disabled = false;
        limpaFormulario('#formCadastroPessoa');
        naj.loadData('#formCadastroPessoa', response.usuario[0]);

        //A senha é gerada aleatóriamente e o login é o cpf do usuário
        $('input[name=login]').val(response.usuario[0].cpf);
        $('input[name=password]').val(generationPassword());

        //A data de inclusão deve ser a data atual
        $('input[name=data_inclusao]').val(getDateProperties(new Date()).fullDate);

        $('#usuario_tipo_id')[0].disabled = true;
        $('#statusUser')[0].disabled      = true;
        $('#statusUser').val('A');
        $('#usuario_tipo_id').val('3');
        $('input[name=mobile_recuperacao]').trigger('change').trigger('input');

        codigoUsuario   = response.usuario[0].id;
        loadTableDispositivos(response.usuario[0].id);
    } else {
        let response = await naj.getData(`${baseURL}pessoas/cpf/${cpf}`);

        if (response && response.length > 0) {
            response = response[0]
            $('#iconCodigoAcesso').addClass('fas fa-check');
            $('#iconCodigoAcesso').addClass('iconSuccess');
            $('#divResultadoUsuario')[0].innerHTML = `Usuário: ${response.NOME}<br>CPF&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: ${response.CPF}`;
            $('#recuperacao #proximoAcesso')[0].disabled = false;
            limpaFormulario('#formCadastroPessoa');

            $('input[name=nome]').val(response.NOME);
            $('input[name=apelido]').val(response.NOME.split(' ')[0]);
            $('input[name=cpf]').val(response.CPF);

            // naj.loadData('#formCadastroPessoa', response);

            //A senha é gerada aleatóriamente e o login é o cpf do usuário
            $('input[name=login]').val(response.CPF);
            $('input[name=password]').val(generationPassword());

            //A data de inclusão deve ser a data atual
            $('input[name=data_inclusao]').val(getDateProperties(new Date()).fullDate);

            $('input[name=mobile_recuperacao]').trigger('change').trigger('input');

            $('#usuario_tipo_id')[0].disabled = true;
            $('#statusUser')[0].disabled      = true;
            $('#statusUser').val('A');
            $('#usuario_tipo_id').val('3');

            codigoUsuario = null;
            loadTableDispositivos(null);
        } else {

            $('#iconCodigoAcesso').addClass('fas fa-times');
            $('#iconCodigoAcesso').addClass('iconError');
            $('#divResultadoUsuario')[0].innerHTML = "O CPF informado é inválido!";
            $('#proximoAcesso')[0].disabled = true;
        }

    }

    $('#login')[0].disabled = true;
    loadingDestroy('loading-codigo');
}

async function addRowTable(data, callStore = false) {
    let item = $("#tbody-table-relacionamento").children("tr").last().attr("item");

    if (typeof(item)  === "undefined")
        item = 0;

    item++;

    if (callStore) {
        data.usuario_id = codigoUsuario;
        await naj.store(`${baseURL}usuarios/relacionamentos`, data);
    }

    let htmlPermissions = ``;

    if (data.agenda == 'S')
        htmlPermissions += `<span class="badge badge-warning mr-1">Agenda</span>`
    if (data.atividades == 'S')
        htmlPermissions += `<span class="badge badge-warning mr-1">Atividade</span>`
    if (data.contas_pagar == 'S')
        htmlPermissions += `<span class="badge badge-warning mr-1">Contas Pagar</span>`
    if (data.contas_receber == 'S')
        htmlPermissions += `<span class="badge badge-warning mr-1">Contas Receber</span>`
    if (data.processos == 'S')
        htmlPermissions += `<span class="badge badge-warning mr-1">Processos</span>`

    $('#table-relacionamento').append(
        `<tr item="${item}">
            <td>
                <i class="fas fa-trash btn-onedit iconDanger" title="Excluir" onclick="removeRowRelacionamento(${item});"></i>
                <i class="fas fa-edit btn-onedit cursor-pointer ml-2" onclick="addPermissions(${data.usuario_id}, ${data.pessoa_codigo});" title="Adicionar/Alterar Permissões"></i>
            </td>
            <td id="td-codigo-pessoa">${data.pessoa_codigo}</td>
            <td class="txt-oflo">${data.nome}</td>
            <td>${(data.cpf == null) ? '' : data.cpf}</td>
            <td>${(data.cidade == null) ? '' : data.cidade}</td>
            <td>${(htmlPermissions.length > 10) ? htmlPermissions : 'Sem permissões'}</td>
        </tr>`
    );
}

function removeRowRelacionamento(key) {
    keyExcluir = key;
    exibeModalExcluirRelacionamento();
}

function addPermissions(userId, personId) {
    loadPermissionsToClient(userId, personId);
}

async function loadTableRelacionamento(codigoUsuario) {
    filterUser = {val: codigoUsuario, op: "I", col: "usuario_id", origin: btoa({val: codigoUsuario, op: "I", col: "usuario_id"})}
    response = await naj.getData(`${baseURL}usuarios/relacionamentos/paginate?f=${btoa(JSON.stringify(filterUser))}`);

    if(response.resultado.length > 0) {
        content = '';
        $('#tbody-table-relacionamento')[0].innerHTML = "";

        for(var i = 0; i < response.resultado.length; i++)
            addRowTable(response.resultado[i]);
    }
}

async function loadTableDispositivos(codigoUsuario) {
    response = await naj.getData(`${baseURL}usuarios/dispositivos/${codigoUsuario}`);

    if(response.naj.length > 0) {
        content = '';
        for(var i = 0; i < response.naj.length; i++) {
            content += `
                <tr>
                    <td>${response.naj[i].id}</td>
                    <td>${response.naj[i].modelo}</td>
                    <td>${response.naj[i].versao_so}</td>
                    <td class="txt-oflo">
                        <input type="checkbox" onchange="onChangeDispositivoAtivo(this, '${response.naj[i].id}');" ${response.naj[i].ativo == 'A' ? 'checked' : ''} data-size="mini" />
                    </td>
                </tr>
            `
        }
        $('#table-content-dispositivos')[0].innerHTML = "";
        $('#table-content-dispositivos').append(content);
        $(".bt-switch input[type='checkbox'], .bt-switch input[type='radio']").bootstrapSwitch();
    }
}

async function onChangeDispositivoAtivo(element, id) {
    loadingStart('loading-codigo');
    let chave = btoa(JSON.stringify({ "id": id, "usuario_id": codigoUsuario }));

    let dados = { 'ativo': (element.checked) ? 'A' : 'B' };

    response = await naj.update(`${baseURL}usuarios/dispositivos/${chave}`, dados);
    loadingDestroy('loading-codigo');
}

async function getPessoaRelacionamento(element) {
    if(element.value.length < 3) return;

    setTimeout(async function() {
        result =  await searchData(element.value);
        updateListaData(result);
    }, 500);
}

async function searchData(value) {
    let content = '';
    response = await naj.getData(`${baseURL}pessoas/getPessoasFilter/${value}`);

    if (response.data.length > 0) {
        for(var i = 0; i < response.data.length; i++) {
            content+= `
                <div class="row row-full" style="flex-wrap: nowrap !important;">
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
    $('#content-select-ajax-naj')[0].innerHTML = "";
    $('#content-select-ajax-naj').append(data);
    $('#content-select-ajax-naj').show();
}

function exibeModalCodigoAcessoUsuario(el) {
    let hasPermission = perm('SenhaServicosAoCliente')

    //Apenas usuário do tipo supervisor ou adm podem liberar usuário. OBS: Qualquer coisa fala com Nelson, ele quem falou para validar isso!
    if(tipoUsuarioLogado != '0' && tipoUsuarioLogado != '1' && !hasPermission) {
        NajAlert.toastWarning('Apenas usuários do tipo Supervisor ou Administrador podem Liberar Acesso ao App!');
        return;
    }

    $('#modal-codigo-acesso-usuario').modal('show');
    $(el).tooltip('hide');
}

function exibeModalExcluirRelacionamento() {
    NajAlert.confirm({
        title: 'Atenção',
        text: `Você confirma a exclusão do registro?`
    }, {
        success: async () => {
            loadingStart('loading-codigo');

            try {
                let colCodigoPessoa = $('#tbody-table-relacionamento').children('tr[item='+keyExcluir+']').children('td[id=td-codigo-pessoa]');
                
                if(!colCodigoPessoa.text()) {
                    loadingDestroy('loading-codigo');
                    NajAlert.toastSuccess("Não foi possível excluir o registro, tente novamente mais tarde.");
                    return;
                } 
                chaveExc = btoa(JSON.stringify({"pessoa_codigo" : colCodigoPessoa.text(), "usuario_id": codigoUsuario}));

                const { data } = await api.delete(`${baseURL}usuarios/relacionamentos/many/${chaveExc}`);

                //Se não veio a mensagem de sucesso é pq deu merda.
                if(data.mensagem != "Operação realizada com sucesso.") {
                    loadingDestroy('loading-codigo');
                    return;
                }

                $('#tbody-table-relacionamento').children('tr[item='+keyExcluir+']').remove();

                NajAlert.toastSuccess(data.mensagem);
                loadingDestroy('loading-codigo');
            } catch(e) {
                NajAlert.toastError('Erro ao excluir o registro');

                loadingDestroy('loading-codigo');
            }
        }
    });
}

async function onClickFinalizarCodigoAcesso() {
    //Limpar todo o modal;
    $('#iconCodigoAcesso').addClass('fas fa-check');
    $('#iconCodigoAcesso').removeClass('iconSuccess');
    $('#divResultadoUsuario')[0].innerHTML = ``;
    $('#proximoAcesso')[0].disabled = true;
    $('input').val('');

    $('#table-content-dispositivos')[0].innerHTML = "";
    $('#tbody-table-relacionamento')[0].innerHTML = "";
    $('#modal-codigo-acesso-usuario').modal('hide');
}

function generationPassword() {
    return Math.random().toString(36).slice(-10);
}

async function shootNotificationOneSignal(codigoUsuario) {
    response = await naj.getData(`${baseURL}usuarios/dispositivos/${codigoUsuario}`);

    if(!response.naj[0]) return;

    const nome_empresa = sessionStorage.getItem('@NAJ_WEB/nomeEmpresa');
    let dados = {
        "app_id": "5bd804ab-bdd2-439f-b4ed-77c29bc6f766",
        "headings": {
            "en": "Liberação de Usuário"
        },
        "contents": {
            "en": `Seu usuário foi autorizado a consultar dados em: ${nome_empresa}`
        },
        "data": {
            "action": "@ACT/add_adv"
        },
        "include_player_ids": [
            response.naj[0].one_signal_id
        ]
    };
    
    axios({
        method : 'post',
        url    : 'https://onesignal.com/api/v1/notifications',
        data   : dados
    })
    .then(response => {
        NajAlert.toastSuccess("Sucesso, liberação enviada ao usuário!");
    }).catch(e => {
        NajAlert.toastError("Erro, liberação não enviada ao usuário!");
    });
}

async function loadPermissionsToClient(userId, personId) {
    let response = await naj.getData(`${baseURL}usuarios/relacionamentos/show/${btoa(JSON.stringify({'usuario_id': userId, 'pessoa_codigo': personId}))}`);

    if(!response) {
        NajAlert.toastWarning("Não foi possível buscar os dados, tente novamente mais tarde!");
        return;
    }

    if (response.pessoa_codigo)
        is_alteracao = true
    else 
        is_alteracao = false

    $('#contas_pagar')[0].checked   = (response.contas_pagar == 'S') ? true : false;
    $('#contas_receber')[0].checked = (response.contas_receber == 'S') ? true : false;
    $('#atividades')[0].checked     = (response.atividades == 'S') ? true : false;
    $('#processos')[0].checked      = (response.processos == 'S') ? true : false;
    $('#agenda')[0].checked         = (response.agenda == 'S') ? true : false;

    $('#modal-novo-relacionamento-usuario-pessoa').modal('show');
    $('#modal-codigo-acesso-usuario').addClass('z-index-100');
    codigoPessoaPermissao = response.pessoa_codigo;

    $('#form-novo-relacionamento-pessoa-usuario #input-nome-pesquisa').val(response.nome);
    $('#form-novo-relacionamento-pessoa-usuario #input-nome-pesquisa')[0].disabled = true;

}

async function onClickGravarNovoRelacionamento() {
    let dados = {
        "pessoa_codigo"  : codigoPessoaPermissao,
        "usuario_id"     : codigoUsuario,
        "contas_pagar"   : ($('#contas_pagar')[0].checked) ? 'S' : 'N',
        "contas_receber" : ($('#contas_receber')[0].checked) ? 'S' : 'N',
        "atividades"     : ($('#atividades')[0].checked) ? 'S' : 'N',
        "processos"      : ($('#processos')[0].checked) ? 'S' : 'N',
        "agenda"         : ($('#agenda')[0].checked) ? 'S' : 'N'
    };

    let pessoa = await naj.getData(`${baseURL}pessoa/usuario/${codigoUsuario}`);

    if(!pessoa[0]) return NajAlert.toastWarning("O usuário não possui nenhuma pessoa vinculado a ele, volte no cadastro e confirme o envio dos dados!");

    let isClient = await naj.getData(`${baseURL}pessoa/cliente/isPessoaCliente/${pessoa[0].pessoa_codigo}`);

    //Se for cliente não deixa dar permissão!
    if(isClient.length == 0) {
        NajAlert.toastWarning("É possível incluir um relacionamento apenas para usuário do tipo cliente!");
        $("#content-select-ajax-naj-relacionamento-usuarios").hide();
        return;
    }
    
    let result;

    //Identificando se é alteração ou inclusão
    if(is_alteracao) {
        let key = btoa(JSON.stringify({'usuario_id' : codigoUsuario, 'pessoa_codigo': codigoPessoaPermissao}));
        result = await naj.update(`${baseURL}usuarios/relacionamentos/${key}`, dados);
    } else {
        result = await naj.store(`${baseURL}usuarios/relacionamentos`, dados);
    }

    NajAlert.toastSuccess("Permissão inserido/alterado com sucesso!");

    $('#modal-novo-relacionamento-usuario-pessoa').modal('hide');
    loadTableRelacionamento(codigoUsuario);
}

$('.mascaracpf').mask('000.000.000-00', {placeholder: "___.___.___-__"});
$('.mascaracnpj').mask('00.000.000/0000-00', {placeholder: "__.___.___/____-__"});
$('.mascaracep').mask("00.000-000", {placeholder: "__.____-__"});