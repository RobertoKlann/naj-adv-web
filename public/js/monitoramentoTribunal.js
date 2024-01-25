//---------------------- Parâmetros -----------------------//

const rotaBaseTribunal             = 'monitoramento/tribunais';
const tableTribunal                = (isIndex(rotaBaseTribunal)) ? new MonitoramentoTribunalTable : false;
const najTribunal                  = new Naj('MonitoramentoTribunal', tableTribunal);
let   indexTribunal                = null; //Indíce do registro na tabela de consulta de Monitoramento Tribunal
let   filtroNovasMovimetacoes      = false; //Define se utiliza o filtro de monitoramentos com novas movimentações na busca
let   filtroBuscasAndamentos       = false; //Define se utiliza o filtro de monitoramentos com o último status de 'pendente' na busca
let   filtroErroUltimaBusca        = false; //Define se utiliza o filtro de monitoramentos com o último status de 'erro' na busca
let   filtroSemMovimentacoes       = false; //Define se utiliza o filtro de monitoramentos sem movimentações vinculadas na busca
let   filtroMonitoramentosBaixados = false; //Define se utiliza o filtro de monitoramentos sem movimentações vinculadas na busca

//---------------------- Eventos -----------------------//

$(document).ready(async function () {

    //Carregamento inicial...
    //Verifica se é a rotina de consulta
    if (isIndex(rotaBaseTribunal)) {
        try{
            //Ativa loader no menu lateral
            loaderOn('#sideMenuMT');
            //Renderiza a tabela
            await buscaNovasMovimetacoes(true);
            setTimeout(async function(){
                //Verifica se contêm registros de novas movimentações, caso contrário busca todas
                if(tableTribunal.data.total === 0){
                    //Seleciona primeira opção do menu lateral
                    setSelectedOptionMenuMD();
                    await buscaTodosMonitoramentos();
                }
            },5000);
            //Cria os filtros personalizados do MPT
            await getCustomFiltersMPT();
            //Renderiza o grid do CMP
            await tableCMP.render();
            if(tableTribunal.data.total == 0){
                NajAlert.toastWarning('Ajuste o PERÍODO com intervalo de datas MAIOR que o atual para visualizar mais informações!');
            }
        }catch (e){
            console.log(e);
        }finally {
            setTimeout(async function(){
                //Desativa loader no menu lateral
                loaderOff('#sideMenuMT');
            },6000);
        }
    }
    
    //Ao clicar em "guideTodos"...
    $(document).on('click', '#datatable-monitoramento-tribunal .data-table-row', function() {
        getIndexMT();
    });
    
    //Ao clicar em pesquisar...
    $(document).on("click", '#search-button', function () {
        buscaPersonalizadaMonitoramentoTribunal();
    });
    
    //Ao mudar a informação de CNJ ou parte
    $(document).on("change", '#filter-CNJ-Parte', async function () {
        buscaPersonalizadaMonitoramentoTribunal();
    });
    
    //Ao mudar a opção de tipo periodo
    $(document).on("change", '#filter-tipo-data', function () {
        buscaPersonalizadaMonitoramentoTribunal();
    });
    
    //Abre ou fecha drop das data rápidas ao clicar no botão das datas rápidas
    $(document).on('click', '#dropDatasRapidas', function() {
        if($('#listDatasRapidas')[0].attributes.class.value.search("action-in-open") > 0){
            removeClassCss('action-in-open', '#listDatasRapidas');
        } else {
            addClassCss('action-in-open', '#listDatasRapidas');
        }
    });
    
    //Fecha o drop down das data rápidas ao clicar fora do drop down das datas rápidas 
    $(document).on('click', function (e) {
        if(e.target.attributes['class'] != undefined){
            if(e.target.attributes.class.value.search('componenteDatasRapidas') == -1){
                removeClassCss('action-in-open', '#listDatasRapidas');
            }
        }
    });
    
    //Quando clica no botão de "ações" abre o drop das ações 
    $(document).on('click', '#' + tableTribunal.ids.actionsInButton, function() {
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
    
    //Executa a busca ao presionar enter com um campo focado
    $(document).keypress(function(e){
        if(e.keyCode === 13){
            if($("#filter-data-inicial").is(":focus") || $("#filter-data-final").is(":focus") || $("#filter-CNJ-Parte").is(":focus")){
                buscaPersonalizadaMonitoramentoTribunal();
            }
        }
    });
    
    //Ao esconder o modal de '#modal-manutencao-comentario-movimentacao-processo' remove a classe 'z-index-100' do modal '#modal-conteudo-movimentacao-processo'
    $('#modal-manutencao-comentario-movimentacao-processo').on('hidden.bs.modal', function(){
        $('#modal-conteudo-movimentacao-processo').removeClass('z-index-100');
    });
    
    //Ao esconder o modal de '#modal-manutencao-monitoramento-processo-tribunal' remove a classe 'z-index-100' do modal '#modal-conteudo-movimentacao-processo'
    $('#modal-manutencao-monitoramento-processo-tribunal').on('hidden.bs.modal', function(){
        $('#modal-conteudo-movimentacao-processo').removeClass('z-index-100');
    });
    
    //Carrega o modal manutencao MPT no modo edição edicao
    $(document).on('click', '#datatable-monitoramento-tribunal .btn-onedit', async function() {
        //Verifica se seleciona linha do Monitoramento Tribunal
        verificaSeSelecionaLinhaMT();
        //Carrega Modal de Manutenção Monitoramento Processo Tribunal
        await carregaModalManutencaoMPTedicao();
    });
    
    //Carrega o modal manutencao MPT no modo edição edicao
    $(document).on('click', '#btnMonitorarEdicao', async function() {
        $(`#btnMonitorarEdicao`).blur();
        $('#btnMonitorarEdicao').tooltip('hide');
        $('#modal-conteudo-movimentacao-processo').addClass('z-index-100');
        //Carrega Modal de Manutenção Monitoramento Processo Tribunal
        await carregaModalManutencaoMPTedicao();
    });
    
    //Ao clicar em "Ver" carrega o modal de conteúdo da movimentação do processo
    $(document).on('click', '.btnVerConteudoMovimentacaoProcesso', function() {
        //Verifica se seleciona linha do Monitoramento Tribunal
        verificaSeSelecionaLinhaMT();
        //Carrega Modal Conteúdo Movimentações Processo
        carregaModalCMP();
    });
    
    //Aumenta os icones no menu de 3 pontinhos
    $('.remove-btn-icon').removeClass('btn-icon');
    $('.remove-btn-icon').removeClass('btn-icon');
    
    //Exibe modal de manutenção de tarefa
    $(document).on('click', '#btnCadastrarTarefa', async function() {
        $(`#btnCadastrarTarefa`).blur();
        $('#btnCadastrarTarefa').tooltip('hide');
        carregaModalManutencaoTarefa();
    });
    
    //Ao clicar em "btnCopiarCNJ"...
    $(document).on('click', '.btnCopiarCNJ', function() {
        //Verifica se a rotina corrente é a de monitoramento tribunal, poi se for precisa fazer o controle de registro selecionado no grid
        if(typeof rotaBaseTribunal != "undefined"){
            getIndexMT();
            verificaSeSelecionaLinhaMT();
        }
        let numero_cnj = tableTribunal.data.resultado[indexTribunal].numero_cnj;
        copiarTextoParaAreaDeTranferencia(numero_cnj,'Número CNJ copiado para a área de transferência!');
    });
    
    //Ao clicar em "onClickFichaProcessoMT"...
    $(document).on('click', '.onClickFichaProcessoMT', function() {
        //Verifica se a rotina corrente é a de monitoramento tribunal, poi se for precisa fazer o controle de registro selecionado no grid
        if(typeof rotaBaseTribunal != "undefined"){
            getIndexMT();
            verificaSeSelecionaLinhaMT();
        }
        onClickFichaProcessoMT(tableTribunal.data.resultado[indexTribunal].codigo_processo);
    });
    
});

//---------------------- Functions -----------------------//

/**
 * Obtêm o index do registro selecionado no datatable do Monitoramento Tribunal pelo id
 * 
 * @returns {Number|indexTribunal}
 */
function getIndexMTpeloId(id){
    if(!id) return null;
    for(let i = 0 ; i < tableTribunal.data.resultado.length; i++){
        if(tableTribunal.data.resultado[i].id == id){
            indexTribunal = i;
            return indexTribunal;
        }
    }
    return null;
}

/**
 * Obtêm o index do registro selecionado no datatable do Monitoramento Tribunal
 * 
 * @returns {Number|indexTribunal}
 */
function getIndexMT(){
    for(let i = 0 ; i < $('#datatable-monitoramento-tribunal .data-table-row').length; i++){
        let selecionado = $('#datatable-monitoramento-tribunal .data-table-row')[i].className.includes('row-selected');
        if(selecionado){
            indexTribunal = i;
            return indexTribunal;
        }
    }
    return null;
}

/**
 * Obtêm o registro selecionado no datatable do Monitoramento Tribunal
 * 
 * @returns {object}
 */
function getRegistroSelecionadoMT(){
    //Obtêm o index da linha selecionada
    getIndexMT();
    if(indexTribunal != null){
        let registro = tableTribunal.data.resultado[indexTribunal];
        //Substitui os valores nulos do objeto por string vazias
        registro     = replaceNullByEmptyInObject(registro);
        //Retorna o registro selecionado
        return registro;
    }
    return null;
}

/**
 * Carrega o modal de manutenção do monitoramento do processo do tribunal no modo edição
 * 
 * @param {type} e
 */
async function carregaModalManutencaoMPTedicao(){
    loadingStart();
    let registro = getRegistroSelecionadoMT();
    dados = {
        "id"              : registro.id_mpt,
        "numero_cnj"      : registro.numero_cnj,
        "frequencia"      : registro.frequencia,
        "status"          : registro.status_mpt,
        "codigo_processo" : registro.codigo_processo,
        "abrangencia"     : (registro.abrangencia == "" || registro.abrangencia == null) ? "0" : registro.abrangencia
    }
    //Seta na sessão o modo edição para o modal de manutenção
    sessionStorage.setItem('@NAJ_WEB/monitoramento_processo_tribunal','edit');
    //Carrega o modal de manutenção do monitoramento do processo do tribunal
    await carregaModalManutencaoMonitoramentoProcessoTribunal(dados);
    //Se hoverem movimentações relacionadas a este monitormaento iremos então desabilitar o campo de 'abrangência'
    if(tableTribunal.data.resultado[indexTribunal].movimentacoes.length > 0){
        $('#form-processo-tribunal #abrangencia').attr('disabled',true);
    }
    //Se tipo do usuário for supervisor
    if(getConsts().tipoUsuarioLogado != "0"){
        habilitaDesabilitaCamposMTP(false);
    }
    loadingDestroy();
}

/**
 * Carrega o modal de manutenção do monitoramento do processo do tribunal no modo criação
 * 
 * @param {int} codido
 * @param {string} cnj
 */
async function carregaModalManutencaoMPTinclussao(codido_processo = null, numero_cnj = null){
    try{
        loadingStart();
        //Obtêm total de monitoramentos no sistema
        totalmonitoramentos = await najTribunal.getData(`${baseURL}` + `${rotaBaseTribunal}/totalmonitoramentosativos?XDEBUG_SESSION_START=netbeans-xdebug`);
        //Obtêm a quota de buscas do sys_config no BD
        quota_de_buscas_sys_config = await najTribunal.getData(`${baseURL}sysconfig/searchsysconfigall/PROCESSOS/MONITORAMENTO_TRIBUNAL_QUOTA`); 
        if(totalmonitoramentos >= quota_de_buscas_sys_config.VALOR){
            NajAlert.toastWarning('Você já atingiu a quantidade máxima de monitoramentos cadastrados no sistema, contate o seu supervisor!')
            loadingDestroy();
            return;
        }
        sessionStorage.setItem('@NAJ_WEB/monitoramento_processo_tribunal', 'create');
        await carregaModalManutencaoMonitoramentoProcessoTribunal();
        //Foca no primeiro campo
        $('#form-processo-tribunal #codigo_processo_mpt').focus();
        //Seta valor campo codigo processo
        $('#form-processo-tribunal #codigo_processo_mpt').val(codido_processo);
        //Seta valor campo numero_cnj
        $('#form-processo-tribunal #numero_cnj').val(numero_cnj);
        //Desabilita o modo somente leitura do campo numero_cnj
        $('#form-processo-tribunal #numero_cnj').attr('readonly', false);
        //Seta a primeira opção do campo abrangencia
        $('#form-processo-tribunal select[name=abrangencia]').val(0);
        //Desaabilita o atributo hidden do elemento linhaCodigoProcessoMTP
        $('#form-processo-tribunal #linhaCodigoProcessoMTP').attr('hidden', false);
    }finally {
        loadingDestroy();
    }
}

/**
 * Carrega os filtros personalizados da tabela
 */
async function getCustomFiltersMPT(){
    //Carrega os options do campo select de advogados
    //let options = await carregaOptionsSelect('monitoramento/diarios' + '/buscanomedostermos', 'filter-termo', true, "data", true); 
    content =  `<div style="display: flex;" class="font-12">
                    <input id="filter-CNJ-Parte" type="text" class="mt-1 mr-1 mb-1 col-3" placeholder="Filtrar por Número do Processo ou Nome da Parte">
                    <div style="display: flex; align-items: center;" class="m-1">
                        <span>Última:</span>
                    </div>
                    <select id="filter-tipo-data" width="200" class="mt-1 mr-1 mb-1">
                        <option value="0">Movimentação</option>
                        <option value="1">Busca</option>
                    </select>
                    <div style="display: flex; align-items: center;" class="m-1">
                        <span>Entre</span>
                    </div>
                    <input type="text" id="filter-data-inicial" width="150" class="form-control" placeholder="__/__/____  ">
                    <div style="display: flex; align-items: center;" class="m1">
                        <span>E</span>
                    </div>
                    <input type="text" id="filter-data-final" width="150" class="form-control" placeholder="__/__/____">
                    <div class="actions-in m-1">
                        <button id="dropDatasRapidas" class="btn btnCustom action-in-button componenteDatasRapidas">
                            <i id="iconDropDatasRapidas" class="fas fa-filter btn-icon componenteDatasRapidas"></i>
                        </button>
                        <ul id="listDatasRapidas" class="actions-in-list" style="display:none;">
                            <li class="action-in-item componenteDatasRapidas" onclick="setaDataRapida(1,this)">
                                <span class="componenteDatasRapidas">Mês Atual</span>
                            </li>
                            <li class="action-in-item componenteDatasRapidas" onclick="setaDataRapida(2,this)">
                                <span class="componenteDatasRapidas">Últimos 15 dias</span>
                            </li>
                            <li class="action-in-item componenteDatasRapidas" onclick="setaDataRapida(3,this)">
                                <span class="componenteDatasRapidas">Últimos 30 dias</span>
                            </li>
                            <li class="action-in-item componenteDatasRapidas" onclick="setaDataRapida(4,this)">
                                <span class="componenteDatasRapidas">Últimos 60 dias</span>
                            </li>
                            <li class="action-in-item componenteDatasRapidas dropItemSelected" onclick="setaDataRapida(5,this)">
                                <span class="componenteDatasRapidas">Últimos 90 dias</span>
                            </li>
                            <li class="action-in-item componenteDatasRapidas" onclick="setaDataRapida(6,this)">
                                <span class="componenteDatasRapidas">Todos</span>
                            </li>
                        </ul>
                    </div>
                    <button id="search-button" class="btn btnCustom action-in-button m-1">
                        <i class="fas fa-search btn-icon"></i>&nbsp;&nbsp;
                        Pesquisar
                    </button>
                </div>`;
    //Seta os filtros personalizados no cabeçalho do datatable
    $('.data-table-filter').html(content);
    //Seta o "datepicker" no campo de "data_distribuicao"
    $('#filter-data-inicial').datepicker({
        uiLibrary: 'bootstrap4',
        locale: 'pt-br',
        format: 'dd/mm/yyyy'
    });
    $('#filter-data-final').datepicker({
        uiLibrary: 'bootstrap4',
        locale: 'pt-br',
        format: 'dd/mm/yyyy'
    });
    //Seta máscaras
    $('#filter-data-inicial').mask('00/00/0000');
    $('#filter-data-final').mask('00/00/0000');
    //Seta margens e paddings 
    addClassCss('m-1',$('#filter-data-inicial').parent());
    addClassCss('m-1',$('#filter-data-final').parent());
    addClassCss('p-0',$('.btn-outline-secondary'));
    removeClassCss('border-left-0',$('.btn-outline-secondary'));
    //Remove icone de calendário do datepicker e seta icone calendário do fontwelsome
    $('.gj-icon').html("");
    addClassCss('far fa-calendar-alt',$('.gj-icon'));
    removeClassCss('gj-icon',$('.gj-icon'));
    //Seta data nos campos de data
    setaDataRange(5);
}

/**
 * Seta a data personalizada selecionada e executa a busca personalizada 
 * 
 * @param integer dataRapida
 * @param element el
 */
async function setaDataRapida(opcaoDataRapida, el){
    setaDataRange(opcaoDataRapida); 
    buscaPersonalizadaMonitoramentoTribunal();
    removeClassCss('dropItemSelected', $('.componenteDatasRapidas'));
    el.attributes.class.value += " dropItemSelected";
    removeClassCss('action-in-open', '#listDatasRapidas');
}

/**
 * Seta a opção de data rápida selecionada o drop down nos campos de data inicial e data final
 * @param data data
 */
function setaDataRange(opcaoDataRapida){
    dataFinal = getDateProperties(new Date()).fullDate;
    switch(parseInt(opcaoDataRapida)){
        case 1:
            //Mês atual
            month = new Date().getMonth();
            if(month < 10) month = '0' + month;
            dataInicial = getDateProperties(new Date(new Date().getFullYear(), month)).fullDate;
            break;
        case 2:
            //Últimos 15 dias
            dataInicial = getDateProperties(new Date(new Date().getTime() - (15 * 86400000))).fullDate;
            break;
        case 3:
            //Últimos 30 dias
            dataInicial = getDateProperties(new Date(new Date().getTime() - (30 * 86400000))).fullDate; //Aqui precisa voltar para 30 depois
            break;
        case 4:
            //Últimos 60 dias
            dataInicial = getDateProperties(new Date(new Date().getTime() - (60 * 86400000))).fullDate;
            break;        
        case 5:
            //Últimos 60 dias
            dataInicial = getDateProperties(new Date(new Date().getTime() - (90 * 86400000))).fullDate;
            break;        
        case 6:
            //Todos
            dataInicial = "0001-01-01";
            dataFinal   = "9999-01-01";
            break;
    }
    $('#filter-data-inicial').val(formatDate(dataInicial));
    $('#filter-data-final').val(formatDate(dataFinal));
}

/**
 * Verifica se o token das taxas foram setadas no Banco de Dados
 * 
 * @returns {boolean}
 */
async function verificaTokenEscavador(){
    console.log('Verifica Token Escavador');
    response = await najTribunal.getData(`${baseURL}` + `escavador/verificatokenescavador`);
    if(response.code == 400){
        NajAlert.toastError(response.message);
        return false;
    }
    return true;
}

/**
 * Requesita a pesquisa dos processos na escavador e obtêm as movimentacoes dos processos que a escavador já encontrou
 */
async function obterMovimentacoesMPT(){
    loadingStart();
    try {   
        //Antes de executar o metódo de 'obterMovimentacaoMPT' vamos 
        //primeiro verificar se existe o token da Escavador no BD
        if(!await verificaTokenEscavador()){
            return;
        }
        url      = `${baseURL}` + `${rotaBaseTribunal}/obtermovimentacoestribunal?XDEBUG_SESSION_START=netbeans-xdebug`;
        response = await najTribunal.getData(url);
        if(response.code == 200){
            let msg = "";
            for(let i = 0; i < response.message.length; i++){
                msg += JSON.parse(response.message[i]).message + "</br>"; 
                if(JSON.parse(response.message[i]).code == 400){
                    NajAlert.toastError(msg);
                }else if(JSON.parse(response.message[i]).code == 300){
                    NajAlert.toastWarning(msg);
                }else{
                    msg = `<span style="text-align: left;">${msg}</span>`;
                    NajAlert.toastSuccess(msg);
                }
            }
        } else if(response.code == 400){
            NajAlert.toastError(response.message);
        }else{
            NajAlert.toastError('Erro ao obter movimentações, contate o suporte!');
            console.log(response);
        }
    } catch (e) {
        NajAlert.toastError('Erro ao obter movimentações, contate o suporte!');
        console.log(response);
    } finally {
        await tableTribunal.load();
        if(tableTribunal.data.total_novas_movimentacoes > 0){
            await buscaNovasMovimetacoes();
            setSelectedOptionMenuMD($('#optionNovas')[0]);
        }
        await atualizaBadgesQtdsMT();
        await recarregaOsTooltip();
        loadingDestroy();
    }
}

/**
 * Obtêm as movimentacoes dos processos que a escavador já encontrou
 */
async function obterPendentesMPT(){
    loadingStart();
    try {   
        //Antes de executar o metódo de 'obterPendentesMPT' vamos 
        //primeiro verificar se existe o token da Escavador no BD
        if(!await verificaTokenEscavador()){
            return;
        }
        url      = `${baseURL}` + `${rotaBaseTribunal}/obterpendentestribunal?XDEBUG_SESSION_START=netbeans-xdebug`;
        response = await najTribunal.getData(url);
        if(response.code == 200){
            msg = response.message + "</br>"; 
            msg = `<span style="text-align: left;">${msg}</span>`;
            NajAlert.toastSuccess(msg);
        } else if(response.code == 300){
            NajAlert.toastWarning(response.message);
        } else if(response.code == 400){
            NajAlert.toastError(response.message);
        }else{
            NajAlert.toastError('Erro ao obter movimentações, contate o suporte!');
            console.log(response);
        }
    } catch (e) {
        NajAlert.toastError('Erro ao obter movimentações, contate o suporte!');
        console.log(response);
    } finally {
        await tableTribunal.load();
        if(tableTribunal.data.total_novas_movimentacoes > 0){
            await buscaNovasMovimetacoes();
            setSelectedOptionMenuMD($('#optionNovas')[0]);
        }
        await atualizaBadgesQtdsMT();
        await recarregaOsTooltip();
        loadingDestroy();
    }
}

/**
 * Abre nova guia redirecionado para o sistema antigo
 * 
 * @param {int} codigo do processo
 */
function onClickFichaProcessoMT(codigo = null) {
    if(codigo == null){
        NajAlert.toastWarning('Código do processo não foi informado, contate o suporte!');
    }
    window.open(`${najAntigoUrl}?idform=processos&processoid=${codigo}`);
}

/**
 * Abre nova guia redirecionado para o site do tribunal que contêm o processo
 * 
 * @param {string} url
 */
function onClickTribunalProcesso(url = null) {
    if(url == null){
        return;
    }
    window.open(`${url}`);
}

/**
 * Seleciona a opção do menu lateral (seleciona o primeiro se o elemento não for informado)
 * 
 * @param {elemento} el
 */
function setSelectedOptionMenuMD(el = null){
    optionsMenu = $('.list-group-item');
    for(let i = 0; i < optionsMenu.length; i++){
        optionsMenu[i].classList.remove('option-selected');
    }
    if(el){
        //Se receber elemento por parâmetro...
        el.classList.add('option-selected');
    }else{
        //Se não seleciona o primeiro elemento...
        optionsMenu[0].classList.add('option-selected');
    }
}

/**
 * Busca todos os monitoramentos
 */
async function buscaTodosMonitoramentos(){
    tableTribunal.page           = 1;
    filtroNovasMovimetacoes      = false;
    filtroBuscasAndamentos       = false;
    filtroErroUltimaBusca        = false;
    filtroSemMovimentacoes       = false;
    filtroMonitoramentosBaixados = false;
    await buscaPersonalizadaMonitoramentoTribunal();
}

/**
 * Busca monitoramentos com novas movimentações
 * 
 * @param {bool} render Define se irá renderizar a tabela ou apenas carrega-lá, false por default
 */
async function buscaNovasMovimetacoes(render = false){
    tableTribunal.page           = 1;
    filtroNovasMovimetacoes      = true;
    filtroBuscasAndamentos       = false;
    filtroErroUltimaBusca        = false;
    filtroSemMovimentacoes       = false;
    filtroMonitoramentosBaixados = false;
    await buscaPersonalizadaMonitoramentoTribunal(render);
}

/**
 * Busca monitoramentos com busca em andamento
 */
function buscaBuscasEmAndamentos(){
    tableTribunal.page           = 1;
    filtroNovasMovimetacoes      = false;
    filtroBuscasAndamentos       = true;
    filtroErroUltimaBusca        = false;
    filtroSemMovimentacoes       = false;
    filtroMonitoramentosBaixados = false;
    buscaPersonalizadaMonitoramentoTribunal();
}

/**
 * Busca monitoramentos com erro na última na busca
 */
function buscaErroUltimaBusca(){
    tableTribunal.page           = 1;
    filtroNovasMovimetacoes      = false;
    filtroBuscasAndamentos       = false;
    filtroErroUltimaBusca        = true;
    filtroSemMovimentacoes       = false;
    filtroMonitoramentosBaixados = false;
    buscaPersonalizadaMonitoramentoTribunal();
}

/**
 * Busca monitoramentos sem movimentações vinculadas
 */
function buscaSemMovimentacoes(){
    tableTribunal.page           = 1;
    filtroNovasMovimetacoes      = false;
    filtroBuscasAndamentos       = false;
    filtroErroUltimaBusca        = false;
    filtroSemMovimentacoes       = true;
    filtroMonitoramentosBaixados = false;
    buscaPersonalizadaMonitoramentoTribunal();
}

/**
 * Busca monitoramentos baixados
 */
function buscaMonitoramentosBaixados(){
    tableTribunal.page           = 1;
    filtroNovasMovimetacoes      = false;
    filtroBuscasAndamentos       = false;
    filtroErroUltimaBusca        = false;
    filtroSemMovimentacoes       = false;
    filtroMonitoramentosBaixados = true;
    buscaPersonalizadaMonitoramentoTribunal();
}

/**
 * Obtêm os valores dos filtros personalizados e executa a busca
 * 
 * @param render Define se irá renderizar a tabela ou apenas carrega-lá, false por default
 */
async function buscaPersonalizadaMonitoramentoTribunal(render = false){
    try{
        let CNJ_Parte   = $('#filter-CNJ-Parte').val()    ? $('#filter-CNJ-Parte').val()    : "";
        let tipo_data   = $('#filter-tipo-data').val()    ? $('#filter-tipo-data').val()    : 0;
        let dataInicial = $('#filter-data-inicial').val() ? $('#filter-data-inicial').val() : '01/01/0001';
        let dataFinal   = $('#filter-data-final').val()   ? $('#filter-data-final').val()   : '01/01/9999';
        let colFiltro   = null;

        //Limpa filtros 
        tableTribunal.filtersForSearch = [];
        
        //Verifica tipo da data
        if(tipo_data == 0){
            colData = 'data_ultimo_andamento';
        } else if (tipo_data == 1){
            colData = 'data_ultima_busca';
        } 
        
        //Verifica tipo do filtro1
        if(Number.isNaN(parseInt(removeFormatacaoCNJ(CNJ_Parte)))){
            colFiltro = 'parte';
        } else {
            colFiltro = 'numero_cnj';
        }

        //Seta filtro do termo
        if(CNJ_Parte != ""){
            filter1        = {}; 
            filter1.val    = CNJ_Parte;
            filter1.op     = "I";
            filter1.col    = colFiltro;
            filter1.origin = btoa(filter1);
            tableTribunal.filtersForSearch.push(filter1);
        }

        //Seta filtro do periodo
        if(dataInicial && dataFinal){
            filter2        = {}; 
            filter2.val    = formatDate(dataInicial, false);
            filter2.val2   = formatDate(dataFinal, false);
            filter2.op     = "B";
            filter2.col    = colData;
            filter2.origin = btoa(filter2);
            tableTribunal.filtersForSearch.push(filter2);
        }

        //status_code_ultima_busca
        if(filtroNovasMovimetacoes){
            filter3        = {}; 
            filter3.val    = 0;
            filter3.op     = "BT";
            filter3.col    = "qtde_novas_andamentos";
            filter3.origin = btoa(filter3);
            tableTribunal.filtersForSearch.push(filter3);
        }else if(filtroBuscasAndamentos){
            filter3        = {}; 
            filter3.val    = 0;
            filter3.op     = "I";
            filter3.col    = "status_code_ultima_busca";
            filter3.origin = btoa(filter3);
            tableTribunal.filtersForSearch.push(filter3);
        }else if(filtroErroUltimaBusca){
            filter3        = {}; 
            filter3.val    = 2;
            filter3.op     = "I";
            filter3.col    = "status_code_ultima_busca";
            filter3.origin = btoa(filter3);
            tableTribunal.filtersForSearch.push(filter3);
        }else if(filtroSemMovimentacoes){
            filter3        = {}; 
            filter3.val    = 0;
            filter3.op     = "I";
            filter3.col    = "qtde_total_andamentos";
            filter3.origin = btoa(filter3);
            tableTribunal.filtersForSearch.push(filter3);
        }else if(filtroMonitoramentosBaixados){
            filter3        = {}; 
            filter3.val    = "B";
            filter3.op     = "I";
            filter3.col    = "status";
            filter3.origin = btoa(filter3);
            tableTribunal.filtersForSearch.push(filter3);
        }
        if(!filtroMonitoramentosBaixados){
            filter4        = {}; 
            filter4.val    = "A";
            filter4.op     = "I";
            filter4.col    = "status";
            filter4.origin = btoa(filter3);
            tableTribunal.filtersForSearch.push(filter4);
        }

        //verifica se renderiza a tabela ou apenas carrega os dados
        if(render){
            await tableTribunal.render();
        }else{
            await tableTribunal.load();
        }
        await atualizaBadgesQtdsMT();  
        await recarregaOsTooltip();
    }catch (e){
        console.log(e);
    }
        
}

/**
 *  Atualiza os badges de quantidade dos tipos de monitoramentos 
 */
async function atualizaBadgesQtdsMT(){
    setTimeout(function(){
        if(tableTribunal.data.total_novas_movimentacoes > 0){
            total = tableTribunal.data.total_novas_movimentacoes < 100 ? tableTribunal.data.total_novas_movimentacoes : '+99'; 
            $('#badgeNovasMovimentacoes').html(total);
            $('#badgeNovasMovimentacoes').show();
        }else{
            $('#badgeNovasMovimentacoes').hide();
        }
        if(tableTribunal.data.total_buscas_em_andamento > 0){
            total = tableTribunal.data.total_buscas_em_andamento < 100 ? tableTribunal.data.total_buscas_em_andamento : '+99'; 
            $('#badgeBuscasEmAndamento').html(total);
            $('#badgeBuscasEmAndamento').show();
        }else{
            $('#badgeBuscasEmAndamento').hide();
        }
        if(tableTribunal.data.total_erro_na_ultima_busca > 0){
            total = tableTribunal.data.total_erro_na_ultima_busca < 100 ? tableTribunal.data.total_erro_na_ultima_busca : '+99'; 
            $('#badgeErroNaUltimaBusca').html(total);
            $('#badgeErroNaUltimaBusca').show();
        }else{
            $('#badgeErroNaUltimaBusca').hide();
        }
        if(tableTribunal.data.total_sem_movimentacoes > 0){
            total = tableTribunal.data.total_sem_movimentacoes < 100 ? tableTribunal.data.total_sem_movimentacoes : '+99'; 
            $('#badgeSemMovimentacoes').html(total);
            $('#badgeSemMovimentacoes').show();
        }else{
            $('#badgeSemMovimentacoes').hide();
        }
        if(tableTribunal.data.total_monitoramentos_baixados > 0){
            total = tableTribunal.data.total_monitoramentos_baixados < 100 ? tableTribunal.data.total_monitoramentos_baixados : '+99'; 
            $('#badgeMonitoramentosBaixados').html(total);
            $('#badgeMonitoramentosBaixados').show();
        }else{
            $('#badgeMonitoramentosBaixados').hide();
        }
    },6000);    
}

/**
 * Verifica se seleciona linha no monitoramento 
 * @param {elemento} e
 */
function verificaSeSelecionaLinhaMT(){
    //Verifica se exite linhas selecionadas no datatable
    if(tableTribunal.selectedRows.length == 0){
        if(indexTribunal){
            //Seta o checkbox da linha como 'checked'
            $('#datatable-monitoramento-tribunal .data-table-row')[indexTribunal].classList.add("row-selected");
            //Desmarca o 'checked' do checkbox da linha
            $('#datatable-monitoramento-tribunal .data-table-row')[indexTribunal].querySelector('input[type=checkbox]').checked = true;
        }
    }
};

/**
 * Exibe ou oculta linha dos demais envolvidos do grupo cliente
 */
function exibeOcultaGrupoCliente(codigo_processo){
    let listaGrupoClienteHidden = $(`#btn_exibe_oculta_grupo_cliente_prc_${codigo_processo}`).hasClass('fa-arrow-circle-right');
    if(listaGrupoClienteHidden == true){
        $(`#btn_exibe_oculta_grupo_cliente_prc_${codigo_processo}`).removeClass('fa-arrow-circle-right');
        $(`#btn_exibe_oculta_grupo_cliente_prc_${codigo_processo}`).addClass('fa-arrow-circle-down');
        $(`#lista_grupo_cliente_prc_${codigo_processo}`).show(350);
    }else{
        $(`#btn_exibe_oculta_grupo_cliente_prc_${codigo_processo}`).addClass('fa-arrow-circle-right');
        $(`#btn_exibe_oculta_grupo_cliente_prc_${codigo_processo}`).removeClass('fa-arrow-circle-bottom');
        $(`#lista_grupo_cliente_prc_${codigo_processo}`).hide(350);
    }
}

/**
 * Exibe ou oculta linha dos demais envolvidos do grupo adversário
 */
function exibeOcultaGrupoAdversario(codigo_processo){
    let listaGrupoAdversariosIsHidden = $(`#btn_exibe_oculta_grupo_adversario_prc_${codigo_processo}`).hasClass('fa-arrow-circle-right');
    if(listaGrupoAdversariosIsHidden == true){
        $(`#btn_exibe_oculta_grupo_adversario_prc_${codigo_processo}`).removeClass('fa-arrow-circle-right');
        $(`#btn_exibe_oculta_grupo_adversario_prc_${codigo_processo}`).addClass('fa-arrow-circle-down');
        $(`#lista_grupo_adversario_prc_${codigo_processo}`).show(350);
    }else{
        $(`#btn_exibe_oculta_grupo_adversario_prc_${codigo_processo}`).addClass('fa-arrow-circle-right');
        $(`#btn_exibe_oculta_grupo_adversario_prc_${codigo_processo}`).removeClass('fa-arrow-circle-bottom');
        $(`#lista_grupo_adversario_prc_${codigo_processo}`).hide(350);
    }
}

/**
 * Carrega Modal Manutencao Tarefa
 */
async function carregaModalManutencaoTarefa(){
    loadingStart('bloqueio-modal-conteudo-movimentacao-processo');
    await carregaModalNovaTarefaProcesso();
    $('#modal-conteudo-movimentacao-processo').addClass('z-index-100');
    loadingDestroy('bloqueio-modal-conteudo-movimentacao-processo');
}

/*
 * Marca todos os andamentos de todos os monitoramentos como lidos
 */
async function marcaTodosComoLidosMT(){
    try{
        loadingStart();
        //Url da requisição
        let url  = `${baseURL}` + `${rotaBaseTribunal}/movimentacoes/setatodosregistroscomolidos?XDEBUG_SESSION_START=netbeans-xdebug`;
        //Dispara requisição
        response = await najTribunal.getData(url);
        if(response.code == 200){
            NajAlert.toastSuccess(response.message);
            //Seleciona primeira opção do menu lateral
            setSelectedOptionMenuMD();
            await buscaTodosMonitoramentos();
        }else{
            NajAlert.toastWarning('Erro ao marcar todos os registros de todos os monitoramentos como lidos, contate o supote!');
        }
    }catch(e){
        NajAlert.toastWarning('Erro ao marcar todos os registros de todos os monitoramentos como lidos, contate o supote!')
    }finally{
        loadingDestroy();
    }
}

/**
 * Sweet Alert para solicitar ao usuário a confirmação da remoção da atividade
 * 
 * @param {int} id_atividade
 */
async function sweetAlertForcarBuscaParaMonitoramentosComErroNaUltimaBusca() {
    let text = "Você tem certeza que deseja forçar busca para " + tableTribunal.data.total_erro_na_ultima_busca + " monitoramentos com erro na última busca?";
    await Swal.fire({
        title: "Atenção!",
        text: text,
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#DD6B55',
        confirmButtonText: "Sim, eu tenho!",
        cancelButtonText: "Cancelar",
        onClose: () => {
        }
    }).then(async (result) => {
        if (result.value) {
            await forcarBuscaParaMonitoramentosComErroNaUltimaBusca();
        }
    });
} 

/**
 * Força a busca para monitoramentos com erro na última busca
 */
async function forcarBuscaParaMonitoramentosComErroNaUltimaBusca(){
    try{
        loadingStart();
        //Requisição para cadastrar a pesquisa do CNJ na Escavador
        response = await najMPT.getData(`${baseURL}` + `monitoramento/tribunais/pesquisaprocessoscomerros`);
        if(response.code == 200){
            //Success Message
            await Swal.fire("Sucesso!", "Monitoramento incluído com sucesso, estamos efetuando a busca por novas Movimentações!", "success");
        }else{
            NajAlert.toastError('Erro ao cadastrar a pesquisa do CNJ na Escavador, contate o suporte!');
            console.log(response);
        }
    }finally {
        loadingDestroy();
        await tableTribunal.load();
        await atualizaBadgesQtdsMT();
    }
    
}
