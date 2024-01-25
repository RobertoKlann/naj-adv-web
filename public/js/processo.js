//---------------------- Parametrôs -----------------------//

const najProcesso      = new Naj('Processo', null);
const rotaBaseProcesso = 'processos';

//---------------------- Eventos -----------------------//

$(document).ready(function() {
    
    //Ao clicar em gravar...
    $(document).on("click", '#gravarProcesso', function () {
        gravarDadosProcesso();
    });
    
    //Ao clicar no botão de cadastro do processo, carrega modal de manutenção de processo no modo create
    $(document).on('click', '#btnCadastrarProcesso', async function() {
        $(`#modal-conteudo-publicacao #btnCadastrarProcesso`).blur();
        $('#btnCadastrarProcesso').tooltip('hide');
        sessionStorage.setItem('@NAJ_WEB/processo_action', 'create');
        carregaModalManutencaoProcesso();
    });
    
    //Ao clicar no botão de ver processo, carrega modal de manutenção de processo no modo edit
    $(document).on('click', '#btnVerProcesso', async function() {
        $(`#modal-conteudo-publicacao #btnVerProcesso`).blur();
        $('#btnVerProcesso').tooltip('hide');
        sessionStorage.setItem('@NAJ_WEB/processo_action', 'edit');
        carregaModalManutencaoProcesso();
    });
    
    //Ao clicar no botão de ver processo, carrega modal de manutenção de processo no modo edit
    $(document).on('click', '#btnDesvincularProcesso', async function() {
        $('#btnDesvincularProcesso').tooltip('hide');
        desvincularProcesso();
    });
    
    //Ao clicar no registro da caixa de pesquisa do cliente...
    $('#content-select-ajax-naj-cliente').click(function(el) {
        onClickContentSelectAjax(el, 'nome_cliente_processo', 'codigo_cliente_processo', this.id);
    });

    //Ao clicar no registro da caixa de pesquisa do adversário...
    $('#content-select-ajax-naj-adversario').click(function(el) {
        onClickContentSelectAjax(el, 'nome_adversario', 'codigo_adversario', this.id);
    });
    
    //Ao clicar no registro da caixa de pesquisa do advogado do cliente...
    $('#content-select-ajax-naj-adv-cliente').click(function(el) {
        onClickContentSelectAjax(el, 'nome_adv_cliente', 'codigo_adv_cliente', this.id);
    });
    
    //Ao clicar no registro da caixa de pesquisa da classe da ação...
    $('#content-select-ajax-naj-classe').click(function(el) {
        onClickContentSelectAjax(el, 'nome_classe', 'codigo_classe', this.id);
    });
    
    //Ao clicar no registro da caixa de pesquisa de cartório...
    $('#content-select-ajax-naj-cartorio').click(function(el) {
        onClickContentSelectAjax(el, 'nome_cartorio', 'codigo_cartorio', this.id);
    });
    
    //Ao clicar no registro da caixa de pesquisa de comarca...
    $('#content-select-ajax-naj-comarca').click(function(el) {
        onClickContentSelectAjax(el, 'nome_comarca', 'codigo_comarca', this.id);
    });

    //Realiza a busca de pessoas ao clicar no campo "nome_cliente"
    $('#nome_cliente_processo').click(function(element) {
        getPessoas(element.target, true, '#nome_cliente_processo');
    });
    
    //Realiza a busca de pessoas ao clicar no campo "nome_adversario"
    $('#nome_adversario').click(function(element) {
        getPessoas(element.target, true, '#nome_adversario');
    });
    
    //Realiza a busca de pessoas ao clicar no campo "nome_adv_cliente"
    $('#nome_adv_cliente').click(function(element) {
        getPessoas(element.target, true, '#nome_adv_cliente');
    });
    
    //Realiza a busca de classes de ação ao clicar no campo "nome_classe"
    $('#nome_classe').click(function(element) {
        getClasses(element.target, false);
    });
    
    //Realiza a busca de cartórios ao clicar no campo "nome_cartorio"
    $('#nome_cartorio').click(function(element) {
        getCartorios(element.target, false);
    });
    
    //Realiza a busca de comarcas de ação ao clicar no campo "nome_comarca"
    $('#nome_comarca').click(function(element) {
        getComarcas(element.target, false);
    });
    
    //Esconde as caixas de pesquisa ao clicar fora das mesmas
    $('#content-modal-processo').click(function() {
        $("#content-select-ajax-naj-cliente").hide();
        $("#content-select-ajax-naj-adversario").hide();
        $("#content-select-ajax-naj-adv-cliente").hide();
        $("#content-select-ajax-naj-classe").hide();
        $("#content-select-ajax-naj-cartorio").hide();
        $("#content-select-ajax-naj-comarca").hide();
    });
    
    //Ao esconder o modal de '#modal-manutencao-pessoa' remove a classe 'z-index-100' do modal '#modal-manutencao-processo'
    $('#modal-manutencao-pessoa').on('hidden.bs.modal', function(){
        $('#modal-manutencao-processo').removeClass('z-index-100');  
    });
    
    //Ao esconder o modal de '#modal-manutencao-processo' remove a classe 'z-index-100' do modal '#modal-conteudo-publicacao'
    $('#modal-manutencao-processo').on('hidden.bs.modal', function(){
        modalProcessoCarregado = false;
        $('#modal-conteudo-publicacao').removeClass('z-index-100');    
    });
    
    //Ao esconder o modal de '#modal-manutencao-processo-classe' remove a classe 'z-index-100' do modal '#modal-manutencao-processo'
    $('#modal-manutencao-processo-classe').on('hidden.bs.modal', function(){
        $('#modal-manutencao-processo').removeClass('z-index-100');    
    });
    
    //Ao esconder o modal de '#modal-manutencao-processo-cartorio' remove a classe 'z-index-100' do modal '#modal-manutencao-processo'
    $('#modal-manutencao-processo-cartorio').on('hidden.bs.modal', function(){
        $('#modal-manutencao-processo').removeClass('z-index-100');    
    });
    
    //Ao esconder o modal de '#modal-manutencao-processo-comarca' remove a classe 'z-index-100' do modal '#modal-manutencao-processo'
    $('#modal-manutencao-processo-comarca').on('hidden.bs.modal', function(){
        $('#modal-manutencao-processo').removeClass('z-index-100');    
    });
    
    //Fecha calendário "data_distribuicao" se o mesmo estiver aberto ao rolar a tela
    $('#content-modal-processo').scroll(function(){
        $('#data_distribuicao').datepicker().close();
    });
});

