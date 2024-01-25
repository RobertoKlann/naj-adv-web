//---------------------- Parametrôs -----------------------//

const najBP                = new Naj('BuscasPadrao', null);
const rotaSysConfig        = 'sysconfig/';
let dias_semana_sys_config = null; //Armazena o registro corrente do BD
let dias_semana_form       = null; //Armazena o registro corrente do BD
let dias_mes_sys_config    = null; //Armazena o registro corrente do BD
let dias_mes_form          = null; //Armazena o registro corrente do BD

//---------------------- Eventos -----------------------//

$(document).ready(function() {
    
    //Ao clicar em gravar...
    $(document).on("click", '#gravarBuscasPadrao', function () {
        gravarDadosModalBP();
    });
    
});

//---------------------- Functions -----------------------//

/**
 * Carrega modal buscas padrão
 */
async function carregaModalBP(){
    try{
        loadingStart();
        if(getConsts().tipoUsuarioLogado != "0"){
            NajAlert.toastWarning('Área restrita somente ao usuário supervisor!');
            return
        }
        limpaFormulario('#form-buscas-padrao'); 
        sessionStorage.removeItem('@NAJ_WEB/buscas_mensal_padrao');
        sessionStorage.removeItem('@NAJ_WEB/buscas_semanal_padrao');
        //Obtêm os dias do mês do sys_config no BD
        dias_mes_sys_config = await najBP.getData(`${baseURL}` + `sysconfig/searchsysconfigall/TRIBUNAIS/DIAS_MES`); 
        //Obtêm os dias da semana do sys_config no BD
        dias_semana_sys_config = await najBP.getData(`${baseURL}` + `sysconfig/searchsysconfigall/TRIBUNAIS/DIAS_SEMANA`); 
        let state_mensal  = true;
        let state_semanal = true;
        //Verifica se existe o registro de dias mes e dias semana no sys_config
        if(!jQuery.isEmptyObject(dias_mes_sys_config) && !jQuery.isEmptyObject(dias_semana_sys_config)){
            //Verifica se o valor do registro de dias mes e dias semana no sys_config é diferente de nulo
            if(dias_mes_sys_config.VALOR && dias_semana_sys_config.VALOR){
                NajAlert.toastError("Atenção, detectado inconsistência de dados, dias do mês e dias da semana setados ao mesmo tempo no sys_config, contate o suporte!" );
                return;
            }
        }
        //Verifica se existe o registro de dias mes no sys_config
        if(jQuery.isEmptyObject(dias_mes_sys_config)){
            sessionStorage.setItem('@NAJ_WEB/buscas_mensal_padrao','create');
            dias_mes_form = null;
            state_mensal  = false;
        }else{
            sessionStorage.setItem('@NAJ_WEB/buscas_mensal_padrao','edit');
            let dados = {'frequencia_mes': dias_mes_sys_config.VALOR};
            //Verifica se o valor do registro de dias mes no sys_config é diferente de nulo
            if(!dias_mes_sys_config.VALOR){
                dias_mensal_form = null;
                state_mensal     = false;
            }else{
                dias_mes_form = dias_mes_sys_config.VALOR;
                najMPT.loadData("#form-buscas-padrao", dados);
            }
        }
        //Verifica se existe o registro de dias semana no sys_config
        if(jQuery.isEmptyObject(dias_semana_sys_config)){
            sessionStorage.setItem('@NAJ_WEB/buscas_semanal_padrao','create');
            dias_semana_form = null;
            state_semanal    = false;
        }else{
            sessionStorage.setItem('@NAJ_WEB/buscas_semanal_padrao','edit');
            let dados = {'frequencia': dias_semana_sys_config.VALOR};
            //Verifica se o valor do registro de dias semana no sys_config é diferente de nulo
            if(!dias_semana_sys_config.VALOR){
                dias_semana_form = null;
                state_semanal    = false;
            }else{
                dias_semana_form = dias_semana_sys_config.VALOR;
                najMPT.loadData("#form-buscas-padrao", dados);
            }
        }
        //Destroi bootstrapSwitch de "buscas_semanal_padrao" e "buscas_mensal_padrao" criado anteriormente 
        $(".bt-switch #buscas_mensal_padrao").bootstrapSwitch('destroy', true);
        $(".bt-switch #buscas_semanal_padrao").bootstrapSwitch('destroy', true);
        //Inicializa o botão switch mensal e cofigura o seu onchange
        $(".bt-switch #buscas_mensal_padrao").bootstrapSwitch({
            'state':state_mensal,
            'onSwitchChange': function(event, state_mensal){
                if(state_mensal){
                    habilitaDesabilitaCamposBMP(true);
                }else{
                    habilitaDesabilitaCamposBMP(false);
                }
                if($(".bt-switch #buscas_mensal_padrao")[0].checked == true && $(".bt-switch #buscas_semanal_padrao")[0].checked == true){
                        $(".bt-switch #buscas_semanal_padrao").click();
                }
            }
        });
        //Inicializa o botão switch semanal e cofigura o seu onchange
        $(".bt-switch #buscas_semanal_padrao").bootstrapSwitch({
            'state':state_semanal,
            'onSwitchChange': function(event, state_semanal){
                if(state_semanal){
                    habilitaDesabilitaCamposBSP(true);
                }else{
                    habilitaDesabilitaCamposBSP(false);
                }
                if($(".bt-switch #buscas_mensal_padrao")[0].checked == true && $(".bt-switch #buscas_semanal_padrao")[0].checked == true){
                        $(".bt-switch #buscas_mensal_padrao").click();
                }
            }
        });
        habilitaDesabilitaCamposBMP(state_mensal);
        habilitaDesabilitaCamposBSP(state_semanal);
        //Marca o botão switch "buscas_semanal_padrao" e "buscas_mensal_padrao" como checado
        $('#form-buscas-padrao input[name=buscas_mensal_padrao]').prop('checked',state_mensal);
        $('#form-buscas-padrao input[name=buscas_semanal_padrao]').prop('checked',state_semanal);
        $('#modal-manutencao-buscas-padrao').modal('show'); 
    }finally {
        loadingDestroy();
    }
    
}

