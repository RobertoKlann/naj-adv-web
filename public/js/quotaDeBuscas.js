//---------------------- Parametrôs -----------------------//

const najQuotaDeBuscas  = new Naj('QuotaDeBuscas', null);
const rotaQuotaDeBuscas = 'sysconfig/';
let quota_de_buscas_sys_config = null;

//---------------------- Eventos -----------------------//

$(document).ready(function() {
    
    //Ao clicar em gravar...
    $(document).on("click", '#gravarQuotaDeBuscas', function () {
        gravarDadosQuotaDeBuscas();
    });
    
});

//---------------------- Functions -----------------------//

/**
 * Grava dados da tela de manutenção
 */
function gravarDadosQuotaDeBuscas(){
    //Valida form
    result = validaForm('form-quota-de-buscas');
    if(result){
        //Obtêm dados do form
        var dados = getDadosFormQuotaDeBuscas();
        createOrUpdateQuotaDeBuscas(dados);
    }
}

/**
 * Carrega Modal Manutencao Processo Inclussão
 * 
 * @param {object} cartorio
 */
async function carregaModalManutencaoQuotaDeBuscas() {
    try{
        loadingStart();
        //Limpa os campos do formulário
        limpaFormulario('#modal-manutencao-quota-de-buscas');
        //Remove as validações do formulário
        removeClassCss('was-validated', '#form-quota-de-buscas');
        sessionStorage.removeItem('@NAJ_WEB/quota_de_buscas');
        if(getConsts().tipoUsuarioLogado != "0"){
            NajAlert.toastWarning('Área restrita somente ao usuário supervisor!');
            loadingDestroy();
            return;
        }
        //Obtêm a quota de buscas do sys_config no BD
        quota_de_buscas_sys_config = await najQuotaDeBuscas.getData(`${baseURL}${rotaQuotaDeBuscas}searchsysconfigall/PROCESSOS/MONITORAMENTO_TRIBUNAL_QUOTA`); 
        //Verifica se os dias semana no sys_config é vazio
        if(jQuery.isEmptyObject(quota_de_buscas_sys_config)){
            sessionStorage.setItem('@NAJ_WEB/quota_de_buscas','create');
        }else{
            sessionStorage.setItem('@NAJ_WEB/quota_de_buscas','edit');
            let dados = {'quota-de-buscas': quota_de_buscas_sys_config.VALOR};
            najQuotaDeBuscas.loadData("#form-quota-de-buscas ", dados);
        }
        //Exibe modal
        $('#modal-manutencao-quota-de-buscas').modal('show');
        //Foca no primeiro campo
        $('#form-quota-de-buscas #quota-de-buscas').focus();
    }finally{
        loadingDestroy();
    }
}

/**
 * Prepara tela de manutenção para um novo registro 
 */
async function novoRegistroQuotaDeBuscas(){
    //Limpa os campos do formulário
    limpaFormulario('#modal-manutencao-quota-de-buscas');
    //Remove as validações do formulário
    removeClassCss('was-validated', '#form-quota-de-buscas');
    //Carrega modal
    await carregaModalManutencaoQuotaDeBuscas();
}

/**
 * Obtêm dados do formulário
 * 
 * @returns {getDadosForm.dados}
 */
function getDadosFormQuotaDeBuscas() {
    let dados = {
        'VALOR': $('#form-quota-de-buscas input[name=quota-de-buscas]').val()
    };
    return dados;
}

/**
 * Insere ou atualiza registro conforme a ação selecionada
 * 
 * @param JSON dados
 */
async function createOrUpdateQuotaDeBuscas(dados) {
    try{
        //Bloqueia o modal de manutenção
        loadingStart('bloqueio-modal-manutencao-quota-de-buscas');
        if(dados.VALOR == quota_de_buscas_sys_config.VALOR){
            NajAlert.toastWarning('Não foi detectado nem uma alteração!');
            return;
        }
        let url = `${baseURL}${rotaQuotaDeBuscas}PROCESSOS/MONITORAMENTO_TRIBUNAL_QUOTA/${dados.VALOR}?XDEBUG_SESSION_START=netbeans-xdebug`;
        if(sessionStorage.getItem('@NAJ_WEB/quota_de_buscas') == 'create'){
            result = await najQuotaDeBuscas.postData(url);
        }else if(sessionStorage.getItem('@NAJ_WEB/quota_de_buscas') == 'edit'){
            result = await najQuotaDeBuscas.updateData(url);
        }
        if(result){
            quota_de_buscas_sys_config.VALOR = dados.VALOR;
            NajAlert.toastSuccess('Nova configuração salva com sucesso!');
        }else{
            NajAlert.toastError('Não foi posssível salvar as novas configuração, contate o suporte!');
        }
    }catch (e){
        NajAlert.toastError(e)
    }finally {
        //Desbloqueia o modal de manutenção
        loadingDestroy('bloqueio-modal-manutencao-quota-de-buscas');
    }
}