//---------------------- Parametrôs -----------------------//

const najProcessoClasse      = new Naj('ProcessoClasse', null);
const rotaBaseProcessoClasse = 'processos/classe';

//---------------------- Eventos -----------------------//

$(document).ready(function() {
    
    //Ao clicar em gravar...
    $(document).on("click", '#gravarProcessoClasse', function () {
        gravarDadosProcessoClasse();
    });
    
});

//---------------------- Functions -----------------------//

/**
 * Grava dados da tela de manutenção
 */
function gravarDadosProcessoClasse(){
    //Valida form
    result = validaForm('form-processo-classe');
    if(result){
        //Obtêm dados do form
        var dados = getDadosFormProcessoClasse();
        createOrUpdateProcessoClasse(dados);
    }
}

/**
 * Carrega Modal Manutencao Processo Inclussão
 * 
 * @param {object} classe
 */
async function carregaModalManutencaoProcessoClasse(classe = null) {
    //Limpa os campos do formulário
    limpaFormulario('#modal-manutencao-processo-classe');
    //Remove as validações do formulário
    removeClassCss('was-validated', '#form-processo-classe');
    if (sessionStorage.getItem('@NAJ_WEB/processo_classe_action') == "create") {
        //Obtêm o próximo código de processo
        let codigo = await najProcessoClasse.getData(`${baseURL}${rotaBaseProcessoClasse}/proximo`);
        //Seta o próximo código do processo no campo do formulário
        $('#form-processo-classe #codigo_processo_classe').val(codigo + 1);
    } else if (sessionStorage.getItem('@NAJ_WEB/processo_classe_action') == "edit") {
        najProcessoClasse.loadData('#form-processo-classe', classe);
    }   
    //Exibe modal
    $('#modal-manutencao-processo-classe').modal('show');
    //Foca no primeiro campo
    $('#form-processo-classe #nome_processo_classe').focus();
}

/**
 * Prepara tela de manutenção para um novo registro 
 */
async function novoRegistroProcessoClasse(){
    //Limpa os campos do formulário
    limpaFormulario('#modal-manutencao-processo-classe');
    //Remove as validações do formulário
    removeClassCss('was-validated', '#form-processo-classe');
    //Carrega modal
    carregaModalManutencaoProcessoClasse();
}

/**
 * Obtêm dados do formulário
 * 
 * @returns {getDadosForm.dados}
 */
function getDadosFormProcessoClasse() {
    let dados = {
        'CODIGO': $('#form-processo-classe input[name=CODIGO]').val(),
        'CLASSE': $('#form-processo-classe input[name=CLASSE]').val(),
        'TIPO': $('#form-processo-classe select[name=TIPO]').val(),
    };
    return dados;
}

/**
 * Insere ou atualiza registro conforme a ação selecionada
 * 
 * @param JSON dados
 */
async function createOrUpdateProcessoClasse(dados) {
    try{
        //Bloqueia o modal de manutenção
        loadingStart('bloqueio-modal-manutencao-processo-classe');
        if (sessionStorage.getItem('@NAJ_WEB/processo_classe_action') == 'create') {
            //Verifica se a classe já existe no BD 
            response = await najProcessoClasse.getData(`${baseURL}${rotaBaseProcessoClasse}/getclassebyname/${dados.CLASSE}`);
            if(response.length > 0){
                NajAlert.toastWarning('Já existe uma classe cadastrada com essa descrição!');
                return;
            }
            response = await najProcessoClasse.store(`${baseURL}${rotaBaseProcessoClasse}`, dados);
            //Atualzia o código da classe no campo do formulário do processo
            $('#form-processo #codigo_classe').val(dados.CODIGO);
            //Muda para o modo edição
            sessionStorage.setItem('@NAJ_WEB/processo_classe_action','edit');
            //Carrega o modal processo classe
            await carregaModalManutencaoProcessoClasse(dados);
        } else if (sessionStorage.getItem('@NAJ_WEB/processo_classe_action') == 'edit') {
            response = await najProcessoClasse.update(`${baseURL}${rotaBaseProcessoClasse}/${btoa(JSON.stringify({CODIGO: dados.CODIGO}))}`, dados);
        }
        //Atualzia o nome da classe no campo do formulário do processo
        $('#form-processo #nome_classe').val(dados.CLASSE);
    }catch (e){
        NajAlert.toastError(e);
    }finally {
        //Desbloqueia o modal de manutenção
        loadingDestroy('bloqueio-modal-manutencao-processo-classe');
    }
}