//---------------------- Parametrôs -----------------------//

const tableCMP = new ConteudoMovimentacoesProcessoTable;
let   indexCMP = null; //Indíce do registro na tabela de consulta de Conteúdo Movimentações Processo

//---------------------- Eventos -----------------------//

$(document).ready(async function () {
    
    //Ao clicar em "Comentário"...
    $(document).on('click', '.btnComentarAndamento', function(e) {
        $(this).blur();
        //Verifica se seleciona linha do Monitoramento Tribunal
        //let id = e.target.attributes['id'].value
        getIndexCMP();
        verificaSeSelecionaLinhaCMP();
        carregaModalComentarioMP();
    });
    
    //Quando clica no botão de "ações" abre o drop das ações 
    $(document).on('click', '#' + tableCMP.ids.actionsInButton, function() {
        if($('#datatable-conteudo-movimentacao-processo #list-actions-default')[0].attributes.class.value.search("action-in-open") > 0){
            $('#datatable-conteudo-movimentacao-processo #list-actions-default').removeClass('action-in-open');
        } else {
            $('#datatable-conteudo-movimentacao-processo #list-actions-default').addClass('action-in-open');
        }
    });

    //Fecha o drop down das ações ao clicar fora do drop down das ações
    $(document).on('click', function (e) {
        if(e.target.attributes['class'] != undefined){
            if(e.target.attributes.class.value.search('btn btnCustom action-in-button btn-action-default') == -1 && e.target.attributes.class.value.search('fas fa-ellipsis-v btn-icon') == -1 ){
                $('#datatable-conteudo-movimentacao-processo #list-actions-default').removeClass('action-in-open');
            }
        }
    });
    
    //Ao esconder o modal de '#modal-confirmacao-exclusao-andamentos' remove a classe 'z-index-100' do modal '#modal-conteudo-movimentacao-processo'
    $('#modal-confirmacao-exclusao-andamentos').on('hidden.bs.modal', function(){
        $('#modal-conteudo-movimentacao-processo').removeClass('z-index-100');
    });
    
    //Ao clicar em "onClickVerProcessoSiteTribunal"...
    $(document).on('click', '.onClickVerProcessoSiteTribunal', function() {
        //Verifica se a rotina corrente é a de monitoramento tribunal, poi se for precisa fazer o controle de registro selecionado no grid
        if(typeof rotaBaseTribunal != "undefined"){
            getIndexCMP();
            verificaSeSelecionaLinhaCMP();
        }
        window.open(tableCMP.data.resultado[indexCMP].url_tj);
    });
        
});

//---------------------- Functions -----------------------//

/**
 * Verifica se seleciona linha no Comentário Monitoramento Processo 
 * @param {elemento} e
 */
function verificaSeSelecionaLinhaCMP(){
    //Verifica se exite linhas selecionadas no datatable
    if(tableCMP.selectedRows.length == 0){
        if(indexCMP){
            //Seta o checkbox da linha como 'checked'
            $('#datatable-conteudo-movimentacao-processo .data-table-row')[indexCMP].classList.add("row-selected");
            //Desmarca o 'checked' do checkbox da linha
            $('#datatable-conteudo-movimentacao-processo .data-table-row')[indexCMP].querySelector('input[type=checkbox]').checked = true;
        }
    }
};

/**
 * Obtêm o index do registro selecionado no datatable do Conteudo Movimentações Processo pelo id
 * 
 * @returns {Number|indexCMP}
 */
function getIndexCMPpeloId(id = null){
    if(!id) return null;
    for(let i = 0 ; i < tableCMP.data.resultado.length; i++){
        if(tableCMP.data.resultado[i].id == id){
            indexCMP = i;
            return indexCMP;
        }
    }
    return null;
}

/**
 * Obtêm o index do registro selecionado no datatable do Monitoramento Tribunal
 * 
 * @returns {Number|indexTribunal}
 */
