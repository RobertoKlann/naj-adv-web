class MonitoramentoTribunalTable extends Table {

    constructor() {
        super();
        
        this.target           = 'datatable-monitoramento-tribunal';
        this.name             = 'Monitoramento Tribunal';
        this.route            = `monitoramento/tribunais`;
        this.key              = ['id'];
        this.openLoaded       = true; //Não carregar dados inicialmente 
        this.isItDestructible = true;
        this.isItEditable     = true;
        this.defaultFilters   = false;
        this.showTitle        = false;
        //Precisamos sobreescrver o onEdit se não irá executar o onEdit da classe mãe
        this.onEdit           = function() {
        };

        this.addField({
            name: 'id_mpt',
            title: 'Monitoramentos',
            width: 35,
            onLoad: (data,linha) =>  {
                let result = '';
                if(data != null){
                    let status_palavras             = ['PENDENTE', 'SUCESSO', 'ERRO'];
                    let status_labels               = ['warning', 'success', 'danger'];
                    let data_ultimo_andamento       = linha.data_ultimo_andamento     ? formatDate(linha.data_ultimo_andamento)         : "--/--/--";
                    let qtde_total_andamentos       = linha.qtde_total_andamentos     ? linha.qtde_total_andamentos                     : 0;
                    let qtde_novas_andamentos       = linha.qtde_novas_andamentos     ? linha.qtde_novas_andamentos                     : 0;
                    let sigla_tribunal              = linha.sigla_tribunal            ? linha.sigla_tribunal                            : "---";
                    let nome_tribunal               = linha.nome_tribunal             ? linha.nome_tribunal                            : "---";
                    let status_palavra_ultima_busca = linha.status_code_ultima_busca  ? status_palavras[linha.status_code_ultima_busca] : "---";
                    let status_msg_ultima_busca     = linha.status_msg_ultima_busca   ? linha.status_msg_ultima_busca                   : "";
                    let status_label                = linha.status_code_ultima_busca  ? status_labels[linha.status_code_ultima_busca]   : "secondary";
                    let conteudo_ultimo_andamento   = linha.conteudo_ultimo_andamento ? linha.conteudo_ultimo_andamento                 : "";
                    let status_msg_ultima_busca_title   = "";
                    //Limita o "conteudo_ultimo_andamento" a 150 carácteres
                    if(conteudo_ultimo_andamento.length > 150){
                        conteudo_ultimo_andamento = conteudo_ultimo_andamento.substring(0,150) + ` ...`;
                    }
                    if(status_msg_ultima_busca.length > 150){
                        status_msg_ultima_busca_title = status_msg_ultima_busca.substring(0,150) + "...";
                    }else{
                        status_msg_ultima_busca_title = status_msg_ultima_busca;
                    }
                    result = `
                        <span class="row">
                            <span class="row ml-3" style="width: 100%">
                                    <span class="text-uppercase font-10" style="width: 20%">Última</span>
                                    <span class="text-uppercase font-10" style="width: 34%">Resultados</span>
                                    <span class="text-uppercase font-10" style="width: 14%">Tribunal</span>
                                    <span class="text-uppercase font-10" style="width: 25%">Monitoramento</span>
                            </span>
                            <span class="row ml-3" style="width: 100%">
                                    <span class="font-medium" style="width: 20%">${data_ultimo_andamento}</span>
                                    <span class="" style="width: 18%">
                                            <button type="button" class="btn btn-sm waves-effect waves-light btn-rounded btn-outline-dark btnVerConteudoMovimentacaoProcesso"><i class="fas fa-search mr-1"></i>Ver</button>&nbsp;
                                    </span>
                                    <span class="" style="width: 16%">
                                        <span id="qtde_total_andamentos_${linha.id_mpt}" class="font-medium">${qtde_total_andamentos}</span>
                                        <span id="qtde_novas_andamentos_${linha.id_mpt}" class="badge badge-warning font-medium" ${qtde_novas_andamentos == 0 ? 'hidden' : ''}>${qtde_novas_andamentos}</span>
                                    </span>
                                    <span class="" style="width: 14%">
                                        <span class="tooltip-naj  badge text-white blue-grey-text text-darken-4 font-normal badge-pill badge-secondary" data-toggle="tooltip" data-placement="top" title="${nome_tribunal}">${sigla_tribunal}</span>
                                    </span>
                                    <span class="" style="width: 20%">
                                        <span id="btnStatus_${linha.id_mpt}" class="tooltip-naj verStatus badge text-white blue-grey-text text-darken-4 font-normal badge-pill badge-${status_label}" data-toggle="tooltip" data-placement="top" title='${status_msg_ultima_busca_title}'>${status_palavra_ultima_busca}</span>
                                    </span>
                            </span>
                            <span class="row ml-3 text-uppercase text-black" style="width: 90%">${conteudo_ultimo_andamento}</span>
                        <span>
                    `;
                }
                return result;
            }
        });

        this.addField({
            name: 'NOME_CLIENTE',
            title: 'Envolvido(s)',
            width: 30,
            onLoad: (data,linha) =>  {
                let result = '';
                if(data != null){
                    let NOME_CLIENTE       = linha.NOME_CLIENTE     ? linha.NOME_CLIENTE     : "";
                    let NOME_ADVERSARIO    = linha.NOME_ADVERSARIO  ? linha.NOME_ADVERSARIO  : "";
                    let QTDE_ADVERSARIOS   = linha.QTDE_ADVERSARIOS ? linha.QTDE_ADVERSARIOS : 0;
                    let QTDE_CLIENTES      = linha.QTDE_CLIENTES    ? linha.QTDE_CLIENTES    : 0;
                    let badgeGrupoCliente    = "";
                    let badgeGrupoAdversario = "";
                    let listaGrupoCliente    = "";
                    let listaGrupoAdversario = "";
                    if(QTDE_CLIENTES > 0){
                        if(linha.envolvidos_grupo_cliente){
                            badgeGrupoCliente = `
                                                <span class="row pl-3">
                                                    <span class="tooltip-naj badge text-white blue-grey-text text-darken-4 font-normal badge-pill badge-secondary" data-toggle="tooltip" data-placement="top" title="Clique aqui para ver os demais envolvidos do grupo cliente." onclick="exibeOcultaGrupoCliente(${`${linha.codigo_processo}`})">+${QTDE_CLIENTES} Envolvido(s)</span>
                                                    <i id="btn_exibe_oculta_grupo_cliente_prc_${linha.codigo_processo}" class="tooltip-naj fas fa-arrow-circle-right font-16 exibe-oculta-demais-envolvidos" title="Clique aqui para ver os demais envolvidos do grupo cliente." data-toggle="tooltip" onclick="exibeOcultaGrupoCliente(${`${linha.codigo_processo}`})"></i>
                                                </span>`;
                            //Monta lista do grupo de clientes
                            let grupo_cliente = linha.envolvidos_grupo_cliente;
                            for(let i = 0; i < grupo_cliente.length; i++){
                                let qualificacao = grupo_cliente[i].QUALIFICACAO != null ? `(${grupo_cliente[i].QUALIFICACAO})` : ``;
                                let nome = "";
                                let info = "";
                                if(grupo_cliente[i].NOME.length > 40){
                                    nome = `${grupo_cliente[i].NOME.substr(0,40).toUpperCase()}...`;
                                    info += `<i class="tooltip-naj fas fa-info-circle" style="font-size: 14px;" data-toggle="tooltip" data-placement="top" title="${grupo_cliente[i].NOME.toUpperCase()}"></i>`;
                                }else{
                                    nome = grupo_cliente[i].NOME.toUpperCase();
                                }
                                listaGrupoCliente = listaGrupoCliente + `<span class="row pl-3">${nome} ${info} ${qualificacao}</span>`;
                            }
                        }
                    }
                    if(QTDE_ADVERSARIOS > 0){
                        if(linha.envolvidos_grupo_adversario){
                            badgeGrupoAdversario = `
                                            <span class="row pl-3">
                                                <span class="tooltip-naj badge text-white blue-grey-text text-darken-4 font-normal badge-pill badge-secondary" data-toggle="tooltip" data-placement="top" title="Clique aqui para ver os demais envolvidos do grupo adversário." onclick="exibeOcultaGrupoAdversario(${`${linha.codigo_processo}`})">+${QTDE_ADVERSARIOS} Envolvido(s)</span>
                                                <i id="btn_exibe_oculta_grupo_adversario_prc_${linha.codigo_processo}" class="tooltip-naj fas fa-arrow-circle-right font-16 exibe-oculta-demais-envolvidos" title="Clique aqui para ver os demais envolvidos do grupo adversário." data-toggle="tooltip" onclick="exibeOcultaGrupoAdversario(${`${linha.codigo_processo}`})"></i>
                                            </span>`;
                            //Monta lista do grupo de adversários
                            let grupo_adversario = linha.envolvidos_grupo_adversario;
                            for(let i = 0; i < grupo_adversario.length; i++){
                                let qualificacao = grupo_adversario[i].QUALIFICACAO != null ? `(${grupo_adversario[i].QUALIFICACAO})` : ``;
                                let nome = "";
                                let info = "";
                                if(grupo_adversario[i].NOME.length > 40){
                                    nome = `${grupo_adversario[i].NOME.substr(0,40).toUpperCase()}...`;
                                    info += `<i class="tooltip-naj fas fa-info-circle" style="font-size: 14px;" data-toggle="tooltip" data-placement="top" title="${grupo_adversario[i].NOME.toUpperCase()}"></i>`;
                                }else{
                                    nome = grupo_adversario[i].NOME.toUpperCase();
                                }
                                listaGrupoAdversario = listaGrupoAdversario + `<span class="row pl-3">${nome} ${info} ${qualificacao}</span>`;
                            }
                        }
                    }
                    result +=  `
                        <span class="row">
                            <span class="col-12 text-uppercase font-medium">${NOME_CLIENTE}</span>
                            <span class="col-12">${badgeGrupoCliente}</span>
                            <span id="lista_grupo_cliente_prc_${linha.codigo_processo}" class="col-12" style="display:none">${listaGrupoCliente}</span>
                            <span class="col-12 text-uppercase">${NOME_ADVERSARIO}</span>
                            <span class="col-12">${badgeGrupoAdversario}</span>
                            <span id="lista_grupo_adversario_prc_${linha.codigo_processo}" class="col-12" style="display:none">${listaGrupoAdversario}</span>
                        </span>`;
                }
                return result;
            }
        });
        
        this.addField({
            name: 'codigo_processo',
            title: 'Informações do Processo',
            width: 25,
            onLoad: (data,linha) =>  {
                let result = '';
                if(data != null){
                    let codigo_processo = linha.codigo_processo ? `Código: ${linha.codigo_processo} <i class="onClickFichaProcessoMT font-16 mdi mdi-open-in-new cursor-pointer text-dark" data-toggle="tooltip" data-placement="top" title="Ver ficha do processo"></i>` : "";
                    let CARTORIO        = linha.CARTORIO        ? linha.CARTORIO + " - " : "";
                    let COMARCA         = linha.COMARCA         ? linha.COMARCA          : "";
                    let COMARCA_UF      = linha.COMARCA_UF      ? "-" + linha.COMARCA_UF : "";
                    let numero_cnj      = linha.numero_cnj      ? linha.numero_cnj       : "";
                    let CLASSE          = linha.CLASSE          ? linha.CLASSE           : "";
                    let instancia       = linha.instancia       ? linha.instancia        : "";
                    let btnCopiarCNJ    = linha.numero_cnj      ? `&nbsp;<i class="tooltip-naj btnCopiarCNJ far fa-copy cursor-pointer text-dark" data-toggle="tooltip" data-placement="top" title="" title="Copiar número do CNJ"></i>` : "";
                    //let btnCopiarCNJ  = linha.numero_cnj      ? `&nbsp;<span class="tooltip-naj badge text-white blue-grey-text text-darken-4 font-normal badge-pill badge-success" data-toggle="tooltip" data-placement="top" title="Copiar Número do CNJ" onclick="copiarTextoParaAreaDeTranferencia('${numero_cnj}','Número CNJ copiado para a área de transferência!')">Copiar <i class="far fa-copy cursor-pointer text-dark" ></i></span>` : "";
                    result +=  ` 
                    <span class="row">
                        <span class="col-12 text-uppercase font-medium">${CARTORIO} ${COMARCA} ${COMARCA_UF}</span>
                        <span class="col-12 text-uppercase">${numero_cnj}${btnCopiarCNJ}</span>
                        <span class="col-12 text-uppercase">${CLASSE}</span>
                        <span class="col-12 text-uppercase">${codigo_processo} ${instancia}</span>
                    </span>`;
                }
                return result;
            }
        });
        
        this.addAction({
            name: 'buscarMovimentacoes',
            title: 'Buscar Movimentações',
            icon: 'fas fa-cloud-download-alt remove-btn-icon',
            onValidate: () => {
                if(
                    (
                        filtroNovasMovimetacoes      == false &&
                        filtroBuscasAndamentos       == false &&
                        filtroErroUltimaBusca        == false &&
                        filtroSemMovimentacoes       == false &&
                        filtroMonitoramentosBaixados == false 
                    ) 
                    || 
                    filtroNovasMovimetacoes == true
                ){
                    return true
                }else{
                    return false
                }
            },
            onClick: async () => {
                await obterMovimentacoesMPT();
            }    
        });
        
        this.addAction({
            name: 'buscarPendentes',
            title: 'Buscar Pendentes',
            icon: 'fas fa-cloud-download-alt remove-btn-icon',
            onValidate: () => {
                if(filtroBuscasAndamentos == true){
                    return true
                }else{
                    return false
                }
            },
            onClick: async () => {
                await obterPendentesMPT();
            }    
        });
        
        this.addAction({
            name: 'marcarTodosComoLido',
            title: 'Marcar Todos Como Lido',
            icon: 'mdi mdi-flag remove-btn-icon',
            onValidate: () => true,
            onClick: async () => {
                await marcaTodosComoLidosMT();
            }    
        });
        
        this.addAction({
            name: 'novoMonitoramento',
            title: 'Novo Monitoramento',
            icon: 'fas fa-plus remove-btn-icon',
            onValidate: () => true,
            onClick: () => {
                carregaModalManutencaoMPTinclussao();
            }    
        });
        
        this.addAction({
            name: 'buscasPadrao',
            title: 'Buscas Padrão',
            icon: 'fas fa-tasks remove-btn-icon',
            onValidate: () => true,
            onClick: () => {
                carregaModalBP();
            }    
        });
        
        this.addAction({
            name: 'quotaDeBuscas',
            title: 'Quota de Buscas',
            icon: 'fas fa-tasks remove-btn-icon',
            onValidate: () => true,
            onClick: () => {
                carregaModalManutencaoQuotaDeBuscas();
            }    
        });
        
        this.addAction({
            name: 'validacaoprocessos',
            title: 'Validação Processos',
            icon: 'fas fa-tasks remove-btn-icon',
            onValidate: () => true,
            onClick: () => {
                carregaModalValidacaoProcessos();
            }    
        });
        
        this.addAction({
            name: 'forcarbuscascomerro',
            title: 'Forçar Busca Dos Com Erro',
            icon: 'fas fa-cloud-download-alt remove-btn-icon',
            onValidate: () => {
                if(filtroErroUltimaBusca == true){
                    return true
                }else{
                    return false
                }
            },
            onClick: async () => {
                await sweetAlertForcarBuscaParaMonitoramentosComErroNaUltimaBusca();
            }    
        });
        
    }
    
}
