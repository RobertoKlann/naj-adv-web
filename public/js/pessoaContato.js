//---------------------- Parametrôs -----------------------//

const rotaBasePC = 'pessoa/contato';
const tablePC    = new PessoaContatoTable;
const najPC      = new Naj(rotaBasePC, tablePC);

//---------------------- Eventos -----------------------//

$(document).ready(function () {
    
    //Ao clicar em gravar...
    $(document).on('click', '#gravarPessoaContato', function () {
        gravarDadosPessoaContato();
    });
    
    $(document).on('change', '#tipo_contato', function(){
        aplicaMascaraTelefoneEmail(this);
    });
    
    //Ao esconder o modal de '#modal-manutencao-pessoa-contato' remove a classe 'z-index-100' do modal '#modal-manutencao-pessoa'
    $('#modal-manutencao-pessoa-contato').on('hidden.bs.modal', function(){
        $('#modal-manutencao-pessoa').removeClass('z-index-100');    
    });
    
});

//---------------------- Functions -----------------------//

/**
 * Grava dados da tela de manutenção
 */
function gravarDadosPessoaContato(){
    //Valida form
    result = validaForm('form-pessoa-contato');
    if(result){
        //Obtêm dados do form
        var dados = getDadosFormPessoaContato();
        createOrUpdatePessoaContato(dados);
    }
}

/**
 * Insere ou atualiza registro conforme a ação selecionada
 * 
 * @param JSON dados
 */
async function createOrUpdatePessoaContato(dados) {
    try{
        //Bloqueia o modal de manutenção
        loadingStart('bloqueio-modal-manutencao-pessoa-contato');
        if (sessionStorage.getItem('@NAJ_WEB/pessoa_contato_action') == 'create') {
            response = await najPC.store(`${baseURL}` + `${rotaBasePC}`, dados);
            $('#modal-manutencao-pessoa-contato').modal('hide');
        } else if (sessionStorage.getItem('@NAJ_WEB/pessoa_contato_action') == 'edit') {
            id = JSON.parse(sessionStorage.getItem('@NAJ_WEB/pessoa_contato_key'));
            response = await najPC.update(`${baseURL}` + `${rotaBasePC}/${btoa(JSON.stringify({CODIGO: id}))}?XDEBUG_SESSION_START=netbeans-xdebug`, dados);
        }
    }catch (e){
        NajAlert.toastError(e);
    }finally {
        //Desbloqueia o modal de manutenção
        loadingDestroy('bloqueio-modal-manutencao-pessoa-contato');
        //Foca no primeiro campo
        $('#form-pessoa-contato #pessoa').focus();
    }
}

/**
 * Obtêm dados do formulário
 * 
 * @returns {getDadosForm.dados}
 */
function getDadosFormPessoaContato() {
    let dados = {
        'CODIGO': $('#form-pessoa-contato input[name=CODIGO]').val(),
        'CODIGO_DIVISAO': $('#form-pessoa select[name=CODIGO_DIVISAO]').val(),
        'CODIGO_GRUPO': $('#form-pessoa select[name=CODIGO_GRUPO]').val(),
        'CODIGO_PESSOA': $('#form-pessoa input[name=CODIGO]').val(),
        'CONTATO': $('#form-pessoa-contato input[name=CONTATO]').val(),
        'TIPO': $('#form-pessoa-contato select[name=TIPO]').val(),
        'PESSOA': $('#form-pessoa-contato input[name=PESSOA]').val(),
        'PRINCIPAL': "S",
        'NOTIFICA': "S",
        'AGENDA': "S",
        'TEXTOS': "S"
    };
    return dados;
}

/**
 * Seta o código e o nome da pessoa no formulário de pessoa contato
 */
function setCodigoNomePessoaContato(){
    //Obtêm e seta o código e o nome da pessoa no formulário
    let codigo_pessoa = $('#form-pessoa input[name=CODIGO]').val();
    let nome          = $("#form-pessoa input[name=NOME]").val();
    let pessoa        = getFirstWordOfPhrase(nome);
    $('#form-pessoa-contato input[name=CODIGO_PESSOA]').val(codigo_pessoa);
    $('#form-pessoa-contato input[name=NOME]').val(nome);
    $('#form-pessoa-contato input[name=PESSOA]').val(pessoa);
}

/**
 * Prepara tela de manutenção para um novo registro 
 */
