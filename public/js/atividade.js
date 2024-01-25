//---------------------- Parametrôs -----------------------//

(isIndex('atividades')) ? table = new AtividadeTable : table = false;
const NajApi      = new Naj('Atividade', table);
const rotaBaseAtividade = 'atividades';
let atividadeCodigoFilter;

//---------------------- Eventos -----------------------//

$(document).ready(async function () {

    if(isIndex('usuarios')) {
        // sessionStorage.removeItem('@NAJ_WEB/usuario_key');
        // sessionStorage.removeItem('@NAJ_WEB/usuario');
        //Cria os filtros personalizados
        getCustomFilters();
        //Renderiza a tabela
        await table.render();
    }

    //Ao clicar em pesquisar...
    $(document).on("click", '#search-button', function () {
        buscaPersonalizada();
    });

    //Ao clicar em gravar...
    $(document).on("click", '#gravarTermoMonitorado', function () {
        gravarDadosTermoMonitorado();
    });

    //Ao mudar a opção de tipo periodo
    $(document).on("change", '#filter-pessoa-atividade', function () {
        buscaPersonalizada();
    });

    $('#modal-manutencao-pessoa').on('hidden.bs.modal', function() {
        onChangeCodigosPessoasTarefa('codigo_cliente', 'nome_cliente');
        onChangeCodigosPessoasTarefa('codigo_supervisor', 'nome_supervisor');
        onChangeCodigosPessoasTarefa('codigo_responsavel', 'nome_responsavel');
    });

    //Realiza a busca
    $('#nome_cliente').on('click', function(element) {
        buscaDadosCliente(element);
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
                buscaPersonalizada();
            }
        }
    });

    // //Ao esconder o modal de '#modal-nova-tarefa-processo' remove a classe 'z-index-100' do modal '#modal-conteudo-publicacao'
    // $('#modal-nova-tarefa').on('hidden.bs.modal', function(){
    //     ModalTarefaProcesso = false;
    //     $('#modal-conteudo-movimentacao-processo').removeClass('z-index-100');    
    // });

});

//---------------------- Functions -----------------------//

/**
 * Grava dados da tela de manutenção
 */