//---------------------- Functions -----------------------//

/**
 * Desvincula o processo da movimentação
 * 
 */
async function desvincularProcesso(){
    try{
        //Bloqueia o modal de manutenção
        loadingStart('bloqueio-modal-conteudo-publicacao');
        codigo_processo = $('input[name="processo_semelhante"]:checked').val();
        //Desvincula o processo a publicação em "monitora_termo_processo"
        await najProcesso.postData(`${baseURL}${rotaBaseDiario}/desvinculaprocesso/` + btoa(JSON.stringify({'id':tableDiario.data.resultado[indexDiario].id_processo})) + `?XDEBUG_SESSION_START=netbeans-xdebug`);
        //Total de publicações correntes
        let totalBefore = tableDiario.data.total;
        await buscaTodasPublicacoesMD();
        //Total de publicações com o filtro todas as publicações
        let totalAllPubliMD  = tableDiario.data.total;
        //Se a pesquisa for do tipo "não lidos" ou "pendentes"
        if(filtroNaoLidos == false || filtroPendentes == false){
            //Precisamos saber qual o index que o modal terá com o filtro todas as publicações
            let indexDiario = totalAllPubliMD - totalBefore - totalPublicacoesDescartadasMD + indexDiario;
        }
        await carregaModalConteudoPublicacao(null, indexDiario);
        setSelectedOptionMenuMD();
    }catch (e){
        NajAlert.toastError(e);
    }finally {
        //Desbloqueia o modal de manutenção
        loadingDestroy('bloqueio-modal-conteudo-publicacao');
    }
}

/**
 * Carrega Modal Manutencao Processo Inclussão
 * 
 * @param {element} elemento
 */
