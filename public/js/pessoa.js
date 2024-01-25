//---------------------- Parametrôs -----------------------//

const najPessoa      = new Naj('Pessoa', null);
const rotaBasePessoa = `pessoas`;

//---------------------- Eventos -----------------------//

$(document).ready(function () {
    
    //Remove o código de pessoa na sessão
    sessionStorage.removeItem('@NAJ_WEB/codigo_pessoa');
    
    //Exibe modal manutenção pessoa
    $(document).on("click", '#chamaModalManutencaoPessoa', function () {
        sessionStorage.setItem('@NAJ_WEB/pessoa_action', 'create');
        carregaModalManutencaoPessoa();
    });
    
    //Ao clicar em gravar...
    $(document).on("click", '#gravarPessoa', function () {
        gravarDadosPessoa();
    });
    
    //Ao mudar a opção do tipo de pessoa...
    $(document).on("change","#tipo", function(){
        changeTipoPessoa(this);
    });
    
});

//---------------------- Functions -----------------------//

/**
 * Alterna entre os campos CPF e CNPJ no formulário de manutenção pessoa
 * 
 * @param {element} elemento
 */
function changeTipoPessoa(elemento){
    if(elemento.value == "F"){
        $('#label_cnpj').hide();
        $('#cnpj').hide();
        $('#label_cpf').show();
        $('#cpf').val(null);
        $('#cpf').show();
    }else if (elemento.value == "J"){
        $('#label_cpf').hide();
        $('#cpf').hide();
        $('#label_cnpj').show();
        $('#cnpj').val(null);
        $('#cnpj').show();
    }
}

/**
 * Grava dados da tela de manutenção
 */
function gravarDadosPessoa() {
    //Valida form
    result = validaForm('form-pessoa');
    if(result){
        //Obtêm dados do form
        var dados = getDadosFormPessoa();
        createOrUpdatePessoa(dados);
    }
}

/**
 * Obtêm dados do formulário
 * 
 * @returns {getDadosForm.dados}
 */
function getDadosFormPessoa() {
    let dados = {
        'CODIGO': $('#form-pessoa input[name=CODIGO]').val(),
        'NOME': $('#form-pessoa input[name=NOME]').val().removeTrema(),
        'TIPO': $('#form-pessoa select[name=TIPO]').val(),
        'CPF': $('#form-pessoa input[name=CPF]').val(),
        'CNPJ': $('#form-pessoa input[name=CNPJ]').val(),
        'CODIGO_DIVISAO': $('#form-pessoa select[name=CODIGO_DIVISAO]').val(),
        'CODIGO_GRUPO': $('#form-pessoa select[name=CODIGO_GRUPO]').val(),
        'ENDERECO_TIPO': $('#form-pessoa select[name=ENDERECO_TIPO]').val(),
        'ENDERECO': $('#form-pessoa input[name=ENDERECO]').val(),
        'NUMERO': $('#form-pessoa input[name=NUMERO]').val(),
        'BAIRRO': $('#form-pessoa input[name=BAIRRO]').val(),
        'COMPLEMENTO': $('#form-pessoa input[name=COMPLEMENTO]').val(),
        'CIDADE': $('#form-pessoa input[name=CIDADE]').val(),
        'UF': $('#form-pessoa select[name=UF]').val(),
        'DATA_CADASTRO': getDateProperties().fullDate,
    };
    return dados;
}

/**
 * Insere ou atualiza registro conforme a ação selecionada
 * 
 * @param JSON dados
 */
async function createOrUpdatePessoa(dados) {
    try{
        //Bloqueia o modal de manutenção
        loadingStart('bloqueio-modal-manutencao-pessoa');
        if (sessionStorage.getItem('@NAJ_WEB/pessoa_action') == 'create') {
            response = await najPessoa.store(`${baseURL}${rotaBasePessoa}`, dados);
            //Seta o código de pessoa na sessão
            sessionStorage.setItem('@NAJ_WEB/codigo_pessoa', dados.CODIGO);
            //Verifica se a rotina corrente é a rotina de monitoramento diário
            if(typeof rotaBaseDiario != "undefined"){
                //Vamos atualizar o registro de envolvido vinculando a ele o código da pessoa que acabamos de inserir 
                await atualizaEnvolvido(); //Função em Monitoramento Diário
            }
            sessionStorage.setItem('@NAJ_WEB/pessoa_action', 'edit');
            await carregaModalManutencaoPessoa();
        } else if (sessionStorage.getItem('@NAJ_WEB/pessoa_action') == 'edit') {
            response = await najPessoa.update(`${baseURL}${rotaBasePessoa}/${btoa(JSON.stringify({CODIGO: $('#form-pessoa input[name=CODIGO]').val()}))}`, dados);
        }
    }catch (e){
        NajAlert.toastError(e);
    }finally {
        //Desbloqueia o modal de manutenção
        loadingDestroy('bloqueio-modal-manutencao-pessoa');
    }
}

/**
 * Prepara tela de manutenção para um novo registro 
 */
