//---------------------- Parametrôs -----------------------//

const najComentarioMP = new Naj('ComentarioMovimentacaoProcesso', null);
const rotaBaseComentarioMP = 'processomovimento';

//---------------------- Eventos -----------------------//

$(document).ready(function() {
    
    //Ao clicar em gravar...
    $(document).on("click", '#gravarComentarioMovimentacaoProcesso', function () {
        gravarDadosModalComentarioAtividadeMP();
    });
    
    //Ao esconder o modal de '#modal-manutencao-comentario-movimentacao-processo' remove a classe 'z-index-100' do modal '#modal-conteudo-movimentacao-processo'
    $('#modal-manutencao-comentario-movimentacao-processo').on('hidden.bs.modal', function(){
        $('#modal-conteudo-movimentacao-processo').removeClass('z-index-100');
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
 */
async function carregaModalComentarioMP(){
    loadingStart('bloqueio-modal-conteudo-movimentacao-processo');
    //Remove as validações do formulário
    removeClassCss('was-validated', '#form-comentario-movimentacao-processo');
    removeClassCss('was-validated', '#form-atividade-movimentacao-processo');
    //Limpa os campos do formulário
    limpaFormulario('#form-comentario-movimentacao-processo');
    limpaFormulario('#form-atividade-movimentacao-processo');
    if(!tableTribunal.data.resultado[indexTribunal].movimentacoes[indexCMP].id_atividade){
        sessionStorage.setItem('@NAJ_WEB/atividade_action','create');
    }else{
        sessionStorage.setItem('@NAJ_WEB/atividade_action','edit');
    }
    //Dados do comentário
    let data_andamento      = tableCMP.data.resultado[indexCMP].data;
    let DESCRICAO_ANDAMENTO = tableCMP.data.resultado[indexCMP].conteudo;
    let TRADUCAO_ANDAMENTO  = tableCMP.data.resultado[indexCMP].TRADUCAO_ANDAMENTO ? tableCMP.data.resultado[indexCMP].TRADUCAO_ANDAMENTO : "Análise do Andamento Processual do dia " + formatDate(data_andamento);
    //Seta os dados do form do comentário em seus respectivos campos
    $('#form-comentario-movimentacao-processo #data_andamento').val(formatDate(data_andamento));
    $('#form-comentario-movimentacao-processo #DESCRICAO_ANDAMENTO').val(DESCRICAO_ANDAMENTO);
    $('#form-comentario-movimentacao-processo #TRADUCAO_ANDAMENTO').val(TRADUCAO_ANDAMENTO);
    //Dados da atividade
    let id_atividade        = tableCMP.data.resultado[indexCMP].id_atividade       ? tableCMP.data.resultado[indexCMP].id_atividade       : await najComentarioMP.getData(`${baseURL}processos/atividades/proximo`) + 1;
    let DATA                = tableCMP.data.resultado[indexCMP].DATA ;
    DATA                    = DATA                                                 ? DATA.substr(0,10) + "T" + DATA.substr(11,5)          : getDateProperties().getFullDateTimeT();
    let TEMPO               = tableCMP.data.resultado[indexCMP].TEMPO              ? tableCMP.data.resultado[indexCMP].TEMPO              : "00:00:00";
    //Seta os dados do form da atividade em seus respectivos campos
    tableCMP.data.resultado[indexCMP].ENVIAR == "S" ? $('#form-atividade-movimentacao-processo input[name=ENVIAR]').prop('checked',true) : $('#form-atividade-movimentacao-processo input[name=ENVIAR]').prop('checked',false);
    $('#form-atividade-movimentacao-processo  #CODIGO').val(id_atividade);
    $('#form-atividade-movimentacao-processo  #DATA').val(DATA);
    $('#form-atividade-movimentacao-processo  #TEMPO').val(TEMPO);
    response = await carregaOptionsSelect(`processos/atividades/tipos/getallatividadestipos`, 'ID_TIPO_ATIVIDADE', false, 'data', false, 0);
    //Verifica se tem o ID_TIPO_ATIVIDADE para selecionar a opção no campo select
    if(tableCMP.data.resultado[indexCMP].ID_TIPO_ATIVIDADE){
        let ID_TIPO_ATIVIDADE = parseInt(tableCMP.data.resultado[indexCMP].ID_TIPO_ATIVIDADE);
        $('#form-atividade-movimentacao-processo select[name=ID_TIPO_ATIVIDADE]').val(ID_TIPO_ATIVIDADE);
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
    $('#alerta_atividade').html(el_alerta_atividade);
    $('#quadro_atividades').show();
    //Destroi bootstrapSwitch de adicionar_atividade criado anteriormente 
    $(".bt-switch #adicionar_atividade").bootstrapSwitch('destroy', true);
    //Inicializa o botão switch e cofigura o seu onchange
    $(".bt-switch #adicionar_atividade").bootstrapSwitch({
        'state':true,
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
    $('#adicionar_atividade').prop('checked', true);
    //Aplica máscara em "TEMPO"
    $('#form-atividade-movimentacao-processo #TEMPO').mask('00:00:00');
    //Aplica o z-index
    $('#modal-conteudo-movimentacao-processo').addClass('z-index-100');
    //Exibe modal
    $('#modal-manutencao-comentario-movimentacao-processo').modal('show');
    loadingDestroy('bloqueio-modal-conteudo-movimentacao-processo');
}

async function gravarDadosModalComentarioAtividadeMP(){
    try{
        //Bloqueia o modal de manutenção
        loadingStart('bloqueio-modal-manutencao-comentario-movimentacao-processo');
        if($('#form-comentario-movimentacao-processo #TRADUCAO_ANDAMENTO').val() == ""){
            NajAlert.toastWarning('O campo Descrição Simplificada deve ser informado!')
        }
        //Valida form1
        form1validado    = validaForm('form-comentario-movimentacao-processo');
        let TRADUCAO_ANDAMENTO = tableTribunal.data.resultado[indexTribunal].movimentacoes[indexCMP].TRADUCAO_ANDAMENTO;
        let form2marcado = $('#adicionar_atividade').prop('checked');
        //Verifica se o formulário 2 foi marcado
        if(form2marcado){
            //Valida form2
            form2validado = validaForm('form-atividade-movimentacao-processo');;
            //Verifica se form1 e form2 estão válidados
            if(form1validado && form2validado){
                //Obtêm dados do form1
                var dadosForm1 = getDadosFormComentarioMP();
                //Verifica se houve alteração no comentário, precisamos fazer essa verificação prévia devido a 'DATA_ALTERACAO' que sempre se altera pois se não sempre irá atualizar a 'DATA_ALTERACAO' mesmo que não tenha sido feito alteração no comentário
                if(dadosForm1.TRADUCAO_ANDAMENTO != TRADUCAO_ANDAMENTO){
                    //Realiza requisição com dados do form1
                    await createOrUpdateComentarioMP(dadosForm1);
                }else{
                    NajAlert.toastSuccess('Nenhuma alteração encontrada no andamento!');
                }
                //Obtêm dados do form2
                var dadosForm2 = await getDadosFormAtividadeMP();
                if(tableCMP.data.resultado[indexCMP].id_atividade){
                    sessionStorage.setItem('@NAJ_WEB/atividade_action','edit');
                }else{
                    sessionStorage.setItem('@NAJ_WEB/atividade_action','create');
                }
                //Realiza requisição com dados do form2
                await createOrUpdateAtividadeMP(dadosForm2);
            }
        }else{
            //Verifica se form1 está válidado
            if(form1validado){
                //Obtêm dados do form1
                var dadosForm1 = getDadosFormComentarioMP();
                //Verifica se houve alteração no comentário, precisamos fazer essa verificação prévia devido a 'DATA_ALTERACAO' que sempre se altera pois se não sempre irá atualizar a 'DATA_ALTERACAO' mesmo que não tenha sido feito alteração no comentário
                if(dadosForm1.TRADUCAO_ANDAMENTO != TRADUCAO_ANDAMENTO){
                    //Realiza requisição com dados do form1
                    await createOrUpdateComentarioMP(dadosForm1);
                }else{
                    NajAlert.toastSuccess('Nenhuma alteração encontrada no andamento!');
                }
            }
            if(tableTribunal.data.resultado[indexTribunal].movimentacoes[indexCMP].id_atividade){
                await sweetAlertRemoverAtividade(tableTribunal.data.resultado[indexTribunal].movimentacoes[indexCMP].id_atividade);
            }
        }
        //Recarrega dados da tabela CMP
        await tableCMP.load();
    }catch (e){
        console.log(e);
    }finally{
        //Desbloqueia o modal de manutenção
        loadingDestroy('bloqueio-modal-manutencao-comentario-movimentacao-processo'); 
    }
}

/**
 * Obtêm dados do formulário comentário
 * 
 * @returns {getDadosForm.dados}
 */
function getDadosFormComentarioMP() {
    let dados = {
        'ID': tableCMP.data.resultado[indexCMP].id_prc_movimento,
        'TRADUCAO_ANDAMENTO': $('#form-comentario-movimentacao-processo textarea[name=TRADUCAO_ANDAMENTO]').val(),
        'DATA_ALTERACAO': getDataHoraAtual()
    };
    return dados;
}

/**
 * Obtêm dados do formulário atividade
 * 
 * @returns {getDadosForm.dados}
 */
async function getDadosFormAtividadeMP() {
    //Requesita o 'pessoa_codigo' do usuário logado
    let usuario = await najComentarioMP.getData(`${baseURL}pessoa/usuario/${getConsts().idUsuarioLogado}`);
    let CODIGO_USUARIO = usuario[0].pessoa_codigo;
    let dados = {
        'CODIGO'            : $('#form-atividade-movimentacao-processo input[name=CODIGO]').val(),
        'CODIGO_USUARIO'    : CODIGO_USUARIO,
        'CODIGO_DIVISAO'    : tableTribunal.data.resultado[indexTribunal].CODIGO_DIVISAO,
        'DATA'              : $('#form-atividade-movimentacao-processo input[name=DATA]').val(),
        'HORA_INICIO'       : $('#form-atividade-movimentacao-processo input[name=DATA]').val().split('T')[1],
        'TEMPO'             : $('#form-atividade-movimentacao-processo input[name=TEMPO]').val(),
        'ID_TIPO_ATIVIDADE' : $('#form-atividade-movimentacao-processo select[name=ID_TIPO_ATIVIDADE]').val(),
        'ENVIAR'            : ($('#form-atividade-movimentacao-processo input[name=ENVIAR]').prop('checked') == true) ? "S" : "N",
        'HISTORICO'         : $('#form-comentario-movimentacao-processo textarea[name=TRADUCAO_ANDAMENTO]').val(),
        'CODIGO_PROCESSO'   : tableTribunal.data.resultado[indexTribunal].codigo_processo,
        'CODIGO_CLIENTE'    : tableTribunal.data.resultado[indexTribunal].CODIGO_CLIENTE
    };
    //Verifica se tem processo
    if(tableTribunal.data.resultado[indexTribunal].codigo_processo){
        //Verifica se tem "ID_AREA_JURIDICA"
        //if(tableTribunal.data.resultado[indexTribunal].ID_AREA_JURIDICA){
            //Requisição para obter o "ID_AREA_JURIDICA" atualizado
            let ID_AREA_JURIDICA = await najComentarioMP.getData(`${baseURL}processos/idareajuridica/${tableTribunal.data.resultado[indexTribunal].codigo_processo}`);
            //Seta o "ID_AREA_JURIDICA
            dados.ID_AREA_JURIDICA = ID_AREA_JURIDICA;
        //}
    }
    //Requesita do sys_config a configuração do 'atividade|isolar_prc' 
    let isolar_prc = await najComentarioMP.getData(`${baseURL}sysconfig/searchsysconfig/ATIVIDADES/ISOLAR_PRC`);
    if(isolar_prc == "SIM"){
        dados.CODIGO_CLIENTE  = null;
    }
    return dados;
}

/**
 * Atualiza registro conforme a ação selecionada
 * 
 * @param JSON dados
 */
async function createOrUpdateComentarioMP(dados) {
    try{
        //O modo do modal de comentário sempre será do tipo "edit" pois já criamos o registro na leitura dos callbacks
        response = await najComentarioMP.updateData(`${baseURL}${rotaBaseComentarioMP}/${btoa(JSON.stringify({ID: dados.ID}))}`, dados);
        if(response.mensagem == "Registro alterado com sucesso."){
            NajAlert.toastSuccess('Andamento alterado com sucesso!');
        }else{
            NajAlert.toastSuccess(response.mensagem);
        }        
        //Atualiza os dados da tabela CMP
        tableCMP.data.resultado[indexCMP].TRADUCAO_ANDAMENTO = dados.TRADUCAO_ANDAMENTO;
    }catch (e){
        NajAlert.toastError(e);
    }
}

/**
 * Insere ou atualiza registro conforme a ação selecionada para a atividade
 * 
 * @param JSON dados
 */
async function createOrUpdateAtividadeMP(dados) {
    try{
        if (sessionStorage.getItem('@NAJ_WEB/atividade_action') == 'create') {
            //Requisição para inserir a atividade
            response = await najComentarioMP.postData(`${baseURL}processos/atividades?XDEBUG_SESSION_START=netbeans-xdebug`, dados);
            if(response.mensagem == "Registro inserido com sucesso."){
                //Requisição para vincular a atividade a movimentação
                response = await najComentarioMP.updateData(`${baseURL}monitoraprocessomovimentacao/${btoa(JSON.stringify({id: tableTribunal.data.resultado[indexTribunal].movimentacoes[indexCMP].id}))}?XDEBUG_SESSION_START=netbeans-xdebug`, {"id_atividade":dados.CODIGO});
                if(response.mensagem == "Registro alterado com sucesso."){
                    NajAlert.toastSuccess('Atividade cadastrada com sucesso!');
                }else{
                    console.log(response);
                    NajAlert.toastError('Não foi possível vincular a atividade a movimentação, contate o suporte!');
                    //Requisição para excluir a atividade
                    response = await najComentarioMP.destroy(`${baseURL}processos/atividades/many/${btoa(JSON.stringify({CODIGO: dados.CODIGO}))}?XDEBUG_SESSION_START=netbeans-xdebug`);
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
            response = await najComentarioMP.updateData(`${baseURL}processos/atividades/${btoa(JSON.stringify({CODIGO: dados.CODIGO}))}?XDEBUG_SESSION_START=netbeans-xdebug`, dados);
            if(response.mensagem == "Registro alterado com sucesso."){
                NajAlert.toastSuccess('Atividade alterada com sucesso!');
            }else{
                NajAlert.toastError('Não foi possível alterar a atividade, contate osuporte!');
                console.log(response);
            }
        }
        //Atualiza os dados da tabela CMP
        tableCMP.data.resultado[indexCMP].id_atividade      = dados.CODIGO;
        tableCMP.data.resultado[indexCMP].DATA              = dados.DATA;
        tableCMP.data.resultado[indexCMP].TEMPO             = dados.TEMPO;
        tableCMP.data.resultado[indexCMP].HORA_INICIO       = dados.HORA_INICIO;
        tableCMP.data.resultado[indexCMP].ID_TIPO_ATIVIDADE = dados.ID_TIPO_ATIVIDADE;
        tableCMP.data.resultado[indexCMP].ENVIAR            = dados.ENVIAR;
        //Atualiza os dados da tabela Monitoramento tribunal
        tableTribunal.data.resultado[indexTribunal].movimentacoes[indexCMP].id_atividade      = dados.CODIGO;;
        tableTribunal.data.resultado[indexTribunal].movimentacoes[indexCMP].DATA              = dados.DATA;
        tableTribunal.data.resultado[indexTribunal].movimentacoes[indexCMP].TEMPO             = dados.TEMPO;
        tableTribunal.data.resultado[indexTribunal].movimentacoes[indexCMP].HORA_INICIO       = dados.HORA_INICIO;
        tableTribunal.data.resultado[indexTribunal].movimentacoes[indexCMP].ID_TIPO_ATIVIDADE = dados.ID_TIPO_ATIVIDADE;
        tableTribunal.data.resultado[indexTribunal].movimentacoes[indexCMP].ENVIAR            = dados.ENVIAR;
    }catch (e){
        NajAlert.toastError(e);
    }
}

/**
 * Desvincula a Atividade Da Publicacao
 */
async function desvincularAtividadeDaPublicacao(){
    //Requisição para desvincular a atividade da movimentação
    response = await najComentarioMP.getData(`${baseURL}monitoraprocessomovimentacao/desvincularatividade/${tableTribunal.data.resultado[indexTribunal].movimentacoes[indexCMP].id}?XDEBUG_SESSION_START=netbeans-xdebug`);
    if(response){
        id_atividade = tableTribunal.data.resultado[indexTribunal].movimentacoes[indexCMP].id_atividade;
        //Requisição para excluir a atividade
        response = await najComentarioMP.destroy(`${baseURL}processos/atividades/many/${btoa(JSON.stringify({CODIGO: id_atividade}))}?XDEBUG_SESSION_START=netbeans-xdebug`);
        //Atualiza os dados da tabela CMP
        tableCMP.data.resultado[indexCMP].id_atividade      = null;
        tableCMP.data.resultado[indexCMP].DATA              = null;
        tableCMP.data.resultado[indexCMP].TEMPO             = null;
        tableCMP.data.resultado[indexCMP].HORA_INICIO       = null;
        tableCMP.data.resultado[indexCMP].ID_TIPO_ATIVIDADE = null;
        tableCMP.data.resultado[indexCMP].ENVIAR            = null;
        //Atualiza os dados da tabela Monitoramento tribunal
        tableTribunal.data.resultado[indexTribunal].movimentacoes[indexCMP].id_atividade      = null;
        tableTribunal.data.resultado[indexTribunal].movimentacoes[indexCMP].DATA              = null;
        tableTribunal.data.resultado[indexTribunal].movimentacoes[indexCMP].TEMPO             = null;
        tableTribunal.data.resultado[indexTribunal].movimentacoes[indexCMP].HORA_INICIO       = null;
        tableTribunal.data.resultado[indexTribunal].movimentacoes[indexCMP].ID_TIPO_ATIVIDADE = null;
        tableTribunal.data.resultado[indexTribunal].movimentacoes[indexCMP].ENVIAR            = null;
        await carregaModalComentarioMP();
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



