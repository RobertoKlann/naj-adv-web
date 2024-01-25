//---------------------- Parametrôs -----------------------//

//Sobreescrevemos o filtro para definir ele como DEFAULT
TableDefaults.filters.CARRY = { id: 'C', title: 'Contenha', isDefault: true };

(isIndex('contavirtual')) ? table = new ContaVirtualTable : table = false;

const najCV    = new Naj('ContaVirtual', table);
const rotaBase = 'contavirtual';

//---------------------- Eventos -----------------------//

$(document).ready(function () {

    //Verifica se é a rotina de consulta
    if (isIndex(rotaBase)) {
        //Remove chave da conta virtual 
        sessionStorage.removeItem('@NAJ_WEB/conta_virtual_key');
        //Renderiza tabela
        table.render();
    }
    
    //Ao clicar em gravar...
    $(document).on("click", '#gravarContaVirtual', function () {
        gravarDados();
    });
    
    //Ao mudar o select do "especie_pagamento" no modal de manutenção, preenche o campo "especie_unidade_finaceira" com o valor referente
    $('#codigo_especie').change(function(){
        carregaEspecieUnidadeFinaceira();
    })

});

//---------------------- Functions -----------------------//

/**
 * Grava dados da tela de manutenção
 */
function gravarDados(){
    //Valida form
    result = validaForm();
    multa               = $('#multa').val().replace('%','').replace(',','.');
    desconto_percentual = $('#desconto_percentual').val().replace('%','').replace(',','.');
    dias_apos           = parseInt($('#dias_apos').val());
    if(result){
        //Obtêm dados do form
        var dados = getDadosForm();
        if(dados.multa > 20){
            NajAlert.toastWarning('Atenção, o valor da multa não pode ser superior a 20% do valor da fatura.');
            return;
        };
        if(dados.desconto_percentual > 50){
            NajAlert.toastWarning('Atenção, o valor do desconto não pode ser superior a 50% do valor da fatura.');
            return;
        };
        if(dados.dias_apos > 30 || dias_apos < 0){
            NajAlert.toastWarning('Atenção, o valor para "Dias Após Vencimento" deve estar entre 0 e 30.');
            return;
        };
        createOrUpdateContaVirtual(dados);
    }
}

/**
 * Carrega na tela de manutenção a Unidade Finaceira da Especie selecionada
 */
function carregaEspecieUnidadeFinaceira(){
    let index = $('#codigo_especie')[0].selectedIndex;
    let especie_unidade_finaceira = $('#codigo_especie')[0].options[index].attributes['especie_unidade_finaceira'].value;
    if(especie_unidade_finaceira != 'null'){
        $('#especie_unidade_finaceira').val(especie_unidade_finaceira);
    }else{
        $('#especie_unidade_finaceira').val('');
    }
}

/**
 * Carrega modal de manutenção
 */
async function carregaModalManutencaoContaVirtual() {
    loadingStart();
    limpaFormulario('#form-conta-virtual');    
    removeClassCss('was-validated', '#form-conta-virtual')   
    await carregaOptionsSelect('pagamentoespecie', 'codigo_especie', false, 'especie_unidade_finaceira');    
    await carregaOptionsSelect('unidadefinanceira/unidades', 'codigo_unidade');    
    //Se ação igual a "create"...
    if (sessionStorage.getItem('@NAJ_WEB/conta_virtual_action') == "create") {
        sessionStorage.removeItem('@NAJ_WEB/conta_virtual_key');
        response = await najCV.getData(`${baseURL}` + `${rotaBase}/proximo`);
        $('#id').val(response + 1);
    //Se ação igual a "edit"...
    } else if (sessionStorage.getItem('@NAJ_WEB/conta_virtual_action') == "edit") {
        id = JSON.parse(sessionStorage.getItem('@NAJ_WEB/conta_virtual_key'));
        response = await najCV.getData(`${baseURL}` + `${rotaBase}/show/${btoa(JSON.stringify({id: id}))}`);
        
        if(response.valor_comissao_boleto) response.valor_comissao_boleto = convertIntToMoney(response.valor_comissao_boleto);
        if(response.valor_tarifa_saque)    response.valor_tarifa_saque    = convertIntToMoney(response.valor_tarifa_saque);
        if(response.saque_montante)        response.saque_montante        = convertIntToMoney(response.saque_montante);
        if(response.saque_minimo)          response.saque_minimo          = convertIntToMoney(response.saque_minimo);
        
        najCV.loadData('#form-conta-virtual', response);
        carregaEspecieUnidadeFinaceira();
    }
    //Aplica mascáras
    $('#valor_comissao_boleto').mask("#.##0,00", {reverse: true});
    $('#valor_tarifa_saque').mask("#.##0,00", {reverse: true});
    $('#saque_montante').mask("#.##0,00", {reverse: true});
    $('#saque_minimo').mask("#.##0,00", {reverse: true});
    $('#multa').mask('##0,00%', {reverse: true});
    $('#desconto_percentual').mask('##0,00%', {reverse: true});
    //fecha loader
    loadingDestroy();
    //Exibe Modal
    $('#modal-manutencao-conta-virtual').modal('show');
}

