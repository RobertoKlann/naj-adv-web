const tableRel       = new RelacionamentoTable;
let codigoPessoa;
let is_alteracao;

//---------------------- Functions -----------------------//

$(document).ready(function() {

    //Carregando a tabela
    tableRel.render();
    addClassCss('selected', '#sidebar-usuario');    

    //Add novo relacionamento
    $('#content-select-ajax-naj-relacionamento-usuarios').click(function(el) {
        onClickContentSelectAjaxRelacionamento(el);
    });

    //Esconde caixa do campo de pesquisa
    $('#content-outside-novo-rel').on('click', function() {
        $("#content-select-ajax-naj-relacionamento-usuarios").hide();
    });

    //Realiza a busca
    $('#input-nome-pesquisa').click(function(element) {
        if(element.target.value.length < 3) {
            return;
        }
    
        setTimeout(async function() {
            result =  await searchData(element.target.value);
            updateListaData(result);
        }, 500);
    });
});

function getPessoaRelacionamento(element) {
    if(element.value.length < 3) {
        return;
    }

    setTimeout(async function() {
        result =  await searchData(element.value);
        updateListaData(result);
    }, 500);
}

async function searchData(value) {
    let content = '';
    response = await naj.getData(`${baseURL}pessoas/getPessoasFilter/${value}`);
    if(response.data.length > 0) {
        for(var i = 0; i < response.data.length; i++) {
            content+= `
                <div class="row row-full content-rows-permissao" style="flex-wrap: nowrap !important;">
                    <div class="col-sm-0 col-codigo" style="display: none;">${response.data[i].pessoa_codigo}</div>
                    <div class="col-lg-6 col-md-6 col-sm-8 col-name">${response.data[i].nome}</div>
                    <div class="col-lg-3 col-md-3 col-sm-4 col-cpf">${(response.data[i].cpf) ? response.data[i].cpf : response.data[i].cnpj}</div>
                    <div class="col-lg-3 col-md-3 col-sm-3 col-cidade">${(response.data[i].cidade == null) ? '' : response.data[i].cidade}</div>
                </div>
            `;
        }
    } else {
        content += '<p class="text-center">Nenhum registro encontrado...</p>';
    }

    return content;
}

function updateListaData(data) {
    $('#content-select-ajax-naj-relacionamento-usuarios')[0].innerHTML = "";
    $('#content-select-ajax-naj-relacionamento-usuarios').append(data);
    $('#content-select-ajax-naj-relacionamento-usuarios').show();
}

async function onClickContentSelectAjaxRelacionamento(el) {
    let pai = el.target.parentElement;
    if(!pai.getElementsByClassName('col-codigo')[0]) return;
    codigoPessoa = pai.getElementsByClassName('col-codigo')[0].textContent;

    $('#input-nome-pesquisa').val(pai.getElementsByClassName('col-name')[0].textContent);
    
    $("#content-select-ajax-naj-relacionamento-usuarios").hide();
}


function exibeModalNovoRelacionamento() {
    is_alteracao = false;
    $('#input-nome-pesquisa').val('');
    $('#input-nome-pesquisa')[0].disabled = false;

    $('#contas_pagar')[0].checked   = false;
    $('#contas_receber')[0].checked = false;
    $('#atividades')[0].checked     = false;
    $('#processos')[0].checked      = false;
    $('#agenda')[0].checked         = false;
    $('#modal-novo-relacionamento-usuario-pessoa').modal('show');
}

async function onClickGravarNovoRelacionamento() {
    let usuario = JSON.parse(sessionStorage.getItem('@NAJ_WEB/usuario_key'));

    if(usuario.id) {
        usuario = usuario.id;
    }

    let dados = {
        "pessoa_codigo"  : codigoPessoa,
        "usuario_id"     : usuario,
        "contas_pagar"   : ($('#contas_pagar')[0].checked) ? 'S' : 'N',
        "contas_receber" : ($('#contas_receber')[0].checked) ? 'S' : 'N',
        "atividades"     : ($('#atividades')[0].checked) ? 'S' : 'N',
        "processos"      : ($('#processos')[0].checked) ? 'S' : 'N',
        "agenda"         : ($('#agenda')[0].checked) ? 'S' : 'N'
    };

    let pessoa = await naj.getData(`${baseURL}pessoa/usuario/${usuario}`);

    if(!pessoa[0])
        return NajAlert.toastWarning("O usuário não possui nenhuma pessoa vinculado a ele, volte no cadastro e confirme o envio dos dados!");

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
        let key = btoa(JSON.stringify({'usuario_id' : usuario, 'pessoa_codigo': codigoPessoa}));
        result = await naj.update(`${baseURL}usuarios/relacionamentos/${key}`, dados);
    } else {
        result = await naj.store(`${baseURL}usuarios/relacionamentos`, dados);
    }

    NajAlert.toastSuccess("Relacionamento inserido/alterado com sucesso!");

    $('#modal-novo-relacionamento-usuario-pessoa').modal('hide');
    tableRel.load();
}

async function loadDadosAlteracaoRelacionamentoPessoaUsuario(key) {
    let response = await naj.getData(`${baseURL}usuarios/relacionamentos/show/${btoa(JSON.stringify({'usuario_id': key.usuario_id, 'pessoa_codigo': key.pessoa_codigo}))}`);

    if(!response) {
        NajAlert.toastWarning("Não foi possível buscar os dados, tente novamente mais tarde!");
        return;
    }

    $('#contas_pagar')[0].checked   = (response.contas_pagar == 'S') ? true : false;
    $('#contas_receber')[0].checked = (response.contas_receber == 'S') ? true : false;
    $('#atividades')[0].checked     = (response.atividades == 'S') ? true : false;
    $('#processos')[0].checked      = (response.processos == 'S') ? true : false;
    $('#agenda')[0].checked         = (response.agenda == 'S') ? true : false;

    codigoPessoa = response.pessoa_codigo;
    is_alteracao = true;
    $('#input-nome-pesquisa').val(response.nome);
    $('#input-nome-pesquisa')[0].disabled = true;

    $('#modal-novo-relacionamento-usuario-pessoa').modal('show');
}