async function carregaModalManutencaoProcesso() {
    try{
        loadingStart('bloqueio-modal-conteudo-publicacao');
        //Limpa os campos do formulário
        limpaFormulario('#modal-manutencao-processo');
        //Remove as validações do formulário
        removeClassCss('was-validated', '#form-processo');
        //Desabilita o campo NUMERO_PROCESSO_NEW
        $("#form-processo input[name=NUMERO_PROCESSO_NEW]").attr('readonly',true);
        //Obtêm e carrega os options dos campos de "QUALIFICA_CLIENTE"
        await carregaOptionsSelect(`${rotaBaseProcesso}/prcqualificacao`, ['qualifica_cliente_cliente','qualifica_cliente_adversario'], false, 'data', false, null, 1);
        //Obtêm e carrega os options dos campo "CODIGO_DIVISAO"
        await carregaOptionsSelect(`${rotaBaseProcesso}/areajuridica`, 'id_area_juridica', false, 'data', false);
        //Obtêm e carrega os options dos campo "CODIGO_DIVISAO"
        await carregaOptionsSelect(`pessoas/divisao`, 'codigo_divisao_processo', false, 'data', false);
        //Obtêm e carrega os options dos campo "CODIGO_SITUACAO"
        await carregaOptionsSelect(`${rotaBaseProcesso}/prcsituacao`, 'codigo_situacao', false, 'data', false);
        if (sessionStorage.getItem('@NAJ_WEB/processo_action') == "create") {
            //Esconde externo do processo no modal de processo
            $('#externoProcessoModalProcesso').hide();
            //Obtêm o próximo código de processo
            let codigo = await najProcesso.getData(`${baseURL}${rotaBaseProcesso}/proximo`);
            //Seta o próximo código do processo no campo do formulário
            $('#form-processo #codigo_processo').val(codigo + 1);
            //Seta a data corrente no campo do formulário
            $('#form-processo input[name=DATA_CADASTRO]').val(getDateProperties().fullDateSlash);
            //Obtêm e carrega o nome e código do advogado
            let advogado = await najProcesso.getData(`${baseURL}pessoas/getPessoaFilter/` + tableDiario.data.resultado[indexDiario].termo_pesquisa);
            if(advogado){
                $('#form-processo input[name=CODIGO_ADV_CLIENTE]').val(advogado.codigo);
                $('#form-processo input[name=nome_adv_cliente]').val(advogado.nome);
            } else {
                $('#form-processo input[name=nome_adv_cliente]').val(tableDiario.data.resultado[indexDiario].termo_pesquisa);
            }
            $('#form-processo input[name=NUMERO_PROCESSO_NEW]').val(tableDiario.data.resultado[indexDiario].processo.numero_novo);
            //Seta Classe e Comarca e Cartorio
            let classe      = null;
            let strComarca  = null;
            let strCartorio = null;
            //Se a publicação for do tipo "diario_movimentacao_nova" obtêm a Classe e Comarca e Cartorio
            if(JSON.parse(tableDiario.data.resultado[1].conteudo_json).event == "diario_movimentacao_nova"){
                if(JSON.parse(tableDiario.data.resultado[indexDiario].conteudo_json).movimentacao.tipo != null){
                    classe = JSON.parse(tableDiario.data.resultado[indexDiario].conteudo_json).movimentacao.tipo.replaceAll('/');
                }
                if(JSON.parse(tableDiario.data.resultado[indexDiario].conteudo_json).movimentacao.secao != null){
                    strComarca  = JSON.parse(tableDiario.data.resultado[indexDiario].conteudo_json).movimentacao.secao.replaceAll('_').toLowerCase();
                }
                if(JSON.parse(tableDiario.data.resultado[indexDiario].conteudo_json).movimentacao.secao){
                    strCartorio = JSON.parse(tableDiario.data.resultado[indexDiario].conteudo_json).movimentacao.secao.replaceAll('_').toLowerCase();
                }
            }
            if(classe){
                $('#form-processo input[name=CLASSE]').val(classe);
            }
            if(strComarca){
                //Verifica se contêm na string a informação "comarca de"
                let indexComarcaDe = strComarca.indexOf('comarca de ');
                let comarca        = "";
                if(indexComarcaDe > 0){
                    comarca = strComarca.substr(indexComarcaDe);
                    comarca = comarca.substr(11);
                    comarca = comarca.capitalize();
                    comarca = comarca.substr(0, comarca.length -1);
                } 
                $('#form-processo input[name=nome_comarca]').val(comarca);
            }
            if(strCartorio){
                //Verifica se contêm "vara alguma coisa"
                let indexCartorio = strCartorio.indexOf('vara');
                let cartorio      = "";        
                if(indexCartorio > 0){
                    cartorio          = strCartorio.substr(indexCartorio + 5);
                    indexCartorioLast = cartorio.indexOf(" ");
                    indexCartorioLast += 5 + indexCartorio;
                    cartorio = strCartorio.substr(0, indexCartorioLast);
                }
                $('#form-processo input[name=CARTORIO]').val(cartorio);
            }
            //Se a quantidade de opções for igual a um seta a opção como selecionado
            if($('#form-processo select[name=CODIGO_DIVISAO]')[0].options.length == 1 ){
                $('#form-processo select[name=CODIGO_DIVISAO]').val(1);
            }
            $('#form-processo select[name=CODIGO_SITUACAO]').val(1);
            $('#form-processo select[name=GRAU_JURISDICAO]').val('');
        } else if (sessionStorage.getItem('@NAJ_WEB/processo_action') == "edit") {
            $('#externoProcessoModalProcesso').show();
            
            id         = btoa(JSON.stringify({CODIGO: tableDiario.data.resultado[indexDiario].processo.codigo_processo}));
            response   = await najProcesso.getData(`${baseURL}` + `${rotaBaseProcesso}/show/${id}?XDEBUG_SESSION_START=netbeans-xdebug`);
            
            if(response.VALOR_CAUSA != null) response.VALOR_CAUSA = convertIntToMoney(response.VALOR_CAUSA);
            await najProcesso.loadData('#form-processo', response);
            if($(`#form-processo input[name=CODIGO_CLIENTE`).val()){
                await buscaRegistroCampoListagemProcesso('CODIGO_CLIENTE', 'nome_cliente', 'pessoas');
            }
            if($(`#form-processo input[name=CODIGO_ADVERSARIO`).val()){
                await buscaRegistroCampoListagemProcesso('CODIGO_ADVERSARIO', 'nome_adversario', 'pessoas');
            }
            if($(`#form-processo input[name=CODIGO_ADV_CLIENTE`).val()){
                await buscaRegistroCampoListagemProcesso('CODIGO_ADV_CLIENTE', 'nome_adv_cliente', 'pessoas');
            }
            if($(`#form-processo input[name=CODIGO_CLASSE`).val()){
                await buscaRegistroCampoListagemProcesso('CODIGO_CLASSE', 'CLASSE', 'processos/classe', 'CLASSE');
            }
            if($(`#form-processo input[name=CODIGO_COMARCA`).val()){
                await buscaRegistroCampoListagemProcesso('CODIGO_COMARCA', 'nome_comarca', 'processos/comarca', 'COMARCA');
            }
            if($(`#form-processo input[name=CODIGO_CARTORIO`).val()){
                await buscaRegistroCampoListagemProcesso('CODIGO_CARTORIO', 'CARTORIO', 'processos/cartorio', 'CARTORIO');
            }
        }
        //Seta máscaras
        $('#form-processo #data_distribuicao').mask('00/00/0000');
        $('#form-processo #valor_causa').mask('000.000.000.000.000,00', {reverse: true});
        //Seta o "datepicker" no campo de "data_distribuicao"
        $('#data_distribuicao').datepicker({
            uiLibrary: 'bootstrap4',
            locale: 'pt-br',
            format: 'dd/mm/yyyy'
        });
        //Remove icone de calendário do datepicker e seta icone calendário do fontwelsome
        $('.gj-icon').html("");
        addClassCss('far fa-calendar-alt',$('.gj-icon'));
        removeClassCss('gj-icon',$('.gj-icon'));
        $('#btnCadastrarProcesso').blur();
        //Exibe modal
        $('#modal-manutencao-processo').modal('show');
        //Foca no primeiro campo
        $('#form-processo input[name=CODIGO]').focus();
        $('#modal-conteudo-publicacao').addClass('z-index-100');
        modalProcessoCarregado = true;
    }finally {
        loadingDestroy('bloqueio-modal-conteudo-publicacao');
    }
}

/**
 * Carrega registros dos campos do tipo listagem
 * 
 * @param {string} input_codigo
 * @param {string} input_name
 * @param {string} rota
 * @param {string} campo_bd
 * @param {string} campo_id_bd
 */
async function buscaRegistroCampoListagemProcesso(input_codigo, input_name, rota, campo_bd = 'NOME', campo_id_bd = "CODIGO"){
    let codigo = $(`#form-processo input[name=${input_codigo}]`).val();
    let objeto = JSON.stringify(JSON.parse(`{"${campo_id_bd}" : ${codigo}}`));
    let dados  = await najProcesso.getData(`${baseURL}${rota}/show/${btoa(objeto)}`);
    $(`#form-processo input[name=${input_name}]`).val(dados[campo_bd]);
}

/**
 * Seta código e nome do registro nos respectivos campos ao clicar em um registro da caixa de pesquisa
 * 
 * @param {elemento} el
 * @param {string} input_nome
 * @param {string} input_id
 * @param {string} id_content
 */
async function onClickContentSelectAjax(el, input_nome, input_id, id_content) {
    var pai    = el.target.parentElement;
    if(!pai.getElementsByClassName('col-codigo')[0]) return;
    let codigo = pai.getElementsByClassName('col-codigo')[0].textContent;
    let nome   = pai.getElementsByClassName('col-name')[0].textContent;
    $(`#form-processo #${input_id}`).val(codigo);
    $(`#form-processo #${input_nome}`).val(nome);
    $(`#form-processo #${id_content}`).hide();
}