/**
 * Habilita ou desabilita os campos checkbox dos dias da semana do form 'form-buscas-padrao'
 * 
 * @param {boll} value true = habilita, false = desabilita
 */
function habilitaDesabilitaCamposBSP(value){
    let checkboxs_dias_semana_forms = $('#form-buscas-padrao input[name=frequencia]');
    for(let i = 0; i < checkboxs_dias_semana_forms.length; i++){
        if(value){
            $('#form-buscas-padrao input[name=frequencia]')[i].removeAttribute('disabled');
        }else{
            $('#form-buscas-padrao input[name=frequencia]')[i].setAttribute('disabled', value);
            $('#form-buscas-padrao input[name=frequencia]')[i].checked = false;
        }
    } 
}

/**
 * Habilita ou desabilita os campos checkbox dos dias do mês do form 'form-buscas-padrao'
 * 
 * @param {boll} value true = habilita, false = desabilita
 */
function habilitaDesabilitaCamposBMP(value){
    let checkboxs_dias_mes_forms = $('#form-buscas-padrao input[name=frequencia_mes]');
    for(let i = 0; i < checkboxs_dias_mes_forms.length; i++){
        if(value){
            $('#form-buscas-padrao input[name=frequencia_mes]')[i].removeAttribute('disabled');
        }else{
            $('#form-buscas-padrao input[name=frequencia_mes]')[i].setAttribute('disabled', value);
            $('#form-buscas-padrao input[name=frequencia_mes]')[i].checked = false;
        }
    } 
}

/**
 * Grava dados da tela de manutenção
 */
