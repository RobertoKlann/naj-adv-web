let divisaoHasLoad = [];
let grupoHasLoadModuloEssencial = [];
let codDivisaoCurrent;
let pessoa_codigo;
let nomeDivisaoCurrent = 'MATRIZ';
let codigoUsuarioCopiar;

const checkAllModulos        = [];
const checkModulosEssenciais = [];

//---------------------- Functions -----------------------//
$(document).ready(function() {
    addClassCss('selected', '#sidebar-usuario');
    $('.nav-list-usuarios').children().removeClass('tabActiveNaj');
    addClassCss('tabActiveNaj', '#tabUsuarioPermissao');

    let usuario = JSON.parse(sessionStorage.getItem('@NAJ_WEB/usuario'));
    $('#headerPermissao')[0].innerHTML = `PERMISSÕES DE: ${usuario.nome}`;

    //Carregando as divisões
    loadDivisoesPermissao();

    $('#all-modulos-matriz').on('click', function() {
        let bChecked = this.checked,
            aChecks  = $(`#${nomeDivisaoCurrent} input[type=checkbox]`).not('.input-check-hasPermissao'),
            divisaoNome;
    
        for(var i = 0; i < checkAllModulos.length; i++) {
            if(checkAllModulos[i].divisao == nomeDivisaoCurrent) {
                checkAllModulos[i].checked = bChecked;
                divisaoNome = checkAllModulos[i].nomeDivisaoFormated;
            }
        }

        for(var i = 0; i < aChecks.length; i++) {
            aChecks[i].checked = bChecked;
        }

        let stringMarcadoDesmarcado = (bChecked) ? 'MARCADOS' : 'DESMARCADOS' ;

        NajAlert.toastSuccess('Todos os módulos foram ' + stringMarcadoDesmarcado + ' para a divisão: ' + divisaoNome);
    });

    $('#modulos-especiais').on('click', function() {
        let bChecked = this.checked;

        for(var i = 0; i < checkModulosEssenciais.length; i++) {
            if(checkModulosEssenciais[i].divisao == nomeDivisaoCurrent) {
                checkModulosEssenciais[i].checked = bChecked;
            }
        }

        //Se tiver desmarcando
        if(!bChecked) {
            hideModulosEspeciais();
        } else {
            loadModulosEspeciais();
        }
    });

    //Esconde caixa do campo de pesquisa do copiar
    $('#content-outside').click(function() {
        $("#content-select-ajax-naj").hide();
    });

    //Realiza a busca do copiar
    $('#input-nome-pesquisa').click(function(element) {
        if(element.target.value.length < 3) {
            return;
        }
    
        setTimeout(async function() {
            result =  await searchData(element.target.value);
            updateListaData(result);
        }, 500);
    });

    //Não faz nada por padrão
    $('#content-select-ajax-naj').click(function(el) {
        $("#content-select-ajax-naj").hide();
    });
});