function getIndexCMP(){
    for(let i = 0 ; i < $('#datatable-conteudo-movimentacao-processo .data-table-row').length; i++){
        let selecionado = $('#datatable-conteudo-movimentacao-processo .data-table-row')[i].className.includes('row-selected');
        if(selecionado){
            indexCMP = i;
            return indexCMP;
        }
    }
    return null;
}

/**
 * Obtêm o registro selecionado no datatable do Conteudo Movimentações Processo
 * 
 * @returns {object}
 */
function getRegistroSelecionadoCMP(){
    //Obtêm o index da linha selecionada
    getIndexCMP();
    if(indexCMP != null){
        let registro = tableCMP.data.resultado[indexCMP];
        //Substitui os valores nulos do objeto por string vazias
        registro     = replaceNullByEmptyInObject(registro);
        //Retorna o registro selecionado
        return registro;
    }
    return null;
}

/**
 * Carrega o modal do modal do Conteudo Movimentações Processo
 * 
 * @returns {undefined}
 */
async function carregaModalCMP(){
    try{
        loadingStart();
        //Obtêm o registro selecionado
        let registro = getRegistroSelecionadoMT();
        //Monda conteúdo do header do modal
        carregaHeaderModalCMP(registro);
        //Realiza a busca personalizada do Conteudo Movimentações Processo
        await  carregamentoInicialTableCMP(registro.movimentacoes);    
        //Seta o valor 10 no "exibir" e o desabilita
        $(`#${tableCMP.ids.perPage}`)[0].innerHTML = '<option>10</option>';
        $(`#${tableCMP.ids.perPage}`)[0].setAttribute('disabled','');
        //Exibe modal
        $('#modal-conteudo-movimentacao-processo').modal('show');
        //Recarrega os tooltips de status
        await recarregaOsTooltip();
    }finally {
        loadingDestroy();
    }
}

/**
 * Monda conteúdo do header do modal do Conteudo Movimentações Processo
 * 
 * @param {object} registro da tabela Monitorameto tribunal
 */
function carregaHeaderModalCMP(registro){
    let linha1 = `
        ${registro.CARTORIO} 
        ${registro.CARTORIO && registro.COMARCA ? ` - ` : ``} 
        ${registro.COMARCA } 
        ${registro.COMARCA && registro.COMARCA_UF ? ` - ` : ``}
        ${registro.COMARCA_UF }
    `; 
    let linha3 = `
        <span class="tooltip-naj badge text-white blue-grey-text text-darken-4 font-normal badge-pill badge-secondary" data-toggle="tooltip" data-placement="top">${registro.sigla_tribunal}</span>
        ${registro.numero_cnj}
        <i class="tooltip-naj far fa-copy cursor-pointer text-dark" data-toggle="tooltip" data-placement="top" title="Copiar número do CNJ" onclick="copiarTextoParaAreaDeTranferencia('${registro.numero_cnj}','Número CNJ copiado para a área de transferência!')"></i>&nbsp;
        ${registro.codigo_processo ? `<span class="text-muted">Código: ${registro.codigo_processo} </span><i class="onClickFichaProcessoMT tooltip-naj font-18 mdi mdi-open-in-new cursor-pointer text-dark" data-toggle="tooltip" data-placement="top" title="Ver ficha do processo"></i>` : ``} 
        ${registro.instancia}
    `; 
    let linha4 = `
        ${registro.NOME_CLIENTE}
        <span class="text-muted">${registro.QUALIFICA_CLIENTE ? `(${registro.QUALIFICA_CLIENTE})` : ``}</span>
        ${registro.NOME_ADVERSARIO}
        <span class="text-muted">${registro.QUALIFICA_ADVERSARIO ? `(${registro.QUALIFICA_ADVERSARIO})` : ``}</span>
    `;
    let contentHeader = ` 
    <div class="row font-12">
        <div class="col-12">${linha1}</div  >
        <div class="col-12">${registro.CLASSE}</div>
        <div class="col-12">${linha3}</div>
        <div class="col-12">${linha4}</div>
    </div>
    `;
    //Insere conteúdo no header do modal
    $('#header-modal-conteudo-movimentacao-processo').html(contentHeader);
}

