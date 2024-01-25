//---------------------- Parametrôs -----------------------//

const najProcessoAreaJuridica      = new Naj('ProcessoAreaJuridica', null);
const rotaBaseProcessoAreaJuridica = 'processos/areajuridica';

//---------------------- Eventos -----------------------//

$(document).ready(function() {
    
    //Ao clicar em gravar...
    $(document).on("click", '#gravarProcessoAreaJuridica', function () {
        gravarDadosProcessoAreaJuridica();
    });
    
});

//---------------------- Functions -----------------------//

/**
 * Grava dados da tela de manutenção
 */
function gravarDadosProcessoAreaJuridica(){
    //Valida form
    result = validaForm('form-processo-area-juridica');
    if(result){
        //Obtêm dados do form
        var dados = getDadosFormProcessoAreaJuridica();
        createOrUpdateProcessoAreaJuridica(dados);
    }
}

/**
 * Carrega Modal Manutencao Processo Inclussão
 * 
 * @param {object} area_juridica
 */
async function carregaModalManutencaoProcessoAreaJuridica(area_juridica = null) {
    if (sessionStorage.getItem('@NAJ_WEB/processo_area_juridica_action') == "create") {
        //Obtêm o próximo código de processo
        let codigo = await najProcessoAreaJuridica.getData(`${baseURL}${rotaBaseProcessoAreaJuridica}/proximo`);
        //Seta o próximo código do processo no campo do formulário
        $('#form-processo-area-juridica #codigo_processo_area_juridica').val(codigo + 1);
    } else if (sessionStorage.getItem('@NAJ_WEB/processo_area_juridica_action') == "edit") {
        najProcessoAreaJuridica.loadData('#form-processo-area-juridica', area_juridica);
    }   
    //Exibe modal
    $('#modal-manutencao-processo-area-juridica').modal('show');
}

/**
 * Prepara tela de manutenção para um novo registro 
 */
async function novoRegistroProcessoAreaJuridica(){
    //Limpa os campos do formulário
    limpaFormulario('#modal-manutencao-processo-area-juridica');
    //Remove as validações do formulário
    removeClassCss('was-validated', '#form-processo-area-juridica');
    //Carrega modal
    carregaModalManutencaoProcessoAreaJuridica();
}

/**
 * Obtêm dados do formulário
 * 
 * @returns {getDadosForm.dados}
 */
function getDadosFormProcessoAreaJuridica() {
    let dados = {
        'ID': $('#form-processo-area-juridica input[name=ID]').val(),
        'AREA': $('#form-processo-area-juridica input[name=AREA]').val()
    };
    return dados;
}

/**
 * Insere ou atualiza registro conforme a ação selecionada
 * 
 * @param JSON dados
 */
async function createOrUpdateProcessoAreaJuridica(dados) {
    try{
        //Bloqueia o modal de manutenção
        loadingStart('bloqueio-modal-manutencao-processo-area-juridica');
        if (sessionStorage.getItem('@NAJ_WEB/processo_area_juridica_action') == 'create') {
            response = await najProcessoAreaJuridica.store(`${baseURL}${rotaBaseProcessoAreaJuridica}`, dados);
            novoRegistroProcessoAreaJuridica();
        } else if (sessionStorage.getItem('@NAJ_WEB/processo_area_juridica_action') == 'edit') {
            response = await najProcessoAreaJuridica.update(`${baseURL}${rotaBaseProcessoAreaJuridica}/${btoa(JSON.stringify({ID: dados.ID}))}`, dados);
            //Atualzia o nome do área jurídica no campo do formulário do processo
            $('#form-processo #nome_area').val(dados.AREA);
        }
    }catch (e){
        NajAlert.toastError(e);
    }finally {
        //Desbloqueia o modal de manutenção
        loadingDestroy('bloqueio-modal-manutencao-processo-area-juridica');
    }
}