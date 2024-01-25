//---------------------- Parametrôs -----------------------//

const tableDiario            = (isIndex('monitoramento/diarios')) ? new MonitoramentoDiarioTable : false;
const najDiario              = new Naj('MonitoramentoDiario', tableDiario);
const rotaBaseDiario         = 'monitoramento/diarios';
let   indexDiario            = null;  //Indíce do registro na tabela de consulta de Monitoramento Diário
let   idRegistroDiario       = null;  //Id do registro na tabela de consulta de Monitoramento Diário
let   filtroPendentes        = false; //Define se utiliza o filtro de registros pendentes na busca
let   filtroSemPrazoDefinido = false; //Define se utiliza o filtro de registros sem prazo definido
let   filtroNaoMonitorados   = false; //Define se utiliza o filtro de registros não monitorados
let   filtroDescartados      = false; //Define se utiliza o filtro de registros descartados na busca
let   filtroNaoLidos         = false; //Define se utiliza o filtro de registros não lidos na busca
let   novaCarregada          = false; //Define se foi aberto o modal de conteúdo da publicação com uma nova publicação, precisamos dessa informação para sabermos se será necessário ou não recarregar a listagem
let   modalProcessoCarregado = false;
let   ModalTarefaProcesso    = false;
let   totalPublicacoesNovasMD       = 0;
let   totalPublicacoesPendentesMD   = 0;
let   totalPublicacoesDescartadasMD = 0;
//---------------------- Eventos -----------------------//

