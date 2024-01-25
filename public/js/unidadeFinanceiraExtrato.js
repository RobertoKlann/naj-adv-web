//---------------------- Parametrôs -----------------------//

(isIndex('unidadefinanceiraextrato')) ? table = new UnidadeFinanceiraExtratoTable : table = false;

const najUFE  = new Naj('UnidadeFinanceiraExtrato', table);
const urlBase = 'unidadefinanceiraextrato';

//---------------------- Eventos -----------------------//

$(document).ready(async function () {
    
    //Esconde o saldo da conta virtual por default
    $('#saldoContaVirtual').hide();
    
    //remove account_id do session storage
    sessionStorage.removeItem("account_id");

    //Verifica se é a rotina de consulta
    if (isIndex('urlBase')) {
        sessionStorage.removeItem('@NAJ_WEB/unidade_financeira_extrato_key');
        //Cria os filtros personalizados
        getCustomFilters();
        //Renderiza a tabela
        await table.render();
    }
    
    //Ao clicar em pesquisar...
    $(document).on("click", '#search-button', function () {
        buscaPersonalizada();
    });
    
    //Ao mudar a opção de unidade financeira
    $(document).on("change", '#filter-unidade-financeira', async function () {
        let index      = $('#filter-unidade-financeira')[0].selectedIndex;
        let account_id = $('#filter-unidade-financeira')[0].options[index].attributes['account_id'].value
        sessionStorage.setItem("account_id", account_id);
        buscaPersonalizada();
    });
    
    //Ao mudar a opção de tipo periodo
    $(document).on("change", '#filter-tipo-periodo', function () {
        buscaPersonalizada();
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
    
    //Define ação ao presionar enter no modat de alteração de data do registro
    $('#form-manutencao-unidade-financeira-data').submit(function(e) {
        e.preventDefault();
        alteraUnidadeFinanceiraData();
        return;
    });
    
});

//---------------------- Functions -----------------------//

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
    options = await carregaOptionsSelect('unidadefinanceiraextrato/unidades', null, true, "account_id"); 
    content =   `<div style="display: flex;" class="font-12">
                    <div style="display: flex; align-items: center;" class="mt-1 mr-1 mb-1">
                        <span>Unidade Financeira</span>
                    </div>
                    <select id="filter-unidade-financeira" class="m-1">
                        ${options}
                    </select>
                    <div style="display: flex; align-items: center;" class="m-1">
                        <span>Período</span>
                    </div>
                    <select id="filter-tipo-periodo" class="m-1">
                        <option value="1">Lançamento</option>
                        <option value="2">Conciliação</option>
                    </select>
                    <div style="display: flex; align-items: center;" clcass="m-1">
                        <span>Entre</span>
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
 * Busca o Saldo anterior
 * @param integer unidade finaceira
 */
async function buscaSaldoAterior(uf,data){
    let saldoAnteriorValor = "0,00";
    try{
        dados    = {
            "uf" : $('#filter-unidade-financeira').val(),
            "data" :  formatDate($('#filter-data-inicial').val(),false),
            "tipo_data" : $('#filter-tipo-periodo').val()
        };
        response = await najUFE.postData(`${baseURL}` + 'unidadefinanceiraextrato/saldoanterior?XDEBUG_SESSION_START=netbeans-xdebug', dados);
        saldo    = response.saldo_anterior_valor;
        if(saldo != null){
            saldoAnteriorValor = formatter.format(saldo).replace('R$','').substr(1, saldo.length + 1)
        }
        $('#saldoAnteriorValor').html(saldoAnteriorValor);
        $('#saldoAnterior').show();
    } catch (e){
        NajAlert.toastError('Erro ao consultar o saldo anterior, contate o suporte!');
    }
}

/**
 * Obtêm os valores dos filtros personalizados e executa a busca
 * @returns {undefined}
 */
async function buscaPersonalizada(render = false){
    let uf          = $('#filter-unidade-financeira').val();
    let periodo     = $('#filter-tipo-periodo').val();
    let dataInicial = $('#filter-data-inicial').val();
    let dataFinal   = $('#filter-data-final').val();
    let colData     = null;
    let account_id  = sessionStorage.getItem("account_id");
    await buscaSaldoAterior();
    if(account_id  != "null" && account_id != null){
        await verificaSaldoBD(account_id);
    }else{
        $('#saldoContaVirtual').hide();
    }
    //limpa filtros 
    table.filtersForSearch = [];

    if(periodo == 1){
        colData = 'DATA';
        table.fields[0].name = 'DATA';
        table.fields[4].name = 'SALDO_ATUAL';
    } else {
        colData = 'DATA_CONCILIACAO';
        table.fields[0].name = 'DATA_CONCILIACAO';
        table.fields[4].name = 'SALDO_ATUAL_CONCILIACAO';
    }

    filter1        = {}; 
    if(uf){
        filter1.val    = uf;
    } else {
        filter1.val    = 0;
    }
    filter1.op     = "I";
    filter1.col    = "unidade_financeira_extrato.CODIGO_UNIDADE";
    filter1.origin = btoa(filter1);
    table.filtersForSearch.push(filter1);

    if(dataInicial && dataFinal){
        filter2        = {}; 
        filter2.val    = formatDate(dataInicial, false);
        filter2.val2   = formatDate(dataFinal, false);
        filter2.op     = "B";
        filter2.col    = "unidade_financeira_extrato." + colData;
        filter2.origin = btoa(filter2);
        table.filtersForSearch.push(filter2);
    }
    //verifica se renderiza a tabela ou apenas carrega os dados
    if(render){
        await table.render();
    }else{
        await table.load();
    }
}

/**
 * Verifica saldo disponivel no banco de dados
 * @param string account_id
 * @returns bool
 */
async function verificaSaldoBD(account_id){
    console.log('verificaSaldoBD');
    let saldoAtualValor = "0,00";
    let saldoDisponivelValor = "0,00";
    let saldoDisponivelData  = "--/--/--";
    let saldoDisponivelHora  = "--/--/--";
    try{
        response             = await najUFE.getData(`${baseURL}` + 'unidadefinanceiraextrato/saldocontavirtual/' + account_id + '?XDEBUG_SESSION_START=netbeans-xdebug');
        if(saldo != null){
            saldoAtualValor = formatter.format(response.saldo_atual_valor).replace('R$','').substr(1, response.saldo_atual_valor.length + 1)
            saldoDisponivelValor = formatter.format(response.saldo_disponivel_valor).replace('R$','').substr(1, response.saldo_disponivel_valor.length + 1)
        }
        if(response.saldo_disponivel_data != null){
            saldoDisponivelData  = formatDate(response.saldo_disponivel_data.substr(0,10));
            saldoDisponivelHora  = response.saldo_disponivel_data.substr(11,19);
        }
        $('#saldoAtualValor').html(saldoAtualValor);
        $('#saldoDisponivelValor').html(saldoDisponivelValor);
        $('#saldo_disponivel_para_saque').val(saldoDisponivelValor);
        $('#saldoDisponivelData').html(saldoDisponivelData);
        $('#saldoDisponivelHora').html(saldoDisponivelHora);
        $('#saldoContaVirtual').show();
        return true;
    } catch (e){
        NajAlert.toastError('Erro ao consultar o saldo da conta virtual, contate o suporte!');
        return false;
    }
}

/**
 * Executa requisição para Verificar Saldo Disponível na API boleto-iugu-naj
 * @returns {undefined}
 */
async function verificaSaldoDisponivelIUGU(){
    removeClassCss('action-in-open', '#listAcoesCV');
    console.log('verificaSaldoDisponivelIUGU');
    try{
        loadingStart();
        let msg  =  `<span style='text-align: left;'>Saldo Disponível Anterior: R$ ${$('#saldoDisponivelValor').html()} Data: ${$('#saldoDisponivelData').html()} Hora: ${$('#saldoDisponivelHora').html()} (última consulta)<br>`;
        dados    = {"account_id" : sessionStorage.getItem("account_id")};
        response = await najUFE.postData(`${baseUrlApiBoletos}` + `verificasaldodisponivel?XDEBUG_SESSION_START=netbeans-xdebug`, dados);
        if(response.response[0].status_code == 200){
            if(response.response[0].status_message == 'Saldo atualizado com sucesso'){
                await verificaSaldoBD(sessionStorage.getItem("account_id"));
                msg += `Saldo Disponível Atual: R$ ${$('#saldoDisponivelValor').html()} Data: ${$('#saldoDisponivelData').html()} Hora: ${$('#saldoDisponivelHora').html()}</span>`;
                NajAlert.toastSuccess(msg);
            }else{
                NajAlert.toastSuccess(response.response[0].status_message);
            }
        }else{
            NajAlert.toastError('Erro ao verificar saldo disponível, contate o suporte!');
            console.log(response);
        }
    } catch (e){
        NajAlert.toastError('Erro ao verificar saldo disponível, contate o suporte!');
    } finally {
        loadingDestroy();
        buscaPersonalizada();
    }
}

/**
 * Executa requisição para verificar se existe um saque na IUGU que não foi salvo no BD com base no arquivo temporário 
 * @returns bool
 */
async function verificaSaqueArquivoTemporario(){
    console.log('verificaSaqueArquivoTemporario');
    try{
        NajAlert.toastSuccess('Verificando se existe algum saque pendente na IUGU');
        dados    = {"account_id" : sessionStorage.getItem("account_id")};
        response = await najUFE.postData(`${baseUrlApiBoletos}` + `verificasaquearquivotemporario?XDEBUG_SESSION_START=netbeans-xdebug`, dados);
        if(response.response[0].status_code == 200){
            console.log(response.response[0].status_message); 
            table.load();
            return true;
        }else{
            NajAlert.toastError('Erro ao verificar saque pendente na IUGU, contate o suporte!');
            console.log(response);
            return false;
        }
    } catch (e){
        NajAlert.toastError('Erro ao verificar saque pendente na IUGU, contate o suporte!');
        return false;
    } finally {
    }
}

/**
 * Executa requisição para Realizar Saque na API boleto-iugu-naj
 * @returns {undefined}
 */
async function exibeModalSaque(){
    console.log('exibeModalSaque');
    //Fecha drop de acções da CV
    removeClassCss('action-in-open', '#listAcoesCV');
    //Reseta modal
    $('#form-realizar-saque').removeClass('was-validated');
    $('#valor_saque').val('');
    //Aplica mascáras
    $('#saldo_disponivel_para_saque').mask('#.##0,00', {reverse: true})
    $('#valor_saque').mask('#.##0,00', {reverse: true})
    //Exibe Modal
    $('#modal-realizar-saque').modal('show');
}

/**
 * Executa requisição para Realizar Saque na API boleto-iugu-naj
 * @returns {undefined}
 */
async function realizarSaque(){
    //Valida form
    result = validaForm();
    if(result){
        let valor_saque                 = convertMoneyToFloat($('#valor_saque').val());
        let saldo_disponivel_para_saque = convertMoneyToFloat($('#saldo_disponivel_para_saque').val());
        if(valor_saque > saldo_disponivel_para_saque){
            NajAlert.toastWarning('O valor para saque não pode ser superior ao valor do saldo disponível!');
            return;
        } else if(valor_saque < 5){
            NajAlert.toastWarning('O valor para saque não pode ser inferior ao valor mínimo para saque na conta virtual de R$ 5,00 reais!');
            return;
        }
        console.log('realizarSaque');
        try{
            $('#modal-realizar-saque').modal('hide');
            loadingStart();
            dados = {
                "account_id"  : sessionStorage.getItem("account_id"),
                "valor_saque" : valor_saque
            };
            response = await najUFE.postData(`${baseUrlApiBoletos}` + `realizasaque?XDEBUG_SESSION_START=netbeans-xdebug`, dados);
            if(response.response[0].status_code == 200){
                NajAlert.toastSuccess(response.response[0].status_message);
            }else{
                NajAlert.toastError('Erro ao realizar saque, contate o suporte!');
                console.log(response);
            }
        } catch (e){
            NajAlert.toastError('Erro ao realizar saque, contate o suporte!');
        }finally {
            loadingDestroy();
            buscaPersonalizada();
        }
    } 
}

/**
 * Limpa formulário de realizar Saque
 * @returns {undefined}
 */
async function limpaFormularioRealizarSaque(){
    $('#valor_saque').val('');
    removeClassCss('was-validated', '#form-conta-virtual')
}

/**
 * Executa requisição para Verificar Boletos na API boleto-iugu-naj
 * @param {number} situacao situação do boleto na IUGU
 */
async function verificarBoletos(situacao){
    removeClassCss('action-in-open', '#listAcoesCV');
    situacoes = ['PENDENTE', 'PAGO', 'CANCELADO', 'RASCUNHO', 'EXPIRADO'];
    console.log('verifica boletos ' + situacoes[situacao]);
    try{
        loadingStart();
        dados    = {
            "account_id"   : sessionStorage.getItem("account_id"),
            "situacao"     : situacao,
            "data_inicial" : formatDate($('#filter-data-inicial').val(), false),
            "data_final"   : formatDate($('#filter-data-final').val(), false)
        };
        response = await najUFE.postData(`${baseUrlApiBoletos}` + `verificaboletos?XDEBUG_SESSION_START=netbeans-xdebug`, dados);
        if(response.response[0].status_code == 200){
            let msg = response.response[0].status_message;
            if(typeof msg == 'object'){
                msg = "<span style='text-align: left;'>Boletos verificados: " + msg.boletosVerificados + " Boleto(s) <br>" +
                msg.boletosPagos + " Pagos(s) <br>" +
                msg.boletosCancelados + " Cancelado(s) <br>" +
                msg.boletosExpirados + " Expirado <br>" +
                msg.boletosPendentes + " Pendente(s) <br></span>";
            }
            NajAlert.toastSuccess(msg);
        }else{
            let msg = 'Erro ao verificar boletos ' + situacoes[situacao] + ', contate o suporte!';
            NajAlert.toastError(msg);
            console.log(response);
        }
    } catch (e){
        let msg = 'Erro ao verificar boletos ' + situacoes[situacao] + ', contate o suporte!';
        NajAlert.toastError(msg);
        NajAlert.toastError('Erro ao verificar boletos pendentes, contate o suporte!');
    } finally{
        loadingDestroy();
        buscaPersonalizada();
    }
}

/**
 * Carrega modal de manutenção unidade financeira data
 * @param {number} id_registro
 * @param {string} data_registro
 */
async function carregaModalManutencaoUnidadeFinanceiraData(registro){
    //abre loader
    loadingStart();
    $('#unidade-financeira-data').mask("00/00/0000", {placeholder: "__/__/____"});
    $('#unidade-financeira-data').val(formatDate(registro.data));
    //fecha loader
    loadingDestroy();
    //Exibe Modal
    $('#modal-manutencao-unidade-financeira-data').modal('show');
}

/**
 * Altera data de um registro da Unidade Financeira
 */
async function alteraUnidadeFinanceiraData(){
    try{
        loaderOn('#modal-manutencao-unidade-financeira-data');
        //Primeiramente vamos definir o tipo de data que está sendo consultado
        //TIPO, 0 == DATA, 1 == DATA_CONCILIACAO 
        let tipo = 0;
        let maxdata = table.data.resultado[0].MAX_DATA;
        if(table.filtersForSearch[1].col == 'unidade_financeira_extrato.DATA_CONCILIACAO'){
            tipo = 1;
            maxdata = table.data.resultado[0].MAX_DATA_CONCILIACAO;
        }
        //Busca no BD em unidade_financeira_extrato o max() da data com base no tipo de data 
        //let maxdata = await najUFE.getData(`${baseURL}` + `unidadefinanceiraextrato/maxdata/${tipo}?XDEBUG_SESSION_START=netbeans-xdebug`);
        if(!maxdata){
            NajAlert.toastError('Atenção não foi possível obter a data máxima da unidade finaceira, contate o suporte!');
            return;
        }
        //Obtêm o registro do session storage
        let registro = JSON.parse(sessionStorage.getItem('registro_json'));
        if(!registro){
            NajAlert.toastError('Atenção não foi possível obter os dados para editar o registro, contate o suporte!');
            return;
        }      
        let data_form = formatDate($('#unidade-financeira-data').val(), false);
        //Vamos verificar se a data informada pelo usuário não é igual a data atual do registro
        if(data_form == registro.data){
            NajAlert.toastWarning('Atenção a data não pode ser igual a data atual!');
            return;
        }
        //Vamos verificar se a data informada pelo usuário não é igual ou superior a data máxima em unidade_financeira_extrato
        if((data_form == maxdata) || (data_form > maxdata)){
            NajAlert.toastWarning('Atenção a data não pode ser igual ou superior a data ' + formatDate(maxdata));
            return;
        }
        registro.data = data_form;
        registro.tipo = tipo;  
        //Executa requisição para a alteração da data no BD em unidade_financeira_extrato
        response = await najUFE.postData(`${baseURL}` + `unidadefinanceiraextrato/editadata?XDEBUG_SESSION_START=netbeans-xdebug`, registro);
        msg = response.status_message;
        if(response.status_code == 200){
            NajAlert.toastSuccess(msg);
            //Atualiza informação na tela
            let id = '#data-' + registro.id;
            $(id).text(formatDate(registro.data));
            //Executa requisição para atualizar os saldos
            dados = {
                'data' : dados.data,
                'codigoUnidadeFinanceira' : table.filtersForSearch[0].val,
                'tipo' : tipo,
            }
            response = await najUFE.postData(`${baseUrlApiBoletos}` + `atualizasaldo?XDEBUG_SESSION_START=netbeans-xdebug`, dados);
            var msg = response.response[0].status_message;
            if(response.response[0].status_code == 200){
                await NajAlert.toastSuccess(msg);
                await buscaPersonalizada();
            }else{
                NajAlert.toastError(msg);
            }
        }else{
            NajAlert.toastError(msg);
        }
    } catch (e){
        NajAlert.toastError('Erro na execução do procedimento, contate o suporte!');
        console.log(e);
    } finally{
        loaderOff('#modal-manutencao-unidade-financeira-data');
        $('#modal-manutencao-unidade-financeira-data').modal('hide');
    }
}