async function gravarDadosTermoMonitorado() {
    try {
        loadingStart('bloqueio-modal-manutencao-termo-monitorado');
        //Valida form
        result = validaForm();
        if (result) {
            //Obtêm dados do form
            let dados = getDadosFormTermoMonitorado();
            //Verifica se termo tem no mínimo 3 caracteres
            if(dados.termo_pesquisa.length < 3){
                NajAlert.toastWarning('Atenção, o termo deve ter no mínimo 3 caracteres.');
                return;
            };
            let termo_monitorado = {};
            termo_monitorado.code = 200;
            //Bloqueia o modal de manutenção
            //Verifica se a rotina de manutenção é de criação
            if (sessionStorage.getItem('@NAJ_WEB/termo_monitorado_action') == 'create') {
                if(dados.status == 1){
                    //Registra novo termo na Escavador
                    termo_monitorado = await registrarNovoMonitoramento(dados);
                    if (termo_monitorado.code == 200) {
                        //Obtem id do monitoramento através do retorno do Escavador
                        dados.id_monitoramento = termo_monitorado.content.monitoramento.id;
                    } else {
                        NajAlert.toastError('Não foi possível registrar monitoramento na Escavador.');
                    }
                } else {
                    dados.id_monitoramento = 0;
                }
            //ou se a rotina de manutenção é de edição
            } else if (sessionStorage.getItem('@NAJ_WEB/termo_monitorado_action') == 'edit') {
                let statusTermo = sessionStorage.getItem('statusTermo');
                //Vamos verificar se a situação do termo foi alterada
                if(statusTermo != dados.status){
                    if(dados.status == 1){
                        //Registra novo termo na Escavador
                        termo_monitorado = await registrarNovoMonitoramento(dados);
                        if (termo_monitorado.code == 200) {
                            //Obtem id do monitoramento através do retorno do Escavador
                            dados.id_monitoramento = termo_monitorado.content.monitoramento.id;
                            //Seta na tela de manutenção o id do monitoramento
                            $('#id_monitoramento').val(dados.id_monitoramento);
                            //Seta no session a situação atual do termo
                            sessionStorage.setItem('statusTermo', 1);
                        } else {
                            NajAlert.toastError('Não foi possível registrar monitoramento na Escavador.');
                        }
                    } else if(dados.status == 0) {
                        //Remove termo na Escavador
                        termo_monitorado = await removerMonitoramento(dados.id_monitoramento);
                        if (termo_monitorado.code == 200) {
                            //Seta o id_monitoramento como null, pois o termo não está mais cadastradao na Escavador
                            dados.id_monitoramento = 0;
                            //Seta na tela de manutenção o id do monitoramento
                            $('#id_monitoramento').val('');
                            //Seta no session a situação atual do termo
                            sessionStorage.setItem('statusTermo', 0);
                        } else {
                            NajAlert.toastError('Não foi possível remover monitoramento na Escavador.');
                        }
                    }
                } else {
                    //Verifica primeiramente se o termo está ativo
                    if(dados.status == 1){
                        let variacoesTermo = sessionStorage.getItem('variacoesTermo');
                        //Vamos verificar se as variações do termo foram alteradas
                        if(variacoesTermo != arrayToString(dados.variacoes)){
                            //Altera termo na Escavador
                            termo_monitorado = await editarMonitoramento(dados);
                            if (termo_monitorado.code == 200) {
                                //Seta no session as variações atuais do termo
                                sessionStorage.setItem('variacoesTermo', arrayToString(dados.variacoes));
                            } else {
                                NajAlert.toastError('Não foi possível editar monitoramento na Escavador.');
                                console.log(termo_monitorado);
                            }
                        }
                    }
                }
            }
            //Verifica se a requisição para a Escavador foi bem sucedida
            if (termo_monitorado.code == 200) {
                //Converte alguns dados de array para string
                dados.variacoes        = arrayToString(dados.variacoes);
                dados.contem           = arrayToString(dados.contem);
                dados.nao_contem       = arrayToString(dados.nao_contem);
                //Cadastra ou atualiza o termo no banco de dados
                await createOrUpdateTermoMonitorado(dados);
            } else {
                console.log(termo_monitorado.message);
                //Verifica se há um objeto JSON na mensagem
                let indexInicial = termo_monitorado.message.search('{');
                let indexFinal   = termo_monitorado.message.search('}');
                if(indexInicial > 0 && indexFinal > 0){
                    //Extari JSON da mensagem
                    let response     = JSON.parse(termo_monitorado.message.substr(indexInicial, indexFinal));
                    for(let i = 0; i < response.errors.length; i++){
                        NajAlert.toastError(response.errors[i]);
                    }
                } else {
                    NajAlert.toastError('Erro na comunicação com a Escavador, contate o suporte!');
                }
            }
            
        }
    } catch (e) {
        NajAlert.toastError('Erro ao cadastrar o termo, contate o suporte!');
    }finally {
        //Desbloqueia o modal de manutenção
        loadingDestroy('bloqueio-modal-manutencao-termo-monitorado');
    }
}

/**
 * Registrar Novo termo Monitorado
 * 
 * 2 CRÉDITOS/DIA * O valor dos créditos se referem a cada dia em que o monitoramento estiver ativo.
 * @param object dados
 * @returns object
 */
async function registrarNovoMonitoramento(dados) {
    termos_auxiliares = obterTermosAuxiliares();
    dados = {
        //O tipo do valor a ser monitorado. 
        //Valores permitidos: termo, processo. (obrigatório).
        "tipo": "termo",
        //O termo a ser monitorado nos diários. 
        //Obrigatório se tipo = termo. (opcional).
        "termo": dados.termo_pesquisa,
        //Array de ids dos diarios que deseja monitorar. 
        //Saiba como encontrar esses ids em Retornar origens.
        //Obrigatório se tipo = termo. (opcional).
        "origens_ids": [],
        //Array de strings com as variações do termo monitorado. 
        //O array deve ter no máximo 3 variações. (opcional).
        "variacoes": dados.variacoes,
        //Array de array de strings com termos e condições para o alerta do monitoramento.
        //As condições que podem ser utilizadas são as seguintes: 
        //CONTEM: apenas irá alertar se na página conter todos os nomes informados. 
        //NAO_CONTEM: apenas irá alertar se não tiver nenhum dos termos informados. 
        //CONTEM_ALGUMA: apenas irá alertar, se tiver pelo menos 1 dos termos informados. (opcional).
        "termos_auxiliares": termos_auxiliares
    };
    return await NajApi.postData(`${baseURL}` + `escavador/registrarnovomonitoramentodiarios?XDEBUG_SESSION_START=netbeans-xdebug`, dados);
}