$(document).ready(async function () {
    
    //Carrega badges totais 
    $('#badgeNovasPublicacoes').hide();
    $('#badgePendentes').hide();
    $('#badgeDescartados').hide();

    //Verifica se é a rotina de consulta
    if (isIndex(rotaBaseDiario)) {
        //Cria os filtros personalizados
        getCustomFilters();
        //Seta para carregar primeiramente as publicações não lidas
        filtroNaoLidos = true;
        loaderOn('#sideMenuMD');
        //Renderiza a tabela
        await buscaPersonalizadaMonitoramentoDiario(true);
        loaderOff('#sideMenuMD');
        if(tableDiario.data.total == 0){
            NajAlert.toastWarning('Ajuste o PERÍODO com intervalo de datas MAIOR que o atual para visualizar mais informações!');
        }
    }
    
    //Ao clicar em pesquisar...
    $(document).on("click", '#search-button', function () {
        buscaPersonalizadaMonitoramentoDiario();
    });
    
    //Ao mudar a opção de advogado
    $(document).on("change", '#filter-termo', async function () {
        buscaPersonalizadaMonitoramentoDiario();
    });
    
    //Ao mudar a opção de tipo periodo
    $(document).on("change", '#filter-tipo-data', function () {
        buscaPersonalizadaMonitoramentoDiario();
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
    
    //Executa a busca ao presionar enter com um campo focado
    $(document).keypress(function(e){
        if(e.keyCode === 13){
            if($("#filter-data-inicial").is(":focus") || $("#filter-data-final").is(":focus")){
                buscaPersonalizadaMonitoramentoDiario();
            }
        }
    });
    
    //Verifica se seleciona linha do Monitoramento Diário
    $(document).on('click', '.btnLeiaNaIntegra', function() {
        verificaSeSelecionaLinhaMD(this);
    });
    
    //Exibe modal de manutenção de pessoa para inclussão
    $(document).on('click', '.btnAdicionaEnvolvido', async function() {
        carregaModalManutencaoPessoaInclussao(this);
    });
    
    //Exibe modal de manutenção de pessoa para edição
    $(document).on('click', '.btnEditaEnvolvido', async function() {
        carregaModalManutencaoPessoaEdicao(this);
    });
    
    //Exibe modal de manutenção de tarefa
    $(document).on('click', '#btnCadastrarTarefa', async function() {
        carregaModalManutencaoTarefa();
    });
    
    //Ao esconder o modal de '#modal-manutencao-pessoa' remove a classe 'z-index-100' do modal '#modal-conteudo-publicacao'
    $('#modal-manutencao-pessoa').on('hidden.bs.modal', function(){
        if(modalProcessoCarregado == false && ModalTarefaProcesso == false){
            $('#modal-conteudo-publicacao').removeClass('z-index-100');    
        }
    });
    
    //Ao esconder o modal de '#modal-manutencao-comentario-publicacao-processo' remove a classe 'z-index-100' do modal '#modal-conteudo-publicacao'
    $('#modal-manutencao-comentario-publicacao-processo').on('hidden.bs.modal', function(){
        if(modalProcessoCarregado == false && ModalTarefaProcesso == false){
            $('#modal-conteudo-publicacao').removeClass('z-index-100');    
        }
    });
    
    //Ao esconder o modal de '#modal-nova-tarefa-processo' remove a classe 'z-index-100' do modal '#modal-conteudo-publicacao'
    $('#modal-nova-tarefa-processo').on('hidden.bs.modal', function(){
        ModalTarefaProcesso = false;
        $('#modal-conteudo-publicacao').removeClass('z-index-100');    
    });
    
    //Ao clicar em descartar publicação...
    $(document).on('click', '#btnDescartarPublicacao', async function() {
        $('#btnDescartarPublicacao').tooltip('hide');
        descartarPublicacao();
    });
    
    //Exibe modal de manutenção de pessoa para inclussão
    $(document).on('click', '#btnCadastrarMonitoramento', async function() {
        $('#btnCadastrarMonitoramento').tooltip('hide');
        $('#btnCadastrarMonitoramento').blur();
        if(tableDiario.data.resultado[indexDiario].processo.codigo_processo == null){
            NajAlert.toastWarning('Você precisa primeiramente cadastrar um processo a esta publicação');   
        }else{
            if(tableDiario.data.resultado[indexDiario].processo.monitoramento == null){
                carregaModalManutencaoMPTinclussao();
            }else{
                await carregaModalManutencaoMPTedicao();
            }
        }
    });
    
    //Aumenta os icones no menu de 3 pontinhos
    $('.remove-btn-icon').removeClass('btn-icon');
    $('.remove-btn-icon').removeClass('btn-icon');
    $('.remove-btn-icon').removeClass('btn-icon');
    
    await recarregaOsTooltip();
    
});

//---------------------- Functions -----------------------//

/**
 * Carrega o modal de manutenção do monitoramento do processo do tribunal no modo criação
 */
async function carregaModalManutencaoMPTinclussao(){
    try{
        loadingStart('bloqueio-modal-conteudo-publicacao');
        //Obtêm total de monitoramentos no sistema
        totalmonitoramentos = await najDiario.getData(`${baseURL}` + `monitoramento/tribunais/totalmonitoramentos?XDEBUG_SESSION_START=netbeans-xdebug`);
        //Obtêm a quota de buscas do sys_config no BD
        quota_de_buscas_sys_config = await najDiario.getData(`${baseURL}sysconfig/searchsysconfigall/PROCESSOS/MONITORAMENTO_TRIBUNAL_QUOTA`); 
        if(totalmonitoramentos == quota_de_buscas_sys_config.VALOR){
            NajAlert.toastWarning('Você já atingiu a quantidade máxima de monitoramentos cadastrados no sistema, contate o seu supervisor!')
            return;
        }
        sessionStorage.setItem('@NAJ_WEB/monitoramento_processo_tribunal', 'create');
        await carregaModalManutencaoMonitoramentoProcessoTribunal();
        $('#form-processo-tribunal select[name=abrangencia]').val(0);
        $('#form-processo-tribunal #numero_cnj').val(tableDiario.data.resultado[indexDiario].processo.numero_novo);
        $('#form-processo-tribunal #numero_cnj').focus();
        $('#form-processo-tribunal input[name=codigo_processo]').val(tableDiario.data.resultado[indexDiario].processo.codigo_processo);
        $('#modal-conteudo-publicacao').addClass('z-index-100');
    }finally {
        loadingDestroy('bloqueio-modal-conteudo-publicacao');
    }
}

/**
 * Carrega o modal de manutenção do monitoramento do processo do tribunal no modo edição
 * 
 * @param {type} e
 */
async function carregaModalManutencaoMPTedicao(){
    loadingStart('bloqueio-modal-conteudo-publicacao');
    let registro = tableDiario.data.resultado[indexDiario].processo.monitoramento;
    dados = {
        "id"              : registro.id_monitora_tribunal,
        "numero_cnj"      : registro.numero_cnj,
        "frequencia"      : registro.frequencia,
        "status"          : registro.status,
        "codigo_processo" : registro.codigo_processo,
        "abrangencia"     : (registro.abrangencia == "" || registro.abrangencia == null) ? "0" : registro.abrangencia
    }
    //Seta na sessão o modo edição para o modal de manutenção
    sessionStorage.setItem('@NAJ_WEB/monitoramento_processo_tribunal','edit');
    //Carrega o modal de manutenção do monitoramento do processo do tribunal
    await carregaModalManutencaoMonitoramentoProcessoTribunal(dados);
    $('#modal-conteudo-publicacao').addClass('z-index-100');
    loadingDestroy('bloqueio-modal-conteudo-publicacao');
}

/**
 * Verifica se seleciona linha no monitoramento 
 * @param {elemento} e
 */
async function verificaSeSelecionaLinhaMD(e){
    try{
        loadingStart();
        //Verifica se exite linhas selecionadas no datatable
        if(tableDiario.selectedRows.length == 0){
            //Se não tiver vamos selecionar a linha corrente
            let indexLinha = e.attributes['data-index-linha'].value;
            //Seta o checkbox da linha como 'checked'
            $('.data-table-row')[indexLinha].classList.add("row-selected");
            //Desmarca o 'checked' do checkbox da linha
            $('.data-table-row')[indexLinha].querySelector('input[type=checkbox]').checked = true;
        }
        await carregaModalConteudoPublicacao(e);
    }finally{
        loadingDestroy();
    }
};

/**
 * Seleciona a opção do menu lateral (seleciona o primeiro se o elemento não for informado)
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

async function carregaModalManutencaoTarefa(){
    loadingStart('bloqueio-modal-conteudo-publicacao');
    $('#nova-tarefa').hide();
    await carregaModalNovaTarefaProcesso();
    $('#modal-conteudo-publicacao').addClass('z-index-100');
    loadingDestroy('bloqueio-modal-conteudo-publicacao');
}

/**
 * Carrega Modal Manutencao Pessoa Inclussão
 * 
 * @param {element} elemento
 */
async function carregaModalManutencaoPessoaInclussao(elemento){
    loadingStart('bloqueio-modal-conteudo-publicacao');
    sessionStorage.setItem('@NAJ_WEB/pessoa_action', 'create');
    let index_envolvido = elemento.attributes['index_envolvido'].value;
    sessionStorage.setItem('@NAJ_WEB/index_envolvido', index_envolvido);
    await carregaModalManutencaoPessoa();
    $('#modal-conteudo-publicacao').addClass('z-index-100');
    let nome_envolvido = elemento.attributes['nome_envolvido'].value;
    $('#form-pessoa input[name="NOME"]').val(nome_envolvido);
    loadingDestroy('bloqueio-modal-conteudo-publicacao');
}

/**
 * Carrega Modal Manutencao Pessoa Edição
 * 
 * @param {element} elemento
 */
async function carregaModalManutencaoPessoaEdicao(elemento){
    loadingStart('bloqueio-modal-conteudo-publicacao');
    let codigo_pessoa = elemento.attributes['pessoa_codigo'].value;
    sessionStorage.setItem('@NAJ_WEB/pessoa_action', 'edit');
    sessionStorage.setItem('@NAJ_WEB/codigo_pessoa', codigo_pessoa);
    await carregaModalManutencaoPessoa();
    $('#modal-conteudo-publicacao').addClass('z-index-100');
    loadingDestroy('bloqueio-modal-conteudo-publicacao');
}

/**
 * Carrega o Badge Novas Publicações 
 * 
 * @param {int} valor
 */
async function carregaBadgeNovasPublicacoes(valor = null){
    try {
        if(valor){
            totalPublicacoesNovasMD = valor;
        }else{
            //Faz requisição para o BD
            let response = null;
            let url      = `${baseURL}${rotaBaseDiario}/totalpublicacoesnovas`;
            response     = await najDiario.getData(url);
            if(response.code == 200){
                totalPublicacoesNovasMD = parseInt(response.message);
            } else {
                NajAlert.toastError('Erro ao obter o total de registros não lidos, contate o suporte!');
                console.log(response);
            }
        }
        if(totalPublicacoesNovasMD > 0){
            total = totalPublicacoesNovasMD < 100 ? totalPublicacoesNovasMD : "+99";
            $('#badgeNovasPublicacoes').html(total);
            $('#badgeNovasPublicacoes').show();
        }
    }catch(e){
        NajAlert.toastError('Erro ao obter o total de registros não lidos, contate o suporte!');
        console.log(response);
    }
}

/**
 * Carrega o Badge Processos Pendentes
 * 
 * @param {int} valor
 */
async function carregaBadgePendentes(valor = null){
    try {
        if(valor){
            totalPublicacoesPendentesMD = valor;
        }else{
            //Faz requisição para o BD
            let response = null;
            let url      = `${baseURL}` + `${rotaBaseDiario}/totalpublicacoespendentes`;
            response = await najDiario.getData(url);
            if(response.code == 200){
                totalPublicacoesPendentesMD = parseInt(response.message);
            } else {
                NajAlert.toastError('Erro ao obter o total de registros pendentes, contate o suporte!');
                console.log(response);
            }
        }
        if(totalPublicacoesPendentesMD > 0){
            total = totalPublicacoesPendentesMD < 100 ? totalPublicacoesPendentesMD : "+99";
            $('#badgePendentes').html(total);
            $('#badgePendentes').show();
        }
    }catch(e){
        NajAlert.toastError('Erro ao obter o total de registros pendentes, contate o suporte!');
        console.log(response);
    }
}

/**
 * Carrega o Badge Processos Pendentes
 * 
 * @param {int} valor
 */
async function carregaBadgeDescartados(valor = null){
    try {
        if(valor){
            totalPublicacoesDescartadasMD = valor;
        }else{
            //Faz requisição para o BD
            let response = null;
            let url      = `${baseURL}` + `${rotaBaseDiario}/totalpublicacoesdescartados`;
            response = await najDiario.getData(url);
            if(response.code == 200){
                totalPublicacoesDescartadasMD = parseInt(response.message);
            } else {
                NajAlert.toastError('Erro ao obter o total de registros descartados, contate o suporte!');
                console.log(response);
            }
        }
        if(totalPublicacoesDescartadasMD > 0){
            total = totalPublicacoesDescartadasMD < 100 ? totalPublicacoesDescartadasMD : "+99";
            $('#badgeDescartados').html(total);
            $('#badgeDescartados').show();
        }
    }catch(e){
        NajAlert.toastError('Erro ao obter o total de descartados pendentes, contate o suporte!');
        console.log(response);
    }
}

/**
 * Carrega o modal Leia na Íntegra
 * 
 * @param object elemento
 * @param int    index 
 */
async function carregaModalConteudoPublicacao(elemento = null, index = null){
    if(elemento){
        //Extrai indice da linha
        indexDiario      = elemento ? parseInt(elemento.attributes['data-index-linha'].value) : indexDiario;
        //Extrai id da linha
        idRegistroDiario = elemento ? elemento.attributes['data-id-movimentacao'].value       : idRegistroDiario;
    }
    if(index){
        indexDiario = index;
    }
    //Verifica se id da linha é igual ao id da linha selecionada
    if(tableDiario.data.resultado[indexDiario].id == idRegistroDiario){
        //Extrai dados da linha da tabela
        let dados      = tableDiario.data.resultado[indexDiario];
        //Verifica quais atributos do header serão apresentados
        let headerLeftLinha1 = dados.secao ? dados.secao : dados.diario_nome;
        let headerLeftLinha2 = (dados.tipo ? dados.tipo  : dados.diario_competencia) + '&nbsp;';
        let headerLeftLinha3 = "<b>Data Cadastro: </b> " + formatDate(dados.data_hora_inclusao.substr(0,10)) + " <b>Data Publicação: </b> " + formatDate(dados.data_publicacao) + " <b>Página:</b> " + dados.pagina;
        if(dados.lido == "N"){
            headerLeftLinha3 += `<span class="badge text-white font-normal badge-pill badge-warning blue-grey-text text-darken-4 ml-1">Nova</span>`;
        }
        let headerRight  = "";
        let footerRigth  = "";
        //Define o conteúdo que será exibido 
        let conteudo     = jQuery(dados.conteudo_publicacao).text();
        let paginacao    = ((tableDiario.data.pagina - 1) * tableDiario.data.limite) + indexDiario + 1;
            paginacao    = `Publicação ${paginacao} de ${tableDiario.data.total}`;
        let envolvidos   = "";
        let btnMonitora  = "";
        let btnDescartar = "";

        //Verifica se tem processo relacionado a esta publicação (FK da tb monitora_termo_processo)
        if(dados.id_processo != null){
            //Verifica se tem o número novo
            if(dados.processo.numero_novo != null){
                headerRight += `<b>Processo:</b> ${dados.processo.numero_novo}&nbsp;`;
                //Verifica se tem código de processo (FK ta tb PRC), se tiver significa que o processo já está cadastrado no BD
                if(dados.processo.codigo_processo != null){
                    if(dados.processo.URL_TJ != null){
                        headerRight += `<i class="font-14 mdi mdi-open-in-new cursor-pointer text-dark" data-toggle="tooltip" data-placement="top" title="Abrir o processo no site do Tribunal" data-toggle="tooltip" onclick="abreProcessoSiteTribunal('${dados.processo.URL_TJ}');"></i>`;
                    }
                    headerRight += `</br>`;
                    headerRight += `<span class="badge badge-pill badge-success"  data-toggle="tooltip" data-placement="top" title="Já existe um processo cadastrado para esta publicação">Cadastrado</span>&nbsp;`;
                    if(dados.prc_movimento.ID){
                        headerRight += `<span class="badge badge-pill badge-success"  data-toggle="tooltip" data-placement="top" title="Já existe um andamento cadastrado para esta publicação">Andamento</span>&nbsp;`;
                    }
                    //Verifica se o processo já está sendo monitorado
                    if(tableDiario.data.resultado[indexDiario].processo.monitoramento != null){
                        headerRight += `<span class="badge badge-pill badge-success" data-toggle="tooltip" data-placement="top" title="Existe um monitoramento do processo no tribunal">Monitorado</span>&nbsp;`;
                    }
                    if(dados.prc_movimento.ID == null){
                    footerRigth += `
                                    <button type="button" id="btnAddAndamentoProcesssual" class="btn waves-effect waves-light btn-info" data-toggle="tooltip" data-placement="top" title="Clique aqui para cadastrar um novo andamento processual">
                                        <i class="fas fa-plus-circle"></i>
                                        Andamento Processual
                                    </button>`;
                    }
                    footerRigth += `
                                    <button type="button" id="btnVerProcesso" class="btn waves-effect waves-light btn-info" data-toggle="tooltip" data-placement="top" title="Clique aqui para ver o processo">
                                        <i class="fas fa-search"></i>
                                        Ver Processo
                                    </button>`;
                } else {
                    if(dados.descartada != 'S'){
                        headerRight += `<span class="badge badge-pill badge-danger cursor-pointer" data-toggle="tooltip" data-placement="top" title="Não há um processo no NAJ relacionado com esta publicação">Pendente</span>&nbsp;`;
                        //Adiciona botão de cadastro
                        footerRigth += `<button type="button" id="btnCadastrarProcesso" class="btn waves-effect waves-light btn-danger" data-toggle="tooltip" data-placement="top" title="Clique aqui para cadastrar um novo processo">
                                            <i class="fas fa-plus-circle"></i>
                                            Cadastrar
                                        </button>`;
                        btnDescartar = `<button type="button" class="btn waves-effect waves-light btn-secondary" id="btnDescartarPublicacao" data-toggle="tooltip" data-placement="top" title="Clique aqui para descartar a publicação">
                                            <i class="fas fa-trash"></i>
                                            Descartar
                                        </button>`;
                    }else if(dados.descartada == 'S'){
                        headerLeftLinha3 += `<span class="badge badge-pill badge-secondary cursor-pointer ml-1" data-toggle="tooltip" data-placement="top" title="Essa publicação foi descartada">Descartado</span>`;
                    }
                }
            }else{
                headerRight += `<span class="">Não conseguimos identificar o processo </span><i class="icon-info tooltip-naj" data-toggle="tooltip" data-placement="top" title="Localizamos o termo de pesquisa mas não foi possível identificar o processo"></i>&nbsp;`;
            }

            //Verifica se tem relação com processo no sistema
            if(dados.processo.codigo_processo != null){
                $('#titulo-superior-coluna-direita-modal-conteudo-publicacao').html('Processo');
                $('#titulo-inferior-coluna-direita-modal-conteudo-publicacao').show();
                let conteudo_processo = conteudoProcesso(dados.processo, false);
                $('#processos-semelhantes-modal-conteudo-publicacao').html(conteudo_processo);
                $('#processos-semelhantes-modal-conteudo-publicacao').show();
            }else if(dados.processo.processos_semelhantes.length > 0){
                let conteudo_processo = "";
                $('#titulo-superior-coluna-direita-modal-conteudo-publicacao').html('Processos Semelhantes');
                $('#titulo-inferior-coluna-direita-modal-conteudo-publicacao').show();
                for(let i = 0; i < dados.processo.processos_semelhantes.length; i++){
                    conteudo_processo += conteudoProcesso(dados.processo.processos_semelhantes[i], true);
                }
                $('#processos-semelhantes-modal-conteudo-publicacao').html(conteudo_processo);
                $('#button-vincular-processo-a-publicacao').slideUp(300);
                $('#processos-semelhantes-modal-conteudo-publicacao').show();
            }else{
                $('#titulo-superior-coluna-direita-modal-conteudo-publicacao').html('Envolvidos');
                $('#titulo-inferior-coluna-direita-modal-conteudo-publicacao').hide();
                $('#processos-semelhantes-modal-conteudo-publicacao').hide();
            }

            //Verifica se tem envolvidos
            if(dados.processo.envolvidos.length > 0){
                //Verifica se a propriedade existe no objeto
                if('termo_pesquisa' in dados && dados.processo.envolvidos.length >= 2){
                    //Ordena os envolvidos
                    dados.processo.envolvidos = sortEnvolvidosPorAdvogado(dados.processo.envolvidos, dados.termo_pesquisa);
                }
                //Percorre pelos envolvidos
                for(let i = 0; i < dados.processo.envolvidos.length; i++){
                    let tipo    = dados.processo.envolvidos[i].tipo != null? `(${dados.processo.envolvidos[i].tipo})` : "";
                    let externo = "";
                    //Verifica se a pessoa já está cadastrada no BD
                    if(dados.processo.envolvidos[i].pessoa_codigo != null){
                        //Adiciona o ícone de edição
                        envolvidos += `<span><i class="fas fa-edit fa-lg cursor-pointer btnEditaEnvolvido" pessoa_codigo="${dados.processo.envolvidos[i].pessoa_codigo}"></i>`;
                        externo     = `<i class="font-20 mdi mdi-open-in-new cursor-pointer text-dark" title="Ver ficha da pessoa" data-toggle="tooltip" onclick="abreExternoCadastroPessoa(${dados.processo.envolvidos[i].pessoa_codigo});"></i>`;
                    }else{
                        //Adiciona o ícone de inclusão
                        envolvidos += `<span><i class="fas fa-plus-circle fa-lg cursor-pointer btnAdicionaEnvolvido" id_envolvido="${dados.processo.envolvidos[i].id}" nome_envolvido="${dados.processo.envolvidos[i].nome}" index_envolvido=${i}></i>`;
                    }
                    //Verifica se o tipo do envolvido existe
                    if(dados.processo.envolvidos[i].tipo != null){
                       tipo = `<span class="text-muted font-10">${tipo}</span>`; 
                    }
                    //Verifica se aplica reticências ao nome do envolvido 
                    let reticencias = dados.processo.envolvidos[i].nome.length > 30 ? "..." : "";
                    envolvidos     += ` ${dados.processo.envolvidos[i].nome.substr(0, 30)}${reticencias} ${tipo} ${externo}</span></br>`;
                }
            }

            if(tableDiario.data.resultado[indexDiario].processo.monitoramento == null){
                icon_monitorar = "fa-plus-circle";
            }else{
                icon_monitorar = "fa-search";
            }
            btnMonitora = `<button type="button" class="btn waves-effect waves-light btn-info" id="btnCadastrarMonitoramento" data-toggle="tooltip" data-placement="top" title="Clique aqui para cadastrar um novo monitoramento">
                                <i class="fas ${icon_monitorar}"></i>
                                Monitorar
                            </button>`;

        }else{

            $('#titulo-superior-coluna-direita-modal-conteudo-publicacao').html('');
            $('#titulo-inferior-coluna-direita-modal-conteudo-publicacao').hide();
            $('#envolvidos-modal-conteudo-publicacao').hide();
            $('#processos-semelhantes-modal-conteudo-publicacao').hide();
            
            //Se não tem id_processo via de regra é porque é citação
            headerLeftLinha3 += `<span class="badge badge-pill badge-secondary ml-1">Citação</span>`;
            if(dados.descartada != 'S'){
                btnDescartar = `<button type="button" class="btn waves-effect waves-light btn-secondary" id="btnDescartarPublicacao" data-toggle="tooltip" data-placement="top" title="Clique aqui para descartar a publicação">
                                    <i class="fas fa-trash"></i>
                                    Descartar
                                </button>`;
            }else if(dados.descartada == 'S'){
                headerLeftLinha3 += `<span class="badge badge-pill badge-secondary cursor-pointer ml-1" data-toggle="tooltip" data-placement="top" title="Essa publicação foi descartada">Descartado</span>&nbsp;`;
            }
        }

        if(dados.id_tarefa){
            headerRight += `<span class="badge badge-pill badge-success"  data-toggle="tooltip" data-placement="top" title="Já existe uma tarefa cadastrada para esta publicação">Tarefa</span>&nbsp;`;
        }

        //Adiciona os botões "Tarefa"
        footerRigth += `
            ${btnMonitora}
            ${btnDescartar}`;

        if(!dados.id_tarefa){
            footerRigth += `
                            <button type="button" class="btn waves-effect waves-light btn-info" id="btnCadastrarTarefa" data-toggle="tooltip" data-placement="top" title="Clique aqui para cadastrar uma novo tarefa">
                                <i class="fas fa-plus-circle"></i>
                                Tarefa
                            </button>`;
        }

        //Verifica se tem processo relacionado a esta publicação (FK da tb monitora_termo_processo)
        if(dados.id_processo != null){
            //Verifica se tem o número novo
            if(dados.processo.numero_novo != null){
                //Verifica se tem código de processo (FK ta tb PRC), se tiver significa que o processo já está cadastrado no BD
                if(dados.processo.codigo_processo != null){

                    footerRigth += `
                                    <button type="button" id="btnDesvincularProcesso" class="btn waves-effect waves-light btn-light" data-toggle="tooltip" data-placement="top" title="Clique aqui para desvincular o processo da publicação">
                                        <i class="fas fas fa-minus-circle"></i>
                                        Desvincular
                                    </button>`;
                }
            }
        }

        //Destaca o termo_pesquisa no conteudo da publicação caso o mesmo seja encontrado no conteudo da publicação
        if('termo_pesquisa' in dados){

            //Marca termo_pesquisa original
            if(conteudo.indexOf(dados.termo_pesquisa) > 0 ){
                termo_pesquisa_marcado   = '<span class="marcacao">' + dados.termo_pesquisa + '</span>';
                conteudo = conteudo.replaceAll(dados.termo_pesquisa, termo_pesquisa_marcado);
            }

            //Marca termo_pesquisa formatado
            termo_pesquisa_formatado = dados.termo_pesquisa.capitalize();
            if(conteudo.indexOf(termo_pesquisa_formatado) > 0 ){
                termo_pesquisa_marcado   = `<span class="marcacao">${termo_pesquisa_formatado}</span>`;
                conteudo                 = conteudo.replaceAll(termo_pesquisa_formatado, termo_pesquisa_marcado);
            }

            //Marca termo_pesquisa formatado com acentuação
            conteudo_sem_acentuacao  = conteudo.removerAcentos(); 
            if(conteudo_sem_acentuacao.indexOf(termo_pesquisa_formatado) > 0){
                indice                   = conteudo_sem_acentuacao.indexOf(termo_pesquisa_formatado);
                palavra_acentuada        = conteudo.substr(indice, termo_pesquisa_formatado.length);
                termo_pesquisa_marcado   = `<span class="marcacao">${palavra_acentuada}</span>`;
                conteudo                 = conteudo.replaceAll(palavra_acentuada, termo_pesquisa_marcado);
            }

            //Marca os "termo_variacoes"
            if('termo_variacoes' in dados){
                let variacao   = dados.termo_variacoes.split(",")[0]; //extrai a primeira variação que segue o padrão ufOAB
                let letras     = ["D","A","B","E","N","P"]; //Possíveis letras que a OAB pode conter
                let uf         = variacao.substr(0,2);
                let oab        = variacao.substr(2,7);
                let letra_oab  = "";
                let numero_oab = "";
                let index      = 0;
                let encontrou  = false;
                //Verifica se contêm letra na OAB
                for(let i = 0; i < letras.length; i++){
                  index = oab.search(letras[i]);
                  if(index > 0 ){
                    encontrou = true;
                    break;
                  }
                }
                if(encontrou){
                    numero_oab = oab.substr(0, index);
                    letra_oab  = oab.substr(index, 1);
                } else {
                    numero_oab = oab.substr(0, 6);
                }
                let combinacoes = [ 
                    numero_oab + letra_oab + uf, 
                    numero_oab + letra_oab + "/" + uf, 
                    uf + numero_oab + letra_oab, 
                    uf + "/" + numero_oab + letra_oab
                ];
                //Para cada combinação verifica se a mesma está contida no conteúdo da publicação e marca caso estiver  
                for(let i = 0; i < combinacoes.length; i++){
                    if(conteudo.indexOf(combinacoes[i]) > 0){
                        termo_variacao_marcado = `<span class="marcacao">${combinacoes[i]}</span>`;
                        conteudo = conteudo.replaceAll(combinacoes[i], termo_variacao_marcado);
                    }
                }
            }

        }

        //Seta o header do modal
        let headerLeft = `
            <span><b>${headerLeftLinha1}</b></span><br>
            <span>${headerLeftLinha2}</span><br>
            <span>${headerLeftLinha3}</span><br>
        `;

        //Insere header no modal
        $('#header-modal-conteudo-publicacao-left').html(headerLeft);
        $('#header-modal-conteudo-publicacao-right').html(headerRight);
        $('#footer-modal-conteudo-publicacao-right').html(footerRigth);
        //Insere conteúdo no modal
        $('#content-modal-conteudo-publicacao').html(conteudo);
        //Insere paginacao no modal
        $('#paginacao-modal-conteudo-publicacao').html(paginacao);
        //Insere envolvidos no modal
        $('#envolvidos-modal-conteudo-publicacao').html(envolvidos);
        onClickCancelarProcessoSemelhante();
        //Exibe modal
        $('#modal-conteudo-publicacao').modal('show');
        //Verifica se o registro já foi lido
        if(dados.lido == "N"){
            //Chama método para setar registro como "lido" no BD
            await setaRegistroComoLido(idRegistroDiario);
            //Chama método para carregar Badge Novas Publicacoes
            await carregaBadgeNovasPublicacoes();
        }
        //Carrega tooltips do modal
        $('[data-toggle="tooltip"]').tooltip();
    } else {
        NajAlert.toastError('Erro ao exibir conteudo, contate o suporte!');
    } 
}

/*
 * Fecha o modal do conteudo das publicações
 */
async function fecharModalConteudoPublicacao(){
    $(`#modal-conteudo-publicacao #btnFecharModalConteudoPublicacao`).blur();
    $('#btnFecharModalConteudoPublicacao').tooltip('hide');
    $('#modal-conteudo-publicacao').modal('hide');
    await carregaBadgeNovasPublicacoes();
    await carregaBadgePendentes();
    await carregaBadgeDescartados();
}

/**
 * Retorna o conteúdo HTML dos processos para o modal conteúdo da publicação
 * 
 * @param {objesct} processo
 * @param {bool}    exibeRadioBox
 * @returns {string}
 */
function conteudoProcesso(processo, exibeRadioBox = false){
    let grau_jurisdicao   = processo.GRAU_JURISDICAO         ? processo.GRAU_JURISDICAO                                                                 : ``;
    let cartorio          = processo.CARTORIO                ? processo.CARTORIO                                                                        : ``;
    let comarca           = processo.COMARCA                 ? processo.COMARCA + ` (${processo.COMARCA_UF})`                                           : ``;
    let traco             = cartorio != `` &&  comarca != `` ? ` - `                                                                                    : ``;
    let classe            = processo.CLASSE                  ? `<span class="text-muted">Classe: </span><span class="">${processo.CLASSE }</span></br>` : ``;
    let baixado           = processo.situacao == "ENCERRADO" ? `<span class="badge badge-danger badge-rounded" title="Baixado">Baixado</spam>`          : ``;
    let radio             = "";
    if(exibeRadioBox){
        radio =     `<div class="col-1">
                        <input type="radio" name="processo_semelhante" value="${processo.codigo_processo}">
                    </div>`;
    }
    let conteudo_processo = `
        <div class="row align-items-center row-zebrado font-12" onclick="onClickRadioProcessoSemelhante(${processo.codigo_processo})">
            ${radio}
            <div class="col-10">
                <span class="text-muted">Código: </span><span class="">${processo.codigo_processo}</span>&nbsp;<i class="font-18 mdi mdi-open-in-new cursor-pointer text-dark" data-toggle="tooltip" data-placement="top" title="Ver ficha do processo" data-toggle="tooltip" onclick="onClickFichaProcesso(${processo.codigo_processo});"></i>${baixado}</br>
                <span class="text-muted">Número: </span><span class="">${processo.NUMERO_PROCESSO_NEW}</span></br>
                ${classe}
                <span class="text-muted">Grau Jurisdição: </span><span class="">${grau_jurisdicao}</span></br>
                <span class="">${cartorio} ${traco} ${comarca}</span></br>
            </div>
        </div>
    `;
    return conteudo_processo;
}

/**
 * Proximo Conteudo Publicacao
 */
async function proximoConteudoPublicacao(){
    try{
        $(`#modal-conteudo-publicacao #proximoConteudoPublicacao`).blur();
        $('#proximoConteudoPublicacao').tooltip('hide');
        //Verifica se o registro corrente é correpondente ao último registro da página
        if(indexDiario + 1 == tableDiario.data.resultado.length){
            //Verifica se a página corrente é correspondente a última página
            if(tableDiario.data.pagina == tableDiario.totalPages){
                NajAlert.toastWarning('Você já chegou ao último registro da página');
                return;
            }
            tableDiario.page++; 
            loadingStart('bloqueio-modal-conteudo-publicacao');
            await buscaPersonalizadaMonitoramentoDiario();
            loadingDestroy('bloqueio-modal-conteudo-publicacao');
            indexDiario = -1;
        }      
        if(indexDiario >= 0){
            //Remove da linha corrente a classe CSS "row-selected"
            $('.data-table-row')[indexDiario].classList.remove("row-selected");
            //Desmarca o 'checked' do checkbox da linha
            $('.data-table-row')[indexDiario].querySelector('input[type=checkbox]').checked = false;
        }
        indexDiario++;
        //Seta o checkbox da linha como 'checked'
        $('.data-table-row')[indexDiario].classList.add("row-selected");
        //Desmarca o 'checked' do checkbox da linha
        $('.data-table-row')[indexDiario].querySelector('input[type=checkbox]').checked = true;
        idRegistroDiario = tableDiario.data.resultado[indexDiario].id;
        await carregaModalConteudoPublicacao();
    }finally {
        //
    }
}

/**
 * Anterior Conteudo Publicacao
 */
async function anteriorConteudoPublicacao(){
    try{
        $(`#modal-conteudo-publicacao #anteriorConteudoPublicacao`).blur();
        $('#anteriorConteudoPublicacao').tooltip('hide');
        if(indexDiario == 0){
            if(tableDiario.data.pagina == 1){
                NajAlert.toastWarning('Você já chegou ao primeiro registro da página');
                return;
            }
            tableDiario.page--; 
            loadingStart('bloqueio-modal-conteudo-publicacao');
            await buscaPersonalizadaMonitoramentoDiario();
            loadingDestroy('bloqueio-modal-conteudo-publicacao');
            indexDiario = 20;
        }
        if(indexDiario <= 19){
            //Remove da linha corrente a classe CSS "row-selected"
            $('.data-table-row')[indexDiario].classList.remove("row-selected");
            //Desmarca o 'checked' do checkbox da linha
            $('.data-table-row')[indexDiario].querySelector('input[type=checkbox]').checked = false;
        }
        indexDiario--;
        //Seta o checkbox da linha como 'checked'
        $('.data-table-row')[indexDiario].classList.add("row-selected");
        //Desmarca o 'checked' do checkbox da linha
        $('.data-table-row')[indexDiario].querySelector('input[type=checkbox]').checked = true;
        idRegistroDiario = tableDiario.data.resultado[indexDiario].id;
        await carregaModalConteudoPublicacao();
    }finally {
        //
    }
}

/**
 * Monta requisição pra setar registro como "lido" no BD 
 * 
 * @param {int} idRegistroDiario
 */
async function setaRegistroComoLido(idRegistroDiario){
    try {
        loadingStart('bloqueio-modal-conteudo-publicacao');
        let response = null;
        let url      = `${baseURL}` + `${rotaBaseDiario}/setaregistrolido/` + idRegistroDiario;
        response     = await najDiario.getData(url);
        if(response.code == 200){
            //Esconde a tag "Nova" do registro 
            $('#tag-new-' + idRegistroDiario).hide();
            //Seta no datatable o restro como lido
            tableDiario.data.resultado[indexDiario].lido = "S";
        } else {
            NajAlert.toastError('Erro ao alter a situação do registro como lido, contate o suporte!');
            console.log(response);
        }
    }catch(e){
        NajAlert.toastError('Erro ao alter a situação do registro como lido, contate o suporte!');
        console.log(response);
    }finally {
        loadingDestroy('bloqueio-modal-conteudo-publicacao');
    }
}

/**
 * Atualiza o código da pessoa em monitora_termo_envolvidos 
 */
async function atualizaEnvolvido(){
    try{
        console.log('Atualiza Envolvido');
        let response = null;
        let index_envolvido = sessionStorage.getItem('@NAJ_WEB/index_envolvido');
        let codigo_pessoa   = sessionStorage.getItem('@NAJ_WEB/codigo_pessoa');
        let dados = {
            'codigo_pessoa'  : codigo_pessoa,
            'nome_envolvido' : tableDiario.data.resultado[indexDiario].processo.envolvidos[index_envolvido].nome,
            'tipo_envolvido' : tableDiario.data.resultado[indexDiario].processo.envolvidos[index_envolvido].tipo
        }
        let url  = `${baseURL}` + `${rotaBaseDiario}/atualizaenvolvido`;
        response = await najDiario.postData(url, dados);
        tableDiario.data.resultado[indexDiario].processo.envolvidos[index_envolvido].pessoa_codigo = codigo_pessoa;
        sessionStorage.removeItem('@NAJ_WEB/envolvido_key');
        sessionStorage.removeItem('@NAJ_WEB/index_envolvido');
        //Atualiza o modal conteúdo da publicação
        await carregaModalConteudoPublicacao();
        //Atualiza o datatable diário
        await tableDiario.load();
        if(response.code != 200){
            NajAlert.toastError('Erro ao atualizar o registro do envolvido, contate o suporte!');
            console.log(response);
        }
    }catch(e){
        NajAlert.toastError('Erro ao atualizar o registro do envolvido, contate o suporte!');
        console.log(response);
    }
}

/**
 * Carrega os filtros personalizados da tabela
 */
async function getCustomFilters(){
    //Carrega os options do campo select de advogados
    let options = await carregaOptionsSelect(rotaBaseDiario + '/buscanomedostermos', 'filter-termo', true, "data", true); 
    content =  `<div style="display: flex;" class="font-12">
                    <div style="display: flex; align-items: center;" class="m-1">
                        <span>Advogado(a)</span>
                    </div>
                    <select id="filter-termo" class="mt-1 mr-1 mb-1 col-3">
                        ${options}
                    </select>
                    <div style="display: flex; align-items: center;" class="m-1">
                        <span>Data da:</span>
                    </div>
                    <select id="filter-tipo-data" width="200" class="mt-1 mr-1 mb-1">
                        <option value="0">Cadastro</option>
                        <option value="1">Disponibilização</option>
                        <option value="2">Publicação</option>
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
                            <li class="action-in-item componenteDatasRapidas dropItemSelected" onclick="setaDataRapida(2,this)">
                                <span class="componenteDatasRapidas">Últimos 15 dias</span>
                            </li>
                            <li class="action-in-item componenteDatasRapidas" onclick="setaDataRapida(3,this)">
                                <span class="componenteDatasRapidas">Últimos 30 dias</span>
                            </li>
                            <li class="action-in-item componenteDatasRapidas" onclick="setaDataRapida(4,this)">
                                <span class="componenteDatasRapidas">Últimos 60 dias</span>
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
    setaDataRange(2);
}

/**
 * Seta a data personalizada selecionada e executa a busca personalizada 
 * 
 * @param integer dataRapida
 * @param element el
 */
async function setaDataRapida(opcaoDataRapida, el){
    setaDataRange(opcaoDataRapida); 
    buscaPersonalizadaMonitoramentoDiario();
    removeClassCss('dropItemSelected', $('.componenteDatasRapidas'));
    el.attributes.class.value += " dropItemSelected";
    removeClassCss('action-in-open', '#listDatasRapidas');
}

/**
 * Seta a opção de data rápida selecionada o drop down nos campos de data inicial e data final
 * @param data data
 */
function setaDataRange(opcaoDataRapida){
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
    }
    dataFinal   = getDateProperties(new Date()).fullDate;
    $('#filter-data-inicial').val(formatDate(dataInicial));
    $('#filter-data-final').val(formatDate(dataFinal));
}

/**
 * Busca todos os registros respeitando os personalizados filtros definidos
 * 
 * @param {bool} val true by default
 */
async function buscaTodasPublicacoesMD(){
    //Desabilita
    filtroPendentes = true;
    //Desabilita
    filtroNaoLidos         = true;
    filtroDescartados      = false;
    filtroNaoMonitorados   = false;
    filtroSemPrazoDefinido = false;
    tableDiario.page  = 1;
    await buscaPersonalizadaMonitoramentoDiario();
    if(tableDiario.data.total == 0){
        NajAlert.toastWarning('Ajuste o PERÍODO com intervalo de datas MAIOR que o atual para visualizar mais informações!');
    }
}

/**
 * Busca os registros não lidos respeitando os filtros personalizados definidos
 * 
 * @param {bool} val true by default
 */
async function buscaNaoLidos(val = true){
    //Habilita/Desabilita
    filtroNaoLidos = val;
    //Desabilita
    filtroPendentes        = false;
    filtroDescartados      = false;
    filtroNaoMonitorados   = false;
    filtroSemPrazoDefinido = false;
    tableDiario.page  = 1;
    await buscaPersonalizadaMonitoramentoDiario();
    if(totalPublicacoesNovasMD > 0 && tableDiario.data.total == 0){
        NajAlert.toastWarning('Ajuste o PERÍODO com intervalo de datas MAIOR que o atual para visualizar mais informações!');
    }
}

/**
 * Busca os registros pendentes respeitando os filtros personalizados definidos
 * 
 * @param {bool} val true by default
 */
async function buscaPendentes(val = true){
    //Habilita/Desabilita
    filtroPendentes = val;
    //Desabilita
    filtroNaoLidos         = false;
    filtroDescartados      = false;
    filtroNaoMonitorados   = false;
    filtroSemPrazoDefinido = false;
    tableDiario.page  = 1;
    await buscaPersonalizadaMonitoramentoDiario();
    if(totalPublicacoesPendentesMD > 0 && tableDiario.data.total == 0){
        NajAlert.toastWarning('Ajuste o PERÍODO com intervalo de datas MAIOR que o atual para visualizar mais informações!');
    }
}

/**
 * Busca os registros não monitorados
 * 
 * @param {bool} val true by default
 */
async function buscaNaoMonitorados(val = true){
    //Habilita/Desabilita
    filtroNaoMonitorados = val;
    //Desabilita
    filtroPendentes        = false;
    filtroNaoLidos         = false;
    filtroSemPrazoDefinido = false;
    filtroDescartados      = false;
    tableDiario.page       = 1;
    await buscaPersonalizadaMonitoramentoDiario();
    if(totalPublicacoesPendentesMD > 0 && tableDiario.data.total == 0){
        NajAlert.toastWarning('Ajuste o PERÍODO com intervalo de datas MAIOR que o atual para visualizar mais informações!');
    }
}

/**
 * Busca os registros sem uma tarefa vinculada
 * 
 * @param {bool} val true by default
 */
async function buscaSemPrazoDefinido(val = true){
    //Habilita/Desabilita
    filtroSemPrazoDefinido = val;
    //Desabilita
    filtroPendentes      = false;
    filtroNaoLidos       = false;
    filtroNaoMonitorados = false;
    filtroDescartados    = false;
    tableDiario.page     = 1;
    await buscaPersonalizadaMonitoramentoDiario();
    if(totalPublicacoesPendentesMD > 0 && tableDiario.data.total == 0){
        NajAlert.toastWarning('Ajuste o PERÍODO com intervalo de datas MAIOR que o atual para visualizar mais informações!');
    }
}

/**
 * Busca os registros pendentes respeitando os filtros personalizados definidos
 * 
 * @param {bool} val true by default
 */
async function buscaDescartados(val = true){
    //Habilita/Desabilita
    filtroDescartados = val;
    //Desabilita
    filtroNaoLidos         = false;
    filtroPendentes        = false;
    filtroNaoMonitorados   = false;
    filtroSemPrazoDefinido = false
    tableDiario.page  = 1;
    await buscaPersonalizadaMonitoramentoDiario();
    if(totalPublicacoesDescartadasMD > 0 && tableDiario.data.total == 0){
        NajAlert.toastWarning('Ajuste o PERÍODO com intervalo de datas MAIOR que o atual para visualizar mais informações!');
    }
}

/**
 * Obtêm os valores dos filtros personalizados e executa a busca
 * 
 * @param render   Define se irá renderizar a tabela ou apenas carrega-lá, false por default
 */
async function buscaPersonalizadaMonitoramentoDiario(render = false){
    let termo       = $('#filter-termo').val()        ? $('#filter-termo').val()        : "";
    let tipo_data   = $('#filter-tipo-data').val()    ? $('#filter-tipo-data').val()    : 0;
    let dataInicial = $('#filter-data-inicial').val() ? $('#filter-data-inicial').val() : getDateProperties(new Date(new Date().getTime() - (30 * 86400000))).fullDateSlash; //precisa voltar para 30 dias aqui depois
    let dataFinal   = $('#filter-data-final').val()   ? $('#filter-data-final').val()   : getDateProperties().fullDateSlash;
    let colData     = null;
    
    //Limpa filtros 
    tableDiario.filtersForSearch = [];
    
    //Verifica tipo da data
    if(tipo_data == 0){
        colData = 'data_hora_inclusao';
    } else if (tipo_data == 1){
        colData = 'data_disponibilizacao';
    } else if (tipo_data == 2){
        colData = 'data_publicacao';
    }
    
    //Seta filtro do termo
    if(termo != ""){
        filter1        = {}; 
        filter1.val    = termo;
        filter1.op     = "I";
        filter1.col    = "id_monitora_termo";
        filter1.origin = btoa(filter1);
        tableDiario.filtersForSearch.push(filter1);
    }

    //Seta filtro do periodo
    if(dataInicial && dataFinal){
        filter2        = {}; 
        filter2.val    = formatDate(dataInicial, false);
        filter2.val2   = formatDate(dataFinal, false);
        if(tipo_data == 0){
            filter2.val  = filter2.val + " 00:00:01";
            filter2.val2 = filter2.val2 + " 23:59:59";
        }
        filter2.op     = "B";
        filter2.col    = colData;
        filter2.origin = btoa(filter2);
        tableDiario.filtersForSearch.push(filter2);
    }
    
    //Seta filtro de "todos" menos os "descartados"
    if(filtroNaoLidos == true && filtroPendentes == true && filtroDescartados == false && filtroSemPrazoDefinido == false && filtroNaoMonitorados == false){
        //Filtro 4
        filter4        = {}; 
        filter4.val    = "N";
        filter4.op     = "I";
        filter4.col    = "descartada";
        filter4.origin = btoa(filter4);
        tableDiario.filtersForSearch.push(filter4);
    }
    
    //Seta filtro de "naoLidos"
    if(filtroNaoLidos == true && filtroPendentes == false && filtroDescartados == false && filtroSemPrazoDefinido == false && filtroNaoMonitorados == false){
        filter3        = {}; 
        filter3.val    = "N";
        filter3.op     = "I";
        filter3.col    = "lido";
        filter3.origin = btoa(filter3);
        tableDiario.filtersForSearch.push(filter3);
    }
    
    //Seta filtro de "pendentes"
    if(filtroPendentes == true && filtroNaoLidos == false && filtroDescartados == false && filtroSemPrazoDefinido == false && filtroNaoMonitorados == false){
        //Filtro 3
        filter3        = {}; 
        filter3.val    = "";
        filter3.op     = "N";
        filter3.col    = "id_processo";
        filter3.origin = btoa(filter3);
        tableDiario.filtersForSearch.push(filter3);
        //Filtro 4
        filter4        = {}; 
        filter4.val    = "N";
        filter4.op     = "I";
        filter4.col    = "descartada";
        filter4.origin = btoa(filter4);
        tableDiario.filtersForSearch.push(filter4);
        //Filtro 5
        filter5        = {}; 
        filter5.val    = "";
        filter5.op     = "N";
        filter5.col    = "processo.codigo_processo";
        filter5.origin = btoa(filter5);
        tableDiario.filtersForSearch.push(filter5);
    }
    
    //Seta filtro de "Nao Monitorados"
    if(filtroNaoMonitorados == true && filtroSemPrazoDefinido == false && filtroDescartados == false && filtroNaoLidos == false && filtroPendentes == false){
        filter3        = {}; 
        filter3.val    = "";
        filter3.op     = "N";
        filter3.col    = "mptrp.codigo_processo";
        filter3.origin = btoa(filter3);
        tableDiario.filtersForSearch.push(filter3);
    }
    
    //Seta filtro de "Sem Prazo Definido"
    if(filtroSemPrazoDefinido == true && filtroDescartados == false && filtroNaoLidos == false && filtroPendentes == false && filtroNaoMonitorados == false){
        filter3        = {}; 
        filter3.val    = "";
        filter3.op     = "N";
        filter3.col    = "id_tarefa";
        filter3.origin = btoa(filter3);
        tableDiario.filtersForSearch.push(filter3);
    }
    
    //Seta filtro de "descartados"
    if(filtroDescartados == true && filtroNaoLidos == false && filtroPendentes == false && filtroSemPrazoDefinido == false && filtroNaoMonitorados == false){
        filter3        = {}; 
        filter3.val    = "S";
        filter3.op     = "I";
        filter3.col    = "descartada";
        filter3.origin = btoa(filter3);
        tableDiario.filtersForSearch.push(filter3);
    }
    
    //verifica se renderiza a tabela ou apenas carrega os dados
    if(render){
        await tableDiario.render();
    }else{
        await tableDiario.load();
    }
    
    await carregaBadgeNovasPublicacoes(tableDiario.data.total_publicacoes_novas);
    await carregaBadgePendentes(tableDiario.data.total_publicacoes_pendentes);
    await carregaBadgeDescartados(tableDiario.data.total_publicacoes_descartados);
    await recarregaOsTooltip();
}

/**
 * Exibe o modal de Consulta dos Termos Monitorados
 */
function exibeModalConsultaTermos(){
    //Renderiza a tabela
    tableTermoMonitorado.render();
    //Remove filtros default
    $('.data-table-filter')[1].innerHTML = "";
    //Exibe o modal
    $('#modal-consulta-termo-monitorado').modal('show');
}

/**
 * Obtêm as Movimentacoes dos Termos na Escavador
 */
async function obterPublicacoesAgora(){
    console.log('Obter Movimentações Agora');
    loadingStart();
    try {   
        //Antes de executar o metódo de 'obterPublicacoesAgora' vamos 
        //primeiro verificar se existe o token da Escavador no BD
        if(!await verificaTokenEscavador()){
            return;
        }
        url = `${baseURL}` + `${rotaBaseDiario}/obtermovimentacoesdiario?XDEBUG_SESSION_START=netbeans-xdebug`;
        response = await najTermo.getData(url);
        if(response.code == 200){
            NajAlert.toastSuccess(response.message);
        } else{
            NajAlert.toastError('Erro ao obter movimentações, contate o suporte!');
            console.log(response);
        }
    } catch (e) {
        NajAlert.toastError('Erro ao obter movimentações, contate o suporte!');
        console.log(response);
    } finally {
        loadingDestroy();
        buscaPersonalizadaMonitoramentoDiario();
    }
}

/**
 * Persiste os Diários do Escavador no BD   
 */
async function persistirDiarios(){
    try {   
        //Antes de executar o metódo de 'persistirDiarios' vamos 
        //primeiro verificar se existe o token da Escavador no BD
        if(!await verificaTokenEscavador()){
            return;
        }
        loadingStart();
        url = `${baseURL}` + `${rotaBaseDiario}/persistediarios?XDEBUG_SESSION_START=netbeans-xdebug`;
        response = await najTermo.getData(url);
        if(response.code == 200){
            NajAlert.toastSuccess(response.message);
        } else{
            NajAlert.toastError('Erro ao obter diários, contate o suporte!');
            console.log(response);
        }
    } catch (e) {
        NajAlert.toastError('Erro ao obter diários, contate o suporte!');
        console.log(response);
    } finally {
        loadingDestroy();
    }
}

/**
 * Verifica se o token das taxas foram setadas no Banco de Dados
 * 
 * @returns {boolean}
 */
async function verificaTokenEscavador(){
    console.log('Verifica Token Escavador');
    response = await najDiario.getData(`${baseURL}` + `escavador/verificatokenescavador`);
    if(response.code == 400){
        NajAlert.toastError(response.message);
        return false;
    }
    return true;
}

/**
 * Abre nova guia redirecionado para o sistema antigo
 * @param {int} codigo
 * @returns {undefined}
 */
function onClickFichaProcesso(codigo = null) {
    if(codigo == null){
        codigo = $('#form-processo input[name=CODIGO]').val();
    }
    window.open(`${najAntigoUrl}?idform=processos&processoid=${codigo}`);
}

/**
 * Descarta publicação
 * @param {int} id
 */
async function descartarPublicacao(){
    try{
        loadingStart('bloqueio-modal-conteudo-publicacao');
        let id   = tableDiario.data.resultado[indexDiario].id;
        let url  = `${baseURL}` + `${rotaBaseDiario}/descartarpublicacao/` + id;
        response = await najDiario.getData(url);
        if(response == 1){
            NajAlert.toastSuccess('Publicação descartada com sucesso!');
        }else{
            NajAlert.toastWarning('Erro ao descartar publicação');
        }
        tableDiario.data.resultado[indexDiario].descartada = 'S';
        await carregaModalConteudoPublicacao();
    }catch(e){
        NajAlert.toastWarning('Erro ao descartar publicação');
        console.log(e);
    }finally {
        loadingDestroy('bloqueio-modal-conteudo-publicacao');
    }
}

/**
 * Exibe menu para a vincução do processo com a publicação
 */
function onClickRadioProcessoSemelhante(codigo_processo) {
    if(tableDiario.data.resultado[indexDiario].processo.codigo_processo != null){
        return;
    }
    //Seleciona o radio box referente ao processo_semelhante selecionado
    $('input[name="processo_semelhante"]').val([codigo_processo]);
    //Exibe menu para a vincução do processo com a publicação
    $('#button-vincular-processo-a-publicacao').slideDown(300);
}

/**
 * Esconde menu para a vincução do processo com a publicação
 */
function onClickCancelarProcessoSemelhante() {
    //Esconde menu para a vincução do processo com a publicação
    $('#button-vincular-processo-a-publicacao').slideUp(300);
    //Desmarca radio box checked
    $('input[name="processo_semelhante"]:checked').prop('checked', false);
}

/**
 * Vincula um processo com uma publicação, relacionamento entre "monitora_termo_processo" e "prc" 
 */
async function vincularProcessoPublicacao(){
    try{
        //Bloqueia o modal de manutenção
        loadingStart('bloqueio-modal-conteudo-publicacao');
        codigo_processo = $('input[name="processo_semelhante"]:checked').val();
        //Vincula o processo a publicação em "monitora_termo_processo"
        await najProcesso.update(`${baseURL}${rotaBaseDiario}/processo/` + btoa(JSON.stringify({'id':tableDiario.data.resultado[indexDiario].id_processo})), {'codigo_processo':codigo_processo});
        //Total de publicações correntes
        let totalBefore = tableDiario.data.total;
        await buscaTodasPublicacoesMD();
        //Total de publicações com o filtro todas as publicações
        let totalAllPubliMD  = tableDiario.data.total;
        //Se a pesquisa for do tipo "não lidos" ou "pendentes"
        if(filtroNaoLidos == false || filtroPendentes == false){
            //Precisamos saber qual o index que o modal terá com o filtro todas as publicações
            let indexDiario = totalAllPubliMD - totalBefore - totalPublicacoesDescartadasMD + indexDiario;
        }
        await carregaModalConteudoPublicacao(null, indexDiario);
        setSelectedOptionMenuMD();
    }catch (e){
        NajAlert.toastError(e);
    }finally {
        //Desbloqueia o modal de manutenção
        loadingDestroy('bloqueio-modal-conteudo-publicacao');
    }
}

/**
 * Abre nova guia redirecionado para o site do tribunal que contêm o processo
 * 
 * @param {string} url
 */
function abreProcessoSiteTribunal(url = null) {
    if(url == null){
        return;
    }
    window.open(`${url}`);
}