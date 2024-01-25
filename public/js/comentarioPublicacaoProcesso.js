//---------------------- Parametrôs -----------------------//

const najComentarioPP      = new Naj('ComentarioPublicacaoProcesso', null);
const rotaBaseComentarioPP = 'processomovimento';

//---------------------- Eventos -----------------------//

$(document).ready(function() {
    
    //Ao clicar no botão "Andamento Processual"
    $(document).on('click', '#btnAddAndamentoProcesssual', function() {
        $('#btnAddAndamentoProcesssual').tooltip('hide');
        carregaModalManutencaoComentarioPublicacaoProcesso();
    });
    
    //Ao clicar em gravar...
    $(document).on("click", '#gravarComentarioPublicacaoProcesso', function () {
        gravarDadosModalComentarioPublicacaoProcesso();
    });
    
    //Onblur do campo TEMPO...
    $(document).on("blur", '#form-atividade-movimentacao-processo #TEMPO', function () {
        autoCompleteTempo();
    });
    
});

//---------------------- Functions -----------------------//

function autoCompleteTempo(){
    tempo = $("#form-atividade-movimentacao-processo #TEMPO").val();
    if(tempo.length < 8){
        switch(tempo.length){
            case 0:
                tempo += "00:00:00";
                break;
            case 1:
                tempo += "0:00:00";
                break;
            case 2:
                tempo += ":00:00";
                break;
            case 3:
                tempo += "00:00";
                break;
            case 4:
                tempo += "0:00";
                break;
            case 5:
                tempo += ":00";
                break;
            case 6:
                tempo += "00";
                break;
            case 7:
                tempo += "0";
                break;
        }
    }
    $("#form-atividade-movimentacao-processo #TEMPO").val(tempo);
}

/**
 * Carrega Modal Comentario Andamento Processual
 * 
 * @param {string} modo create or edit
 */
