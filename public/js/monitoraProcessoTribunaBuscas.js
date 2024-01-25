//---------------------- Parametrôs -----------------------//

const najMPTB         = new Naj('MonitoraProcessoTribunalBuscas', null);
const tableMPTB       = (isIndex(rotaBaseTribunal)) ? new MonitoraProcessoTribunalBuscasTable : false;
const rotaBasenajMPTB = 'monitoraprocessotribunalbusca';

//---------------------- Eventos -----------------------//

$(document).ready(async function () {
    
    tableMPTB.render();
    //Cria os filtros personalizados
    getCustomFiltersMPTB();
    
    //Ao clicar em pesquisar...
    $(document).on("click", '.verStatus', function () {
        getIndexMT();
        verificaSeSelecionaLinhaMT();
        carregaModalMPTB();
    });
    
    //Ao clicar em pesquisar...
    $(document).on("click", '#search-button-MPTB', function () {
        buscaPersonalizadaMPTB();
    });
    
});

//---------------------- Functions -----------------------//

/**
 * Carrega Modal Comentario Andamento Processual
 */
function carregaModalMPTB(){
    buscaPersonalizadaMPTB();
    $('#modal-consulta-monitora-processo-tribunal-buscas').modal('show');
}

async function buscaPersonalizadaMPTB(){
    //Limpa filtros 
    tableMPTB.filtersForSearch = [];
    
    let value = tableTribunal.data.resultado[indexTribunal].id_mpt;
    if(value){
        filter1        = {}; 
        filter1.val    = value;
        filter1.op     = "I";
        filter1.col    = "id_monitora_tribunal";
        filter1.origin = btoa(filter1);
        tableMPTB.filtersForSearch.push(filter1);
    }
    await tableMPTB.load();
}

/**
 * Carrega os filtros personalizados da tabela
 */
function getCustomFiltersMPTB(){
    //Carrega os options do campo select de advogados
    //let options = await carregaOptionsSelect('monitoramento/diarios' + '/buscanomedostermos', 'filter-termo', true, "data", true); 
    let content =  `<div style="display: flex;" class="font-12">
                        <button id="search-button-MPTB" class="btn btnCustom action-in-button ml-0 mt-1 mr-1 mb-1">
                            <i class="fas fa-search btn-icon"></i>&nbsp;&nbsp;
                            Pesquisar
                        </button>
                        <button id="forcarBusca" class="btn btnCustom action-in-button m-1" onclick="forcarBusca()">
                            <i class="fas fa-cloud-download-alt remove-btn-icon"></i>&nbsp;&nbsp;
                            Forçar Busca
                        </button>
                    </div>`;
    //Seta os filtros personalizados no cabeçalho do datatable
    //let indexHeader = $('.data-table-filter').length - 1;
    $('#datatable-monitora-processo-tribunal-buscas .data-table-filter')[0].innerHTML  = content;
}

/**
 * Força a busca por novos andamentos 
 */
async function forcarBusca(){
    try{
        loadingStart('bloqueio-modal-consulta-monitora-processo-tribunal-buscas');
        dados = {
            'id': tableTribunal.data.resultado[indexTribunal].id_mpt,
            'numero_cnj': tableTribunal.data.resultado[indexTribunal].numero_cnj,
            'abrangencia': tableTribunal.data.resultado[indexTribunal].abrangencia
        }
        //Requisição para cadastrar a pesquisa do CNJ na Escavador
        response = await najMPT.postData(`${baseURL}` + `monitoramento/tribunais/pesquisaprocesso`, dados);
        if(response.code == 200){
            //Success Message
            await Swal.fire("Sucesso!", "Monitoramento incluído com sucesso, estamos efetuando a busca por novas Movimentações!", "success");
        }else{
            NajAlert.toastError('Erro ao cadastrar a pesquisa do CNJ na Escavador, contate o suporte!');
            console.log(response);
        }
    }finally {
        loadingDestroy('bloqueio-modal-consulta-monitora-processo-tribunal-buscas');
        //tableTribunal.load();
        await atualizaRegistroMTaposForcarBusca();
        await atualizaBadgesQtdsMT();
        await carregaModalMPTB();
    }
    
}

/**
 * Atualiza o registro no datatable do monitoramento tribunal após forçar a busca por novas movimentações 
 * 
 */
async function atualizaRegistroMTaposForcarBusca(){
    let id_mpt      = tableTribunal.data.resultado[indexTribunal].id_mpt;
    let idBtnStatus = 'btnStatus_' + id_mpt; 
    $(`#${idBtnStatus}`).html('PENDENTE');
    $(`#${idBtnStatus}`)[0].attributes['data-original-title'].value = 'PENDENTE: Busca em andamento';
    removeClassById(idBtnStatus,'badge-danger');
    addClassById(idBtnStatus,'badge-warning');
    tableTribunal.data.total_erro_na_ultima_busca = (tableTribunal.data.total_erro_na_ultima_busca - 1);
    tableTribunal.data.total_buscas_em_andamento  = (tableTribunal.data.total_buscas_em_andamento + 1);
}