async function novoRegistroPessoa(){
    loadingStart('bloqueio-modal-manutencao-pessoa');
    //Remove o código de pessoa na sessão
    sessionStorage.removeItem('@NAJ_WEB/codigo_pessoa');
    //Seta o tipo de ação para pessoa
    sessionStorage.setItem('@NAJ_WEB/pessoa_action','create') ;
    //Seta tab 1 como a corrente
    changeTabManutencaoPessoa(1);
    //Carrega formulário
    await carregaModalManutencaoPessoa();
    loadingDestroy('bloqueio-modal-manutencao-pessoa');
}

/**
 *  Carrega o Modal de Manutenção de Pessoa 
 */
async function carregaModalManutencaoPessoa(){
    //Limpa os campos do formulário
    limpaFormulario('#form-pessoa');
    //Remove as validações do formulário
    removeClassCss('was-validated', '#form-pessoa');
    //Seta máscaras em cpf e cnpj
    $('#form-pessoa #cpf').mask('000.000.000-00', {reverse: true});
    $('#form-pessoa #cnpj').mask('00.000.000/0000-00', {reverse: true});
    response = await carregaOptionsSelect(`${rotaBasePessoa}/divisao`, 'codigo_divisao', false, 'data', false, 1);
    response = await carregaOptionsSelect(`${rotaBasePessoa}/grupopessoa`, 'codigo_grupo', false, 'data', false);
    //Seta a tab 1 como a a tab corrente
    changeTabManutencaoPessoa(1);
    //Se ação igual a "create"...
    if (sessionStorage.getItem('@NAJ_WEB/pessoa_action') == "create") {
        //Esconde o icone de acesso externo a ficha da Pessoa
        $('#form-pessoa #externoPessoa').hide();
        //Remove o código de pessoa na sessão
        sessionStorage.removeItem('@NAJ_WEB/codigo_pessoa');
        //Obtêm o id do próximo registro
        response = await najPessoa.getData(`${baseURL}${rotaBasePessoa}/proximo`);
        //Seta o id do registro
        $('#form-pessoa #codigo').val(response + 1);
        //Se ação igual a "edit"...
        //Esconde inicialmente o label e o campo do CNPJ
        $('#form-pessoa #label_cnpj').hide();
        $('#form-pessoa #cnpj').hide();
        //Seta pessoa física por default
        $('#form-pessoa #tipo').val('F');
        $('#titulo-modal-manutencao-pessoa').html('Nova Pessoa');
    } else if (sessionStorage.getItem('@NAJ_WEB/pessoa_action') == "edit") {
        id = JSON.parse(sessionStorage.getItem('@NAJ_WEB/codigo_pessoa'));
        response = await najPessoa.getData(`${baseURL}${rotaBasePessoa}/show/${btoa(JSON.stringify({CODIGO: id}))}`);
        if(response.CNPJ == "" || response.CNPJ == null){
            //Esconde o label e o campo do CNPJ
            $('#form-pessoa #label_cnpj').hide();
            $('#form-pessoa #cnpj').hide();
        }else if(response.CEP == "" || response.CEP == null){
            //Esconde o label e o campo do CEP
            $('#form-pessoa #label_cpf').hide();
            $('#form-pessoa #cpf').hide();
        }
        najPessoa.loadData('#form-pessoa', response);
        $('#titulo-modal-manutencao-pessoa').html('Alterando Pessoa');
    }
    //Exibe modal
    $('#modal-manutencao-pessoa').modal('show');
    //Foca no primeiro campo
    $('#form-pessoa #nome').focus();
}

/**
 * Muda a tab da tela de manutenção do termo monitorado
 */
async function changeTabManutencaoPessoa(tab) {
    //Seta a tab corrente
    if (tab == 1) {
        $('#tabPessoa').addClass('active');
        $('#guidePessoa').addClass('active');
        $('#guidePessoa').attr('aria-selected', true);
        $('#tabContatos').removeClass('active');
        $('#guideContatos').removeClass('active');
        $('#guideContatos').attr('aria-selected', false);
    } else if (tab == 2) {
        //Obtêm o código da pessoa
        let codigo_pessoa = sessionStorage.getItem('@NAJ_WEB/codigo_pessoa');
        //Verifica primeiramente se apessoa foi cadastrada
        if(!codigo_pessoa){
            NajAlert.toastWarning('Você deve primeiramente cadastrar a pessoa!');
            return;
        }
        //Carrega a tabela de contatos da pessoa
        await buscaPersonalizadaPessoaContato(codigo_pessoa, 1);
        $('#tabPessoa').removeClass('active');
        $('#guidePessoa').removeClass('active');
        $('#guidePessoa').attr('aria-selected', false);
        $('#tabContatos').addClass('active');
        $('#guideContatos').addClass('active');
        $('#guideContatos').attr('aria-selected', true);
    }
}

function abreExternoCadastroPessoa(codigo_pessoa = null) {
    if(!codigo_pessoa){
        codigo_pessoa = sessionStorage.getItem('@NAJ_WEB/codigo_pessoa');
    }
    window.open(`${najAntigoUrl}?idform=pessoas&pessoaid=${codigo_pessoa}`);
}