/**
 * Realiza o carregamento inicial do datatable do modal do Conteudo Movimentações Processo com o filtro de "novos"
 * 
 * @param {array} movimentacoes movimentacoes do processo
 * @param {int}   pagina        página da tabela
 */
async function carregamentoInicialTableCMP(movimentacoes, pagina = 1){
    //Carrega dados da tabela
    let data = {
        "limite": 10,
        "pagina": pagina,
        "resultado": movimentacoes.slice(0,10), //Extrai os primeiros 10 registros do array
        "total": movimentacoes.length,
    }
    //Seta a página 1 como inicial 
    tableCMP.page = pagina;
    //Seta o data no datatable
    tableCMP.data = data;
    //Carrega tabela das movimentações
    await tableCMP.load();
    //Se tiver movimentacoes novas dispara requisição para seta-los como lidos
    await verificaRegistrosLidosCMP();
}

/**
 * Filtra pelas movimentações não lidas do datatable do modal do Conteudo Movimentações Processo
 * 
 * @param {array} registro
 * @returns {array}
 */
function getMovimentacoesProcessoNaoLidosCMP(movimentacoes){
    let movimentacoesNovas = [];
    //Primeiramente iremos selecionar as movimentações não lidas
    for(let i = 0; i < movimentacoes.length; i++){
        if(movimentacoes[i].lido == "N"){
            movimentacoesNovas.push(movimentacoes[i]);
        }
    }
    return movimentacoesNovas;
}

/**
 * Verifica os Registros que forma lidos do datatable do modal do Conteudo Movimentações Processo
 */
async function verificaRegistrosLidosCMP(){
    let ids  = []; 
    let lido = []; 
    //Obtêm os ids dos registros
    for(let i = 0; i < tableCMP.data.resultado.length; i++){
        lido.push(tableCMP.data.resultado[i].lido);
        ids.push(tableCMP.data.resultado[i].id);
    }
    //Verifica se contêm pelo menos um registro não lido
    if(lido.indexOf('N') >= 0){
        //Dispara requisição para setar registros como lidos
        await setaRegistroscomoLidosCMP(ids);
    }
} 

/**
 * Seta Registros do modal do Conteudo Movimentações Processo como lidos
 * 
 * @param {array} ids
 * @param {bool} todos define se todos os registros da tabela CMP serão setados como lidos
 */
