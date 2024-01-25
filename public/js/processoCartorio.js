//---------------------- Parametrôs -----------------------//

const najProcessoCartorio      = new Naj('ProcessoCartorio', null);
const rotaBaseProcessoCartorio = 'processos/cartorio';

//---------------------- Eventos -----------------------//

$(document).ready(function() {
    
    //Ao clicar em gravar...
    $(document).on("click", '#gravarProcessoCartorio', function () {
        gravarDadosProcessoCartorio();
    });
    
});

//---------------------- Functions -----------------------//

/**
 * Grava dados da tela de manutenção
 */
function gravarDadosProcessoCartorio(){
    //Valida form
    result = validaForm('form-processo-cartorio');
    if(result){
        //Obtêm dados do form
        var dados = getDadosFormProcessoCartorio();
        createOrUpdateProcessoCartorio(dados);
    }
}

/**
 * Carrega Modal Manutencao Processo Inclussão
 * 
 * @param {object} cartorio
 */
async function carregaModalManutencaoProcessoCartorio(cartorio = null) {
    //Limpa os campos do formulário
    limpaFormulario('#modal-manutencao-processo-cartorio');
    //Remove as validações do formulário
    removeClassCss('was-validated', '#form-processo-cartorio');
    if (sessionStorage.getItem('@NAJ_WEB/processo_cartorio_action') == "create") {
        //Obtêm o próximo código de processo
        let codigo = await najProcessoCartorio.getData(`${baseURL}${rotaBaseProcessoCartorio}/proximo`);
        //Seta o próximo código do processo no campo do formulário
        $('#form-processo-cartorio #codigo_processo_cartorio').val(codigo + 1);
    } else if (sessionStorage.getItem('@NAJ_WEB/processo_cartorio_action') == "edit") {
        najProcessoCartorio.loadData('#form-processo-cartorio', cartorio);
    }   
    //Exibe modal
    $('#modal-manutencao-processo-cartorio').modal('show');
    //Foca no primeiro campo
    $('#form-processo-cartorio #nome_processo_cartorio').focus();
}

/**
 * Prepara tela de manutenção para um novo registro 
 */
async function novoRegistroProcessoCartorio(){
    //Limpa os campos do formulário
    limpaFormulario('#modal-manutencao-processo-cartorio');
    //Remove as validações do formulário
    removeClassCss('was-validated', '#form-processo-cartorio');
    //Carrega modal
    carregaModalManutencaoProcessoCartorio();
}

/**
 * Obtêm dados do formulário
 * 
 * @returns {getDadosForm.dados}
 */
function getDadosFormProcessoCartorio() {
    let dados = {
        'CODIGO': $('#form-processo-cartorio input[name=CODIGO]').val(),
        'CARTORIO': $('#form-processo-cartorio input[name=CARTORIO]').val()
    };
    return dados;
}

/**
 * Insere ou atualiza registro conforme a ação selecionada
 * 
 * @param JSON dados
 */
async function createOrUpdateProcessoCartorio(dados) {
    try{
        //Bloqueia o modal de manutenção
        loadingStart('bloqueio-modal-manutencao-processo-cartorio');
        if (sessionStorage.getItem('@NAJ_WEB/processo_cartorio_action') == 'create') {
            //Verifica se a comarca já existe no BD 
            response = await najProcessoCartorio.getData(`${baseURL}${rotaBaseProcessoComarca}/getcartoriobyname/${dados.CARTORIO}`);
            if(response.length > 0){
                NajAlert.toastWarning('Já existe um cartório cadastrada com essa descrição!');
                return;
            }
            response = await najProcessoCartorio.store(`${baseURL}${rotaBaseProcessoCartorio}`, dados);
            //Atualzia o código da classe no campo do formulário do processo
            $('#form-processo #codigo_cartorio').val(dados.CODIGO);
            //Muda para o modo edição
            sessionStorage.setItem('@NAJ_WEB/processo_cartorio_action','edit');
            //Carrega o modal processo cartorio
            await carregaModalManutencaoProcessoCartorio(dados);
        } else if (sessionStorage.getItem('@NAJ_WEB/processo_cartorio_action') == 'edit') {
            response = await najProcessoCartorio.update(`${baseURL}${rotaBaseProcessoCartorio}/${btoa(JSON.stringify({CODIGO: dados.CODIGO}))}`, dados);
        }
        //Atualzia o nome do cartorio no campo do formulário do processo
        $('#form-processo #nome_cartorio').val(dados.CARTORIO);
    }catch (e){
        NajAlert.toastError(e);
    }finally {
        //Desbloqueia o modal de manutenção
        loadingDestroy('bloqueio-modal-manutencao-processo-cartorio');
    }
}