async function carregaModalManutencaoComentarioPublicacaoProcesso(modo = "create"){
    loadingStart('bloqueio-modal-conteudo-publicacao');
    //Remove as validações do formulário
    removeClassCss('was-validated', '#form-comentario-publicacao-processo');
    removeClassCss('was-validated', '#form-atividade-publicacao-processo');
    //Limpa os campos do formulário
    limpaFormulario('#form-comentario-publicacao-processo');
    limpaFormulario('#form-atividade-publicacao-processo');
    //Remove os itens da sessão anterior caso existam
    sessionStorage.removeItem('@NAJ_WEB/atividade_action');
    sessionStorage.removeItem('@NAJ_WEB/comentario_publicacao_processo_action');
    //Seta o modo de manutenção do modal na sessão
    sessionStorage.setItem('@NAJ_WEB/comentario_publicacao_processo_action',modo);
    if(!tableDiario.data.resultado[indexDiario].atividade.CODIGO){
        sessionStorage.setItem('@NAJ_WEB/atividade_action','create');
    }else{
        sessionStorage.setItem('@NAJ_WEB/atividade_action','edit');
    }
    //desabilitaHabilitaCamposComentarioPublicacaoProcesso(false);
    //Seta os dados do form do comentário em seus respectivos campos
    let codigoComentario    = await najComentarioPP.getData(`${baseURL}processomovimento/proximo`) + 1;
    let data_publicacao     = formatDate(tableDiario.data.resultado[indexDiario].data_publicacao);
    let DESCRICAO_ANDAMENTO = '## INTIMAÇÃO ## \n' + jQuery(tableDiario.data.resultado[indexDiario].conteudo_publicacao).text();
    let TRADUCAO_ANDAMENTO  = "Análise do Andamento Processual do dia " + data_publicacao;
    //Seta os dados do form do comentário em seus respectivos campos
    $('#form-comentario-publicacao-processo input[name=ID]').val(codigoComentario); 
    $('#form-comentario-publicacao-processo input[name=DATA]').val(data_publicacao); 
    $('#form-comentario-publicacao-processo textarea[name=DESCRICAO_ANDAMENTO]').val(DESCRICAO_ANDAMENTO);
    $('#form-comentario-publicacao-processo textarea[name=TRADUCAO_ANDAMENTO]').val(TRADUCAO_ANDAMENTO);
    $('#form-comentario-publicacao-processo input[name=DATA]').attr('readonly',true);
    //Dados da atividade
    let id_atividade        = tableDiario.data.resultado[indexDiario].atividade.CODIGO ? tableDiario.data.resultado[indexDiario].atividade.CODIGO : await najComentarioPP.getData(`${baseURL}processos/atividades/proximo`) + 1;
    let DATA                = tableDiario.data.resultado[indexDiario].atividade.DATA   ? tableDiario.data.resultado[indexDiario].atividade.DATA   : dateTime.getDataHoraAtual();
    DATA                    = DATA                                                     ? DATA.substr(0,10) + "T" + DATA.substr(11,5)              : getDateProperties().getFullDateTimeT();
    let TEMPO               = tableDiario.data.resultado[indexDiario].atividade.TEMPO  ? tableDiario.data.resultado[indexDiario].atividade.TEMPO  : "00:00:00";
    //Seta os dados do form da atividade em seus respectivos campos
    tableDiario.data.resultado[indexDiario].ENVIAR == "S" ? $('#form-atividade-publicacao-processo input[name=ENVIAR]').prop('checked',true) : $('#form-atividade-publicacao-processo input[name=ENVIAR]').prop('checked',false);
    $('#form-atividade-publicacao-processo  #CODIGO').val(id_atividade);
    $('#form-atividade-publicacao-processo  #DATA').val(DATA);
    $('#form-atividade-publicacao-processo  #TEMPO').val(TEMPO);
    response = await carregaOptionsSelect(`processos/atividades/tipos/getallatividadestipos`, 'ID_TIPO_ATIVIDADE', false, 'data', false, 0);
    //Verifica se tem o ID_TIPO_ATIVIDADE para selecionar a opção no campo select
    if(tableDiario.data.resultado[indexDiario].ID_TIPO_ATIVIDADE){
        let ID_TIPO_ATIVIDADE = parseInt(tableDiario.data.resultado[indexDiario].ID_TIPO_ATIVIDADE);
        $('#form-atividade-publicacao-processo select[name=ID_TIPO_ATIVIDADE]').val(ID_TIPO_ATIVIDADE);
    } 
    //Seta o título do modal
    if(TRADUCAO_ANDAMENTO){
        $('#tituloAndamentoProcessual').html('Alteração Andamento Processual');
    }else{
        $('#tituloAndamentoProcessual').html('Novo Andamento Processual');
    }
    let el_alerta_atividade = `
                    <div class="alert alert-danger alert-rounded" > 
                        <i class="fa fa-exclamation-triangle"></i>
                        A Descrição Simplificada será incluída como uma ATIVIDADE do processo.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
                    </div>
                `;
    $('#alerta_atividade').html('');
    $('#quadro_atividades').hide();
    //Destroi bootstrapSwitch de adicionar_atividade criado anteriormente 
    $(".bt-switch #adicionar_atividade").bootstrapSwitch('destroy', true);
    //Inicializa o botão switch e cofigura o seu onchange
    $(".bt-switch #adicionar_atividade").bootstrapSwitch({
        'state':false,
        'onSwitchChange': function(event, state){
            if(state){
                $('#quadro_atividades').show(350);
                $('#alerta_atividade').html(el_alerta_atividade);
            }else{
                $('#quadro_atividades').hide(350);
                $('#alerta_atividade').html('');
            }
        }
    });
    //Marca o botão switch "adicionar_atividade" como checado
    $('#adicionar_atividade').prop('checked', false);
    //Aplica máscara em "TEMPO"
    $('#form-atividade-publicacao-processo #TEMPO').mask('00:00:00');
    //Esconde a linha do id
    $('#form-comentario-publicacao-processo #row_id_andamento').hide();
    //Aplica o z-index
    $('#modal-conteudo-publicacao').addClass('z-index-100'); 
    //Exibe modal
    $('#modal-manutencao-comentario-publicacao-processo').modal('show');
    loadingDestroy('bloqueio-modal-conteudo-publicacao');
}