async function setaRegistroscomoLidosCMP(ids, todos = false){
    try {
        loadingStart('bloqueio-modal-conteudo-movimentacao-processo');
        //Monta objeto
        let dados ={
            "ids" : ids
        }
        //Url da requisição
        let url      = `${baseURL}` + `${rotaBaseTribunal}/movimentacoes/setaregistroslidos?XDEBUG_SESSION_START=netbeans-xdebug`;
        let response = null;
        //Dispara requisição
        response     = await najTribunal.postData(url, dados);
        //Verifica se a requisição foi bem sucedida
        if(response.code == 200){
            //Verifica se a atualização foi para todos os registros
            if(todos){
                //Para cada registro de movimentações na tableTribunal...
                for(let i = 0; i < tableTribunal.data.resultado[indexTribunal].movimentacoes.length; i++){
                    //Atualiza o atribudo lido do registro na tableTribunal 
                    tableTribunal.data.resultado[indexTribunal].movimentacoes[i].lido = "S";
                }
            }else{
                //Indice final tableCMP
                let indiceFinalTableTribunal = tableCMP.page * 10;
                //Indice inicial tableCMP
                let indiceTableTribunal = indiceFinalTableTribunal - 10 > 0 ? indiceFinalTableTribunal - 10 : 0;
                for(let i = 0; i < tableCMP.data.resultado.length; i++){
                    //Atualiza o atribudo lido do registro na tableTribunal 
                    tableTribunal.data.resultado[indexTribunal].movimentacoes[indiceTableTribunal].lido = "S";
                    //incrementa o indice das movimentações na tableTribunal
                    indiceTableTribunal++;
                    //Atualiza o atribudo lido do registro na tableCMP 
                    tableCMP.data.resultado[i].lido = "S"; 
                }
            }
            let qtd_andamentos = $(`#qtd_novos_andamentos_${tableTribunal.data.resultado[indexTribunal].id_mpt}`).html();
            qtd_andamentos = qtd_andamentos - ids.length;
            if(qtd_andamentos <= 0){
                //Esconde o badge de novos andamentos do registro do MT selecionado
                $(`#qtd_novos_andamentos_${tableTribunal.data.resultado[indexTribunal].id_mpt}`).hide();
            }else{
                //Atualiza o badge de novos andamentos do registro do MT selecionado
                $(`#qtd_novos_andamentos_${tableTribunal.data.resultado[indexTribunal].id_mpt}`).html(qtd_andamentos)
            }
//            let id_mpt_corrente = tableTribunal.data.resultado[indexTribunal].id_mpt;
            //Recarrega os dados da tabela da rotina monitoramento tribunal
            //await tableTribunal.load();
            //atualizaBadgesQtdsMT();
            //Seleciona novamente a linha corrente da tabela da rotina monitoramento tribunal
//            if(tableTribunal.data.resultado.length != 0){
//                if(tableTribunal.data.resultado[indexTribunal].id_mpt == id_mpt_corrente){
//                    verificaSeSelecionaLinhaMT();
//                }
//            }else{
//                $('#modal-conteudo-movimentacao-processo').modal('hide');
//            }
        } else {
            NajAlert.toastError('Erro ao alter a situação dos registros como lido, contate o suporte!');
            console.log(response);
        }
    }catch(e){
        NajAlert.toastError('Erro ao alter a situação dos registros como lido, contate o suporte!');
        console.log(response);
    }finally {
        loadingDestroy('bloqueio-modal-conteudo-movimentacao-processo');
    }
}

/**
 * Proximo Conteudo Movimentacao Processo
 */
async function proximoConteudoMovimentacaoProcesso(){
    $(`#proximoConteudoMovimentacaoProcesso`).blur();
    $('#proximoConteudoMovimentacaoProcesso').tooltip('hide');
    //Verifica se o registro corrente é correpondente ao último registro da página
    if(indexTribunal + 1 == tableTribunal.data.resultado.length){
        //Verifica se a página corrente é correspondente a última página
        if(tableTribunal.data.pagina == tableTribunal.totalPages){
            NajAlert.toastWarning('Você já chegou ao último registro da página');
            return;
        }
        tableTribunal.page++; 
        loadingStart('bloqueio-modal-conteudo-movimentacao-processo');
        await tableTribunal.load();
        await atualizaBadgesQtdsMT();
        await recarregaOsTooltip();
        loadingDestroy('bloqueio-modal-conteudo-movimentacao-processo');
        indexTribunal = -1;
    }      
    if(indexTribunal >= 0){
        //Remove da linha corrente a classe CSS "row-selected"
        $('.data-table-row')[indexTribunal].classList.remove("row-selected");
        //Desmarca o 'checked' do checkbox da linha
        $('.data-table-row')[indexTribunal].querySelector('input[type=checkbox]').checked = false;
    }
    indexTribunal++;
    //Seta o checkbox da linha como 'checked'
    $('.data-table-row')[indexTribunal].classList.add("row-selected");
    //Desmarca o 'checked' do checkbox da linha
    $('.data-table-row')[indexTribunal].querySelector('input[type=checkbox]').checked = true;
    carregaModalCMP();
}

