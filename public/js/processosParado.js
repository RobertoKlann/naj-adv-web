let tableProcessosParado = new ProcessosParadoTable();
const NajApi = new Naj('Processos Parado', tableProcessosParado);

$(document).ready(() => {
    loadTableProcessosParado();

    $(document).on("click", '#search-button', function () {
        tableProcessosParado.load();
    });

    //Ao clicar em "iconCodigoProcesso"...
    $(document).on('click', '.iconCodigoProcesso', function() {
        let codigo = this.parentElement.innerText;

        if(tableProcessosParado.selectedRows.length == 0){
            //Seta o checkbox da linha como 'checked'
            this.parentElement.parentElement.parentElement.classList.add('row-selected');
            //Desmarca o 'checked' do checkbox da linha
            this.parentElement.parentElement.parentElement.querySelector('input[type=checkbox]').checked = true;
        }

        onClickFichaProcesso(codigo);
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

async function loadTableProcessosParado() {
    await loadConfigSystem();
    await tableProcessosParado.render();
    await loadCustomFiltersProcessosParado();
}

async function loadCustomFiltersProcessosParado() {
    let selected = {
        'um': '',
        'dois': '',
        'tres': '',
        'seis': '',
        'doze': '',
    };

    switch(CONFIG.PROCESSOS.PRC_PARADOS_PERIODO) {
        case '1':
            selected.um = 'selected';
        break;

        case '2':
            selected.tres = 'selected';

        break;

        case '6':
            selected.seis = 'selected';
        break;

        case '12':
            selected.doze = 'selected';
        break;

        default:
            selected.tres = 'selected';
    }

    const htmlFilter = content =  `
        <div style="display: flex;" class="font-12">
            <div style="display: flex; align-items: center;" class="m-1">
                <span>Situação dos Processo: </span>
            </div>
            <select id="filter-situacao-processo" class="mt-1 mr-1 mb-1 col-2">
                <option value="S" selected>Somente Ativos</option>
                <option value="N">Somente Baixados</option>
                <option value="All">Todos</option>
            </select>
            <div style="display: flex; align-items: center;" class="m-1">
                <span>Sem Movimentações: </span>
            </div>
            <select id="filter-movimentacao-processo" class="mt-1 mr-1 mb-1 col-2">
                <option value="1" ${selected.um}>Mais de 1 Mês</option>
                <option value="2" ${selected.dois}>Mais de 2 Meses</option>
                <option value="3" ${selected.tres}>Mais de 3 Meses</option>
                <option value="6" ${selected.seis}>Mais de 6 Meses</option>
                <option value="12" ${selected.doze}>Mais de 1 Ano</option>
            </select>
            <div class="custom-control custom-checkbox" style="display: flex; align-items: center;" class="m-1">
                <input class="custom-control-input" type="checkbox" value="1" ${CONFIG.PROCESSOS.PRC_PARADOS_ATIVIDADES == 'SIM' ? 'checked' : ''} id="sem-atividades" name="sem-atividades">
                <label class="custom-control-label ml-2" for="sem-atividades">Sem Atividades</label><br>
            </div>
            <div class="custom-control custom-checkbox" style="display: flex; align-items: center;" class="m-1">
                <input class="custom-control-input" type="checkbox" ${CONFIG.PROCESSOS.PRC_PARADOS_ANDAMENTOS == 'SIM' ? 'checked' : ''} value="1" id="sem-andamentos" name="sem-andamentos">
                <label class="custom-control-label ml-2" for="sem-andamentos">Sem Andamentos</label><br>
            </div>
            <div class="custom-control custom-checkbox" style="display: flex; align-items: center;" class="m-2">
                <input class="custom-control-input" type="checkbox" ${CONFIG.PROCESSOS.PRC_PARADOS_INTIMACAO == 'SIM' ? 'checked' : ''} value="1" id="sem-intimacao" name="sem-intimacao">
                <label class="custom-control-label ml-2" for="sem-intimacao">Sem Intimações</label><br>
            </div>
            <button id="search-button" class="btn btnCustom action-in-button m-1">
                <i class="fas fa-search btn-icon"></i>&nbsp;&nbsp;
                Pesquisar
            </button>
            <span class="ml-1 mt-2">
                <i class="fas fa-info-circle" style="font-size: 15px; margin-top: 3px;"  data-toggle="tooltip" data-placement="top" title="" data-original-title="Quanto MAIOR o número de opções MARCADAS, MENOR serão os resultados com processos parados, pois AUMENTA-SE a chance de encontrar informações que MOVIMENTARAM o processo!"></i>
            </span>
        </div>
    `;

    $('.data-table-filter').html(htmlFilter);
    $('.fa-info-circle').tooltip('update');
}

function onClickCollapseAndamentoProcesso(text, codigo, date, el) {
    if(el.children) {
        let className = el.children.item(0).className;

        if(className == 'fas fa-chevron-circle-up icone-partes-processo-expanded') {
            el.children.item(0).className = 'fas fa-chevron-circle-right icone-partes-processo-expanded';

            text = text.substr(0, 60) + '...';
        } else {
            el.children.item(0).className = 'fas fa-chevron-circle-up icone-partes-processo-expanded';
        }
    }

    let sHtml = `
        <span id="andamento-processo-${codigo}">
            ${date} - ${text}
        </span>
    `;

    $(`#andamento-processo-${codigo}`)[0].innerHTML = sHtml;
    $('.fa-info-circle').tooltip('update');
}

function onClickCollapseAtividadeProcesso(text, codigo, date, el) {
    if(el.children) {
        let className = el.children.item(0).className;

        if(className == 'fas fa-chevron-circle-up icone-partes-processo-expanded') {
            el.children.item(0).className = 'fas fa-chevron-circle-right icone-partes-processo-expanded';

            text = text.substr(0, 60) + '...';
        } else {
            el.children.item(0).className = 'fas fa-chevron-circle-up icone-partes-processo-expanded';
        }
    }

    let sHtml = `
        <span id="atividade-processo-${codigo}">
            ${date} - ${text}
        </span>
    `;

    $(`#atividade-processo-${codigo}`)[0].innerHTML = sHtml;
    $('.fa-info-circle').tooltip('update');
}

function onClickCollapseIntimacaoProcesso(text, codigo, date, el) {
    if(el.children) {
        let className = el.children.item(0).className;

        if(className == 'fas fa-chevron-circle-up icone-partes-processo-expanded') {
            el.children.item(0).className = 'fas fa-chevron-circle-right icone-partes-processo-expanded';

            text = text.substr(0, 60) + '...';
        } else {
            el.children.item(0).className = 'fas fa-chevron-circle-up icone-partes-processo-expanded';
        }
    }

    let sHtml = `
        <span id="intimacao-processo-${codigo}">
            ${date} - ${text}
        </span>
    `;

    $(`#intimacao-processo-${codigo}`)[0].innerHTML = sHtml;
    $('.fa-info-circle').tooltip('update');
}

async function onClickEnvolvidosProcesso(codigo, el) {
    let parameters = btoa(JSON.stringify({codigo})),
        envolvidos = await NajApi.getData(`${baseURL}processos/partes/cliente/${parameters}`),
        sHtml      = '';

    if(el.children) {
        let className = el.children.item(0).className;

        if(className == 'fas fa-chevron-circle-up icone-partes-processo-expanded') {
            el.children.item(0).className = 'fas fa-chevron-circle-right icone-partes-processo-expanded';
            return;
        }
        el.children.item(0).className = 'fas fa-chevron-circle-up icone-partes-processo-expanded';
    }

    for(var indice = 0; indice < envolvidos.length; indice++) {
        sHtml += `
            <div class="row" style="width: 100%; height: 20px !important;">
                <div class="col-12" style="margin-left: 5% !important;">
                    ${(envolvidos[indice].NOME.length > 55) 
                    ?
                    `${envolvidos[indice].NOME.substr(0, 50)}...
                        <span class="ml-1">
                            <i class="fas fa-info-circle" style="font-size: 14px;" data-toggle="tooltip" data-placement="top" title="${envolvidos[indice].NOME}"></i>
                        </span>
                    `
                    :
                    `${envolvidos[indice].NOME}`} (${envolvidos[indice].QUALIFICACAO})
                </div>
            </div>
        `;
    }

    $(`#partes-processo-${codigo}`)[0].innerHTML = sHtml;
    $('.fa-info-circle').tooltip('update');
}

async function onClickEnvolvidosProcessoAdv(codigo, el) {
    let parameters = btoa(JSON.stringify({codigo})),
        envolvidos = await NajApi.getData(`${baseURL}processos/partes/adversaria/${parameters}`),
        sHtml      = '';

    if(el.children) {
        let className = el.children.item(0).className;

        if(className == 'fas fa-chevron-circle-up icone-partes-processo-expanded') {
            el.children.item(0).className = 'fas fa-chevron-circle-right icone-partes-processo-expanded';
            return;
        }
        el.children.item(0).className = 'fas fa-chevron-circle-up icone-partes-processo-expanded';
    }

    for(var indice = 0; indice < envolvidos.length; indice++) {
        sHtml += `
            <div class="row" style="width: 100%; height: 20px !important;">
                <div class="col-12" style="margin-left: 5% !important;">
                    ${(envolvidos[indice].NOME.length > 55) 
                    ?
                    `${envolvidos[indice].NOME.substr(0, 50)}...
                        <span class="ml-1">
                            <i class="fas fa-info-circle" style="font-size: 14px;" data-toggle="tooltip" data-placement="top" title="${envolvidos[indice].NOME}"></i>
                        </span>
                    `
                    :
                    `${envolvidos[indice].NOME}`} (${envolvidos[indice].QUALIFICACAO})
                </div>
            </div>
        `;
    }

    $(`#partes-adv-processo-${codigo}`)[0].innerHTML = sHtml;
    $('.fa-info-circle').tooltip('update');
}

async function loadConfigSystem() {
    await axios({
        method: 'get',
        url: `${baseURL}configuracao/padrao`
    }).then(response => {
        if (!response.data) return;

        sessionStorage.setItem('@NAJ_WEB/configuracaoSistema', JSON.stringify(response.data));

        CONFIG = response.data;
    });
}

function onClickFichaProcesso(codigo) {
    window.open(`${najAntigoUrl}?idform=processos&processoid=${codigo}`);
}

async function exportarProcessoParado() {
    const status = $('#filter-situacao-processo').val()
    const period = $('#filter-movimentacao-processo').val()

    let filterProcess = ''

    filterProcess += `status=${status}`
    filterProcess += `&period=${period}`

    let withoutActivits  = $('[name=sem-atividades]')[0].checked
    let withoutProgress  = $('[name=sem-andamentos]')[0].checked
    let withoutIntimacao = $('[name=sem-intimacao]')[0].checked
    
    if(withoutActivits)
        filterProcess += `&withoutActivits=${withoutActivits}`

    if(withoutProgress)
        filterProcess += `&withoutProgress=${withoutProgress}`

    if(withoutIntimacao)
        filterProcess += `&withoutIntimacao=${withoutIntimacao}`

    const data = await NajApi.getData(`${baseURL}processos/parado/paginate?${filterProcess}&usuario_id=${idUsuarioLogado}&limit=1000000&page=1`)
    console.log(data.resultado)

    let html = ``

    for (var i = 0; i < data.resultado.length; i++) {
        let rowOutrasInfo = getDataRowOutrasInfo(data.resultado[i])
        let rowInfoProcesso = getDataRowInfoProcesso(data.resultado[i])
        let rowNomePartes = getDataRowNomePartes(data.resultado[i])

        html += `
            <tr>
                <td>${data.resultado[i].CODIGO_PROCESSO}</td>
                <td>${rowOutrasInfo}</td>
                <td>${rowInfoProcesso}</td>
                <td>${rowNomePartes}</td>
            </tr>
        `
    }

    const content = `
        <table border="1">
            <thead>
                <th>Código</th>
                <th>Outras Informações</th>
                <th>Informações do Processo</th>
                <th>Nome das Partes</th>
            </thead>
            <tbody>
                ${html}
            </tbody>
        </table>
    `

    return exportToExcel(content)
}

function getDataRowOutrasInfo(row) {
    let novas_atividades = '<tr><td><span class="title-andamento-atividade-processo-parado">Última atividade: </span><span>Não há informações</span></td></tr>';
    let novos_andamentos = '<tr><td><span class="title-andamento-atividade-processo-parado">Último andamento: </span><span>Não há informações</span></td></tr>';
    let novas_intimacoes = '<tr><td><span class="title-andamento-atividade-processo-parado">Última intimação: </span><span>Não há informações</span></td></tr>';

    if(row.ULTIMA_ATIVIDADE_DATA && row.ULTIMA_ATIVIDADE_DESCRICAO) {
        if(row.ULTIMA_ATIVIDADE_DESCRICAO.length > 60) {
            novas_atividades = `
                <tr style="margin-top: 5px !important;">
                    <td>
                        <span class="title-andamento-atividade-processo-parado">Última atividade: </span>
                        <span id="atividade-processo-${row.CODIGO_PROCESSO}">
                            ${formatDate(row.ULTIMA_ATIVIDADE_DATA, true, true)} - ${row.ULTIMA_ATIVIDADE_DESCRICAO}
                        </span>
                        <span class="action-icons">
                            <a data-toggle="collapse" data-key-processo="${row.CODIGO_PROCESSO}" aria-expanded="false" onclick="onClickCollapseAtividadeProcesso('${row.ULTIMA_ATIVIDADE_DESCRICAO.replace(/(\r\n|\n|\r)/gm, " ")}', ${row.CODIGO_PROCESSO}, '${formatDate(row.ULTIMA_ATIVIDADE_DATA, true, true)}', this);">
                                <i class="fas fa-chevron-circle-right icone-partes-processo-expanded" title="Clique para ampliar e ver a última atividade" data-toggle="tooltip"></i>
                            </a>
                        </span>
                    </td>
                </tr>
            `;
        } else {
            novas_atividades = `
                <tr style="margin-top: 5px !important;">
                    <td>
                        <span class="title-andamento-atividade-processo-parado">Última atividade: </span>
                        ${formatDate(row.ULTIMA_ATIVIDADE_DATA, true, true)} - ${row.ULTIMA_ATIVIDADE_DESCRICAO}
                    </td>
                </tr>
            `;
        }
    }

    if(row.ULTIMO_ANDAMENTO_DATA && row.ULTIMO_ANDAMENTO_DESCRICAO) {
        if(row.ULTIMO_ANDAMENTO_DESCRICAO.length > 60) {
            novos_andamentos = `
                <tr style="margin-top: 5px !important;">
                    <td>
                        <span class="title-andamento-atividade-processo-parado">Último andamento: </span>
                        <span id="andamento-processo-${row.CODIGO_PROCESSO}">
                            ${formatDate(row.ULTIMO_ANDAMENTO_DATA, true, true)} - ${row.ULTIMO_ANDAMENTO_DESCRICAO}
                        </span>
                        <span class="action-icons">
                            <a data-toggle="collapse" data-key-processo="${row.CODIGO_PROCESSO}" aria-expanded="false" onclick="onClickCollapseAndamentoProcesso('${row.ULTIMO_ANDAMENTO_DESCRICAO.replace(/(\r\n|\n|\r)/gm, " ")}', ${row.CODIGO_PROCESSO}, '${formatDate(row.ULTIMO_ANDAMENTO_DATA, true, true)}', this);">
                                <i class="fas fa-chevron-circle-right icone-partes-processo-expanded" title="Clique para ampliar e ver o último andamento" data-toggle="tooltip"></i>
                            </a>
                        </span>
                    </td>
                </tr>
            `;
        } else {
            novos_andamentos = `
                <tr style="margin-top: 5px !important;">
                    <td>
                        <span class="title-andamento-atividade-processo-parado">Último andamento: </span>
                        ${formatDate(row.ULTIMO_ANDAMENTO_DATA, true, true)} - ${row.ULTIMO_ANDAMENTO_DESCRICAO}
                    </td>
                </tr>
            `;
        }
    }

    if(row.ULTIMA_INTIMACAO_DATA && row.ULTIMA_INTIMACAO_DESCRICAO) {
        if(row.ULTIMA_INTIMACAO_DESCRICAO.length > 60) {
            novas_intimacoes = `
                <tr style="margin-top: 5px !important;">
                    <td>
                        <span class="title-andamento-atividade-processo-parado">Última intimação: </span>
                        <span id="intimacao-processo-${row.CODIGO_PROCESSO}">
                            ${formatDate(row.ULTIMA_INTIMACAO_DATA)} - ${row.ULTIMA_INTIMACAO_DESCRICAO}
                        </span>
                        <span class="action-icons">
                            <a data-toggle="collapse" data-key-processo="${row.CODIGO_PROCESSO}" aria-expanded="false" onclick="onClickCollapseIntimacaoProcesso('${row.ULTIMA_INTIMACAO_DESCRICAO.replace(/(\r\n|\n|\r)/gm, " ")}', ${row.CODIGO_PROCESSO}, '${formatDate(row.ULTIMA_INTIMACAO_DATA)}', this);">
                                <i class="fas fa-chevron-circle-right icone-partes-processo-expanded" title="Clique para ampliar e ver a última intimação" data-toggle="tooltip"></i>
                            </a>
                        </span>
                    </td>
                </tr>
            `;
        } else {
            novas_intimacoes = `
                <tr style="margin-top: 5px !important;">
                    <td>
                        <span class="title-andamento-atividade-processo-parado">Última intimação: </span>
                        ${formatDate(row.ULTIMA_INTIMACAO_DATA)} - ${row.ULTIMA_INTIMACAO_DESCRICAO}
                    </td>
                </tr>
            `;
        }
    }

    return `
        <table class="row-atividade-andamento-processo">
            <tr style="margin-top: 5px !important;">
                ${novas_atividades}
            </tr>
            <tr style="margin-top: 5px !important;">
                ${novos_andamentos}
            </tr>
            <tr style="margin-top: 5px !important;">
                ${novas_intimacoes}
            </tr>
        </table>
    `;
}

function getDataRowInfoProcesso(row) {
    let classeCss = (row.SITUACAO == "ENCERRADO") ? 'badge-danger' : 'badge-success';
    let situacao  = (row.SITUACAO == "ENCERRADO") ? 'Baixado' : 'Em andamento';

    return `
        <table>
            <tr>
                <td>${row.NUMERO_PROCESSO_NEW}</td>
            </tr>                        
            ${(row.CLASSE)
                ?
                `<tr>
                    <td>${row.CLASSE}</td>
                </tr>
                `
                : ``
            }
            ${(row.CARTORIO && row.COMARCA && row.COMARCA_UF)
                ?
                `<tr>
                    <td>${row.CARTORIO} - ${row.COMARCA} (${row.COMARCA_UF})</td>
                </tr>
                `
                : ``
            }
            <tr>
                <td>
                    ${row.GRAU_JURISDICAO}
                    <span class="badge ${classeCss} badge-rounded badge-status-processo">${situacao}</span>
                </td>
            </tr>
        </table>
    `;
}

function getDataRowNomePartes(row) {
    let sHtmlQtdeClientes = '';
    let sHtmlEnvolvidos   = '';
    let sHtmlAdversarios   = '';
    let sHtmlEnvolvidosAdv = '';

    if(row.QTDE_CLIENTES) {
        sHtmlQtdeClientes = `<span class="badge badge-secondary badge-rounded badge-nome-partes-processo" title="+${row.QTDE_CLIENTES} Envolvido(s)">+${row.QTDE_CLIENTES} Envolvido(s)</span>`;
        sHtmlEnvolvidos   = `
            <span class="action-icons">
                <a data-toggle="collapse" href="#partes-processo-${row.CODIGO_PROCESSO}" data-key-processo="${row.CODIGO_PROCESSO}" aria-expanded="false" onclick="onClickEnvolvidosProcesso(${row.CODIGO_PROCESSO}, this);">
                    <i class="fas fa-chevron-circle-right icone-partes-processo-expanded" title="Clique para ver os envolvidos" data-toggle="tooltip"></i>
                </a>
            </span>
        `;
    }

    if(row.QTDE_ADVERSARIOS) {
        sHtmlAdversarios   = `<span class="badge badge-secondary badge-rounded badge-nome-partes-processo" title="+${row.QTDE_ADVERSARIOS} Envolvido(s)">+${row.QTDE_ADVERSARIOS} Envolvido(s)</span>`;
        sHtmlEnvolvidosAdv = `
            <span class="action-icons">
                <a data-toggle="collapse" href="#partes-adv-processo-${row.CODIGO_PROCESSO}" data-key-processo="${row.CODIGO_PROCESSO}" aria-expanded="false" onclick="onClickEnvolvidosProcessoAdv(${row.CODIGO_PROCESSO}, this);">
                    <i class="fas fa-chevron-circle-right icone-partes-processo-expanded" title="Clique para ver os envolvidos" data-toggle="tooltip"></i>
                </a>
            </span>
        `;
    }

    return `
        <table class="w-100 ml-2">
            <tr>
                <td class="td-nome-parte-cliente">${row.NOME_CLIENTE} (${row.QUALIFICA_CLIENTE})</td>
            </tr>
            <tr>
                <td class="td-nome-parte-cliente">
                    <div class="row" style="width: 100% !important; margin-left: 1px !important;">
                        ${sHtmlQtdeClientes}${sHtmlEnvolvidos}
                    </div>
                </td>
            </tr>
            <tr class="collapse well" id="partes-processo-${row.CODIGO_PROCESSO}" aria-expanded="false"></tr>
            ${(row.NOME_ADVERSARIO)
                ?
                `<tr>
                    <td>${row.NOME_ADVERSARIO} (${row.QUALIFICA_ADVERSARIO})</td>
                </tr>
                <tr>
                    <td class="td-nome-parte-cliente">
                        <div class="row" style="width: 100% !important; margin-left: 1px !important;">
                            ${sHtmlAdversarios}${sHtmlEnvolvidosAdv}
                        </div>
                    </td>
                </tr>
                <tr class="collapse well" id="partes-adv-processo-${row.CODIGO_PROCESSO}" aria-expanded="false"></tr>
                `
                : ``
            }                        
        </table>
    `;
}

// function getDataRowNomePartes(row) {
//     let sHtmlQtdeClientes = '';
//     let sHtmlEnvolvidos   = '';
//     let sHtmlAdversarios   = '';
//     let sHtmlEnvolvidosAdv = '';

//     if(row.QTDE_CLIENTES) {
//         sHtmlQtdeClientes = `<span class="badge badge-secondary badge-rounded badge-nome-partes-processo" title="+${row.QTDE_CLIENTES} Envolvido(s)">+${row.QTDE_CLIENTES} Envolvido(s)</span>`;
//         sHtmlEnvolvidos   = `
//             <span class="action-icons">
//                 <a data-toggle="collapse" href="#partes-processo-${row.CODIGO_PROCESSO}" data-key-processo="${row.CODIGO_PROCESSO}" aria-expanded="false" onclick="onClickEnvolvidosProcesso(${row.CODIGO_PROCESSO}, this);">
//                     <i class="fas fa-chevron-circle-right icone-partes-processo-expanded" title="Clique para ver os envolvidos" data-toggle="tooltip"></i>
//                 </a>
//             </span>
//         `;
//     }

//     if(row.QTDE_ADVERSARIOS) {
//         sHtmlAdversarios   = `<span class="badge badge-secondary badge-rounded badge-nome-partes-processo" title="+${row.QTDE_ADVERSARIOS} Envolvido(s)">+${row.QTDE_ADVERSARIOS} Envolvido(s)</span>`;
//         sHtmlEnvolvidosAdv = `
//             <span class="action-icons">
//                 <a data-toggle="collapse" href="#partes-adv-processo-${row.CODIGO_PROCESSO}" data-key-processo="${row.CODIGO_PROCESSO}" aria-expanded="false" onclick="onClickEnvolvidosProcessoAdv(${row.CODIGO_PROCESSO}, this);">
//                     <i class="fas fa-chevron-circle-right icone-partes-processo-expanded" title="Clique para ver os envolvidos" data-toggle="tooltip"></i>
//                 </a>
//             </span>
//         `;
//     }

//     return `
//         <table class="w-100 ml-2">
//             <tr>
//                 <td class="td-nome-parte-cliente">${row.NOME_CLIENTE} (${row.QUALIFICA_CLIENTE})</td>
//             </tr>
//             <tr>
//                 <td class="td-nome-parte-cliente">
//                     <div class="row" style="width: 100% !important; margin-left: 1px !important;">
//                         ${sHtmlQtdeClientes}${sHtmlEnvolvidos}
//                     </div>
//                 </td>
//             </tr>
//             <tr class="collapse well" id="partes-processo-${row.CODIGO_PROCESSO}" aria-expanded="false"></tr>
//             ${(row.NOME_ADVERSARIO)
//                 ?
//                 `<tr>
//                     <td>${row.NOME_ADVERSARIO} (${row.QUALIFICA_ADVERSARIO})</td>
//                 </tr>
//                 <tr>
//                     <td class="td-nome-parte-cliente">
//                         <div class="row" style="width: 100% !important; margin-left: 1px !important;">
//                             ${sHtmlAdversarios}${sHtmlEnvolvidosAdv}
//                         </div>
//                     </td>
//                 </tr>
//                 <tr class="collapse well" id="partes-adv-processo-${row.CODIGO_PROCESSO}" aria-expanded="false"></tr>
//                 `
//                 : ``
//             }                        
//         </table>
//     `;
// }