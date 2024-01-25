//---------------------- Parametrôs -----------------------//

const najMPT       = new Naj('MonitoramentoProcessoTribunal', null);
const rotaBaseMPT  = 'monitoraprocessotribunal';
let   dias_mes     = {};
let   dias_semana  = {};
//---------------------- Eventos -----------------------//

$(document).ready(function () {
    
    $(document).on('click', '#gravarMonitoramentoProcessoTribunal', async function() {
        await gravarDadosMonitoramentoProcessoTribunal();
    });
    
    //Ao esconder o modal de '#modal-manutencao-processo-comarca' remove a classe 'z-index-100' do modal '#modal-manutencao-processo'
    $('#modal-manutencao-monitoramento-processo-tribunal').on('hidden.bs.modal', function(){
        $('#modal-conteudo-publicacao').removeClass('z-index-100');    
    });
    
    //Ao esconder o modal de '#modal-nova-tarefa-processo' remove a classe 'z-index-100' do modal '#modal-conteudo-publicacao'
    $('#modal-nova-tarefa-processo').on('hidden.bs.modal', function(){
        ModalTarefaProcesso = false;
        $('#modal-conteudo-movimentacao-processo').removeClass('z-index-100');    
    });

    $(document).on('click', '#form-processo-tribunal #quadro_dias_semana', function (){
        if(dias_semana.VALOR != null){
            NajAlert.toastWarning('Não é possível alterar os dias da busca semanal pois as buscas semanais padrão está ativada, solicite permissão ao supervisor!')
        }else if(dias_mes.VALOR != null){
            NajAlert.toastWarning('Não é possível alterar os dias da busca semanal pois as buscas mensais padrão está ativada, solicite permissão ao supervisor!')
        }
    })
    
    $(document).on('click', '#form-processo-tribunal #quadro_dias_mes', function (){
        NajAlert.toastWarning('Não é possível alterar os dias da busca mensal pois as buscas mensais padrão só podem ser alteradas pelo painel de "busca padrão", solicite permissão ao supervisor!')
    })
});

//---------------------- Functions -----------------------//

/**
 * Carrega modal de manutenção do Monitoramento Processo Tribunal
 * 
 * @param object dados que serão carregados no modo edição
 */
