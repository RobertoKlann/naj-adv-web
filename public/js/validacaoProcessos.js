//---------------------- Parâmetros -----------------------//

const rotaBaseProcessos = 'validacao/processos';
const tableProcessos    = new ValidacaoProcessosTable();
const najProcessos      = new Naj('ValidacaoProcessos', tableProcessos);
let   indexProcessos    = null;

//---------------------- Eventos -----------------------//

$(document).ready(async function () {
    
    await tableProcessos.render();
    //Cria os filtros personalizados
    await getCustomFiltersValidacaoProcesso();
    
    //Ao clicar em pesquisar...
    $(document).on("click", '#search-button-validacao-processo', async function () {
        await buscaPersonalizadaValidacaoProcessos();
    });
    
    //Ao mudar a opção de status processo
    $(document).on("change", '#filter-status-processo', async function () {
        await buscaPersonalizadaValidacaoProcessos();
    });
    
    //Ao mudar a opção de situacao processo
    $(document).on("change", '#filter-situacao-processo', async function () {
        await buscaPersonalizadaValidacaoProcessos();
    });
    
    //Carrega o modal manutencao MPT no modo edição edicao
    $(document).on('click', '#datatable-validacao-processos .btn-onedit', async function() {
        //Verifica se seleciona linha do Validação Processos
        verificaSeSelecionaLinhaValidacaoProcessos();
        //Carrega Modal de Manutenção Monitoramento Processo Tribunal
        await carregaModalManutencaoMPTemValidacaoProcessosEdicao();
    });
    
    //Ao esconder o modal de '#modal-manutencao-monitoramento-processo-tribunal' remove a classe 'z-index-100' do modal '#modal-consulta-valicao-processos'
    $('#modal-manutencao-monitoramento-processo-tribunal').on('hidden.bs.modal', function(){
        $('#modal-consulta-valicao-processos').removeClass('z-index-100');    
        sessionStorage.removeItem('@NAJ_WEB/monitoramento_processo_tribunal_validacao_processos');
    });
    
    //Ao clicar em "onClickFichaProcessoVP"...
    $(document).on('click', '.onClickFichaProcessoVP', function() {
        //Verifica se a rotina corrente é a de monitoramento tribunal, poi se for precisa fazer o controle de registro selecionado no grid
        if(typeof rotaBaseTribunal != "undefined"){
            getIndexValidacaoProcessos();
            verificaSeSelecionaLinhaValidacaoProcessos();
        }
        onClickFichaProcessoVP(tableProcessos.data.resultado[indexProcessos].CODIGO);
    });
    
});

//---------------------- Functions -----------------------//

/**
 * Obtêm o index do registro selecionado no datatable de Validação Processos
 * 
 * @returns {Number|indexTribunal}
 */
function getIndexValidacaoProcessos(){
    for(let i = 0 ; i < $('#datatable-validacao-processos .data-table-row').length; i++){
        let selecionado = $('#datatable-validacao-processos .data-table-row')[i].className.includes('row-selected');
        if(selecionado){
            indexProcessos = i;
            return indexProcessos;
        }
    }
    return null;
}

/**
 * Obtêm o registro selecionado no datatable de Validação Processos
 * 
 * @returns {object}
 */
function getRegistroSelecionadoValidacaoProcessos(){
    //Obtêm o index da linha selecionada
    getIndexValidacaoProcessos();
    if(indexProcessos != null){
        let registro = tableProcessos.data.resultado[indexProcessos];
        //Substitui os valores nulos do objeto por string vazias
        registro     = replaceNullByEmptyInObject(registro);
        //Retorna o registro selecionado
        return registro;
    }
    return null;
}

/**
 * Verifica se seleciona linha no validação processos
 * @param {elemento} e
 */
function verificaSeSelecionaLinhaValidacaoProcessos(){
    //Verifica se exite linhas selecionadas no datatable
    if(tableProcessos.selectedRows.length == 0){
        //Seta o checkbox da linha como 'checked'
        $('#datatable-validacao-processos .data-table-row')[indexProcessos].classList.add("row-selected");
        //Desmarca o 'checked' do checkbox da linha
        $('#datatable-validacao-processos .data-table-row')[indexProcessos].querySelector('input[type=checkbox]').checked = true;
    }
};

/**
 * Carrega o modal de manutenção do monitoramento do processo do tribunal no modo edição
 * 
 * @param {type} e
 */