/**
 * Busca uma registro no BD através da rota show com base no código do registro
 * 
 * @param {string} input_codigo Campo do código no formulário
 * @param {string} input_name   Campo do nome no formulário
 * @param {string} rota         Rota da requisição
 * @param {string} campo_bd     Campo do nome no BD 'NOME' por default
 * @param {string} campo_id_bd  Campo do código no BD 'CODIGO' por default
 */
async function getShow(input_codigo, input_name, rota, campo_bd = 'NOME', campo_id_bd = "CODIGO") {
    //loadingStart('bloqueio-modal-manutencao-processo');
    //loaderOn('#loader-modal-manutencao-processo');
    let codigo = $(`#form-processo #${input_codigo}`).val();
    if(!codigo) {
        $(`#form-processo #${input_name}`).val('');
        return;
    }
    let objeto = JSON.stringify(JSON.parse(`{"${campo_id_bd}" : ${parseInt(codigo)}}`));
    let dados  = await najProcesso.getData(`${baseURL}${rota}/show/${btoa(objeto)}`);
    $(`#modal-manutencao-processo`).modal('show');
    if(!dados[campo_bd]) return;
    $(`#form-processo #${input_name}`).val(dados[campo_bd]);
    //loaderOff('#loader-modal-manutencao-processo');
    //loadingDestroy('bloqueio-modal-manutencao-processo');
}

/**
 * Realiza a busca de pessoas
 * 
 * @param {element} element
 * @param {bool}    onkeypress Verifica somente se o nome tiver acima de 3 caracteres
 * @param {string}  campo      Nome do campo (utilizado para externos)
 */
function getPessoas(element, onkeypress = true, campo) {
    if(onkeypress){
        if(element.value.length < 3) {
            return;
        }
        //loadingStart('bloqueio-modal-manutencao-processo');
        ////loaderOn('#loader-modal-manutencao-processo');
    }else{
        //loadingStart('bloqueio-modal-manutencao-processo');
        ////loaderOn('#loader-modal-manutencao-processo');
    }
    setTimeout(async function() {
        result = await getDataPessoas(element.value, false, campo);
        await carregaListagem(result, element.attributes['filled_field'].value);
        ////loaderOff('#loader-modal-manutencao-processo');
        //loadingDestroy('bloqueio-modal-manutencao-processo');
    }, 500);
}

/**
 * Realiza a busca de classes assim que o campo do nome estiver acima de 3 caracteres
 * 
 * @param {element} 
 */
function getClasses(element, onkeypress = true) {
    if(onkeypress){
        if(element.value.length < 3) {
            return;
        }
        //loadingStart('bloqueio-modal-manutencao-processo');
        ////loaderOn('#loader-modal-manutencao-processo');
    }else{
        //loadingStart('bloqueio-modal-manutencao-processo');
        ////loaderOn('#loader-modal-manutencao-processo');
    }
    setTimeout(async function() {
        result = await getDataClasses(element.value, false);
        await carregaListagem(result, element.attributes['filled_field'].value);
        ////loaderOff('#loader-modal-manutencao-processo');
        //loadingDestroy('bloqueio-modal-manutencao-processo');
    }, 500);
}

/**
 * Realiza a busca de cartórios assim que o campo do nome estiver acima de 3 caracteres
 * 
 * @param {element} 
 */
function getCartorios(element, onkeypress = true) {
    if(onkeypress){
        if(element.value.length < 3) {
            return;
        }
        //loadingStart('bloqueio-modal-manutencao-processo');
        ////loaderOn('#loader-modal-manutencao-processo');
    }else{
        //loadingStart('bloqueio-modal-manutencao-processo');
        ////loaderOn('#loader-modal-manutencao-processo');
    }
    setTimeout(async function() {
        result = await getDataCartorios(element.value, false);
        await carregaListagem(result, element.attributes['filled_field'].value);
        ////loaderOff('#loader-modal-manutencao-processo');
        //loadingDestroy('bloqueio-modal-manutencao-processo');
    }, 500);
}

/**
 * Realiza a busca de comarcas assim que o campo do nome estiver acima de 3 caracteres
 * 
 * @param {element} 
 */
function getComarcas(element, onkeypress = true) {
    if(onkeypress){
        if(element.value.length < 3) {
            return;
        }
        //loadingStart('bloqueio-modal-manutencao-processo');
        ////loaderOn('#loader-modal-manutencao-processo');
    }else{
        //loadingStart('bloqueio-modal-manutencao-processo');
        ////loaderOn('#loader-modal-manutencao-processo');
    }
    setTimeout(async function() {
        result = await getDataComarcas(element.value, false);
        await carregaListagem(result, element.attributes['filled_field'].value);
        ////loaderOff('#loader-modal-manutencao-processo');
        //loadingDestroy('bloqueio-modal-manutencao-processo');
    }, 500);
}

/**
 * Seta a listagem de pessoas na caixa de pesquisa de pessoas
 * 
 * @param {string} data
 * @param {string} id_content
 */
function carregaListagem(data, id_content)  {
    $(`#${id_content}`)[0].innerHTML = "";
    $(`#${id_content}`).append(data);
    $(`#${id_content}`).show();
}

/**
 * Busca pessoas no BD com base texto digitado no campo de nome da pessoa
 * 
 * @param {string} value
 * @param {bool}   filtra_pessoa_usuario
 * @param {string} campo
 */