async function gravarDadosModalBP(){
    try{
        //Bloqueia o modal de manutenção
        loadingStart('bloqueio-modal-manutencao-buscas-padrao');
        let podeGravarMensal; 
        let podeGravarSemanal;
        let gravouMensal;
        let gravouSemanal;
        podeGravarMensal  = await verificaSePodeGravarDadosModalBMP();
        podeGravarSemanal = await verificaSePodeGravarDadosModalBSP();
        if((podeGravarMensal === "NENHUMA_ALTERACAO") && (podeGravarSemanal === "NENHUMA_ALTERACAO")){
            NajAlert.toastWarning('Nenhuma alteração encontrada nas buscas padrão!');
        }else if((podeGravarMensal === "NENHUMA_ALTERACAO") && (podeGravarSemanal === true)){
            gravouSemanal = await createOrUpdateBSP();
            if(gravouSemanal){
                NajAlert.toastSuccess('Nova configuração salva com sucesso!');
            }
        }else if((podeGravarSemanal === "NENHUMA_ALTERACAO") && (podeGravarMensal === true)){
            gravouMensal = await createOrUpdateBMP();
            if(gravouMensal){
                NajAlert.toastSuccess('Nova configuração salva com sucesso!');
            }
        }else if((podeGravarSemanal === true) && (podeGravarMensal === true)){
            gravouMensal  = await createOrUpdateBMP();
            gravouSemanal = await createOrUpdateBSP();
            if(gravouMensal && gravouSemanal){
                NajAlert.toastSuccess('Nova configuração salva com sucesso!');
            }
        }
    }finally {
        //Desbloqueia o modal de manutenção
        loadingDestroy('bloqueio-modal-manutencao-buscas-padrao');
    }
}

/**
 * Grava dados do mês da tela de manutenção
 * @return {bool|string}
 */
async function verificaSePodeGravarDadosModalBMP(){
    //Obtêm estado do botão "buscas_mensal_padrao"
    buscaMensalAtivo = $('#form-buscas-padrao input[name=buscas_mensal_padrao]').prop('checked');
    //Obtêm dados do form
    let dados = await getDadosFormBMP();
    //Verifica se o form está ativo
    if(buscaMensalAtivo){
        //Verifica se contêm dados
        if(!dados){
            NajAlert.toastWarning('Você deve informar pelo menos um dia do mês, ou desativar as buscas mensais!');
            return "NENHUM_ITEM_INFORMADO";
        }
    }
    //Vamos verificar se houve alguma alteração do formulário em relação ao registro do BD
    if(dias_mes_form == dados){
        //Se a busca semanal estiver desativada
        if(!$('#form-buscas-padrao input[name=buscas_mensal_padrao]').prop('checked')){
            //NajAlert.toastWarning('Nenhuma alteração encontrada nas buscas mensais padrão!');
            return "NENHUMA_ALTERACAO";
        }
        return "NENHUMA_ALTERACAO";
    }
    return true;
}

/**
 * Grava dados da semana da tela de manutenção
 * @return {bool|string} 
 */
async function verificaSePodeGravarDadosModalBSP(){
    //Obtêm estado do botão "buscas_semanal_padrao"
    buscaSemanalAtivo = $('#form-buscas-padrao input[name=buscas_semanal_padrao]').prop('checked');
    //Obtêm dados do form
    let dados = await getDadosFormBSP();
    //Verifica se o form está ativo
    if(buscaSemanalAtivo){
        //Verifica se contêm dados
        if(!dados){
            NajAlert.toastWarning('Você deve informar pelo menos um dia da semana, ou desativar as buscas semanais!');
            return "NENHUM_ITEM_INFORMADO";;
        }
    }
    //Vamos verificar se houve alguma alteração do formulário em relação ao registro do BD
    if(dias_semana_form == dados){
        //Se a busca mensal estiver desativada
        if(!$('#form-buscas-padrao input[name=buscas_mensal_padrao]').prop('checked')){
            //NajAlert.toastWarning('Nenhuma alteração encontrada nas buscas semanais padrão!');
            return "NENHUMA_ALTERACAO";
        }
        return "NENHUMA_ALTERACAO";
    }
    return true;
}

