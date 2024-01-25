let codigoUsuarioTransferir;
let nomeUsuarioTransferir;

//---------------------- Functions -----------------------//
$(document).ready(function() {

    //Esconde caixa do campo de pesquisa do copiar
    $('#content-outside').click(function() {
        $("#content-select-ajax-naj-input-nome-pesquisa-transferir").hide();
    });

    //Realiza a busca do copiar
    $('#input-nome-pesquisa-receber').click(function(element) {
        if(element.target.value.length < 3) {
            return;
        }
    
        setTimeout(async function() {
            result =  await searchData(element.target.value);
            updateListaData(result);
        }, 500);
    });

    //Não faz nada por padrão
    $('#content-select-ajax-naj-input-nome-pesquisa-transferir').click(function(el) {
        $("#content-select-ajax-naj-input-nome-pesquisa-transferir").hide();
    });

});

function getUsuarios(element) {
    if(element.value.length < 3) {
        return;
    }

    setTimeout(async function() {
        result =  await searchData(element.value);
        updateListaData(result);
    }, 500);
}

async function searchData(value) {
    let content    = '';
    let filterUser = [
        {val: value, op: "C", col: "nome", origin: btoa({val: value, op: "C", col: "nome"})},
        {val: 1, val2: 2, op: "B", col: "usuario_tipo_id", origin: btoa({val: 1, val2: 2, op: "B", col: "usuario_tipo_id"})}
    ];

    let response   = await NajApi.getData(`${baseURL}usuarios/paginate?f=${btoa(JSON.stringify(filterUser))}`);
    if(response.resultado.length > 0) {
        for(var i = 0; i < response.resultado.length; i++) {
            content+= `
                <div class="row row-full content-rows-copiar" style="flex-wrap: nowrap !important;" onclick="onClickRowUsuarioSearch(this);">
                    <div class="col-sm-0 col-codigo" style="display: none;">${response.resultado[i].id}</div>
                    <div class="col-sm-8 col-name">${response.resultado[i].nome}</div>
                    <div class="col-sm-4 col-cpf">${(response.resultado[i].cpf) ? response.resultado[i].cpf : `Sem informação`}</div>
                </div>
            `;
        }
    } else {
        content += '<p class="text-center">Nenhum registro encontrado...</p>';
    }

    return content;
}

function updateListaData(data) {
    $('#content-select-ajax-naj-input-nome-pesquisa-transferir')[0].innerHTML = "";
    $('#content-select-ajax-naj-input-nome-pesquisa-transferir').append(data);
    $('#content-select-ajax-naj-input-nome-pesquisa-transferir').show();
}

function onClickRowUsuarioSearch(el) {
    codigoUsuarioTransferir = el.firstElementChild.textContent;
    nomeUsuarioTransferir   = el.childNodes[3].textContent;

    $("#input-nome-pesquisa-receber").val(el.childNodes[3].textContent);
}

async function onClickTransferirAtendimento() {
    if(codigoUsuarioTransferir == idUsuarioLogado) {
        NajAlert.toastError('Não é possível transferir para o mesmo usuário!');
        return;
    }

    await updateTransferChatAtendimento(codigoUsuarioTransferir, nomeUsuarioTransferir);
}