async function getDataPessoas(value, filtra_pessoa_usuario = false, campo) {
    let zeroRegistros = `<div class="col-12">
                            <div class="row">
                                <button type="button" class="btn btnLightCustom" title="Gravar" style="margin: 4px 0px 5px 5px;" onclick="carregaModalManutencaoProcessoPessoaInclussao('${campo}')";"><i class="fas fa-plus"></i> Nova Pessoa</button>
                                <span class="span-contador-registros-table-input-ajax">Exibindo 0 registro(s)</span>
                            </div>
                        </div>`;

//    let zeroRegistros = `
//                        <div class="col-2 mr-1"></div>
//                        <div class="content-select-ajax-naj">
//                            <button type="button" class="btn btnLightCustom" title="Gravar" style="margin: 4px 0px 5px 5px;" onclick="carregaModalManutencaoProcessoPessoaInclussao()";"><i class="fas fa-plus"></i> Nova Pessoa</button>
//                            <span class="">Exibindo 0 registro(s)</span>
//                        </div>
//                        <i class="fas fa-search icon-search-input-naj text-white"></i>
//                        <div class="input-group-text ml-1 button-editar-pessoa text-white"><i class="fas fa-edit"></i></div>
//                        `;

//    let zeroRegistros = `
//                        <div class="col-2 mr-1"></div>
//                        <select>
//                            <option>
//                               <button type="button" class="btn btnLightCustom" title="Gravar" style="margin: 4px 0px 5px 5px;" onclick="carregaModalManutencaoProcessoPessoaInclussao()";"><i class="fas fa-plus"></i> Nova Pessoa</button>
//                               <span class="">Exibindo 0 registro(s)</span>
//                            </option>
//                            <option>
//                               <button type="button" class="btn btnLightCustom" title="Gravar" style="margin: 4px 0px 5px 5px;" onclick="carregaModalManutencaoProcessoPessoaInclussao()";"><i class="fas fa-plus"></i> Nova Pessoa</button>
//                               <span class="">Exibindo 0 registro(s)</span>
//                            </option>
//                        </select>
//                        <i class="fas fa-search icon-search-input-naj text-white"></i>
//                        <div class="input-group-text ml-1 button-editar-pessoa text-white"><i class="fas fa-edit"></i></div>
//                        `;

    if(value.length == 0){
        return zeroRegistros;
    }
    let url      = (filtra_pessoa_usuario) ? `${baseURL}pessoas/getPessoasUsuarioInFilter/${value}` : `${baseURL}pessoas/getPessoasFilter/${value}`;
    let response = await najProcesso.getData(url);
    let content  = '';
    let rows     = '';

    if(response.data.length > 0) {
        for(var i = 0; i < response.data.length; i++) {
            let cpfCnpj = (response.data[i].cpf) ? response.data[i].cpf : response.data[i].cnpj;
            rows += `
                <div class="row row-full content-rows-permissao" style="flex-wrap: nowrap !important;">
                    <div class="col-sm-1 col-icon d-flex align-items-center"><i class="far fa-hand-pointer"></i></div>
                    <div class="col-sm-0 col-codigo" style="display: none;">${response.data[i].pessoa_codigo}</div>
                    <div class="col-sm-5 col-name">${response.data[i].nome}</div>
                    <div class="col-sm-3 col-cpf">${cpfCnpj}</div>
                    <div class="col-sm-3 col-cidade-tarefa">${(response.data[i].cidade == null) ? '' : response.data[i].cidade}</div>
                </div>
            `;
        }
        content += `
            <div class="input-group col-12 p-0 m-0 content-table-input-ajax">
                ${rows}
            </div>
            <div class="input-group col-12 content-footer-table-input-ajax">
                <div class="row">
                    <button type="button" class="btn btnLightCustom" title="Gravar" style="margin: 4px 0px 5px 5px;" onclick="carregaModalManutencaoProcessoPessoaInclussao('${campo}')";"><i class="fas fa-plus"></i> Nova Pessoa</button>
                    <span class="span-contador-registros-table-input-ajax">Exibindo ${response.data.length} registro(s)</span>
                </div>
            </div>
        `;
    } else {
        content += zeroRegistros;
    }

    return content;
}

/**
 * Busca classes no BD com base texto digitado no campo de nome da classe
 * 
 * @param {string} value
 */
async function getDataClasses(value) {
    let zeroRegistros = `<div class="col-12">
                            <div class="row">
                                <button type="button" class="btn btnLightCustom" title="Gravar" style="margin: 4px 0px 5px 5px;" onclick="carregaModalManutencaoProcessoClasseInclussao();"><i class="fas fa-plus"></i> Nova Classe</button>
                                <span class="span-contador-registros-table-input-ajax">Exibindo 0 registro(s)</span>
                            </div>
                        </div>`;
    if(value.length == 0){
        return zeroRegistros;
    }
    let url      = `${baseURL}processos/classe/filter/${value}`;
    let response = await najProcesso.getData(url);
    let content  = '';
    let rows     = '';

    if(response.data.length > 0) {
        for(var i = 0; i < response.data.length; i++) {
            rows += `
                <div class="row row-full content-rows-permissao" style="flex-wrap: nowrap !important;">
                    <div class="col-sm-1 col-icon d-flex align-items-center"><i class="far fa-hand-pointer"></i></div>
                    <div class="col-sm-0 col-codigo" style="display: none;">${response.data[i].CODIGO}</div>
                    <div class="col-sm-5 col-name">${response.data[i].CLASSE}</div>
                    <div class="col-sm-3 col-tipo">${response.data[i].TIPO}</div>
                </div>
            `;
        }
        content += `
            <div class="input-group col-12 p-0 m-0 content-table-input-ajax">
                ${rows}
            </div>
            <div class="input-group col-12 content-footer-table-input-ajax">
                <div class="row">
                    <button type="button" class="btn btnLightCustom" title="Gravar" style="margin: 4px 0px 5px 5px;" onclick="carregaModalManutencaoProcessoClasseInclussao();"><i class="fas fa-plus"></i> Nova Classe</button>
                    <span class="span-contador-registros-table-input-ajax">Exibindo ${response.data.length} registro(s)</span>
                </div>
            </div>
        `;
    } else {
        content += zeroRegistros;
    }

    return content;
}

/**
 * Busca cartorios no BD com base texto digitado no campo de nome do cartorio
 * 
 * @param {string} value
 */
