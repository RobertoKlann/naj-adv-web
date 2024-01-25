class MonitoramentoDiarioTable extends Table {

    constructor() {
        super();
        
        this.target           = 'datatable-monitoramento-diario';
        this.name             = 'Monitoramento Diário';
        this.route            = `monitoramento/diarios`;
        this.key              = ['id'];
        this.openLoaded       = true; //Não carregar dados inicialmente 
        this.isItDestructible = true;
        this.isItEditable     = false;
        this.defaultFilters   = false;
        this.showTitle        = false;
        var countLinha        = 0;

        this.addField({
            name: 'data_hora_inclusao',
            title: 'Cadastro',
            width: 7.5,
            onLoad:(data) => {
                return formatDate(data.substr(0,10))
            }
            
        });
        
        this.addField({
            name: 'data_disponibilizacao',
            title: 'Disponibiliz.',
            width: 7.5,
            onLoad:(data) => formatDate(data)
            
        });
        
        this.addField({
            name: 'data_publicacao',
            title: 'Publicação',
            width: 7.5,
            onLoad:(data) => formatDate(data)
            
        });
        
        this.addField({
            name: 'id_diario',
            title: 'Diário Oficial',
            width: 32.5,
            onLoad: (data,linha) =>  {
                let result = '';
                if(data != null){
                    //Verifica quais atributos serão apresentados
                    let tipoRegistro = linha.id_processo != null ? `` : `<span class="badge badge-pill badge-secondary">Citação</span>`;
                    let linha1 = `<span class="font-medium ">${linha.secao ? linha.secao : linha.diario_nome}</span><br>`;
                    let linha2 = `<span class="text-muted">${linha.tipo  ? linha.tipo  : linha.diario_competencia}</span><br>`;
                    let linha3 = `<button type="button" class="btnLeiaNaIntegra btn btn-sm waves-effect waves-light btn-rounded btn-outline-dark" data-id-movimentacao="${linha.id}" data-index-linha="${countLinha}" data-toggle="modal" data-target="#intimacao_content1"><i class="fas fa-search mr-1"></i>Ver</button>&emsp;`;
                        linha3 += `<span class="font-medium">Página: ${linha.pagina}</span>&emsp;${tipoRegistro} &emsp;`;
                        linha3 += linha.lido == "N"  ? `<span id="tag-new-${linha.id}" class="badge text-white font-normal badge-pill badge-warning blue-grey-text text-darken-4 mr-2">Nova</span>`: "";
                        result +=  `
                            <table>
                                <tbody>
                                    <tr>
                                        <td>
                                            ${linha1}
                                            ${linha2}
                                            ${linha3}
                                        </td>
                                    </tr>
                                <tbody>
                            </table>`;
                }
                //verifica se a linha corrente é inferior ao total de linhas por página
                if(countLinha < tableDiario.data.resultado.length - 1){
                    //Incrementa o contador de linha
                    countLinha++;
                } else {
                    //Reseta o contador de linha
                    countLinha = 0;
                }
                return result;
            }
        });
        
        this.addField({
            name: 'termo_pesquisa',
            title: 'Envolvido(s)',
            width: 20,
            onLoad: (data,linha) =>  {
                if(data != null){
                    let result = '';
                    //Verifica primeiramente se tem processo
                    if(linha.id_processo != null){
                        //Verifica se tem envolvidos, se houver exibe os mesmos
                        if(linha.processo.envolvidos.length > 0){
                            //Extrai envolvidos
                            let linha1         = '';
                            let termo_pesquisa = '';
                            let envolvidos     = linha.processo.envolvidos;
                            //Verifica se a propriedade exixte no objeto
                            if('termo_pesquisa' in linha){
                                if(linha.termo_pesquisa != null){
                                    termo_pesquisa = linha.termo_pesquisa;
                                }
                            }
                            if(linha.processo.envolvidos.length >= 2){
                                //Ordena os envolvidos
                                envolvidos = sortEnvolvidosPorAdvogado(envolvidos, termo_pesquisa);
                            }
                            //Percorre pelos envolvidos
                            for(let i = 0; i < envolvidos.length; i++){
                                //Verifica se interação corrente é menor ou igual ao índice 2, pois só queremos exibir no máximo três primeiros envolvidos na tela
                                if(i < 3){
                                    let tipo = envolvidos[i].tipo != null ? `(${envolvidos[i].tipo})` : "";
                                    linha1 += "<span class='font-medium'>" + envolvidos[i].nome.substr(0, 20) + "...</span><span class='text-muted font-10'> " + tipo + "</span></br>";
                                } else {
                                    //Apartir dos três primeiros envolvidos iremos exibir apenas a quantidade de envolvidos seguintes 
                                    let badge = envolvidos.length > 4 ? "Envolvidos" : "Envolvido";
                                    linha1 += `<span class="badge text-white font-normal badge-pill badge-secondary blue-grey-text text-darken-4 mr-2">+${envolvidos.length - 3} ${badge}</span>`;
                                    break;
                                } 
                            }
                            result +=  `
                                <table>
                                    <tbody>
                                        <tr>
                                            <td>
                                                ${linha1}
                                            </td>
                                        </tr>
                                    <tbody>
                                </table>`;
                            return result;
                        }
                    //Se não hover envolvidos, exibe somente o advodado
                    } else {
                        //carrega dados dos campos numero_oab, letra_oab e uf
                        let letras     = ["D","A","B","E","N","P"];
                        let uf         = "/" + linha.termo_variacoes.substr(0,2); 
                        let variacao   = linha.termo_variacoes.split(",")[0]; //extrai a primeira variação que segue o padrão ufOAB
                        let oab        = variacao.substr(2,7);
                        let numero_oab = "";
                        let letra_oab  = "";
                        let encontrou  = false;
                        let index      = 0;
                        //Para cada letra...
                        for(let i = 0; i < letras.length; i++){
                          //Verifica se a letra está contida na OAB
                          index = oab.search(letras[i]);
                          if(index > 0 ){
                            encontrou = true;
                            break;
                          }
                        }
                        if(encontrou){
                            numero_oab = oab.substr(0, index);
                            letra_oab  = oab.substr(index, 1);
                        } else {
                            numero_oab = oab.substr(0, 6);
                        }
                        let advogado = linha.termo_pesquisa.length > 15 ? `${linha.termo_pesquisa.substr(0, 15)}...` : linha.termo_pesquisa;
                        oab = numero_oab ? `OAB ` + numero_oab + letra_oab + uf : "";
                        result = `<span class='font-medium'> ${advogado} </span><span class='text-muted font-10'> ${oab} </span></br>`;
                        //result = `<span class='font-medium'> ${linha.termo_pesquisa}</span>&nbsp;OAB&nbsp;<span class='text-muted'>${numero_oab + letra_oab + uf}</span>`;
                        return result;
                    }
                }
            }
        });
        
        this.addField({
            name: 'id_processo',
            title: 'Informações do Processo',
            width: 27.5,
            onLoad: (data,linha) =>  {
                let result = '';
                let linha1 = '';
                let linha2 = '';
                let linha3 = '';
                //Verifica se tem processo relacionado a esta movimentação (FK da tb monitora_termo_processo)
                if(linha.id_processo != null){
                        //Verifica se tem o número novo do processo, essa informação vem da Escavador 
                    if(linha.processo.numero_novo != null){
                        //Verifica se tem código de processo (FK ta tb PRC), se tiver significa que o processo já está cadastrado no BD
                        if(linha.processo.codigo_processo != null){
                            linha1 += `<span class="font-medium">Código: ${linha.processo.codigo_processo} </span><i class="font-18 mdi mdi-open-in-new cursor-pointer text-dark" data-toggle="tooltip" data-placement="top" title="Ver ficha do processo" data-toggle="tooltip" onclick="onClickFichaProcesso(1);"></i><br>`;
                            linha3 += `<span class="badge badge-pill badge-success">Cadastrado</span>&nbsp`;
                            if(linha.prc_movimento.ID){
                                linha3 += `<span class="badge badge-pill badge-success">Andamento</span>&nbsp`;
                            }
                            if(linha.id_tarefa){
                                linha3 += `<span class="badge badge-pill badge-success">Tarefa</span>`;
                            }
                            //Verifica se o processo já está sendo monitorado
                            if(linha.processo.monitoramento != null){
                                linha3 += `<span class="badge badge-pill badge-success ml-1">Monitorado</span>`;
                            }
                        } else{
                            if(linha.descartada != 'S'){
                                linha3 = `<span class="badge badge-pill badge-danger">Pendente</span>`;
                            }else if(linha.descartada == 'S'){
                                linha3 = `<span class="badge badge-pill badge-secondary">Descartado</span>`;
                            }
                        }
                        //tags                 = `<span class="badge badge-success">Monitorado</span>`;
                        linha2 = `<span>Processo: <span class="font-medium">${linha.processo.numero_novo}</span></span><br>`;
                    } else {
                        linha2 = `<span class="">Não conseguimos identificar o processo </span><i class="tooltip-naj icon-info" data-toggle="tooltip" data-placement="top" title="Localizamos o termo de pesquisa mas não foi possível identificar o processo"></i><br>`;
                    }
                } else if(linha.id_processo == null){
                    linha1 = `<span class="">Não conseguimos identificar o processo </span><i class="tooltip-naj icon-info" data-toggle="tooltip" data-placement="top" title="Localizamos o termo de pesquisa mas não foi possível identificar o processo"></i><br>`;
                    if(linha.descartada == 'S'){
                        linha2 = `<span class="badge badge-pill badge-secondary">Descartado</span>`;
                    }
                }
                result +=  `
                    <table>
                        <tbody>
                            <tr>
                                <td> 
                                    ${linha1}
                                    ${linha2}
                                    ${linha3}
                                </td>
                            </tr>
                        <tbody>
                    </table>`;
                return result;
            }
        });
        
        this.addAction({
            name: 'obterPublicacoesAgora',
            title: 'Obter Publicações Agora',
            icon: 'mdi mdi-flag remove-btn-icon',
            onValidate: () => true,
            onClick: async () => {
                await obterPublicacoesAgora();
            }    
        });
        
        this.addAction({
            name: 'obterDiariosAgora',
            title: 'Obter Diários Agora',
            icon: 'mdi mdi-flag remove-btn-icon',
            onValidate: () => true,
            onClick: async () => {
                await persistirDiarios();
            }    
        });
        
        this.addAction({
            name: 'termosMonitorados',
            title: 'Termos Monitorados',
            icon: 'mdi mdi-flag remove-btn-icon',
            onValidate: () => true,
            onClick: () => {
                console.log('Exibe modal de Consulta Termos Monitorados');
                exibeModalConsultaTermos();
            }    
        });
        
    }
    
}