async function carregaModalManutencaoMonitoramentoProcessoTribunal(dados = null) {
    limpaFormulario('#form-processo-tribunal');    
    removeClassCss('was-validated', '#form-processo-tribunal')   
    //Obtêm os dias do mês do sys_config no BD
    dias_mes = await najMPT.getData(`${baseURL}` + `sysconfig/searchsysconfigall/TRIBUNAIS/DIAS_MES`); 
    //Obtêm os dias da semana do sys_config no BD
    dias_semana = await najMPT.getData(`${baseURL}` + `sysconfig/searchsysconfigall/TRIBUNAIS/DIAS_SEMANA`); 
    //Se não houver registro de dias_mes e dias semanas no sys_config, habilita campos de semna para edição
    if(jQuery.isEmptyObject(dias_mes) && jQuery.isEmptyObject(dias_semana)){
        habilitaDesabilitaCamposMTP('frequencia',true);
    //Se houver registro de dias_mes e dias semanas no sys_config...
    }else if(!jQuery.isEmptyObject(dias_mes) && !jQuery.isEmptyObject(dias_semana)){
        if(dias_mes.VALOR == null && dias_semana.VALOR == null){
            habilitaDesabilitaCamposMTP('frequencia',true);
        }else{
            habilitaDesabilitaCamposMTP('frequencia',false);
            najMPT.loadData("#form-processo-tribunal", {'frequencia_mes': dias_mes.VALOR});
        }
    //Se houver registro de dias_mes e não houver dias semanas no sys_config...
    }else if(!jQuery.isEmptyObject(dias_mes) && jQuery.isEmptyObject(dias_semana)){
        if(dias_mes.VALOR == null){
            habilitaDesabilitaCamposMTP('frequencia',true);
        }else{
            najMPT.loadData("#form-processo-tribunal", {'frequencia_mes': dias_mes.VALOR});
            habilitaDesabilitaCamposMTP('frequencia',false);
        }
    //Se houver registro de semana e não houver dias mes no sys_config...
    }else if(jQuery.isEmptyObject(dias_mes) && !jQuery.isEmptyObject(dias_semana)){
        if(dias_semana.VALOR == null){
            habilitaDesabilitaCamposMTP('frequencia',true);
        }else{
            habilitaDesabilitaCamposMTP('frequencia',false);
        }
    }
    if (sessionStorage.getItem('@NAJ_WEB/monitoramento_processo_tribunal') == 'create') {
        response = await najMPT.getData(`${baseURL}` + `${rotaBaseMPT}/proximo`);
        $('#form-processo-tribunal input[name=id]').val(response + 1);
        najMPT.loadData("#form-processo-tribunal", {'frequencia': dias_semana.VALOR});
    } else if (sessionStorage.getItem('@NAJ_WEB/monitoramento_processo_tribunal') == 'edit') {
        najMPT.loadData("#form-processo-tribunal", dados);
        //Seta o campo 'numero_cnj' como apenas leitura
        $('#form-processo-tribunal #numero_cnj').attr('readonly', true);
        //Esconde a linha do campo do código do processo
        //$('#form-processo-tribunal #linhaCodigoProcessoMTP').attr('hidden', true);
        $('#form-processo-tribunal #codigo_processo_mpt').attr('disabled', true);
    }
    //Desabilita os campos dos dia do mes
    habilitaDesabilitaCamposMTP('frequencia_mes',false);
    //Aplica máscará no campo 'numero_cnj'
    $('#form-processo-tribunal #numero_cnj').mask('0000000-00.0000.0.00.0000')
    //Exibe modal
    $('#modal-manutencao-monitoramento-processo-tribunal').modal('show'); 
}

/**
 * Grava dados da tela de manutenção
 */
async function gravarDadosMonitoramentoProcessoTribunal(){
    //Valida form
    result = validaForm('form-processo-tribunal');
    if(result){
        //Obtêm dados do form
        var dados = getDadosFormMonitoramentoProcessoTribunal();
        await createOrUpdateMonitoramentoProcessoTribunal(dados);
    }
}

/**
 * Obtêm dados do formulário
 * 
 * @returns {getDadosForm.dados}
 */
function getDadosFormMonitoramentoProcessoTribunal() {
    let dados = {
        'id'              : $('#form-processo-tribunal input[name=id]').val(),
        'numero_cnj'      : $('#form-processo-tribunal input[name=numero_cnj]').val(),
        'frequencia'      : getValuesChekeds('#form-processo-tribunal','frequencia', true),
        'status'          : $('#form-processo-tribunal select[name=status]').val(),
        'abrangencia'     : $('#form-processo-tribunal select[name=abrangencia]').val(),
        'codigo_processo' : $('#form-processo-tribunal input[name=codigo_processo]').val()
    };
    return dados;
}

/**
 * Insere ou atualiza registro conforme a ação selecionada
 * 
 * @param JSON dados
 */