/**
 * Edita um monitoramento de diário oficial. É possível alterar os Termos monitorados, ou as variações do monitoramento.
 * 
 * GRÁTIS por requisição.
 * @param {object} dados
 * @returns {object}
 */
async function editarMonitoramento(dados) {
    return await NajApi.updateData(`${baseURL}` + `escavador/editarmonitoramentodiarios/`, dados);
}

/**
 * Remove um monitoramento de diario cadastrado pelo usuário baseado no seu identificador.
 * 
 * GRÁTIS por requisição.
 * @param {int} id_monitoramento
 * @returns {object}
 */
async function removerMonitoramento(id_monitoramento) {
    return await NajApi.getData(`${baseURL}` + `escavador/removermonitoramentodiarios/` + id_monitoramento);
}

/**
 * Obtêm os termos auxiliares do formulário conforme devem ser comitados para a Escavador
 * 
 * @returns {Array|termos_auxiliares}
 */
function obterTermosAuxiliares() {
    termos_auxiliares = [];
    termos_contem = $('select[name=contem]').val();
    for (i = 0; i < termos_contem.length; i++) {
        let termo_auxiliar = {
            "termo": termos_contem[i],
            "condicao": "CONTEM"
        };
        termos_auxiliares.push(termo_auxiliar);
    }
    termos_nao_contem = $('select[name=nao_contem]').val();
    for (i = 0; i < termos_nao_contem.length; i++) {
        let termo_auxiliar = {
            "termo": termos_nao_contem[i],
            "condicao": "NAO_CONTEM"
        };
        termos_auxiliares.push(termo_auxiliar);
    }
    return termos_auxiliares;
}

/**
 * Insere ou atualiza registro conforme a ação selecionada
 * 
 * @param JSON dados
 */
async function createOrUpdateTermoMonitorado(dados) {
    try {   
        if (sessionStorage.getItem('@NAJ_WEB/termo_monitorado_action') == 'create') {
            let url  = `${baseURL}` + `${rotaBaseAtividade}`;
            response = await NajApi.store(url, dados);
            await novoRegistro();
        } else if (sessionStorage.getItem('@NAJ_WEB/termo_monitorado_action') == 'edit') {
            response = await NajApi.update(`${baseURL}` + `${rotaBaseAtividade}/${btoa(JSON.stringify({id: $('input[name=id]').val()}))}`, dados);
        }
    } catch (e) {
        NajAlert.toastError(e);
    } 
}

/**
 * Obtêm dados do formulário
 * 
 * @returns {getDadosFormTermoMonitorado.dados}
 */
function getDadosFormTermoMonitorado() {
    let dados = {
        'id': $('input[name=id]').val(),
        'id_monitoramento': $('input[name=id_monitoramento]').val(),
        'termo_pesquisa': $('input[name=termo_pesquisa]').val(),
        'variacoes': $('select[name=variacoes]').val(),
        'contem': $('select[name=contem]').val(),
        'nao_contem': $('select[name=nao_contem]').val(),
        'data_inclusao': getDateProperties(new Date).fullDate,
        'status': $('select[name=status]').val(),
    };

    return dados;
}

/**
 * Reseta o formulário do termo monitorado
 */
function resetaFormulario(){
    limpaFormulario('#form-termo-monitorado');
    //Seta valores vazios para os campos
    $('#variacoes').val(null); 
    $('#variacoes').html('');
    $('#contem').val(null); 
    $('#contem').html('');
    $('#nao_contem').val(null); 
    $('#nao_contem').html('');
    //Notify any JS components that the value changed
    $('#variacoes').trigger('change');
    $('#contem').trigger('change');
    $('#nao_contem').trigger('change');
    //reseta validação do formulário 
    removeClassCss('was-validated', '#form-termo-monitorado');
    //Seta a primeira tab por default
}

/**
 * Prepara tela de manutenção para um novo registro 
 */
async function novoRegistro() {
    resetaFormulario();
    response = await NajApi.getData(`${baseURL}` + `${rotaBaseAtividade}/proximo`);
    $('#id').val(response + 1);
    $('#row_id_monitoramento').hide();
}

/**
 * Carrega o modal de manutenção do termo monitorado
 */