async function carregaModalManutencaoMPTemValidacaoProcessosEdicao(){
    try{
        let id_monitora_tribunal = getRegistroSelecionadoValidacaoProcessos().id_monitora_tribunal;
        if(!id_monitora_tribunal){
            NajAlert.toastWarning('Esse processo não está sendo monitorado!');
            return;
        }
        loadingStart('bloqueio-modal-consulta-valicao-processos');
        let registro = await najProcessos.getData(baseURL + `monitoraprocessotribunal/show/` + btoa(JSON.stringify({'id':id_monitora_tribunal})));
        dados = {
            "id"              : registro.id,
            "numero_cnj"      : registro.numero_cnj,
            "frequencia"      : registro.frequencia,
            "status"          : registro.status,
            "codigo_processo" : tableProcessos.data.resultado[indexProcessos].CODIGO,
            "abrangencia"     : (registro.abrangencia == "" || registro.abrangencia == null) ? "0" : registro.abrangencia
        }
        //Seta na sessão o modo edição para o modal de manutenção
        sessionStorage.setItem('@NAJ_WEB/monitoramento_processo_tribunal','edit');
        sessionStorage.setItem('@NAJ_WEB/monitoramento_processo_tribunal_validacao_processos', true);
        //Carrega o modal de manutenção do monitoramento do processo do tribunal
        await carregaModalManutencaoMonitoramentoProcessoTribunal(dados);
        $('#modal-consulta-valicao-processos').addClass('z-index-100');
        $('#form-processo-tribunal #abrangencia').attr('disabled',true);
        //Se tipo do usuário for supervisor
        if(getConsts().tipoUsuarioLogado != "0"){
            habilitaDesabilitaCamposMTP(false);
        }
    }finally{
        loadingDestroy('bloqueio-modal-consulta-valicao-processos');
    }
}

/**
 * Carrega o modal de validação dos procesos
 */
async function carregaModalValidacaoProcessos(){
    try{
        loadingStart();
        $('#filter-status-processo').val(0);
        $('#filter-situacao-processo').val("");
        await buscaPersonalizadaValidacaoProcessos();
    }finally {
        let totalProcessosValidos = tableProcessos.data.total_processos_validos > 9999 ? '99.999+' : tableProcessos.data.total_processos_validos;
        $('#totalPrcValilosBtn').html(totalProcessosValidos);
        $('#modal-consulta-valicao-processos').modal('show');
        loadingDestroy();
    }
}

/**
 * Carrega os filtros personalizados da tabela
 */
function getCustomFiltersValidacaoProcesso(){
    //Carrega os options do campo select de advogados
    //let options = await carregaOptionsSelect('monitoramento/diarios' + '/buscanomedostermos', 'filter-termo', true, "data", true); 
    let content =  `<div style="display: flex;" class="font-12">
                        <div style="display: flex; align-items: center;" class="m-1">
                            <span>Status igual a: </span>
                        </div>
                        <select id="filter-status-processo" class="m-1">
                            <option value="0">-- Todos --</option>
                            <option value="1">CNJ INVÁLIDO</option>
                            <option value="2">REVISAR INSTÂNCIA</option>
                            <option value="3">DISPONÍVEL</option>
                            <option value="4">MONITORAMENTO ATIVO</option>
                            <option value="5">MONITORAMENTO BAIXADO</option>
                        </select>
                        <div style="display: flex; align-items: center;" class="m-1">
                            <span>Situação igual a: </span>
                        </div>
                        <select id="filter-situacao-processo" class="m-1">
                            <option value="">-- Todos --</option>
                            <option value="BAIXADO">BAIXADOS</option>
                            <option value="ATIVO">ATIVOS</option>
                        </select>
                        <button id="search-button-validacao-processo" class="btn btnCustom action-in-button m-1">
                            <i class="fas fa-search btn-icon"></i>&nbsp;&nbsp;
                            Pesquisar
                        </button>
                        <button id="monitorarProcessosDisponivei" class="btn btnCustom m-1" style="background-color: #2cd07e" onclick="carregaSweetAlertMonitorarProcessosDisponiveis()">
                            Adicionar (<span id="totalPrcValilosBtn"></span>)
                        </button>
                    </div>`;
    //Seta os filtros personalizados no cabeçalho do datatable
    //let indexHeader = $('.data-table-filter').length - 1;
    $('#datatable-validacao-processos .data-table-filter')[0].innerHTML  = content;
}

/**
 * Sweet Alert para solicitar ao usuário a confirmação da remoção da atividade
 * 
 * @param {int} id_atividade
 */
async function carregaSweetAlertMonitorarProcessosDisponiveis() {
    if(tableProcessos.data.total_processos_validos > 0){
        await Swal.fire({
            title: "Atenção!",
            text: `Foram detectados ${tableProcessos.data.total_processos_validos} processos válidos no sistema, deseja cadastrar monitoramentos para todos esses processos?`,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#DD6B55',
            confirmButtonText: "Sim!",
            cancelButtonText: "Cancelar",
            onClose: () => {
            }
        }).then(async (result) => {
            if (result.value) {
                await monitorarProcessosDisponiveis();
            }
        });
    }else{
        NajAlert.toastWarning("No momento não há processos dísponíveis para serem monitorados!");
    }
}

/**
 * Executa requisição para monitorar os processsos didponíveis
 */
