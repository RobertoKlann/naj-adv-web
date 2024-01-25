//---------------------- Parametrôs -----------------------//

const tableTermoMonitorado = (isIndex('monitoramento/diarios')) ? new TermoMonitoradoTable : false;
const najTermo      = new Naj('Termo', tableTermoMonitorado);
const rotaBaseTermo = 'monitoramento/diarios/termos';

//---------------------- Eventos -----------------------//

$(document).ready(function () {

    //Ao clicar em gravar...
    $(document).on("click", '#gravarTermoMonitorado', function () {
        gravarDadosTermoMonitorado();
    });

    //Inicializa os elementos select2
    $("#variacoes").select2({
        tags: true,
    });
    $("#contem").select2({
        tags: true,
    });
    $("#nao_contem").select2({
        tags: true,
    });

    //Carrega voltarTab desabilitado por padrão
    $('#voltarTab').addClass('disabled');
    $('#voltarTab').attr('disabled', true);

    //Ao desfocar do campo numero_oab
    $('#numero_oab').blur(function () {
        setaCampoVariacoes();
    });

    //Ao mudar a opção do campo letra_oab
    $('#letra_oab').change(function () {
        setaCampoVariacoes();
    });
    
    //Ao mudar a opção do campo uf
    $('#uf').change(function () {
        setaCampoVariacoes();
    });

    //Ao esconder o modal de '#modal-manutencao-termo-monitorado' remove a classe 'z-index-100' do modal '#modal-consulta-termo-monitorado'
    $('#modal-manutencao-termo-monitorado').on('hidden.bs.modal', function(){
        if(modalProcessoCarregado == false){
            $('#modal-consulta-termo-monitorado').removeClass('z-index-100');    
        }
    });

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
    return await najTermo.postData(`${baseURL}` + `escavador/registrarnovomonitoramentodiarios?XDEBUG_SESSION_START=netbeans-xdebug`, dados);
}

/**
 * Edita um monitoramento de diário oficial. É possível alterar os Termos monitorados, ou as variações do monitoramento.
 * 
 * GRÁTIS por requisição.
 * @param {object} dados
 * @returns {object}
 */
async function editarMonitoramento(dados) {
    return await najTermo.updateData(`${baseURL}` + `escavador/editarmonitoramentodiarios/`, dados);
}

/**
 * Remove um monitoramento de diario cadastrado pelo usuário baseado no seu identificador.
 * 
 * GRÁTIS por requisição.
 * @param {int} id_monitoramento
 * @returns {object}
 */
async function removerMonitoramento(id_monitoramento) {
    return await najTermo.getData(`${baseURL}` + `escavador/removermonitoramentodiarios/` + id_monitoramento);
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
            let url  = `${baseURL}` + `${rotaBaseTermo}`;
            response = await najTermo.store(url, dados);
            await novoRegistro();
        } else if (sessionStorage.getItem('@NAJ_WEB/termo_monitorado_action') == 'edit') {
            response = await najTermo.update(`${baseURL}` + `${rotaBaseTermo}/${btoa(JSON.stringify({id: $('input[name=id]').val()}))}`, dados);
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
    response = await najTermo.getData(`${baseURL}` + `${rotaBaseTermo}/proximo`);
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
        let response = await najTermo.getData(`${baseURL}` + `${rotaBaseTermo}/proximo`);
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
        let response = await najTermo.getData(`${baseURL}` + `${rotaBaseTermo}/show/${btoa(JSON.stringify({id: id}))}`);
        //Armazena na seção a situação atual do termo,
        //essa informação será utilizada posteriormente para verificar se a situação foi alterada
        sessionStorage.setItem('statusTermo', response['status']);
        //Armazena na seção as variações atuais do termo,
        //essa informação será utilizada posteriormente para verificar se as variações foram alteradas
        sessionStorage.setItem('variacoesTermo', response['variacoes']);
        //carrega dados dos demais campos do formulário
        najTermo.loadData('#form-termo-monitorado', response);
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