async function carregaModalManutencaoTermoMonitorado() {
    //Abre loader
    loadingStart();
    resetaFormulario();
    //Se ação igual a "create"...
    if (sessionStorage.getItem('@NAJ_WEB/termo_monitorado_action') == "create") {
        let response = await NajApi.getData(`${baseURL}` + `${rotaBaseAtividade}/proximo`);
        $('#id').val(response + 1);
        $('#row_id_monitoramento').hide();
        $('#termo_pesquisa').prop("disabled", false);
        $('#contem').prop("disabled", false);
        $('#nao_contem').prop("disabled", false);
        //Se ação igual a "edit"...
    } else if (sessionStorage.getItem('@NAJ_WEB/termo_monitorado_action') == "edit") {
        $('#row_id_monitoramento').show();
        $('#termo_pesquisa').prop("disabled", true);
        $('#contem').prop("disabled", true);
        $('#nao_contem').prop("disabled", true);
        let id = JSON.parse(sessionStorage.getItem('@NAJ_WEB/termo_monitorado_key'));
        let response = await NajApi.getData(`${baseURL}` + `${rotaBaseAtividade}/show/${btoa(JSON.stringify({id: id}))}`);
        //Armazena na seção a situação atual do termo,
        //essa informação será utilizada posteriormente para verificar se a situação foi alterada
        sessionStorage.setItem('statusTermo', response['status']);
        //Armazena na seção as variações atuais do termo,
        //essa informação será utilizada posteriormente para verificar se as variações foram alteradas
        sessionStorage.setItem('variacoesTermo', response['variacoes']);
        //carrega dados dos demais campos do formulário
        NajApi.loadData('#form-termo-monitorado', response);
        //carrega dados do campo variacoes
        let options  = stringToArray(response['variacoes'])
        setaCampoSelect2('#variacoes', options);
        //carrega dados dos campos numero_oab, letra_oab e uf
        let letras     = ["D","A","B","E","N","P"]; //Possíveis letras que a OAB pode conter
        let oab        = options[0].substr(2,7);
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
            $('#letra_oab').val(letra_oab);
        } else {
            numero_oab = oab.substr(0, 6);
        }
        $('#numero_oab').val(numero_oab);
        let uf = options[0].substr(0,2);
        $('#uf').val(uf);
        //carrega dados do campo contem
        options  = stringToArray(response['contem'])
        setaCampoSelect2('#contem', options);
        //carrega dados do campo nao_contem
        options  = stringToArray(response['nao_contem'])
        setaCampoSelect2('#nao_contem', options);
    }
    //Fecha loader
    loadingDestroy();
    //Exibe Modal
    $('#modal-manutencao-termo-monitorado').modal('show');
    //Foca no primeiro campo
    $('#form-termo-monitorado #termo_pesquisa').focus();
}

/**
 * Seta os valores do campo "variacoes"
 */
function setaCampoVariacoes(){
    let numero_oab;
    let letra_oab;
    let uf;
    let variacoes;
    let data;
    let newOption;
    let values = [];
    
    if($('#numero_oab').val().length > 0 && $('#uf').val() != null){
        //Primeiramente vamos limpar os valores anteriores
        $('#variacoes').val(null); 
        $('#variacoes').html(''); 

        numero_oab = $('#numero_oab').val();
        letra_oab = $('#letra_oab').val() ? $('#letra_oab').val() : "";
        uf  = $('#uf').val();  
        variacoes = [uf + numero_oab + letra_oab, numero_oab + letra_oab + uf];

        //Iremos criar três variações diferentes
        for(i = 0; i <3; i++){
            //Nova variação
            let variacao = variacoes[i];
            values.push(variacao);
            data = {
                id: variacao,
                text: variacao
            };
            newOption = new Option(data.text, data.id, false, false);
            $('#variacoes').append(newOption);  
        }

        //Set values no campo "variacoes"
        $('#variacoes').val(values);   
        //Notify any JS components that the value changed
        $('#variacoes').trigger('change');   
    }
}

/**
 * Seta os valores dos campos do tipo select 2
 * 
 * @param {string} campo identificador do elemento
 * @param {array} options
 */
function setaCampoSelect2(campo,options){
    if(options == null){
        return;  
    } 
    let data;
    let newOption;
    let values = [];
    
    //Primeiramente vamos limpar os valores anteriores
    $(campo).val(null); 
    $(campo).html(''); 

    //Iremos percorrer as opções para adiciona-las ao campo
    for(i = 0; i < options.length; i++){
        values.push(options[i]);
        data = {
            id: options[i],
            text: options[i]
        };
        newOption = new Option(data.text, data.id, false, false);
        $(campo).append(newOption);  
    }

    //Set values no campo 
    $(campo).val(values);   
    //Notify any JS components that the value changed
    $(campo).trigger('change');   
}