/**
 * Anterior Conteudo Movimentacao Processo 
 */
async function anteriorConteudoMovimentacaoProcesso(){
    $(`#anteriorConteudoMovimentacaoProcesso`).blur();
    $('#anteriorConteudoMovimentacaoProcesso').tooltip('hide');
    if(indexTribunal == 0){
        if(tableTribunal.data.pagina == 1){
            NajAlert.toastWarning('Você já chegou ao primeiro registro da página');
            return;
        }
        tableTribunal.page--; 
        loadingStart('bloqueio-modal-conteudo-movimentacao-processo');
        await tableTribunal.load();
        await atualizaBadgesQtdsMT();
        await recarregaOsTooltip();
        loadingDestroy('bloqueio-modal-conteudo-movimentacao-processo');
        indexTribunal = 20;
    }
    if(indexTribunal <= 19){
        //Remove da linha corrente a classe CSS "row-selected"
        $('.data-table-row')[indexTribunal].classList.remove("row-selected");
        //Desmarca o 'checked' do checkbox da linha
        $('.data-table-row')[indexTribunal].querySelector('input[type=checkbox]').checked = false;
    }
    indexTribunal--;
    //Seta o checkbox da linha como 'checked'
    $('.data-table-row')[indexTribunal].classList.add("row-selected");
    //Desmarca o 'checked' do checkbox da linha
    $('.data-table-row')[indexTribunal].querySelector('input[type=checkbox]').checked = true;
    carregaModalCMP();
}

/**
 *  Exclui os andamentos  
 */
async function ExcluirAndamentosCMP(instancia){
    try {
        loadingStart('bloqueio-modal-conteudo-movimentacao-processo');
        let id_mpt_corrente = tableTribunal.data.resultado[indexTribunal].id_mpt;
        //Url da requisição
        let url      = `${baseURL}` + `${rotaBaseTribunal}/movimentacoes/excluir/${id_mpt_corrente}/${instancia}?XDEBUG_SESSION_START=netbeans-xdebug`;
        let response = null;
        //Dispara requisição
        response     = await najTribunal.getData(url);
        //Verifica se a requisição foi bem sucedida
        if(response.code == 200){
            //Recarrega os dados da tabela da rotina monitoramento tribunal
            //await tableTribunal.load();
            await atualizaMTaposExclussaoAndamentos(id_mpt_corrente, instancia);
            await carregaModalCMP();
            await atualizaBadgesQtdsMT();
            //Verifica se seleciona novamente a linha corrente da tabela da rotina monitoramento tribunal
            if(tableTribunal.data.resultado.length != 0){
//                if(tableTribunal.data.resultado[indexTribunal].id_mpt != id_mpt_corrente){
//                    getIndexMTpeloId(id_mpt_corrente);
//                }
//                verificaSeSelecionaLinhaMT();
                await carregamentoInicialTableCMP(getRegistroSelecionadoMT().movimentacoes);
            }
            return {'code': response.code,
                    'msg' :response.message
            }
        } else if (response.code == 400){
            console.log(response);
            return {'code': response.code,
                    'msg' :response.message
            }
        }
    }catch(e){
        NajAlert.toastError('Erro ao excluir os registros, contate o suporte!');
        console.log(e);
    }finally {
        loadingDestroy('bloqueio-modal-conteudo-movimentacao-processo');
    }
}

/**
 * Sweet Alert para solicitar ao usuário a confirmação da remoção da atividade
 * 
 * @param {int} id_atividade
 */