async function createOrUpdateMonitoramentoProcessoTribunal(dados) {
    try{
        //Bloqueia o modal de manutenção
        loadingStart('bloqueio-modal-manutencao-monitoramento-processo-tribunal');
        if (sessionStorage.getItem('@NAJ_WEB/monitoramento_processo_tribunal') == 'create') {
            dados.id_tribunal = 0;
            //Insere registro do monitoramento no BD
            response = await najMPT.postData(`${baseURL}` + `${rotaBaseMPT}`, dados);
            if(response == true){   
                NajAlert.toastSuccess('Registro inserido com sucesso');
                //Verifica se o status do monitoramento é ativo
                if(dados.status = "A"){
                    //Requisição para verificar se o CNJ já tem alguma pesquisa realizada na Escavador
                    response = await najMPT.postData(`${baseURL}` + `monitoramento/tribunais/verificasecnjjatemmonitoramento`, dados);
                    if(response.message == "Ainda não existem monitoramentos registrados no banco de dados para este CNJ."){
                        //Requisição para cadastrar a pesquisa do CNJ na Escavador
                        response = await najMPT.postData(`${baseURL}` + `monitoramento/tribunais/pesquisaprocesso`, dados);
                        if(response.code == 200){
                            //Success Message
                            await Swal.fire("Sucesso!", "Monitoramento incluído com sucesso, estamos efetuando a primeira busca por novas Movimentações!", "success");
                        }else{
                            NajAlert.toastError('Erro ao cadastrar a pesquisa do CNJ na Escavador, contate o suporte!');
                            console.log(response);
                        }
                    }else{
                        msgHeader = (response.code == 200) ? "Sucesso!" : "Atenção";
                        msgStatus = (response.code == 200) ? "success"  : "error";
                        //Success Message
                        await Swal.fire(msgHeader, `${response.message}`, msgStatus)
                    }
                }
                //Atualiza o grid 
                if(typeof rotaBaseDiario != "undefined"){
                    await tableDiario.load();
                    await carregaModalConteudoPublicacao();
                }else if(typeof rotaBaseTribunal != "undefined"){
                    await tableTribunal.load();
                    await atualizaBadgesQtdsMT();
                    await recarregaOsTooltip();
                }
            }else if(typeof response == 'string'){
                NajAlert.toastWarning(response);
            }else if(response == false){
                NajAlert.toastError('Erro ao inserir registro, contate o suporte');
            }
        } else if (sessionStorage.getItem('@NAJ_WEB/monitoramento_processo_tribunal') == 'edit') {
            dados.codigo_processo = undefined;
            response = await najMPT.updateData(`${baseURL}` + `${rotaBaseMPT}`, dados);
            if(response.menssage == "Registro alterado com sucesso."){
                NajAlert.toastSuccess(response.menssage);
            }else if(response.menssage == "Não foi possível alterar o registro, contate o suporte."){
                NajAlert.toastError(response.menssage);
            }else if(response.menssage == "Nenhuma alteração encontrada."){
                NajAlert.toast(response.menssage);
            }
            if(typeof rotaBaseDiario != "undefined"){
                tableDiario.data.resultado[indexDiario].processo.monitoramento.frequencia = dados.frequencia;
                tableDiario.data.resultado[indexDiario].processo.monitoramento.status     = dados.status;
            }else if(typeof rotaBaseTribunal != "undefined"){
                //Se o modal de monitoramento processo tribunal estiver aberto através da validação de processos...
                if(sessionStorage.getItem('@NAJ_WEB/monitoramento_processo_tribunal_validacao_processos')){
                    await buscaPersonalizadaValidacaoProcessos();
                }else{
                    tableTribunal.data.resultado[indexTribunal].frequencia = dados.frequencia;
                    tableTribunal.data.resultado[indexTribunal].status_mpt = dados.status;
                }
            } 
        }
    }catch (e){
        NajAlert.toastError(e);
    }finally {
        //Desbloqueia o modal de manutenção
        loadingDestroy('bloqueio-modal-manutencao-monitoramento-processo-tribunal');
        //Esconde o modal
        $('#modal-manutencao-monitoramento-processo-tribunal').modal('hide');
    }
}

/**
 * Habilita ou desabilita os campos checkbox dos dias do form 'form-processo-tribunal'
 * 
 * @param {string} name nome do campo 
 * @param {bool}   value true = habilita, false = desabilita
 */
function habilitaDesabilitaCamposMTP(name, value){
    let checkboxs_dias_semanas = $(`#form-processo-tribunal input[name=${name}]`);
    for(let i = 0; i < checkboxs_dias_semanas.length; i++){
        if(value){
            $(`#form-processo-tribunal input[name=${name}]`)[i].removeAttribute('disabled');
        }else{
            $(`#form-processo-tribunal input[name=${name}]`)[i].setAttribute('disabled', true);
        }
    } 
}