//ROBERTO

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
            dataInicial = getDateProperties(new Date(new Date().getTime() - (30 * 86400000))).fullDate;
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
 * Carrega os filtros personalizados da tabela
 */
async function getCustomFilters(){
    let options = await carregaOptionsSelect(rotaBaseAtividade + '/buscanomedonoatividade', 'filter-pessoa-atividade', true, "data", true);

    let content =   `<div style="display: flex;" class="font-12">
                    <div style="display: flex; align-items: center;" class="m-1">
                        <span>Atividades de:</span>
                    </div>
                    <select id="filter-pessoa-atividade" class="mt-1 mr-1 mb-1 col-3">
                        ${options}
                    </select>
                    <div style="display: flex; align-items: center;" class="m-1">
                        <span>Período Entre:</span>
                    </div>
                    <input type="text" id="filter-data-inicial" width="150" class="form-control" placeholder="__/__/____  ">
                    <div style="display: flex; align-items: center;" class="m-1">
                        <span>E</span>
                    </div>
                    <input type="text" id="filter-data-final" width="150" class="form-control" placeholder="__/__/____">
                    <div class="actions-in m-1">
                        <button id="dropDatasRapidas" class="btn btnCustom action-in-button componenteDatasRapidas">
                            <i id="iconDropDatasRapidas" class="fas fa-filter btn-icon componenteDatasRapidas"></i>
                        </button>
                        <ul id="listDatasRapidas" class="actions-in-list" style="display:none;">
                            <li class="action-in-item componenteDatasRapidas dropItemSelected" onclick="setaDataRapida(1,this)">
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
    setaDataRange(1);
}

/**
 * Seta a data personalizada selecionada e executa a busca personalizada 
 * @param integer dataRapida
 * @param element el
 */
async function setaDataRapida(opcaoDataRapida, el){
    setaDataRange(opcaoDataRapida); 
    buscaPersonalizada();
    removeClassCss('dropItemSelected', $('.componenteDatasRapidas'));
    el.attributes.class.value += " dropItemSelected";
    removeClassCss('action-in-open', '#listDatasRapidas');
}

/**
 * Obtêm os valores dos filtros personalizados e executa a busca
 * @returns {undefined}
 */
async function buscaPersonalizada(render = false){
    let dataInicial = $('#filter-data-inicial').val();
    let dataFinal   = $('#filter-data-final').val();
    let pessoa   = $('#filter-pessoa-atividade').val();
    
    //limpa filtros 
    table.filtersForSearch = [];

    if (pessoa) {
        filter1        = {}; 
        filter1.val    = pessoa;
        filter1.op     = "I";
        filter1.col    = "ATIVIDADE.CODIGO_CLIENTE";
        filter1.origin = btoa(filter1);
        table.filtersForSearch.push(filter1);
    }

    if (dataInicial && dataFinal) {
        filter2        = {}; 
        filter2.val    = formatDate(dataInicial, false);
        filter2.val2   = formatDate(dataFinal, false);
        filter2.op     = "B";
        filter2.col    = "ATIVIDADE.DATA";
        filter2.origin = btoa(filter2);
        table.filtersForSearch.push(filter2);
    }

    //verifica se renderiza a tabela ou apenas carrega os dados
    await table.load();
}

async function exibeModalAtividade() {
    loadingStart('bloqueio-nova-atividade');
    limpaFormulario('#form-nova-atividade');
    removeClassCss('was-validated', '#form-nova-atividade');
    // onClickButtonLimparPrazoInterno();
    // onClickButtonLimparPrazoFatal();
    
    await carregaOptionsSelect('divisoes/paginate','codigo_divisao_tarefa',false,'data', false, null);
    await carregaOptionsSelect('processos/atividades/tipos/getallatividadestipos','id_tipo_atividade',false,'data', false, null);
    await loadInputData();
    
    //Carrega campos do Usuário de criação
    $('#codigo_usuario').val(`${idUsuarioLogado}`);
    $('#nome_usuario_criacao').val(`${nomeUsuarioLogado}`);
    
    //Esconde a linha do campo código da tarefa
    // $('#row_codigo_tarefa').hide();

    $('#gravar-tarefa')[0].disabled = false;
    loadingDestroy('bloqueio-nova-atividade');
    $('#modal-nova-atividade').modal('show');
}

async function storeAtividade() {
    try{
        loadingStart('bloqueio-nova-atividade');
        if(!validaForm('form-nova-atividade')) {
            if(!$('#codigo_divisao_tarefa').val()) {
                NajAlert.toastWarning("É necessário informar uma divisão!");
                return;
            }

            if(!$('#id_tipo_atividade').val()) {
                NajAlert.toastWarning("É necessário informar um tipo de atividade!");
                return;
            }

            return;
        }

        let dados = await getDadosFormAtividade();

        if(!dados) {
            NajAlert.toastWarning("O usuário de criação da atividade não tem uma pessoa cadastrada para ele!");
            return;
        }

        let response = await NajApi.postData(`${baseURL}atividades?XDEBUG_SESSION_START`, dados);

        if(!response) {
            NajAlert.toastWarning("Não é possível cadastrar a atividade, tente novamente mais tarde!");
        } else if(response.model) {
            NajAlert.toastSuccess("Atividade cadastrada com sucesso!");
            desabilitaCamposTarefa();
            $('#codigo_tarefa').val(response.model.id);
            $('#gravar-tarefa')[0].disabled = true;
        } else if(response.hasOwnProperty('mensagem')){
            NajAlert.toastWarning(response.mensagem);
        } else {
            NajAlert.toastWarning("Não é possível cadastrar a atividade, tente novamente mais tarde!");
        }

    }catch(e){
        console.log(e);
    }finally{
        loadingDestroy('bloqueio-nova-atividade');
    } 
}

async function getDadosFormAtividade() {
    let pessoa = await NajApi.getData(`${baseURL}pessoa/usuario/${$('#codigo_usuario').val()}`);

    if(!pessoa[0]) return false;
    if(!pessoa[0].pessoa_codigo && (pessoa[0].pessoa_codigo != 0)) return false;

    return {
        "CODIGO_DIVISAO"        : $('#codigo_divisao_tarefa').val(),
        "CODIGO_CLIENTE"        : $('#codigo_cliente').val(),
        "CODIGO_USUARIO": pessoa[0].pessoa_codigo,
        "DESCRICAO"             : $('#descricao').val(),
        "ID_TIPO_ATIVIDADE"               : $('#id_tipo_atividade').val(),
        "DATA"     : $('#data').val(),
        "TEMPO"     : $('#tempo').val(),
        "DATA_TERMINO"     : $('#data').val(),
        "HORA_TERMINO"     : $('#HORA_INICIO').val(),
        "HORA_INICIO"     : $('#HORA_INICIO').val(),

    };
}

function onClickExibirModalAnexoAtividade(codigo) {
    atividadeCodigoFilter = codigo;
    anexoAtividadesTable = new AnexoAtividadeTable();
    anexoAtividadesTable.render();
    $('#modal-anexo-atividade').modal('show');
}

async function onClickObservacaoProcesso(processoCodigo) {
    const result = await NajApi.getData(`processos/observacao/${processoCodigo}`);
    $('#modal-consulta-observacao').modal('show')
    $('#header-obersavao')[0].innerHTML = `Observações do Processo: ${processoCodigo}`

    $('#content-observation')[0].innerHTML = ``

    if (!result.data) return $('#content-observation')[0].innerHTML = `Não foi possível buscar as observações`

    let text = ``

    if (result.data[0].pedidos_processo && result.data[0].observacao) {
        text += `${result.data[0].pedidos_processo}`
        text += `<br><hr>`
        text += `${result.data[0].observacao}`
    } else if (result.data[0].pedidos_processo) {
        text += `${result.data[0].pedidos_processo}`
    } else if (result.data[0].observacao) {
        text += `${result.data[0].observacao}`
    }

    $('#content-observation')[0].innerHTML = text
}

async function onClickButtonCadastroPessoaAtividade(id_input) {
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

async function loadInputData() {
    let dataHora = getDataHoraAtual(),
        data     = dataHora.split(' ')[0];
        hora     = dataHora.split(' ')[1],
        hour     = hora.split(':')[0],
        minutes  = hora.split(':')[1];

    $('#data').val(`${data}`);
    $('#HORA_INICIO').val(`${hour}:${minutes}`);
}