async function monitorarProcessosDisponiveis(){
    try{
        loadingStart('bloqueio-modal-consulta-valicao-processos');
        let situacao = $('#filter-situacao-processo').val() ? $('#filter-situacao-processo').val() : "todos";
        let url      = `${baseURL}${rotaBaseProcessos}/monitorarprocessos/${situacao}?XDEBUG_SESSION_START=netbeans-xdebug`;
        let result   = await najProcessos.getData(url);
        if(result.code == 200){
            NajAlert.toastSuccess(result.message);
            await buscaPersonalizadaValidacaoProcessos();
            let totalProcessosValidos = tableProcessos.data.total_processos_validos > 999 ? '999+' : tableProcessos.data.total_processos_validos;
            $('#totalPrcValilosBtn').html(totalProcessosValidos);
            await tableTribunal.load();
        }else{
            NajAlert.toastError(result.message);
        }
    }catch (e){
        console.log();
        NajAlert.toastError('Erro ao cadastrar os monitoramentos, contate o suporte!');
    }finally {
        loadingDestroy('bloqueio-modal-consulta-valicao-processos');
    }
}

/**
 * Abre nova guia redirecionado para o sistema antigo
 * 
 * @param {int} codigo do processo
 */
function onClickFichaProcessoVP(codigo = null) {
    if(codigo == null){
        NajAlert.toastWarning('Código do processo não foi informado, contate o suporte!');
    }
    window.open(`${najAntigoUrl}?idform=processos&processoid=${codigo}`);
}

/**
 * Obtêm os valores dos filtros personalizados e executa a busca
 * 
 * @param render Define se irá renderizar a tabela ou apenas carrega-lá, false por default
 */
async function buscaPersonalizadaValidacaoProcessos(render = false){
    try{
        //Limpa filtros 
        tableProcessos.filtersForSearch = [];
        
        let status_processo     = $('#filter-status-processo').val()   ? $('#filter-status-processo').val()   : null;
        let situacao_processo   = $('#filter-situacao-processo').val() ? $('#filter-situacao-processo').val() : null;
        
        //CNJ INVÀLIDO
        if(status_processo == 1){
            filter1        = {}; 
            filter1.val    = 0
            filter1.op     = "I";
            filter1.col    = 'CNJ_VALIDO';
            filter1.origin = btoa(filter1);
            tableProcessos.filtersForSearch.push(filter1);
        //REVISAR INSTÂNCIA
        }else if(status_processo == 2){
            filter1        = {}; 
            filter1.val    = 1
            filter1.op     = "I";
            filter1.col    = 'REVISAR_INSTANCIA';
            filter1.origin = btoa(filter1);
            tableProcessos.filtersForSearch.push(filter1);
        //DISPONÍVEL
        }else if(status_processo == 3){
            filter1        = {}; 
            filter1.val    = 1;
            filter1.op     = "I";
            filter1.col    = 'CNJ_VALIDO';
            filter1.origin = btoa(filter1);
            tableProcessos.filtersForSearch.push(filter1);
            filter2        = {}; 
            filter2.val    = 0
            filter2.op     = "I";
            filter2.col    = 'REVISAR_INSTANCIA';
            filter2.origin = btoa(filter2);
            tableProcessos.filtersForSearch.push(filter2);
        //MONITORAMENTO ATIVO
        }else if(status_processo == 4){
            filter1        = {}; 
            filter1.val    = 1
            filter1.op     = "I";
            filter1.col    = 'MONITORADO';
            filter1.origin = btoa(filter1);
            tableProcessos.filtersForSearch.push(filter1);
            filter2        = {}; 
            filter2.val    = 'ATIVO'
            filter2.op     = "I";
            filter2.col    = 'STATUS_MONITORAMENTO';
            filter2.origin = btoa(filter2);
            tableProcessos.filtersForSearch.push(filter2);
        //MONITORAMENTO BAIXADO
        }else if(status_processo == 5){
            filter1        = {}; 
            filter1.val    = 1
            filter1.op     = "I";
            filter1.col    = 'MONITORADO';
            filter1.origin = btoa(filter1);
            tableProcessos.filtersForSearch.push(filter1);
            filter2        = {}; 
            filter2.val    = 'BAIXADO'
            filter2.op     = "I";
            filter2.col    = 'STATUS_MONITORAMENTO';
            filter2.origin = btoa(filter2);
            tableProcessos.filtersForSearch.push(filter2);
        }

        //Seta filtro MONITORADO
        if(status_processo > 0 && status_processo < 4){
            filter3        = {}; 
            filter3.val    = 0   
            filter3.op     = "I";
            filter3.col    = 'MONITORADO';
            filter3.origin = btoa(filter3);
            tableProcessos.filtersForSearch.push(filter3);
        }
        
        //Seta filtro SITUACAO
        if(situacao_processo !== null){
            filter4        = {}; 
            filter4.val    = situacao_processo;
            filter4.op     = "I";
            filter4.col    = "SITUACAO";
            filter4.origin = btoa(filter4);
            tableProcessos.filtersForSearch.push(filter4);
        }

        tableProcessos.page = 1;
        
        //verifica se renderiza a tabela ou apenas carrega os dados
        if(render){
            await tableProcessos.render();
        }else{
            await tableProcessos.load();
        }

    }catch (e){
        console.log(e);
    }finally {
        let totalProcessosValidos = tableProcessos.data.total_processos_validos > 999 ? '999+' : tableProcessos.data.total_processos_validos;
        $('#totalPrcValilosBtn').html(totalProcessosValidos);
    }
}