async function gravarDadosModalComentarioPublicacaoProcesso(){
    try{
        //Bloqueia o modal de manutenção
        loadingStart('bloqueio-modal-manutencao-comentario-publicacao-processo');
        //Verifica se o campo "TRADUCAO_ANDAMENTO" foi preenchido
        if($('#form-comentario-publicacao-processo #TRADUCAO_ANDAMENTO').val() == ""){
            NajAlert.toastWarning('O campo Descrição Simplificada deve ser informado!')
            return;
        }
        //Valida form1
        form1validado          = validaForm('form-comentario-publicacao-processo');
        //Obtêm a informação do campo "TRADUCAO_ANDAMENTO"
        let TRADUCAO_ANDAMENTO = tableDiario.data.resultado[indexDiario].prc_movimento.TRADUCAO_ANDAMENTO;
        //Obtêm o valor do botão "adicionar_atividade"
        let form2marcado = $('#adicionar_atividade').prop('checked');
        //Verifica se o formulário 2 foi marcado
        if(form2marcado){
            //Valida form2
            form2validado = validaForm('form-atividade-publicacao-processo');
            //Verifica se form1 e form2 estão válidados
            if(form1validado && form2validado){
                //Obtêm dados do form1
                var dadosForm1 = getDadosFormComentarioPublicacaoProcesso();
                //Verifica se houve alteração no comentário, precisamos fazer essa verificação préviamente devido a 'DATA_ALTERACAO' que sempre se altera pois se não sempre irá atualizar a 'DATA_ALTERACAO' mesmo que não tenha sido feito alteração no comentário
                if(dadosForm1.TRADUCAO_ANDAMENTO != TRADUCAO_ANDAMENTO){
                    //Realiza requisição com dados do form1
                    await createOrUpdateComentarioPublicacaoProcesso(dadosForm1);
                }else{
                    NajAlert.toastSuccess('Nenhuma alteração encontrada no andamento!');
                }
                //Obtêm dados do form2
                var dadosForm2 = await getDadosFormAtividadePublicacaoProcesso();
                //Verifica se o modo da requisição da atividade será de criação ou edição
                if(tableDiario.data.resultado[indexDiario].atividade.CODIGO){
                    sessionStorage.setItem('@NAJ_WEB/atividade_action','edit');
                }else{
                    sessionStorage.setItem('@NAJ_WEB/atividade_action','create');
                }
                //Realiza requisição com dados do form2
                await createOrUpdateAtividadePublicacaoProcesso(dadosForm2);
                //Recarrega a tabela do Monitoramento Diário
                await tableDiario.load();
                //Recarrega o modal da publicação
                await carregaModalConteudoPublicacao();
                $('#modal-manutencao-comentario-publicacao-processo').modal('hide');
            }
        }else{
            //Verifica se a atividade foi removida
            if(tableDiario.data.resultado[indexDiario].atividade.CODIGO){
                await sweetAlertRemoverAtividade(tableTribunal.data.resultado[indexTribunal].movimentacoes[indexCMP].id_atividade);
            }
            //Verifica se form1 está válidado
            if(form1validado){
                //Obtêm dados do form1
                var dadosForm1 = getDadosFormComentarioPublicacaoProcesso();
                //Verifica se houve alteração no comentário, precisamos fazer essa verificação prévia devido a 'DATA_ALTERACAO' que sempre se altera pois se não sempre irá atualizar a 'DATA_ALTERACAO' mesmo que não tenha sido feito alteração no comentário
                if(dadosForm1.TRADUCAO_ANDAMENTO != TRADUCAO_ANDAMENTO){
                    //Realiza requisição com dados do form1
                    await createOrUpdateComentarioPublicacaoProcesso(dadosForm1);
                    //Recarrega a tabela do Monitoramento Diário
                    await tableDiario.load();
                    //Recarrega o modal da publicação
                    await carregaModalConteudoPublicacao();
                    $('#modal-manutencao-comentario-publicacao-processo').modal('hide');
                }else{
                    NajAlert.toastSuccess('Nenhuma alteração encontrada no andamento!');
                }
            }
        }
    }catch (e){
        console.log(e);
    }finally{
        //Desbloqueia o modal de manutenção
        loadingDestroy('bloqueio-modal-manutencao-comentario-publicacao-processo'); 
    }
}

/**
 * Obtêm dados do formulário comentário
 * 
 * @returns {getDadosForm.dados}
 */
function getDadosFormComentarioPublicacaoProcesso() {
    let dados = {
        'ID': $('#form-comentario-publicacao-processo input[name=ID]').val(),
        'CODIGO_PROCESSO': tableDiario.data.resultado[indexDiario].processo.codigo_processo,
        'DATA': formatDate($('#form-comentario-publicacao-processo input[name=DATA]').val(),false) + " 00:00:00",
        'DESCRICAO_ANDAMENTO': $('#form-comentario-publicacao-processo textarea[name=DESCRICAO_ANDAMENTO]').val(),
        'TRADUCAO_ANDAMENTO': $('#form-comentario-publicacao-processo textarea[name=TRADUCAO_ANDAMENTO]').val(),
        'NOTIFICADO': 'N',
        'NOTIFICAR': 'N',
    };
    return dados;
}

/**
 * Obtêm dados do formulário atividade
 * 
 * @returns {getDadosForm.dados}
 */