async function getDataCartorios(value) {
    let zeroRegistros = `<div class="col-12">
                            <div class="row">
                                <button type="button" class="btn btnLightCustom" title="Gravar" style="margin: 4px 0px 5px 5px;" onclick="carregaModalManutencaoProcessoCartorioInclussao()"><i class="fas fa-plus"></i> Novo Cartório</button>
                                <span class="span-contador-registros-table-input-ajax">Exibindo 0 registro(s)</span>
                            </div>
                        </div>`;
    if(value.length == 0){
        return zeroRegistros;
    }
    let url      = `${baseURL}processos/cartorio/filter/${value}`;
    let response = await najProcesso.getData(url);
    let content  = '';
    let rows     = '';

    if(response.data.length > 0) {
        for(var i = 0; i < response.data.length; i++) {
            rows += `
                <div class="row row-full content-rows-permissao" style="flex-wrap: nowrap !important;">
                    <div class="col-sm-1 col-icon d-flex align-items-center"><i class="far fa-hand-pointer"></i></div>
                    <div class="col-sm-0 col-codigo" style="display: none;">${response.data[i].CODIGO}</div>
                    <div class="col-sm-12 col-name">${response.data[i].CARTORIO}</div>
                </div>
            `;
        }
        content += `
            <div class="input-group col-12 p-0 m-0 content-table-input-ajax">
                ${rows}
            </div>
            <div class="input-group col-12 content-footer-table-input-ajax">
                <div class="row">
                    <button type="button" class="btn btnLightCustom" title="Gravar" style="margin: 4px 0px 5px 5px;" onclick="carregaModalManutencaoProcessoCartorioInclussao()"><i class="fas fa-plus"></i> Novo Cartório</button>
                    <span class="span-contador-registros-table-input-ajax">Exibindo ${response.data.length} registro(s)</span>
                </div>
            </div>
        `;
    } else {
        content += zeroRegistros;
    }

    return content;
}

/**
 * Busca comarcas no BD com base texto digitado no campo de nome da comarca
 * 
 * @param {string} value
 */
async function getDataComarcas(value) {
    let zeroRegistros = `<div class="col-12">
                            <div class="row">
                                <button type="button" class="btn btnLightCustom" title="Gravar" style="margin: 4px 0px 5px 5px;" onclick="carregaModalManutencaoProcessoComarcaInclussao()"><i class="fas fa-plus"></i> Nova Comarca</button>
                                <span class="span-contador-registros-table-input-ajax">Exibindo 0 registro(s)</span>
                            </div>
                        </div>`;
    if(value.length == 0){
        return zeroRegistros;
    }
    let url      = `${baseURL}processos/comarca/filter/${value}`;
    let response = await najProcesso.getData(url);
    let content  = '';
    let rows     = '';

    if(response.data.length > 0) {
        for(var i = 0; i < response.data.length; i++) {
            rows += `
                <div class="row row-full content-rows-permissao" style="flex-wrap: nowrap !important;">
                    <div class="col-sm-1 col-icon d-flex align-items-center"><i class="far fa-hand-pointer"></i></div>
                    <div class="col-sm-0 col-codigo" style="display: none;">${response.data[i].CODIGO}</div>
                    <div class="col-sm-9 col-name">${response.data[i].COMARCA}</div>
                    <div class="col-sm-3 col-uf">${response.data[i].UF}</div>
                </div>
            `;
        }
        content += `
            <div class="input-group col-12 p-0 m-0 content-table-input-ajax">
                ${rows}
            </div>
            <div class="input-group col-12 content-footer-table-input-ajax">
                <div class="row">
                    <button type="button" class="btn btnLightCustom" title="Gravar" style="margin: 4px 0px 5px 5px;" onclick="carregaModalManutencaoProcessoComarcaInclussao()"><i class="fas fa-plus"></i> Nova Comarca</button>
                    <span class="span-contador-registros-table-input-ajax">Exibindo ${response.data.length} registro(s)</span>
                </div>
            </div>
        `;
    } else {
        content += zeroRegistros;
    }

    return content;
}

/**
 * Carrega Modal Manutencao Pessoa no modo inclussão através do modal de Processo
 */
async function carregaModalManutencaoProcessoPessoaInclussao(campo){
    let nome_pessoa = $(`#form-processo ${campo}`).val();
    //loadingStart('bloqueio-modal-manutencao-processo');
    ////loaderOn('#loader-modal-manutencao-processo');
    sessionStorage.setItem('@NAJ_WEB/pessoa_action', 'create');
    await carregaModalManutencaoPessoa();
    $('#form-pessoa input[name=NOME]').val(nome_pessoa);
    $('#modal-manutencao-processo').addClass('z-index-100');
    ////loaderOff('#loader-modal-manutencao-processo');
    //loadingDestroy('bloqueio-modal-manutencao-processo');
}

/**
 * Carrega Modal Manutencao Pessoa no modo edição através do modal de Processo
 * 
 * @param {string} campo
 */
async function carregaModalManutencaoProcessoPessoaEdicao(campo){
    let codigo_pessoa = $(`#form-processo #${campo}`).val();
    if(codigo_pessoa.length == 0){
        NajAlert.toastWarning("Você deve primeiramente informar o código da pessoa!");
        return;
    }
    try{
        //loadingStart('bloqueio-modal-manutencao-processo');
        ////loaderOn('#loader-modal-manutencao-processo');
        let pessoa = await najProcesso.getData(`${baseURL}pessoas/show/${btoa(JSON.stringify({CODIGO: codigo_pessoa}))}`);
        if(typeof pessoa.CODIGO ==  "undefined"){
            NajAlert.toastWarning("O código informado não se refere a nenhuma pessoa do sistema!");
            return;
        }
        sessionStorage.setItem('@NAJ_WEB/pessoa_action', 'edit');
        sessionStorage.setItem('@NAJ_WEB/codigo_pessoa', codigo_pessoa);
        await carregaModalManutencaoPessoa();
        $('#modal-manutencao-processo').addClass('z-index-100');
    }finally{
        //loadingDestroy('bloqueio-modal-manutencao-processo');
        ////loaderOff('#loader-modal-manutencao-processo');
    }
}

/**
 * Carrega Modal Manutencao Processo Classe no modo inclussão através do modal de Processo
 */