/**
 * Obtêm dados do mês no formulário
 * 
 * @returns {getDadosFormBP.dados}
 */
 async function getDadosFormBMP() {
    return await getValuesChekeds('#form-buscas-padrao','frequencia_mes', true);
}

/**
 * Obtêm dados da semana no formulário
 * 
 * @returns {getDadosFormBP.dados}
 */
 async function getDadosFormBSP() {
    return await getValuesChekeds('#form-buscas-padrao','frequencia', true);
}

/**
 * Atualiza registro da semana
 * 
 * @return {bool} 
 */
async function createOrUpdateBMP() {
    try{
        let dados = await getDadosFormBMP();
        let retorno
        let url = `${baseURL}` + `${rotaSysConfig}TRIBUNAIS/DIAS_MES/${dados}?XDEBUG_SESSION_START=netbeans-xdebug`;
        if(sessionStorage.getItem('@NAJ_WEB/buscas_mensal_padrao') == 'create'){
            //Requisição para inserir o registro no sys_config
            result = await najBP.postData(url);
            sessionStorage.setItem('@NAJ_WEB/buscas_mensal_padrao','edit');
        }else if(sessionStorage.getItem('@NAJ_WEB/buscas_mensal_padrao') == 'edit'){
            //Requisição para atualizar o registro no sys_config
            result = await najBP.updateData(url);
        }
        if(result){
            let url = `${baseURL}` + `monitoraprocessotribunal?XDEBUG_SESSION_START=netbeans-xdebug`;
            //Requisição para atualizar o registro no monitora processo tribunal
            result = await najBP.updateData(`${baseURL}` + `monitoraprocessotribunalfrequenica?XDEBUG_SESSION_START=netbeans-xdebug`, {"frequencia":""});
            dias_mes_form = dados;
            await tableTribunal.load();
            await atualizaBadgesQtdsMT();
            await recarregaOsTooltip();
            retorno = true;
        }else{
            NajAlert.toastError('Não foi posssível salvar as novas configuração em busca Mensal padrão, contate o suporte!');
            retorno = false;
        }
        return retorno;
    }catch (e){
        NajAlert.toastError(e)
    }
}

/**
 * Atualiza registro da semana
 * 
 * @returns {bool} 
 */
async function createOrUpdateBSP() {
    try{
        let dados = await getDadosFormBSP();
        let retorno;
        let url = `${baseURL}` + `${rotaSysConfig}TRIBUNAIS/DIAS_SEMANA/${dados}?XDEBUG_SESSION_START=netbeans-xdebug`;
        if(sessionStorage.getItem('@NAJ_WEB/buscas_semanal_padrao') == 'create'){
            //Requisição para inserir o registro no sys_config
            result = await najBP.postData(url);
            sessionStorage.setItem('@NAJ_WEB/buscas_semanal_padrao','edit');
        }else if(sessionStorage.getItem('@NAJ_WEB/buscas_semanal_padrao') == 'edit'){
            //Requisição para atualizar o registro no sys_config
            result = await najBP.updateData(url);
        }
        if(result){
            let url = `${baseURL}` + `monitoraprocessotribunal?XDEBUG_SESSION_START=netbeans-xdebug`;
            //Requisição para atualizar o registro no monitora processo tribunal
            result = await najBP.updateData(`${baseURL}` + `monitoraprocessotribunalfrequenica?XDEBUG_SESSION_START=netbeans-xdebug`, {"frequencia":dados});
            if(result){
                dias_semana_form = dados;
                await tableTribunal.load();
                await atualizaBadgesQtdsMT();
                await recarregaOsTooltip();
                retorno = true;
            }else{
                retorno = false;
                NajAlert.toastError('A alteração em Buscas Padrão foi salva com sucesso no entanto não foi possível atualizar a "frequência" nos registros do Monitora Processo Tribunal, contate o suporte!');
            }
        }else{
            retorno = false;
            NajAlert.toastError('Não foi posssível salvar as novas configuração em Buscas Padrão, contate o suporte!');
        }        
        return retorno;
    }catch (e){
        NajAlert.toastError(e)
    }
}