async function getDadosFormAtividadePublicacaoProcesso() {
    //Requesita o 'pessoa_codigo' do usuário logado
    let usuario = await najComentarioPP.getData(`${baseURL}pessoa/usuario/${getConsts().idUsuarioLogado}`);
    let CODIGO_USUARIO = usuario[0].pessoa_codigo;
    let dados = {
        'CODIGO'            : $('#form-atividade-publicacao-processo input[name=CODIGO]').val(),
        'CODIGO_USUARIO'    : CODIGO_USUARIO,
        'CODIGO_DIVISAO'    : tableDiario.data.resultado[indexDiario].processo.CODIGO_DIVISAO,
        'DATA'              : $('#form-atividade-publicacao-processo input[name=DATA]').val(),
        'HORA_INICIO'       : $('#form-atividade-publicacao-processo input[name=DATA]').val().split('T')[1],
        'TEMPO'             : $('#form-atividade-publicacao-processo input[name=TEMPO]').val(),
        'ID_TIPO_ATIVIDADE' : $('#form-atividade-publicacao-processo select[name=ID_TIPO_ATIVIDADE]').val(),
        'ENVIAR'            : ($('#form-atividade-publicacao-processo input[name=ENVIAR]').prop('checked') == true) ? "S" : "N",
        'HISTORICO'         : $('#form-comentario-publicacao-processo textarea[name=TRADUCAO_ANDAMENTO]').val(),
        'CODIGO_PROCESSO'   : tableDiario.data.resultado[indexDiario].processo.codigo_processo,
        'CODIGO_CLIENTE'    : tableDiario.data.resultado[indexDiario].processo.CODIGO_CLIENTE
    };
    //Verifica se tem processo
    if(tableDiario.data.resultado[indexDiario].processo.codigo_processo){
        //Verifica se tem "ID_AREA_JURIDICA"
        //if(tableTribunal.data.resultado[indexTribunal].ID_AREA_JURIDICA){
            //Requisição para obter o "ID_AREA_JURIDICA" atualizado
            let ID_AREA_JURIDICA = await najComentarioPP.getData(`${baseURL}processos/idareajuridica/${tableDiario.data.resultado[indexDiario].processo.codigo_processo}`);
            //Seta o "ID_AREA_JURIDICA
            dados.ID_AREA_JURIDICA = ID_AREA_JURIDICA;
        //}
    }
    //Requesita do sys_config a configuração do 'atividade|isolar_prc' 
    let isolar_prc = await najComentarioPP.getData(`${baseURL}sysconfig/searchsysconfig/ATIVIDADES/ISOLAR_PRC`);
    if(isolar_prc == "SIM"){
        dados.CODIGO_CLIENTE  = null;
    }
    return dados;
}

/**
 * Insere ou atualiza registro conforme a ação selecionada
 * 
 * @param JSON dados
 */
async function createOrUpdateComentarioPublicacaoProcesso(dados) {
    try{
        if (sessionStorage.getItem('@NAJ_WEB/comentario_publicacao_processo_action') == 'create') {
            //Requisição para inserir o registro em prc_movimento
            response = await najComentarioPP.store(`${baseURL}${rotaBaseComentarioPP}`, dados);
            //Vamos vincular o registro do andamento em "prc_movimento" com o registro da publicação em "monitora_termo_movimentacao"
            await najComentarioPP.updateData(`${baseURL}monitoramento/diarios/update/${btoa(JSON.stringify({id:tableDiario.data.resultado[indexDiario].id}))}?XDEBUG_SESSION_START=netbeans-xdebug`, {"id_prc_movimento":response.ID});
        } else if (sessionStorage.getItem('@NAJ_WEB/comentario_publicacao_processo_action') == 'edit') {
            response = await najComentarioPP.update(`${baseURL}${rotaBaseComentarioPP}/${btoa(JSON.stringify({ID: dados.ID}))}`, dados);
        }
    }catch (e){
        NajAlert.toastError(e);
    }
}

/**
 * Insere ou atualiza registro conforme a ação selecionada para a atividade
 * 
 * @param JSON dados
 */