async function sweetAlertExcluirAndamentos(instancia) {
    await Swal.fire({
        title: "Atenção!",
        text: `Tem certeza que deseja excluir todos os andamentos de ${instancia}º instância deste monitoramento?`,
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#DD6B55',
        confirmButtonText: "Sim, eu tenho!",
        cancelButtonText: "Cancelar",
        onClose: () => {
        }
    }).then(async (result) => {
        if (result.value) {
            result = await ExcluirAndamentosCMP(instancia);
            if(result.code == 200){
                Swal.fire(
                    'Sucesso!',
                    result.msg,
                    'success'
                )
            }else if(result.code == 400){
                Swal.fire(
                    'Atenção!',
                    result.msg,
                    'error'
                )
            }
        }
    });
}

/*
 * Fecha o modal do conteudo das movimentações
 */
async function fecharModalConteudoPublicacao(){
    $(`#modal-conteudo-movimentacao-processo #btnFecharModalConteudoMovimentacao`).blur();
    $('#btnFecharModalConteudoMovimentacao').tooltip('hide');
    $('#modal-conteudo-movimentacao-processo').modal('hide');
    //await tableTribunal.load();
    //await atualizaBadgesQtdsMT();
}

/*
 * Marca todos os andamentos de todos os monitoramentos como lidos
 */
async function marcaTodosComoLidosCMP(){
    let movimentacoes = getRegistroSelecionadoMT().movimentacoes;
    let ids  = []; 
    //Obtêm os ids dos registros
    for(let i = 0; i < movimentacoes.length; i++){
        if(movimentacoes[i].lido == 'N'){
            ids.push(movimentacoes[i].id);
        }
    }
    //Verifica se contêm pelo menos um registro não lido
    if(ids.length > 0){
        //Dispara requisição para setar registros como lidos
        await setaRegistroscomoLidosCMP(ids, true);
        await carregamentoInicialTableCMP(getRegistroSelecionadoMT().movimentacoes);
        NajAlert.toastSuccess('Todos os registros foram marcados como lidos!');
    }else{
        NajAlert.toastWarning('Não exitem registros para serem marcados como lidos!');
    }
}

/**
 * Atualiza a datatable do monitoramento tribunal após a exclusão de movimentações
 * 
 * @param {int} id_mpt_corrente
 * @param {int} instancia
 */
async function atualizaMTaposExclussaoAndamentos(id_mpt_corrente, instancia){
    if(tableTribunal.data.resultado[indexTribunal].id_mpt == id_mpt_corrente){
        let instancias         = ["","PRIMEIRO_GRAU","SEGUNDO_GRAU"];
        instancia              = instancias[instancia];
        let movimentacoes      = tableTribunal.data.resultado[indexTribunal].movimentacoes;
        let movimentacoesNovas = [];
        let contador           = 0
        for(let i = 0; i < movimentacoes.length; i++){
            if(movimentacoes[i].instancia != instancia){
                movimentacoesNovas.push(movimentacoes[i]);
            }else{
                contador++;
            }
        }
        tableTribunal.data.resultado[indexTribunal].movimentacoes         = movimentacoesNovas;
        let lidos                                                         = tableTribunal.data.resultado[indexTribunal].qtde_total_andamentos - tableTribunal.data.resultado[indexTribunal].qtde_novas_andamentos;
        tableTribunal.data.resultado[indexTribunal].qtde_total_andamentos = tableTribunal.data.resultado[indexTribunal].qtde_total_andamentos - contador;
        tableTribunal.data.resultado[indexTribunal].qtde_novas_andamentos = tableTribunal.data.resultado[indexTribunal].qtde_novas_andamentos - contador + lidos; 
        tableTribunal.data.total_novas_movimentacoes                      = tableTribunal.data.total_novas_movimentacoes - contador + lidos;
        
        $('#qtde_total_andamentos_' + id_mpt_corrente).html(tableTribunal.data.resultado[indexTribunal].qtde_total_andamentos);
        $('#qtde_novas_andamentos_' + id_mpt_corrente).html(tableTribunal.data.resultado[indexTribunal].qtde_novas_andamentos);
    }else{
        NajAlert.toastError("Erro ao atualizar o monitoramento no client side, contate o suporte!")
    }
}