/**
 * Insere ou atualiza registro conforme a ação selecionada
 * 
 * @param JSON dados
 */
async function createOrUpdateContaVirtual(dados) {
    try{
        //Bloqueia o modal de manutenção
        loadingStart('bloqueio-modal-manutencao-conta-virtual');
        if (sessionStorage.getItem('@NAJ_WEB/conta_virtual_action') == 'create') {
            response = await najCV.store(`${baseURL}` + `${rotaBase}`, dados);
            novoRegistro();
        } else if (sessionStorage.getItem('@NAJ_WEB/conta_virtual_action') == 'edit') {
            response = await najCV.update(`${baseURL}` + `${rotaBase}/${btoa(JSON.stringify({id: $('input[name=id]').val()}))}`, dados);
        }
    }catch (e){
        NajAlert.toastError(e);
    }finally {
        //Desbloqueia o modal de manutenção
        loadingDestroy('bloqueio-modal-manutencao-conta-virtual');
    }
}

/**
 * Obtêm dados do formulário
 * 
 * @returns {getDadosForm.dados}
 */
function getDadosForm() {
    let dados = {
        'id': $('input[name=id]').val(),
        'codigo_especie': $('select[name=codigo_especie]').val(),
        'codigo_unidade': $('select[name=codigo_unidade]').val(),
        'account_id': $('input[name=account_id]').val(),
        'nome': $('input[name=nome]').val(),
        'live_api_token': $('input[name=live_api_token]').val(),
        'test_api_token': $('input[name=test_api_token]').val(),
        'user_token': $('input[name=user_token]').val(),
        'multa': $('input[name=multa]').val().replace('%','').replace(',','.'),
        'mora': $('select[name=mora]').val(),
        'banco': $('input[name=banco]').val(),
        'agencia': $('input[name=agencia]').val(),
        'tipo_conta': $('select[name=tipo_conta]').val(),
        'status': $('select[name=status]').val(),
        'desconto_percentual': $('input[name=desconto_percentual]').val().replace('%','').replace(',','.'),
        'valor_comissao_boleto': convertMoneyToFloat($('input[name=valor_comissao_boleto]').val()),
        'valor_tarifa_saque': convertMoneyToFloat($('input[name=valor_tarifa_saque]').val()),
        'dias_apos': $('input[name=dias_apos]').val(),
        'saque_semanal': getValuesChekeds('#form-conta-virtual','saque_semanal', true),
        'saque_montante': convertMoneyToFloat($('input[name=saque_montante]').val()),
        'saque_minimo': convertMoneyToFloat($('input[name=saque_minimo]').val()),
    };
    return dados;
}

/**
 * Prepara tela de manutenção para um novo registro 
 */
async function novoRegistro(){
    limpaFormulario('#form-conta-virtual');
    removeClassCss('was-validated', '#form-conta-virtual')
    response = await najCV.getData(`${baseURL}` + `${rotaBase}/proximo`);
    $('#id').val(response + 1);
}

/**
 * Verifica se as naturezas das taxas foram setadas no Banco de Dados
 * 
 * @returns {boolean}
 */
async function verificaNaturezaFinanceira(){
    response = await najCV.getData(`${baseURL}` + `${rotaBase}/verificanaturezafinanceira`);
    console.log('Verifica Natureza Financeira');
    if(response.response[0].status_code == 400){
        msg = '<span style="text-align: left">' + response.response[0].status_message + '</span>';
        NajAlert.toastError(msg);
        return false;
    }
    return true;
}