async function novoRegistroPessoaContato(){
    loadingStart('bloqueio-modal-manutencao-pessoa-contato');
    //Limpa os campos do formulário
    limpaFormulario('#form-pessoa-contato');
    //Remove as validações do formulário
    removeClassCss('was-validated', '#form-pessoa-contato');
    response = await najPC.getData(`${baseURL}` + `${rotaBasePC}/proximo`);
    $('#codigo_contato').val(response + 1);
    setCodigoNomePessoaContato();
    $('#titulo-modal-manutencao-pessoa-contato').html('Novo Contato');
    loadingDestroy('bloqueio-modal-manutencao-pessoa-contato');
}

/**
 * Carrega o modal de manutenção de Pessoa Contato
 */
async function carregaModalManutencaoPessoaContato(){
    loadingStart('bloqueio-modal-manutencao-pessoa');
    //Limpa os campos do formulário
    limpaFormulario('#form-pessoa-contato');
    //Remove as validações do formulário
    removeClassCss('was-validated', '#form-pessoa-contato');
    //Se ação igual a "create"...
    if (sessionStorage.getItem('@NAJ_WEB/pessoa_contato_action') == "create") {
        //Remove 'pessoa_contato_key' da sessão
        sessionStorage.removeItem('@NAJ_WEB/pessoa_contato_key');
        setCodigoNomePessoaContato();
        //Obtêm o id do próximo registro
        response = await najPC.getData(`${baseURL}${rotaBasePC}/proximo`);
        //Seta o id do registro
        $('#codigo_contato').val(response + 1);
        $('#titulo-modal-manutencao-pessoa-contato').html('Novo Contato');
    //Se ação igual a "edit"...
    } else if (sessionStorage.getItem('@NAJ_WEB/pessoa_contato_action') == "edit") {
        id = JSON.parse(sessionStorage.getItem('@NAJ_WEB/pessoa_contato_key'));
        response = await najPC.getData(`${baseURL}${rotaBasePC}/show/${btoa(JSON.stringify({CODIGO: id}))}`);
        najPC.loadData('#form-pessoa-contato', response);
        $('#titulo-modal-manutencao-pessoa-contato').html('Alterando Contato');
    }
    $('#modal-manutencao-pessoa-contato').modal('show');
    loadingDestroy('bloqueio-modal-manutencao-pessoa');
}

/**
 * Executa a busca no BD
 *
 * @param {int} codigo_pessoa 
 * @param {int} camada_consulta Informa em que nivel de camada a modal de consulta se encontra na tela  
 */
async function buscaPersonalizadaPessoaContato(codigo_pessoa, camada_consulta = 0){
    //limpa filtros 
    tablePC.filtersForSearch = [];

    //Seta os filtros iniciais
    let filter    = {}; 
    filter.val    = codigo_pessoa;
    filter.op     = "I";
    filter.col    = "pessoa_contato.CODIGO_PESSOA";
    filter.origin = btoa(filter);
    tablePC.filtersForSearch.push(filter);
    
    //Renderiza tabela
    tablePC.render();
    
    //Remove filtros default
    $('#datatable-pessoa-contato .datatable-body .data-table-filter')[0].innerHTML = "";
}

/**
 * Verifica o tipo de contato selecionado e aplica a máscara e validação de acordo com o tipo
 * 
 * @param {string} e Elemento
 */
function aplicaMascaraTelefoneEmail(e){
    let telefones = ['Fone Trabalho','Fone Residencial','Fone Comercial','Fone Fax'];
    let celulares = ['Fone Celular','Fone Celular WhatsApp','Fone Celular Comercial','Fone Celular Particular','Fone p/ Recados'];
    let emails    = ['E-Mail','E-Mail Particular','E-Mail Comercial','E-Mail Trabalho'];
    //Para Telefones
    if(telefones.indexOf(e.value) >= 0){
        $("#contato").val('');
        $("#contato").attr("type", "tel");
        $("#contato").mask('(00) 0000-0000');
    //Para Celulares
    }else if(celulares.indexOf(e.value) >= 0){
        $("#contato").val('');
        $("#contato").attr("type", "tel");
        $("#contato").mask('(00) 00000-0000');
    //Para Emails
    }else if(emails.indexOf(e.value) >= 0){
        $("#contato").val('');
        $("#contato").unmask();
        $("#contato").attr("type", "email");
        $("#contato").attr("maxlength", 50);
    //Para Outros tipos de contato
    } else{
        $("#contato").val('');
        $("#contato").unmask();
        $("#contato").attr("type", "text");
        $("#contato").attr("maxlength", 50);
    }
    
}