//---------------------- Parametrôs -----------------------//

const najProcessoComarca      = new Naj('ProcessoComarca', null);
const rotaBaseProcessoComarca = 'processos/comarca';

//---------------------- Eventos -----------------------//

$(document).ready(function() {
    
    //Ao clicar em gravar...
    $(document).on("click", '#gravarProcessoComarca', function () {
        gravarDadosProcessoComarca();
    });
    
});

//---------------------- Functions -----------------------//

/**
 * Grava dados da tela de manutenção
 */
function gravarDadosProcessoComarca(){
    //Valida form
    result = validaForm('form-processo-comarca');
    if(result){
        //Obtêm dados do form
        var dados = getDadosFormProcessoComarca();
        createOrUpdateProcessoComarca(dados);
    }
}

/**
 * Carrega Modal Manutencao Processo Inclussão
 * 
 * @param {object} comarca
 */
async function carregaModalManutencaoProcessoComarca(comarca = null) {
    //Limpa os campos do formulário
    limpaFormulario('#modal-manutencao-processo-comarca');
    //Remove as validações do formulário
    removeClassCss('was-validated', '#form-processo-comarca');
    if (sessionStorage.getItem('@NAJ_WEB/processo_comarca_action') == "create") {
        //Obtêm o próximo código de processo
        let codigo = await najProcessoComarca.getData(`${baseURL}${rotaBaseProcessoComarca}/proximo`);
        //Seta o próximo código do processo no campo do formulário
        $('#form-processo-comarca #codigo_processo_comarca').val(codigo + 1);
    } else if (sessionStorage.getItem('@NAJ_WEB/processo_comarca_action') == "edit") {
        najProcessoComarca.loadData('#form-processo-comarca', comarca);
    }   
    //Exibe modal
    $('#modal-manutencao-processo-comarca').modal('show');
    //Foca no primeiro campo
    $('#form-processo-comarca #nome_processo_comarca').focus();
}

/**
 * Prepara tela de manutenção para um novo registro 
 */
async function novoRegistroProcessoComarca(){
    //Limpa os campos do formulário
    limpaFormulario('#modal-manutencao-processo-comarca');
    //Remove as validações do formulário
    removeClassCss('was-validated', '#form-processo-comarca');
    //Carrega modal
    carregaModalManutencaoProcessoComarca();
}

/**
 * Obtêm dados do formulário
 * 
 * @returns {getDadosForm.dados}
 */
function getDadosFormProcessoComarca() {
    let dados = {
        'CODIGO': $('#form-processo-comarca input[name=CODIGO]').val(),
        'COMARCA': $('#form-processo-comarca input[name=COMARCA]').val(),
        'UF': $('#form-processo-comarca select[name=UF]').val()
    };
    return dados;
}

/**
 * Insere ou atualiza registro conforme a ação selecionada
 * 
 * @param JSON dados
 */
async function createOrUpdateProcessoComarca(dados) {
    try{
        //Bloqueia o modal de manutenção
        loadingStart('bloqueio-modal-manutencao-processo-comarca');
        if (sessionStorage.getItem('@NAJ_WEB/processo_comarca_action') == 'create') {
            //Verifica se a comarca já existe no BD 
            response = await najProcessoComarca.getData(`${baseURL}${rotaBaseProcessoComarca}/getcomarcabyname/${dados.COMARCA}`);
            if(response.length > 0){
                NajAlert.toastWarning('Já existe uma comarca cadastrada com essa descrição!');
                return;
            }
            response = await najProcessoComarca.store(`${baseURL}${rotaBaseProcessoComarca}`, dados);
            //Atualzia o código da classe no campo do formulário do processo
            $('#form-processo #codigo_comarca').val(dados.CODIGO);
            //Muda para o modo edição
            sessionStorage.setItem('@NAJ_WEB/processo_comarca_action','edit');
            //Carrega o modal processo comarca
            await carregaModalManutencaoProcessoComarca(dados);
        } else if (sessionStorage.getItem('@NAJ_WEB/processo_comarca_action') == 'edit') {
            response = await najProcessoComarca.update(`${baseURL}${rotaBaseProcessoComarca}/${btoa(JSON.stringify({CODIGO: dados.CODIGO}))}`, dados);
        }
        //Atualzia o nome do comarca no campo do formulário do processo
        $('#form-processo #nome_comarca').val(dados.COMARCA);
    }catch (e){
        NajAlert.toastError(e);
    }finally {
        //Desbloqueia o modal de manutenção
        loadingDestroy('bloqueio-modal-manutencao-processo-comarca');
    }
}