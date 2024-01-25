class ValidacaoProcessosTable extends Table {

    constructor() {
        super();
        
        this.target           = 'datatable-validacao-processos';
        this.name             = 'Validação Processos';
        this.route            = `validacao/processos`;
        this.key              = ['CODIGO'];
        this.openLoaded       = false; //Não carregar dados inicialmente 
        this.isItDestructible = true;
        this.isItEditable     = true;
        this.defaultFilters   = false;
        this.showTitle        = false;
        //Precisamos sobreescrver o onEdit se não irá executar o onEdit da classe mãe
        this.onEdit           = function() {
        };
        
        this.addField({
            name: 'CODIGO',
            title: 'Status',
            width: 20,
            onLoad: (data,linha) =>  {
                let result = '';
                if(data != null){
                    if(linha.MONITORADO){
                        if(linha.STATUS_MONITORAMENTO == 'ATIVO'){
                            result = `<span id="" class="badge badge-info text-white font-normal badge-pill ml-2">MONITORAMENTO ATIVO</span>`;
                        }else if(linha.STATUS_MONITORAMENTO == 'BAIXADO'){
                            result = `<span id="" class="badge badge-secondary text-white font-normal badge-pill ml-2">MONITORAMENTO BAIXADO</span>`;
                        }
                    }else{
                        if(!linha.CNJ_VALIDO){
                            result = `<span id="" class="badge badge-danger text-white font-normal badge-pill ml-2 mb-1">CNJ INVÁLIDO</span>`;
                        }
                        if(linha.REVISAR_INSTANCIA){
                            result += `<span id="" class="badge badge-warning text-white font-normal badge-pill ml-2">REVISAR INSTÂNCIA</span>`;
                        }
                        if(linha.CNJ_VALIDO && !linha.REVISAR_INSTANCIA){
                            result = `<span id="" class="badge badge-success text-white font-normal badge-pill ml-2">DISPONÍVEL</span>`;
                        }
                    }
                }
                result =  `
                            <table>
                                <tbody>
                                    <tr>
                                        <td>
                                            ${result}
                                        </td>
                                    </tr>
                                <tbody>
                            </table>`;
                return result;
            }
        });
        
        this.addField({
            name: 'CODIGO',
            title: 'Informações do Processo',
            width: 40,
            onLoad: (data,linha) =>  {
                let result = '';
                if(data != null){
                    let codigo_processo = linha.CODIGO              ? `Código: ${linha.CODIGO} <i class="onClickFichaProcessoVP font-16 mdi mdi-open-in-new cursor-pointer text-dark" data-toggle="tooltip" data-placement="top" title="Ver ficha do processo"></i>` : "";
                    let CARTORIO        = linha.CARTORIO            ? linha.CARTORIO + " - "    : "";
                    let COMARCA         = linha.COMARCA             ? linha.COMARCA             : "";
                    let COMARCA_UF      = linha.COMARCA_UF          ? "-" + linha.COMARCA_UF    : "";
                    let numero_cnj      = linha.NUMERO_PROCESSO_NEW ? linha.NUMERO_PROCESSO_NEW : "";
                    let CLASSE          = linha.CLASSE              ? linha.CLASSE              : "";
                    let instancia       = linha.GRAU_JURISDICAO     ? linha.GRAU_JURISDICAO     : "";
                    let btnCopiarCNJ    = linha.NUMERO_PROCESSO_NEW ? `<i class="tooltip-naj far fa-copy cursor-pointer text-dark" data-toggle="tooltip" data-placement="top" title="" onclick="copiarTextoParaAreaDeTranferencia('${numero_cnj}','Número CNJ copiado para a área de transferência!')" title="Copiar número do CNJ"></i>` : "";
                    let situacao        = linha.SITUACAO == 'BAIXADO'? `<span id="" class="badge badge-danger text-white font-normal badge-pill">BAIXADO</span>` : "";
                    result +=  ` 
                    <span class="row">
                        <span class="col-12 text-uppercase font-medium">${CARTORIO} ${COMARCA} ${COMARCA_UF}</span>
                        <span class="col-12 text-uppercase">${numero_cnj} ${btnCopiarCNJ}</span>
                        <span class="col-12 text-uppercase">${CLASSE}</span>
                        <span class="col-12 text-uppercase">${codigo_processo} ${instancia} &nbsp; ${situacao}</span>
                    </span>`;
                }
                return result;
            }
        });
        
        this.addField({
            name: 'CODIGO',
            title: 'Envolvidos',
            width: 30,
            onLoad: (data,linha) =>  {
                try{
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
                                        info += `<i class="tooltip-naj fas fa-info-circle" style="font-size: 14px;" data-toggle="tooltip" data-placement="top" title="${grupo_cliente[i].NOME.toUpperCase()}"></i>`;
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
                }catch (e){
                    console.log(e);
                }
            }
        });
        
    }
}