async function loadGruposPermissao(divisao = 'S') {
    let sHtml        = '',
        sHtmlModulos = '',
        parameters   = btoa(JSON.stringify({"divisao": divisao}));
        aGrupos      = await naj.getData(`${baseURL}modulos/grupos/index/${parameters}`),
        sClassActive = 'active';

    for(var i = 0; i < aGrupos.length; i++) {
        sHtml += `
            <li class="nav-item">
                <a class="nav-link ${sClassActive} class-nav-grupo" data-toggle="tab" href="#${aGrupos[i].GRUPO}" role="tab">
                    <span class="hidden-sm-up">
                        <h4><i class="fas fa-lock"></i></h4>
                    </span>
                    <span class="span-li-permissao-naj d-none d-md-block ">${aGrupos[i].GRUPO}</span>
                </a>
            </li>
        `;
        sClassActive = '';

        let grupo = (aGrupos[i].GRUPO == 'Mala Direta') ? 'Mala_Direta' : aGrupos[i].GRUPO;

        sHtmlModulos += `
            <div class="tab-pane active content-full content-grupos-permissao-${nomeDivisaoCurrent}" id="${grupo}" role="tabpanel" style="max-height: 42vh;">
                <div class="content-full content-permissao-naj">
                    <div class="header-table-permissao">
                        <div class="border-0" style="width: 50%;">Módulo</div>
                        <div class="border-0" style="width: 10%;">
                            <div class="custom-control custom-checkbox" style="margin-left: -25px;">
                                <input type="checkbox" class="custom-control-input check-all-by-acao" id="acessar-check-${nomeDivisaoCurrent}-${grupo}">
                                <label class="custom-control-label" for="acessar-check-${nomeDivisaoCurrent}-${grupo}">&nbsp;</label>
                                Acessar
                            </div>
                        </div>
                        <div class="border-0" style="width: 10%;">
                            <div class="custom-control custom-checkbox" style="margin-left: -25px;">
                                <input type="checkbox" class="custom-control-input check-all-by-acao" id="pesquisar-check-${nomeDivisaoCurrent}-${grupo}">
                                <label class="custom-control-label" for="pesquisar-check-${nomeDivisaoCurrent}-${grupo}">&nbsp;</label>
                                Pesquisar
                            </div>
                        </div>
                        <div class="border-0" style="width: 10%;">
                            <div class="custom-control custom-checkbox" style="margin-left: -25px;">
                                <input type="checkbox" class="custom-control-input check-all-by-acao" id="incluir-check-${nomeDivisaoCurrent}-${grupo}">
                                <label class="custom-control-label" for="incluir-check-${nomeDivisaoCurrent}-${grupo}">&nbsp;</label>
                                Incluir
                            </div>
                        </div>
                        <div class="border-0" style="width: 10%;">
                            <div class="custom-control custom-checkbox" style="margin-left: -25px;">
                                <input type="checkbox" class="custom-control-input check-all-by-acao" id="alterar-check-${nomeDivisaoCurrent}-${grupo}">
                                <label class="custom-control-label" for="alterar-check-${nomeDivisaoCurrent}-${grupo}">&nbsp;</label>
                                Alterar
                            </div>
                        </div>
                        <div class="border-0" style="width: 10%;">
                            <div class="custom-control custom-checkbox" style="margin-left: -25px;">
                                <input type="checkbox" class="custom-control-input check-all-by-acao" id="excluir-check-${nomeDivisaoCurrent}-${grupo}">
                                <label class="custom-control-label" for="excluir-check-${nomeDivisaoCurrent}-${grupo}">&nbsp;</label>
                                Excluir
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive naj-scrollable table-permissao" style="height: 37vh;">
                        <table class="table text-muted mb-0 no-wrap recent-table font-light">                            
                            <tbody id="tbody-table-grupo">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    }

    $(`#${nomeDivisaoCurrent} #content-nav-tabs-permissao-${nomeDivisaoCurrent}`)[0].innerHTML = sHtml;
    $(`.content-all-divisao #page-content-${nomeDivisaoCurrent}`).append(sHtmlModulos);

    //Escondendo todos os grupos pra deixar visivel apenas o que já foi selecionado
    hideGruposInContent($(`.content-grupos-permissao-${nomeDivisaoCurrent}`));

    //Pegando o click nos grupos
    $('.class-nav-grupo').on('click', function(e) {
        onClickGrupoPermissao(this.href.split('#')[1]); 
    });

    //Pegando o click nas check master
    $('.check-all-by-acao').on('click', function(e) {
        checkAllAcaoByModuloByDivisao(e);
    });

    //Carregar os modulos do primeiro grupo
    onClickGrupoPermissao('Agenda');
}

async function onClickGrupoPermissao(grupo) {
    let sHtml    = '',
        aGrupos  = $(`div[id=${nomeDivisaoCurrent}] a`),
        especial;

    grupo = (grupo == "Mala%20Direta") ? "Mala_Direta" : grupo;

    //Busca se a divisão já foi carregada ou não
    hasLoadDivisao = divisaoHasLoad.find(function(itemDivisao) {
        return itemDivisao == nomeDivisaoCurrent;
    });

    //Se já carregou não carrega novamente, apenas exibe
    if(hasLoadDivisao) {
        hideGruposInContent($(`.content-grupos-permissao-${nomeDivisaoCurrent}`));
        $(`#page-content-${nomeDivisaoCurrent} #${grupo}`)[0].style.display = 'block';
        return;
    }

    loadingStart('div-bloqueio');

    //Montando os parametros para buscar os modulos do grupo
    usuario = JSON.parse(sessionStorage.getItem('@NAJ_WEB/usuario'));
    pessoa = await naj.getData(`${baseURL}pessoa/usuario/${usuario.id}`);
    
    //Se não encontrou a pessoa deu ruim, abandona tudo e avisa o cara que deu merda
    if(pessoa.length == 0) {
        NajAlert.toastError('Não foi possível buscar as permissões do usuário, usuário não tem uma pessoa vinculada!');
        loadingDestroy('div-bloqueio');
        return;
    } else {
        pessoa_codigo = pessoa[0].pessoa_codigo;
    }

    //Verifica se é para carregar os especiais ou não.
    for(var i = 0; i < checkModulosEssenciais.length; i++) {
        if(checkModulosEssenciais[i].divisao == nomeDivisaoCurrent) {
            especial = (checkModulosEssenciais[i].checked) ? 'SN' : 'N';
        }
    }

    for(var x = 0; x < aGrupos.length; x++) {
        let grupo      = aGrupos[x].href.split('#')[1];
        grupo = (grupo == "Mala%20Direta") ? "Mala_Direta" : grupo;

        let parametros = btoa(JSON.stringify({"grupo" : grupo, "divisao": codDivisaoCurrent, "pessoa_codigo": pessoa_codigo, "especial": especial, "filterDivisao": (nomeDivisaoCurrent == 'GLOBAL') ? 'N' : 'S'}));
        let aModulos = await naj.getData(`${baseURL}modulos/index/${parametros}`);

        sHtml = '';
        for(var i = 0; i < aModulos.length; i++) {
            let dataInputIdRowTrAcessar = `${nomeDivisaoCurrent}-${aModulos[i].id}-acessar-${i}`.replace(/\s/g, '');
            let dataInputIdRowTrPesquisar = `${nomeDivisaoCurrent}-${aModulos[i].id}-pesquisar-${i}`.replace(/\s/g, '');
            sHtml += `
                <tr class="tr-table-permissao advanced-table${(aModulos[i].especial == 'S') ? ' modulos-especiais' : ''}">
                    <td key="${(aModulos[i].id) ? aModulos[i].id : ''}" style="display:none;"></td>
                    <td style="width: 50%;">${aModulos[i].apelido}</td>
                    <td class="pl-3" style="width: 10%;">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" data-input-id-row-tr-acessar="${dataInputIdRowTrAcessar}" onclick="onClickCheckAcessarPesquisar('${dataInputIdRowTrPesquisar}', '${dataInputIdRowTrAcessar}', true);" class="custom-control-input ${nomeDivisaoCurrent}-${grupo}-acessar" id="${nomeDivisaoCurrent}-${aModulos[i].apelido}-acessar-${i}" ${(aModulos[i].acessar == 'S') ? 'checked=""' : ''}>
                            <label class="custom-control-label" for="${nomeDivisaoCurrent}-${aModulos[i].apelido}-acessar-${i}">&nbsp;</label>
                        </div>
                    </td>
                    <td class="pl-3" style="width: 10%;">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" data-input-id-row-tr-pesquisar="${dataInputIdRowTrPesquisar}" onclick="onClickCheckAcessarPesquisar('${dataInputIdRowTrPesquisar}', '${dataInputIdRowTrAcessar}', false);" class="custom-control-input ${nomeDivisaoCurrent}-${grupo}-pesquisar" id="${nomeDivisaoCurrent}-${aModulos[i].apelido}-pesquisar-${i}" ${(aModulos[i].pesquisar == 'S') ? 'checked=""' : ''}>
                            <label class="custom-control-label" for="${nomeDivisaoCurrent}-${aModulos[i].apelido}-pesquisar-${i}">&nbsp;</label>
                        </div>
                    </td>
                    <td class="pl-3" style="width: 10%;">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input ${nomeDivisaoCurrent}-${grupo}-incluir" id="${nomeDivisaoCurrent}-${aModulos[i].apelido}-incluir-${i}" ${(aModulos[i].incluir == 'S') ? 'checked=""' : ''}>
                            <label class="custom-control-label" for="${nomeDivisaoCurrent}-${aModulos[i].apelido}-incluir-${i}">&nbsp;</label>
                        </div>
                    </td>
                    <td class="pl-3" style="width: 10%;">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input ${nomeDivisaoCurrent}-${grupo}-alterar" id="${nomeDivisaoCurrent}-${aModulos[i].apelido}-alterar-${i}" ${(aModulos[i].alterar == 'S') ? 'checked=""' : ''}>
                            <label class="custom-control-label" for="${nomeDivisaoCurrent}-${aModulos[i].apelido}-alterar-${i}">&nbsp;</label>
                        </div>
                    </td>
                    <td class="pl-3" style="width: 10%;">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input ${nomeDivisaoCurrent}-${grupo}-excluir" id="${nomeDivisaoCurrent}-${aModulos[i].apelido}-excluir-${i}" ${(aModulos[i].excluir == 'S') ? 'checked=""' : ''}>
                            <label class="custom-control-label" for="${nomeDivisaoCurrent}-${aModulos[i].apelido}-excluir-${i}">&nbsp;</label>
                        </div>
                    </td>
                    <td class="pl-3" style="display:none;">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input input-check-hasPermissao" id="${nomeDivisaoCurrent}-${aModulos[i].apelido}-hasPermissao-${i}" ${(aModulos[i].hasPermissao == 'S') ? 'checked=""' : ''}>
                            <label class="custom-control-label" for="${nomeDivisaoCurrent}-${aModulos[i].apelido}-hasPermissao-${i}">&nbsp;</label>
                        </div>
                    </td>
                    <td style="display: none;">${aModulos[i].modulo}</td>
                </tr>
            `;
        }

        //Escondendo todos os grupos pra deixar visivel apenas o que já foi selecionado
        hideGruposInContent($(`.content-grupos-permissao-${nomeDivisaoCurrent}`));

        //Adicionando o HTML dos modulos do conteúdo na tela
        $(`.content-all-divisao #page-content-${nomeDivisaoCurrent} #${grupo} #tbody-table-grupo`).append(sHtml);

        //Adiciona no array para não carregar novamente caso o usuário volte ao grupo
        if(aModulos.length > 0) divisaoHasLoad.push(nomeDivisaoCurrent);
        if(aModulos.length > 0 && especial == 'SN') grupoHasLoadModuloEssencial.push({grupo, "divisao" : nomeDivisaoCurrent});
    }

    $(`#page-content-${nomeDivisaoCurrent} #${grupo}`)[0].style.display = 'block';

    loadingDestroy('div-bloqueio');
}

async function loadDivisoesPermissao() {
    let aDivisao             = await naj.getData(`${baseURL}divisoes/paginate`),
        sHtmlContentDivisoes = '',
        sHtmlListDivisoes    = '',
        nomeDivisaoMatriz    = '';

    //Adicionado o Globais
    sHtmlListDivisoes += `
        <li class="nav-item">
            <a href="javascript:void(0)" key="1" id="GLOBAL" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2 active class-nav-divisao" onclick="onClickGlobaisPermissao(this);">
                <span class="span-li-permissao-naj d-none d-md-block ">GLOBAL</span>
            </a>
        </li>
    `;

    //Adicionado o Globais
    sHtmlContentDivisoes += `
        <div class="col-md-12 single-note-item" id="GLOBAL">
            <div class="row">
                <div class="page-content container-fluid" id="page-content-GLOBAL">
                    <ul class="nav nav-tabs manage-tabs nav-permissao" role="tablist" style="font-size: 12px;" id="content-nav-tabs-permissao-GLOBAL">
                        
                    </ul>
                    <!-- Aqui vai os conteudos referentes aos grupos -->
                </div>
            </div>
        </div>
    `;
    //Adicionado o Globais
    checkAllModulos.push({"divisao": 'GLOBAL', "checked": false, "nomeDivisaoFormated" : "GLOBAL"});
    checkModulosEssenciais.push({"divisao": 'GLOBAL', "checked": false});

    for(var i = 0; i < aDivisao.resultado.length; i++) {
        let nomeDivisao = aDivisao.resultado[i].DIVISAO.replace(/ /g,""),
            qtdRemover  = nomeDivisao.length - 15;

        if(qtdRemover > 0) {
            nomeDivisao = aDivisao.resultado[i].DIVISAO.substr(0, 15) + '...';
        } else {
            nomeDivisao = aDivisao.resultado[i].DIVISAO;
        }

        sHtmlListDivisoes += `
            <li class="nav-item">
                <a key="${aDivisao.resultado[i].CODIGO}" id="${aDivisao.resultado[i].DIVISAO.replace(/ /g,"")}" href="javascript:void(0)" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2 class-nav-divisao" id="${aDivisao.resultado[i].DIVISAO}" onclick="onClickDivisaoPermissao(this);">
                    <span class="span-li-permissao-naj d-none d-md-block " title="${aDivisao.resultado[i].DIVISAO}">${nomeDivisao}</span>
                </a>
            </li>
        `;

        sHtmlContentDivisoes += `
            <div class="col-md-12 single-note-item" id="${aDivisao.resultado[i].DIVISAO.replace(/ /g,"")}">
                <div class="row">
                    <div class="page-content container-fluid" id="page-content-${aDivisao.resultado[i].DIVISAO.replace(/ /g,"")}">
                        <ul class="nav nav-tabs manage-tabs nav-permissao" role="tablist" style="font-size: 12px;" id="content-nav-tabs-permissao-${aDivisao.resultado[i].DIVISAO.replace(/ /g,"")}">
                            
                        </ul>
                        <!-- Aqui vai os conteudos referentes aos grupos -->
                    </div>
                </div>
            </div>
        `;

        //Se tiver MATRIZ no nome pega esse nome para utilizar como primeira divisao
        if(aDivisao.resultado[i].DIVISAO.indexOf('MATRIZ') == 0) {
            nomeDivisaoMatriz = aDivisao.resultado[i].DIVISAO.replace(/ /g,"");
        }

        codDivisaoCurrent  = (codDivisaoCurrent == undefined) ? aDivisao.resultado[i].CODIGO : codDivisaoCurrent;
        nomeDivisaoCurrent = (nomeDivisaoCurrent == undefined) ? aDivisao.resultado[i].DIVISAO.replace(/ /g,"") : nomeDivisaoCurrent;
        checkAllModulos.push({"divisao": aDivisao.resultado[i].DIVISAO.replace(/ /g,""), "checked": false, "nomeDivisaoFormated" : aDivisao.resultado[i].DIVISAO});
        checkModulosEssenciais.push({"divisao": aDivisao.resultado[i].DIVISAO.replace(/ /g,""), "checked": false});
    }

    //Atribuindo o nome da matriz como atual divisao selecionada
    nomeDivisaoCurrent = 'GLOBAL';

    $('#content-divisao')[0].innerHTML  = sHtmlListDivisoes;
    $('.content-all-divisao')[0].innerHTML = sHtmlContentDivisoes;

    //Deixando apenas a matriz inicialmente aberta
    let el = $(`div[id=${nomeDivisaoCurrent}]`).fadeIn();
    $('#note-full-container > div').not(el).hide();

    //Carregando os grupos dos modulos
    onClickGlobaisPermissao();
}

function onClickDivisaoPermissao(el) {
    let divisao       = el.getAttribute('id').replace(/ /g,""),
        codigoDivisao = el.getAttribute('key');

    //Busca se a divisão já foi carregada ou não
    hasLoad = divisaoHasLoad.find(function(itemDivisao) {
        return itemDivisao == divisao;
    });

    nomeDivisaoCurrent = divisao;
    codDivisaoCurrent  = codigoDivisao;

    hideDivisoesInContent($(`.single-note-item`));
    var elementoActive = $(`#content-divisao .active`);

    $(`#content-divisao #${elementoActive[0].id.replace(/ /g,"")}`).removeClass('active');
    $(`#content-divisao #${divisao}`).addClass('active');

    $(`div[id=${divisao}]`)[0].style.display = 'block';

    //Marca ou desmarca a check de todos os modulos conforme cada divisâo
    for(var i = 0; i < checkAllModulos.length; i++) {
        if(checkAllModulos[i].divisao == nomeDivisaoCurrent) {
            $('#all-modulos-matriz')[0].checked = checkAllModulos[i].checked;
        }
    }

    //Marca ou desmarca a check de modulos especiais conforme cada divisâo
    for(var i = 0; i < checkModulosEssenciais.length; i++) {
        if(checkModulosEssenciais[i].divisao == nomeDivisaoCurrent) {
            $('#modulos-especiais')[0].checked = checkModulosEssenciais[i].checked;
        }
    }

    //Se já carregou não carrega novamente
    if(hasLoad) return;

    //Carregando os grupos dos modulos
    loadGruposPermissao();
}

function hideGruposInContent(aGruposInContent) {
    for(var i = 0; i < aGruposInContent.length; i++) {
        aGruposInContent[i].style.display = 'none';
    }
}

function hideDivisoesInContent(aDivisoes) {
    for(var i = 0; i < aDivisoes.length; i++) {
        aDivisoes[i].style.display = 'none';
    }
}

/**
 * Grava as permissões, evento disparado no clique do botão GRAVAR
 */
async function onClickGravarPermissao() {
    let bAllModulos,
        aPermissao,
        divisaoNome;

    //Apenas usuário do tipo supervisor ou adm podem dar permissão. OBS: Qualquer coisa fala com Nelson, ele quem falou para validar isso!
    if(tipoUsuarioLogado != '0' && tipoUsuarioLogado != '1') {
        NajAlert.toastWarning('Apenas usuários do tipo Supervisor ou Administrador podem Incluir/Alterar as permissões!');
        return;
    }

    //Buscando se a check de todos os modulos está marcada
    for(var i = 0; i < checkAllModulos.length; i++) {
        if(checkAllModulos[i].divisao == nomeDivisaoCurrent) {
            bAllModulos = checkAllModulos[i].checked;
            divisaoNome = checkAllModulos[i].nomeDivisaoFormated;
        }
    }
    debugger

    if(bAllModulos) {
        NajAlert.confirm({
            title: 'Atenção',
            text: `Você confirma a inclusão/alteração das permissões deste usuário?`
        }, {
            success: async () => {
                loadingStart('div-bloqueio');
                try {

                    usuario = JSON.parse(sessionStorage.getItem('@NAJ_WEB/usuario'));
                    //Se for cliente não deixa dar permissão!
                    if(usuario.usuario_tipo_id == '3') {
                        NajAlert.toastSuccess("Não é possível dar permissão para usuário tipo cliente!");
                        loadingDestroy('div-bloqueio');
                        return;
                    }
                    
                    aPermissao = loadPermissaoStoreUpdate();
                    result = await naj.postData(`${baseURL}usuarios/permissoes`, {"permissao": aPermissao});

                    if(result.mensagem) {
                        NajAlert.toastSuccess("Registro incluido/alterado com sucesso!");                        
                        await loadPermissionsToUser()
                        loadingDestroy('div-bloqueio');

                        window.location.href = window.location.href;
                    } else {
                        NajAlert.toastError("Não foi possível inclui/alterar os registros!");

                        loadingDestroy('div-bloqueio');
                    }
                } catch(e) {
                    NajAlert.toastError('Erro ao incluir/alterar o registro');
    
                    loadingDestroy('div-bloqueio');
                }
            }
        });
    } else {
        loadingStart('div-bloqueio');
        usuario = JSON.parse(sessionStorage.getItem('@NAJ_WEB/usuario'));

        //Se for cliente não deixa dar permissão!
        if(usuario.usuario_tipo_id == '3') {
            NajAlert.toastSuccess("Não é possível dar permissão para usuário tipo cliente!");
            loadingDestroy('div-bloqueio');
            return;
        }

        aPermissao = loadPermissaoStoreUpdate();
        result     = await naj.postData(`${baseURL}usuarios/permissoes?XDEBUG_SESSION_START`, {"permissao": aPermissao});

        if(result) {
            NajAlert.toastSuccess("Registro incluido/alterado com sucesso!");
            await loadPermissionsToUser()
            loadingDestroy('div-bloqueio');
            window.location.href = window.location.href;
        } else {
            NajAlert.toastError("Não foi possível inclui/alterar os registros!");
            loadingDestroy('div-bloqueio');
        }
    }
}

function hideModulosEspeciais() {
    $(`#${nomeDivisaoCurrent} #tbody-table-grupo .modulos-especiais`).hide();
}

async function loadModulosEspeciais() {
    let sHtml = '',
        aGrupos  = $(`div[id=${nomeDivisaoCurrent}] a`);

    loadingStart('div-bloqueio');

    //Montando os parametros para buscar os modulos do grupo
    usuario = JSON.parse(sessionStorage.getItem('@NAJ_WEB/usuario'));
    pessoa = await naj.getData(`${baseURL}pessoa/usuario/${usuario.id}`);
    
    //Se não encontrou a pessoa deu ruim, abandona tudo e avisa o cara que deu merda
    if(pessoa.length == 0) {
        NajAlert.toastError('Não foi possível buscar as permissões do usuário, usuário não tem uma pessoa vinculada!');
        loadingDestroy('div-bloqueio');
        return;
    } else {
        pessoa_codigo = pessoa[0].pessoa_codigo;
    }

    for(var j = 0; j < aGrupos.length; j++) {
        let grupo = aGrupos[j].href.split('#')[1];

        grupo = (grupo == "Mala%20Direta") ? "Mala_Direta" : grupo;

        //Busca se o grupo já foi carregado ou não
        hasLoadModEspecial = grupoHasLoadModuloEssencial.find(function(itemGrupo) {
            return itemGrupo.divisao == nomeDivisaoCurrent && itemGrupo.grupo == grupo;
        });

        //Se já carregou pula
        if(hasLoadModEspecial) {
            $(`#${nomeDivisaoCurrent} #tbody-table-grupo .modulos-especiais`).show();
            break;
        }

        let parametros = btoa(JSON.stringify({"grupo" : grupo, "divisao": codDivisaoCurrent, "pessoa_codigo": pessoa_codigo, "especial": 'S', "filterDivisao": (nomeDivisaoCurrent == 'GLOBAL') ? 'N' : 'S'}));
        let aModulos = await naj.getData(`${baseURL}modulos/index/${parametros}?XDEBUG_SESSION_START`);

        sHtml = '';
        for(var i = 0; i < aModulos.length; i++) {
            sHtml += `
                <tr class="tr-table-permissao advanced-table modulos-especiais">
                    <td key="${(aModulos[i].id) ? aModulos[i].id : ''}" style="display:none;"></td>
                    <td>${aModulos[i].apelido}<span class="badge text-white font-normal badge-warning blue-grey-text text-darken-4 m-2">Especial</span></td>
                    <td class="pl-3" style="width: 10%;">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input ${nomeDivisaoCurrent}-${grupo}-acessar" id="${nomeDivisaoCurrent}-${aModulos[i].apelido}-acessar-${i}" ${(aModulos[i].acessar == 'S') ? 'checked=""' : ''}>
                            <label class="custom-control-label" for="${nomeDivisaoCurrent}-${aModulos[i].apelido}-acessar-${i}">&nbsp;</label>
                        </div>
                    </td>
                    <td class="pl-3" style="width: 10%;">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input ${nomeDivisaoCurrent}-${grupo}-pesquisar" id="${nomeDivisaoCurrent}-${aModulos[i].apelido}-pesquisar-${i}" ${(aModulos[i].pesquisar == 'S') ? 'checked=""' : ''}>
                            <label class="custom-control-label" for="${nomeDivisaoCurrent}-${aModulos[i].apelido}-pesquisar-${i}">&nbsp;</label>
                        </div>
                    </td>
                    <td class="pl-3" style="width: 10%;">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input ${nomeDivisaoCurrent}-${grupo}-incluir" id="${nomeDivisaoCurrent}-${aModulos[i].apelido}-incluir-${i}" ${(aModulos[i].incluir == 'S') ? 'checked=""' : ''}>
                            <label class="custom-control-label" for="${nomeDivisaoCurrent}-${aModulos[i].apelido}-incluir-${i}">&nbsp;</label>
                        </div>
                    </td>
                    <td class="pl-3" style="width: 10%;">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input ${nomeDivisaoCurrent}-${grupo}-alterar" id="${nomeDivisaoCurrent}-${aModulos[i].apelido}-alterar-${i}" ${(aModulos[i].alterar == 'S') ? 'checked=""' : ''}>
                            <label class="custom-control-label" for="${nomeDivisaoCurrent}-${aModulos[i].apelido}-alterar-${i}">&nbsp;</label>
                        </div>
                    </td>
                    <td class="pl-3" style="width: 10%;">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input ${nomeDivisaoCurrent}-${grupo}-excluir" id="${nomeDivisaoCurrent}-${aModulos[i].apelido}-excluir-${i}" ${(aModulos[i].excluir == 'S') ? 'checked=""' : ''}>
                            <label class="custom-control-label" for="${nomeDivisaoCurrent}-${aModulos[i].apelido}-excluir-${i}">&nbsp;</label>
                        </div>
                    </td>
                    <td class="pl-3" style="display:none;">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input input-check-hasPermissao" id="${nomeDivisaoCurrent}-${aModulos[i].apelido}-hasPermissao-${i}" ${(aModulos[i].hasPermissao == 'S') ? 'checked=""' : ''}>
                            <label class="custom-control-label" for="${nomeDivisaoCurrent}-${aModulos[i].apelido}-hasPermissao-${i}">&nbsp;</label>
                        </div>
                    </td>
                    <td style="display: none;">${aModulos[i].modulo}</td>
                </tr>
            `;
        }

        $(`#${nomeDivisaoCurrent} #${grupo} #tbody-table-grupo`).append(sHtml);
        if(aModulos.length > 0) grupoHasLoadModuloEssencial.push({"grupo": grupo, "divisao" : nomeDivisaoCurrent});
    }
    
    loadingDestroy('div-bloqueio');
}

//////////////////////Funções utilizadas para carregar a tab GLOBAIS ///////////////////////////////

async function onClickGlobaisPermissao() {
    //Busca se a divisão já foi carregada ou não
    hasLoad = divisaoHasLoad.find(function(itemDivisao) {
        return itemDivisao == 'GLOBAL';
    });

    nomeDivisaoCurrent = 'GLOBAL';
    codDivisaoCurrent  = 1;

    hideDivisoesInContent($(`.single-note-item`));
    var elementoActive = $(`#content-divisao .active`);

    $(`#content-divisao #${elementoActive[0].id.replace(/ /g,"")}`).removeClass('active');
    $(`#content-divisao #GLOBAL`).addClass('active');

    $(`div[id=GLOBAL]`)[0].style.display = 'block';

    //Marca ou desmarca a check de todos os modulos conforme cada divisâo
    for(var i = 0; i < checkAllModulos.length; i++) {
        if(checkAllModulos[i].divisao == nomeDivisaoCurrent) {
            $('#all-modulos-matriz')[0].checked = checkAllModulos[i].checked;
        }
    }

    //Marca ou desmarca a check de modulos especiais conforme cada divisâo
    for(var i = 0; i < checkModulosEssenciais.length; i++) {
        if(checkModulosEssenciais[i].divisao == nomeDivisaoCurrent) {
            $('#modulos-especiais')[0].checked = checkModulosEssenciais[i].checked;
        }
    }

    //Se já carregou não carrega novamente
    if(hasLoad) return;

    //Carregando os grupos dos modulos
    loadGruposPermissao('N');
}

/**
 * Carrega um array com todos os modulos da divisão selecionada
 * 
 * @returns []
 */
function loadPermissaoStoreUpdate() {
    let aAllPermissao = [],
        aDivisao      = $('#content-divisao a');

    //Percorre todas as divisão
    for(var z = 0; z < aDivisao.length; z++) {
        let divisaoCodAtual = aDivisao[z].getAttribute('key'),
            divisaoAtual    = aDivisao[z].getAttribute('id'),
            aGrupos         = $(`div[id=${divisaoAtual}] a`)
            aPermissao      = [];

        //Percorre todos os grupos para pegar todos os modulos
        for(var i = 0; i < aGrupos.length; i++) {
            let nomeGrupo = aGrupos[i].href.split('#')[1];

            nomeGrupo = (nomeGrupo == "Mala%20Direta") ? "Mala_Direta" : nomeGrupo;
            let linhas = $(`div[id=${divisaoAtual}] #${nomeGrupo} #tbody-table-grupo tr`);

            //Percorrendo todas as linhas para pegar os modulos
            for(var j = 0; j < linhas.length; j++) {
                let colunasLinhas = linhas[j].children;

                aPermissao.push({
                    "id"            : colunasLinhas[0].getAttribute('key'),
                    "codigo_divisao": divisaoCodAtual,
                    "codigo_pessoa" : pessoa_codigo,
                    "modulo"        : colunasLinhas[8].textContent,
                    "acessar"       : (colunasLinhas[2].children[0].children[0].checked) ? 'S' : 'N',
                    "pesquisar"     : (colunasLinhas[3].children[0].children[0].checked) ? 'S' : 'N',
                    "incluir"       : (colunasLinhas[4].children[0].children[0].checked) ? 'S' : 'N',
                    "alterar"       : (colunasLinhas[5].children[0].children[0].checked) ? 'S' : 'N',
                    "excluir"       : (colunasLinhas[6].children[0].children[0].checked) ? 'S' : 'N',
                    "hasPermissao"  : (colunasLinhas[7].children[0].children[0].checked) ? 'S' : 'N'
                });
            }
        }

        aAllPermissao.push(aPermissao);
    }

    return aAllPermissao;
}

//////////////////////Funções utilizadas para o copiar perfil ///////////////////////////////

async function onClickCopiarPerfil(el) {
    $('#modal-copiar-permissao-usuario').modal('show');
    $(el).tooltip('hide');

    $("#input-nome-pesquisa").val("");
    $("#content-select-ajax-naj").hide();
    codigoUsuarioCopiar = 0;

    //Carrega os dados do usuário
    usuario = JSON.parse(sessionStorage.getItem('@NAJ_WEB/usuario'));
    response = await naj.getData(`${baseURL}usuarios/show/${btoa(JSON.stringify({id: usuario.id}))}`);
    $('input[name=codigo_usuario]').val(response.nome);
}

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
    let content = '';

    filterUser = {val: value, op: "C", col: "nome", origin: btoa({val: value, op: "C", col: "nome"})};
    response   = await naj.getData(`${baseURL}usuarios/paginate?f=${btoa(JSON.stringify(filterUser))}`);
    if(response.resultado.length > 0) {
        for(var i = 0; i < response.resultado.length; i++) {
            content+= `
                <div class="row row-full content-rows-copiar" style="flex-wrap: nowrap !important;" onclick="onClickRowUsuarioSearch(this);">
                    <div class="col-sm-0 col-codigo" style="display: none;">${response.resultado[i].id}</div>
                    <div class="col-sm-8 col-name">${response.resultado[i].nome}</div>
                    <div class="col-sm-4 col-cpf">${response.resultado[i].cpf}</div>
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

function onClickRowUsuarioSearch(el) {
    codigoUsuarioCopiar = el.firstElementChild.textContent;

    $("#input-nome-pesquisa").val(el.childNodes[3].textContent);
}

async function onClickCopiarPermissao() {
    loadingStart('bloqueio-copiar-permissao');

    let usuario = JSON.parse(sessionStorage.getItem('@NAJ_WEB/usuario'));

    //Se for cliente não deixa dar permissão!
    if(usuario.usuario_tipo_id == '3') {
        NajAlert.toastSuccess("Não é possível dar permissão para usuário tipo cliente!");
        loadingDestroy('div-bloqueio');
        return;
    }

    //Apenas usuário do tipo supervisor ou adm podem copiar. OBS: Qualquer coisa fala com Nelson, ele quem falou para validar isso!
    if(tipoUsuarioLogado != '0' && tipoUsuarioLogado != '1') {
        NajAlert.toastWarning('Apenas usuários do tipo Supervisor ou Administrador podem Copiar as permissões!');
        return;
    }

    //Se não tiver o usuário
    if(!codigoUsuarioCopiar) {
        NajAlert.toastWarning('Informe o usuário que recebá as permissões!');
        loadingDestroy('bloqueio-copiar-permissao');
        return;
    }

    //Montando os parametros para buscar os modulos do grupo
    usuario = JSON.parse(sessionStorage.getItem('@NAJ_WEB/usuario'));

    pessoaDono = await naj.getData(`${baseURL}pessoa/usuario/${codigoUsuarioCopiar}`);
    pessoa     = await naj.getData(`${baseURL}pessoa/usuario/${usuario.id}`);

    if(!pessoaDono[0] || !pessoa[0]) {
        NajAlert.toastError('Não foi possível copiar as permissões, tente novamente mais tarde!');
        loadingDestroy('bloqueio-copiar-permissao');
        return;
    }

    if(pessoaDono[0].pessoa_codigo == pessoa[0].pessoa_codigo) {
        NajAlert.toastError('Operação inválida, devem ser informados usuários diferentes!');
        loadingDestroy('bloqueio-copiar-permissao');
        return;
    }

    response = await naj.postData(`${baseURL}usuarios/permissoes/copiar`, {"pessoa_codigo": pessoaDono[0].pessoa_codigo, "pessoa_codigo_destino" : pessoa[0].pessoa_codigo});

    if(response.mensagem == "O usuário informado não possui permissões!") {
        NajAlert.toastError(response.mensagem);
        loadingDestroy('bloqueio-copiar-permissao');
    } else if(response.mensagem) {
        NajAlert.toastSuccess("Permissões copiadas com sucesso!");
        loadingDestroy('bloqueio-copiar-permissao');
        window.location.href = window.location.href;
    } else {
        NajAlert.toastError("Não foi possível copiar as permissões!");
        loadingDestroy('bloqueio-copiar-permissao');
    }
}

function checkAllAcaoByModuloByDivisao(e) {
    const grupo = e.currentTarget.parentElement.parentElement.parentElement.parentElement.parentElement.id,
          acao  = e.currentTarget.id.split('-')[0];
        
    let aChecks  = $(`.${nomeDivisaoCurrent}-${grupo}-${acao}`),
        bChecked = e.currentTarget.checked;

    //se for acessar ou pesquisar o grupo faz umas paradas diferentes
    if(acao == 'acessar' || acao == 'pesquisar') {
        let cheksAcessar   = $(`.${nomeDivisaoCurrent}-${grupo}-acessar`);
        let cheksPesquisar = $(`.${nomeDivisaoCurrent}-${grupo}-pesquisar`);

        for(var i = 0; i < cheksAcessar.length; i++) {
            cheksAcessar[i].checked = bChecked;
            $(`#acessar-check-${nomeDivisaoCurrent}-${grupo}`)[0].checked = bChecked;
        }

        for(var i = 0; i < cheksPesquisar.length; i++) {
            cheksPesquisar[i].checked = bChecked;
            $(`#pesquisar-check-${nomeDivisaoCurrent}-${grupo}`)[0].checked = bChecked;
        }
    } else {
        for(var i = 0; i < aChecks.length; i++) {
            aChecks[i].checked = bChecked;
        }
    }
}

function onClickCheckAcessarPesquisar(namePesquisar, nameAcessar, usaInputPesquisar) {
    let bChecked = !$(`[data-input-id-row-tr-acessar=${nameAcessar}]`)[0].checked;

    if(usaInputPesquisar)
        bChecked = !$(`[data-input-id-row-tr-pesquisar=${namePesquisar}]`)[0].checked;

    $(`[data-input-id-row-tr-pesquisar=${namePesquisar}]`)[0].checked = bChecked;
    $(`[data-input-id-row-tr-acessar=${nameAcessar}]`)[0].checked = bChecked;
}