async function carregaModalManutencaoProcessoClasseInclussao(){
    let nome_classe = $('#form-processo #nome_classe').val();
    //loadingStart('bloqueio-modal-manutencao-processo');
    ////loaderOn('#loader-modal-manutencao-processo');
    sessionStorage.setItem('@NAJ_WEB/processo_classe_action', 'create');
    await carregaModalManutencaoProcessoClasse();
    $('#form-processo-classe #nome_processo_classe').val(nome_classe);
    $('#modal-manutencao-processo').addClass('z-index-100');
    ////loaderOff('#loader-modal-manutencao-processo');
    //loadingDestroy('bloqueio-modal-manutencao-processo');
}

/**
 * Carrega Modal Manutencao Processo Classe no modo edição através do modal de Processo
 * 
 * @param {string} campo
 */
async function carregaModalManutencaoProcessoClasseEdicao(){
    let codigo = $(`#form-processo #codigo_classe`).val();
    if(codigo.length == 0){
        NajAlert.toastWarning("Você deve primeiramente informar o código da classe da ação!");
        return;
    }
    try{
        //loadingStart('bloqueio-modal-manutencao-processo');
        ////loaderOn('#loader-modal-manutencao-processo');
        let classe = await najProcesso.getData(`${baseURL}${rotaBaseProcessoClasse}/show/${btoa(JSON.stringify({CODIGO: codigo}))}`);
        if(typeof classe.CODIGO ==  "undefined"){
            NajAlert.toastWarning("O código informado não se refere a nenhuma classe da ação do sistema!");
            return;
        }
        sessionStorage.setItem('@NAJ_WEB/processo_classe_action', 'edit');
        await carregaModalManutencaoProcessoClasse(classe);
        $('#modal-manutencao-processo').addClass('z-index-100');
    }finally{
        ////loaderOff('#loader-modal-manutencao-processo');
        //loadingDestroy('bloqueio-modal-manutencao-processo');
    }
}

/**
 * Carrega Modal Manutencao Processo Cartório no modo inclussão através do modal de Processo
 */
async function carregaModalManutencaoProcessoCartorioInclussao(){
    let nome_cartorio = $('#form-processo #nome_cartorio').val();
    //loadingStart('bloqueio-modal-manutencao-processo');
    ////loaderOn('#loader-modal-manutencao-processo');
    sessionStorage.setItem('@NAJ_WEB/processo_cartorio_action', 'create');
    await carregaModalManutencaoProcessoCartorio();
    $('#form-processo-cartorio #nome_processo_cartorio').val(nome_cartorio);
    $('#modal-manutencao-processo').addClass('z-index-100');
    ////loaderOff('#loader-modal-manutencao-processo');
    //loadingDestroy('bloqueio-modal-manutencao-processo');
}

/**
 * Carrega Modal Manutencao Processo Cartório no modo edição através do modal de Processo
 * 
 * @param {string} campo
 */
async function carregaModalManutencaoProcessoCartorioEdicao(){
    let codigo = $(`#form-processo #codigo_cartorio`).val();
    if(codigo.length == 0){
        NajAlert.toastWarning("Você deve primeiramente informar o código do cartório!");
        return;
    }
    try{
        ////loaderOn('#loader-modal-manutencao-processo');
        //loadingStart('bloqueio-modal-manutencao-processo');
        let cartorio = await najProcesso.getData(`${baseURL}${rotaBaseProcessoCartorio}/show/${btoa(JSON.stringify({CODIGO: codigo}))}`);
        if(typeof cartorio.CODIGO ==  "undefined"){
            NajAlert.toastWarning("O código informado não se refere a nenhuma cartório do sistema!");
            return;
        }
        sessionStorage.setItem('@NAJ_WEB/processo_cartorio_action', 'edit');
        await carregaModalManutencaoProcessoCartorio(cartorio);
        $('#modal-manutencao-processo').addClass('z-index-100');
    }finally{
        ////loaderOff('#loader-modal-manutencao-processo');
        //loadingDestroy('bloqueio-modal-manutencao-processo');
    }
}

/**
 * Carrega Modal Manutencao Processo Comarca no modo inclussão através do modal de Processo
 */
async function carregaModalManutencaoProcessoComarcaInclussao(){
    let nome_comarca = $('#form-processo #nome_comarca').val();
    //loadingStart('bloqueio-modal-manutencao-processo');
    ////loaderOn('#loader-modal-manutencao-processo');
    sessionStorage.setItem('@NAJ_WEB/processo_comarca_action', 'create');
    await carregaModalManutencaoProcessoComarca();
    $('#form-processo-comarca #nome_processo_comarca').val(nome_comarca);
    $('#modal-manutencao-processo').addClass('z-index-100');
    ////loaderOff('#loader-modal-manutencao-processo');
    //loadingDestroy('bloqueio-modal-manutencao-processo');
}

/**
 * Carrega Modal Manutencao Processo Comarca no modo edição através do modal de Processo
 * 
 * @param {string} campo
 */
async function carregaModalManutencaoProcessoComarcaEdicao(){
    let codigo = $(`#form-processo #codigo_comarca`).val();
    if(codigo.length == 0){
        NajAlert.toastWarning("Você deve primeiramente informar o código da comarca!");
        return;
    }
    try{
        //loadingStart('bloqueio-modal-manutencao-processo');
        //loaderOn('#loader-modal-manutencao-processo');
        let comarca = await najProcesso.getData(`${baseURL}${rotaBaseProcessoComarca}/show/${btoa(JSON.stringify({CODIGO: codigo}))}`);
        if(typeof comarca.CODIGO ==  "undefined"){
            NajAlert.toastWarning("O código informado não se refere a nenhuma comarca do sistema!");
            return;
        }
        sessionStorage.setItem('@NAJ_WEB/processo_comarca_action', 'edit');
        await carregaModalManutencaoProcessoComarca(comarca);
        $('#modal-manutencao-processo').addClass('z-index-100');
    }finally{
        //loaderOff('#loader-modal-manutencao-processo');
        //loadingDestroy('bloqueio-modal-manutencao-processo');
    }
}

/**
 * Grava dados da tela de manutenção
 */