async function createOrUpdateAtividadePublicacaoProcesso(dados) {
    try{
        if (sessionStorage.getItem('@NAJ_WEB/atividade_action') == 'create') {
            //Requisição para inserir a atividade
            response = await najComentarioPP.postData(`${baseURL}processos/atividades?XDEBUG_SESSION_START=netbeans-xdebug`, dados);
            if(response.mensagem == "Registro inserido com sucesso."){
                //Requisição para vincular a atividade a movimentação
                response = await najComentarioPP.updateData(`${baseURL}monitoramento/diarios/update/${btoa(JSON.stringify({id: tableDiario.data.resultado[indexDiario].id}))}?XDEBUG_SESSION_START=netbeans-xdebug`, {"id_atividade":dados.CODIGO});
                if(response.mensagem == "Registro alterado com sucesso."){
                    NajAlert.toastSuccess('Atividade cadastrada com sucesso!');
                }else{
                    console.log(response);
                    NajAlert.toastError('Não foi possível vincular a atividade a movimentação, contate o suporte!');
                    //Requisição para excluir a atividade
                    response = await najComentarioPP.destroy(`${baseURL}processos/atividades/many/${btoa(JSON.stringify({CODIGO: dados.CODIGO}))}?XDEBUG_SESSION_START=netbeans-xdebug`);
                    if(response.mensagem == "Erro ao excluir os registros."){
                        NajAlert.toastError('Não foi possível excluir o registro da atividade, contate o suporte!');
                    }
                }
            }else{
                NajAlert.toastError('Não foi possível cadastrar a atividade, contate o suporte!');
                console.log(response);
            }
        } else if (sessionStorage.getItem('@NAJ_WEB/atividade_action') == 'edit') {
            //Requisição para editar a atividade
            response = await najComentarioPP.updateData(`${baseURL}processos/atividades/${btoa(JSON.stringify({CODIGO: dados.CODIGO}))}?XDEBUG_SESSION_START=netbeans-xdebug`, dados);
            if(response.mensagem == "Registro alterado com sucesso."){
                NajAlert.toastSuccess('Atividade alterada com sucesso!');
            }else{
                NajAlert.toastError('Não foi possível alterar a atividade, contate osuporte!');
                console.log(response);
            }
        }
    }catch (e){
        NajAlert.toastError(e);
    }
}

/**
 * Desvincula a Atividade Da Publicacao
 */
async function desvincularAtividadeDaPublicacao(){
    //Requisição para desvincular a atividade da movimentação
    response = await najComentarioPP.getData(`${baseURL}monitoraprocessomovimentacao/desvincularatividade/${tableTribunal.data.resultado[indexTribunal].movimentacoes[indexCMP].id}?XDEBUG_SESSION_START=netbeans-xdebug`);
    if(response){
        id_atividade = tableTribunal.data.resultado[indexTribunal].movimentacoes[indexCMP].id_atividade;
        //Requisição para excluir a atividade
        response = await najComentarioPP.destroy(`${baseURL}processos/atividades/many/${btoa(JSON.stringify({CODIGO: id_atividade}))}?XDEBUG_SESSION_START=netbeans-xdebug`);
        //Recarrega a tabela do Monitoramento Diário
        await tableDiario.load();
        //Recarrega o modal da publicação
        await carregaModalConteudoPublicacao();
    }else{
        NajAlert.toastError('Não foi possível desvincular a atividade da movimentação, contate o suporte!');
        console.log(response);
    }
}

/**
 * Sweet Alert para solicitar ao usuário a confirmação da remoção da atividade
 * 
 * @param {int} id_atividade
 */
async function sweetAlertRemoverAtividade(id_atividade) {
    await Swal.fire({
        title: "Atenção!",
        text: "O andamento tem uma atividade vinculada, você tem certeza que deseja remover essa atividade?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#DD6B55',
        confirmButtonText: "Sim, eu tenho!",
        cancelButtonText: "Cancelar",
        onClose: () => {
        }
    }).then(async (result) => {
        if (result.value) {
            await desvincularAtividadeDaPublicacao();
        }
    });
}


/**
 * Desabilita ou Habilita Campos Comentario Publicacao Processo
 * 
 * @param {bool} onOff true desabilita os campos, false habilita os campos, true por default
 */
function desabilitaHabilitaCamposComentarioPublicacaoProcesso(onOff = true) {
    $('#form-comentario-publicacao-processo input[name=ID]')[0].disabled = onOff;
    $('#form-comentario-publicacao-processo input[name=DATA]')[0].disabled = onOff;
    $('#form-comentario-publicacao-processo textarea[name=DESCRICAO_ANDAMENTO]')[0].disabled = onOff;
    $('#form-comentario-publicacao-processo textarea[name=TRADUCAO_ANDAMENTO]')[0].disabled = onOff;
    $('#gravarComentarioPublicacaoProcesso')[0].disabled = onOff;
}