function gravarDadosProcesso(){
    let step1 = true;
    let step2 = true;
    let step3 = true;
    //Valida form
    if($('#form-processo input[name=CLASSE]').val() != ""){
        $('#form-processo input[name=CODIGO_CLASSE]').attr('required',true);
        if($('#form-processo input[name=CODIGO_CLASSE]').val() == ""){
            NajAlert.toastWarning('A classe da ação informada deve conter um código válido');
            step1 = false;
        }
    }else{
        $('#form-processo input[name=CODIGO_CLASSE]').removeAttr('required');
    }
    if($('#form-processo input[name=nome_comarca]').val() != ""){
        $('#form-processo input[name=CODIGO_COMARCA]').attr('required',true);
        if($('#form-processo input[name=CODIGO_COMARCA]').val() == ""){
            NajAlert.toastWarning('A comarca informada deve conter um código válido');
            step2 = false;
        }
    }else{
        $('#form-processo input[name=CODIGO_COMARCA]').removeAttr('required');
    }
    if($('#form-processo input[name=CARTORIO]').val() != ""){
        $('#form-processo input[name=CODIGO_CARTORIO]').attr('required',true);
        if($('#form-processo input[name=CODIGO_CARTORIO]').val() == ""){
            NajAlert.toastWarning('O cartório informado deve conter um código válido');
            step3 = false;
        }
    }else{
        $('#form-processo input[name=CODIGO_CARTORIO]').removeAttr('required');
    }
    result = validaForm('form-processo');
    if(result && step1 && step2 && step3){
        //Obtêm dados do form
        var dados = getDadosFormProcesso();
        createOrUpdateProcesso(dados);
    }
}

/**
 * Obtêm dados do formulário
 * 
 * @returns {getDadosForm.dados}
 */
function getDadosFormProcesso() {
    let dados = {
        'CODIGO': parseInt($('#form-processo input[name=CODIGO]').val()),
        'DATA_CADASTRO': formatDate($('#form-processo input[name=DATA_CADASTRO]').val(), false),
        'CODIGO_CLIENTE': parseInt($('#form-processo input[name=CODIGO_CLIENTE]').val()),
        'QUALIFICA_CLIENTE': $('#form-processo select[name=QUALIFICA_CLIENTE]').val(),
        'CODIGO_ADVERSARIO': parseInt($('#form-processo input[name=CODIGO_ADVERSARIO]').val()),
        'QUALIFICA_ADVERSARIO': $('#form-processo select[name=QUALIFICA_ADVERSARIO]').val(),
        'CODIGO_ADV_CLIENTE': parseInt($('#form-processo input[name=CODIGO_ADV_CLIENTE]').val()),
        'NUMERO_PROCESSO_NEW': $('#form-processo input[name=NUMERO_PROCESSO_NEW]').val(),
        'NUMERO_PROCESSO_NEW2': removeFormatacaoCNJ($('#form-processo input[name=NUMERO_PROCESSO_NEW]').val()),
        'GRAU_JURISDICAO': $('#form-processo select[name=GRAU_JURISDICAO]').val(),
        'DATA_DISTRIBUICAO': formatDate($('#form-processo input[name=DATA_DISTRIBUICAO]').val(), false),
        'VALOR_CAUSA': $('#form-processo input[name=VALOR_CAUSA]').val() != "" ? convertMoneyToFloat($('#form-processo input[name=VALOR_CAUSA]').val()) : 0.00,
        'CODIGO_CLASSE': parseInt($('#form-processo input[name=CODIGO_CLASSE]').val()),
        'CODIGO_CARTORIO': parseInt($('#form-processo input[name=CODIGO_CARTORIO]').val()),
        'ID_AREA_JURIDICA': parseInt($('#form-processo select[name=ID_AREA_JURIDICA]').val()),
        'CODIGO_COMARCA': parseInt($('#form-processo input[name=CODIGO_COMARCA]').val()),
        'PEDIDOS_PROCESSO': $('#form-processo textarea[name=PEDIDOS_PROCESSO]').val(),
        'CODIGO_DIVISAO': parseInt($('#form-processo select[name=CODIGO_DIVISAO]').val()),
        'CODIGO_SITUACAO': parseInt($('#form-processo select[name=CODIGO_SITUACAO]').val()),
    };
    return dados;
}

/**
 * Insere ou atualiza registro conforme a ação selecionada
 */
async function createOrUpdateProcesso(dados) {
    try{
        //Bloqueia o modal de manutenção
        //loaderOn('#loader-modal-manutencao-processo');
        //loadingStart('bloqueio-modal-manutencao-processo');
        if (sessionStorage.getItem('@NAJ_WEB/processo_action') == 'create') {
            //Armazena o processo no BD
            await najProcesso.store(`${baseURL}${rotaBaseProcesso}`, dados);
            //Vincula o processo a publicação em "monitora_termo_processo"
            await najProcesso.updateData(`${baseURL}${rotaBaseDiario}/processo/` + btoa(JSON.stringify({'id':tableDiario.data.resultado[indexDiario].id_processo})), {'codigo_processo':dados.CODIGO});
            //Total de publicações correntes
            let totalBefore = tableDiario.data.total;
            await buscaTodasPublicacoesMD();
            //Total de publicações com o filtro todas as publicações
            let totalAllPubliMD  = tableDiario.data.total;
            //Se a pesquisa for do tipo "não lidos" ou "pendentes"
            if(filtroNaoLidos == false|| filtroPendentes == false){
                //Precisamos saber qual o index que o modal terá com o filtro todas as publicações
                let indexDiario = totalAllPubliMD - totalBefore - totalPublicacoesDescartadasMD + indexDiario;
            }
            await carregaModalConteudoPublicacao(null, indexDiario);
            setSelectedOptionMenuMD();
            $('#modal-manutencao-processo').modal('hide');
        } else if (sessionStorage.getItem('@NAJ_WEB/processo_action') == 'edit') {
            await najProcesso.update(`${baseURL}${rotaBaseProcesso}/${btoa(JSON.stringify({CODIGO: dados.CODIGO}))}`, dados);
        }
    }catch (e){
        NajAlert.toastError(e);
    }finally {
        //Desbloqueia o modal de manutenção
        //loaderOff('#loader-modal-manutencao-processo');
        //loadingDestroy('bloqueio-modal-manutencao-processo');
    }
}