class ProcessosParadoTable extends Table {

    constructor() {
        super();
        
        this.target     = 'datatable-processos-parado';
        this.name       = 'Processos Parado';
        this.route      = `processos/parado`;
        this.key        = ['CODIGO'];
        this.openLoaded = false;
        this.isItEditable     = false;
        this.isItDestructible = true;
        this.showTitle        = false;
        this.defaultFilters   = false;

        // campos
        this.addField({
            name: 'CODIGO_PROCESSO',
            title: 'Código',
            width: 5,
            onLoad: (data, row) => {
                return `<span class="ml-2">${row.CODIGO_PROCESSO}<i class="iconCodigoProcesso mdi mdi-open-in-new cursor-pointer text-dark ml-1" style="font-size: 15px !important;" title="Ver ficha do processo" data-toggle="tooltip"></i></span>`;
            }
        });

        this.addField({
            name: 'outras_informacao',
            title: 'Outras Informações',
            width: 30,
            onLoad: (data, row) =>  {
                let novas_atividades = '<tr><td><span class="title-andamento-atividade-processo-parado">Última atividade: </span><span>Não há informações</span></td></tr>';
                let novos_andamentos = '<tr><td><span class="title-andamento-atividade-processo-parado">Último andamento: </span><span>Não há informações</span></td></tr>';
                let novas_intimacoes = '<tr><td><span class="title-andamento-atividade-processo-parado">Última intimação: </span><span>Não há informações</span></td></tr>';

                if(row.ULTIMA_ATIVIDADE_DATA && row.ULTIMA_ATIVIDADE_DESCRICAO) {
                    if(row.ULTIMA_ATIVIDADE_DESCRICAO.length > 60) {
                        novas_atividades = `
                            <tr style="margin-top: 5px !important;">
                                <td>
                                    <span class="title-andamento-atividade-processo-parado">Última atividade: </span>
                                    <span id="atividade-processo-${row.CODIGO_PROCESSO}">
                                        ${formatDate(row.ULTIMA_ATIVIDADE_DATA, true, true)} - ${row.ULTIMA_ATIVIDADE_DESCRICAO.substr(0, 60)}...
                                    </span>
                                    <span class="action-icons">
                                        <a data-toggle="collapse" data-key-processo="${row.CODIGO_PROCESSO}" aria-expanded="false" onclick="onClickCollapseAtividadeProcesso('${row.ULTIMA_ATIVIDADE_DESCRICAO.replace(/(\r\n|\n|\r)/gm, " ")}', ${row.CODIGO_PROCESSO}, '${formatDate(row.ULTIMA_ATIVIDADE_DATA, true, true)}', this);">
                                            <i class="fas fa-chevron-circle-right icone-partes-processo-expanded" title="Clique para ampliar e ver a última atividade" data-toggle="tooltip"></i>
                                        </a>
                                    </span>
                                </td>
                            </tr>
                        `;
                    } else {
                        novas_atividades = `
                            <tr style="margin-top: 5px !important;">
                                <td>
                                    <span class="title-andamento-atividade-processo-parado">Última atividade: </span>
                                    ${formatDate(row.ULTIMA_ATIVIDADE_DATA, true, true)} - ${row.ULTIMA_ATIVIDADE_DESCRICAO}
                                </td>
                            </tr>
                        `;
                    }
                }

                if(row.ULTIMO_ANDAMENTO_DATA && row.ULTIMO_ANDAMENTO_DESCRICAO) {
                    if(row.ULTIMO_ANDAMENTO_DESCRICAO.length > 60) {
                        novos_andamentos = `
                            <tr style="margin-top: 5px !important;">
                                <td>
                                    <span class="title-andamento-atividade-processo-parado">Último andamento: </span>
                                    <span id="andamento-processo-${row.CODIGO_PROCESSO}">
                                        ${formatDate(row.ULTIMO_ANDAMENTO_DATA, true, true)} - ${row.ULTIMO_ANDAMENTO_DESCRICAO.substr(0, 60)}...
                                    </span>
                                    <span class="action-icons">
                                        <a data-toggle="collapse" data-key-processo="${row.CODIGO_PROCESSO}" aria-expanded="false" onclick="onClickCollapseAndamentoProcesso('${row.ULTIMO_ANDAMENTO_DESCRICAO.replace(/(\r\n|\n|\r)/gm, " ")}', ${row.CODIGO_PROCESSO}, '${formatDate(row.ULTIMO_ANDAMENTO_DATA, true, true)}', this);">
                                            <i class="fas fa-chevron-circle-right icone-partes-processo-expanded" title="Clique para ampliar e ver o último andamento" data-toggle="tooltip"></i>
                                        </a>
                                    </span>
                                </td>
                            </tr>
                        `;
                    } else {
                        novos_andamentos = `
                            <tr style="margin-top: 5px !important;">
                                <td>
                                    <span class="title-andamento-atividade-processo-parado">Último andamento: </span>
                                    ${formatDate(row.ULTIMO_ANDAMENTO_DATA, true, true)} - ${row.ULTIMO_ANDAMENTO_DESCRICAO}
                                </td>
                            </tr>
                        `;
                    }
                }

                if(row.ULTIMA_INTIMACAO_DATA && row.ULTIMA_INTIMACAO_DESCRICAO) {
                    if(row.ULTIMA_INTIMACAO_DESCRICAO.length > 60) {
                        novas_intimacoes = `
                            <tr style="margin-top: 5px !important;">
                                <td>
                                    <span class="title-andamento-atividade-processo-parado">Última intimação: </span>
                                    <span id="intimacao-processo-${row.CODIGO_PROCESSO}">
                                        ${formatDate(row.ULTIMA_INTIMACAO_DATA)} - ${row.ULTIMA_INTIMACAO_DESCRICAO.substr(0, 60)}...
                                    </span>
                                    <span class="action-icons">
                                        <a data-toggle="collapse" data-key-processo="${row.CODIGO_PROCESSO}" aria-expanded="false" onclick="onClickCollapseIntimacaoProcesso('${row.ULTIMA_INTIMACAO_DESCRICAO.replace(/(\r\n|\n|\r)/gm, " ")}', ${row.CODIGO_PROCESSO}, '${formatDate(row.ULTIMA_INTIMACAO_DATA)}', this);">
                                            <i class="fas fa-chevron-circle-right icone-partes-processo-expanded" title="Clique para ampliar e ver a última intimação" data-toggle="tooltip"></i>
                                        </a>
                                    </span>
                                </td>
                            </tr>
                        `;
                    } else {
                        novas_intimacoes = `
                            <tr style="margin-top: 5px !important;">
                                <td>
                                    <span class="title-andamento-atividade-processo-parado">Última intimação: </span>
                                    ${formatDate(row.ULTIMA_INTIMACAO_DATA)} - ${row.ULTIMA_INTIMACAO_DESCRICAO}
                                </td>
                            </tr>
                        `;
                    }
                }

                return `
                    <table class="row-atividade-andamento-processo">
                        <tr style="margin-top: 5px !important;">
                            ${novas_atividades}
                        </tr>
                        <tr style="margin-top: 5px !important;">
                            ${novos_andamentos}
                        </tr>
                        <tr style="margin-top: 5px !important;">
                            ${novas_intimacoes}
                        </tr>
                    </table>
                `;
            }
        });

        this.addField({
            name: 'informacao_processo',
            title: 'Informações do Processo',
            width: 40,
            onLoad: (data, row) =>  {
                let classeCss = (row.SITUACAO == "ENCERRADO") ? 'badge-danger' : 'badge-success';
                let situacao  = (row.SITUACAO == "ENCERRADO") ? 'Baixado' : 'Em andamento';

                return `
                    <table>
                        <tr>
                            <td>${row.NUMERO_PROCESSO_NEW}</td>
                        </tr>                        
                        ${(row.CLASSE)
                            ?
                            `<tr>
                                <td>${row.CLASSE}</td>
                            </tr>
                            `
                            : ``
                        }
                        ${(row.CARTORIO && row.COMARCA && row.COMARCA_UF)
                            ?
                            `<tr>
                                <td>${row.CARTORIO} - ${row.COMARCA} (${row.COMARCA_UF})</td>
                            </tr>
                            `
                            : ``
                        }
                        <tr>
                            <td>
                                ${row.GRAU_JURISDICAO}
                                <span class="badge ${classeCss} badge-rounded badge-status-processo">${situacao}</span>
                            </td>
                        </tr>
                    </table>
                `;
            }
        });

        this.addField({
            name: 'nome_partes',
            title: 'Nome das Partes',
            width: 25,
            onLoad: (data, row) =>  {
                let sHtmlQtdeClientes = '';
                let sHtmlEnvolvidos   = '';
                let sHtmlAdversarios   = '';
                let sHtmlEnvolvidosAdv = '';

                if(row.QTDE_CLIENTES) {
                    sHtmlQtdeClientes = `<span class="badge badge-secondary badge-rounded badge-nome-partes-processo" title="+${row.QTDE_CLIENTES} Envolvido(s)">+${row.QTDE_CLIENTES} Envolvido(s)</span>`;
                    sHtmlEnvolvidos   = `
                        <span class="action-icons">
                            <a data-toggle="collapse" href="#partes-processo-${row.CODIGO_PROCESSO}" data-key-processo="${row.CODIGO_PROCESSO}" aria-expanded="false" onclick="onClickEnvolvidosProcesso(${row.CODIGO_PROCESSO}, this);">
                                <i class="fas fa-chevron-circle-right icone-partes-processo-expanded" title="Clique para ver os envolvidos" data-toggle="tooltip"></i>
                            </a>
                        </span>
                    `;
                }

                if(row.QTDE_ADVERSARIOS) {
                    sHtmlAdversarios   = `<span class="badge badge-secondary badge-rounded badge-nome-partes-processo" title="+${row.QTDE_ADVERSARIOS} Envolvido(s)">+${row.QTDE_ADVERSARIOS} Envolvido(s)</span>`;
                    sHtmlEnvolvidosAdv = `
                        <span class="action-icons">
                            <a data-toggle="collapse" href="#partes-adv-processo-${row.CODIGO_PROCESSO}" data-key-processo="${row.CODIGO_PROCESSO}" aria-expanded="false" onclick="onClickEnvolvidosProcessoAdv(${row.CODIGO_PROCESSO}, this);">
                                <i class="fas fa-chevron-circle-right icone-partes-processo-expanded" title="Clique para ver os envolvidos" data-toggle="tooltip"></i>
                            </a>
                        </span>
                    `;
                }

                return `
                    <table class="w-100 ml-2">
                        <tr>
                            <td class="td-nome-parte-cliente">${row.NOME_CLIENTE} (${row.QUALIFICA_CLIENTE})</td>
                        </tr>
                        <tr>
                            <td class="td-nome-parte-cliente">
                                <div class="row" style="width: 100% !important; margin-left: 1px !important;">
                                    ${sHtmlQtdeClientes}${sHtmlEnvolvidos}
                                </div>
                            </td>
                        </tr>
                        <tr class="collapse well" id="partes-processo-${row.CODIGO_PROCESSO}" aria-expanded="false"></tr>
                        ${(row.NOME_ADVERSARIO)
                            ?
                            `<tr>
                                <td>${row.NOME_ADVERSARIO} (${row.QUALIFICA_ADVERSARIO})</td>
                            </tr>
                            <tr>
                                <td class="td-nome-parte-cliente">
                                    <div class="row" style="width: 100% !important; margin-left: 1px !important;">
                                        ${sHtmlAdversarios}${sHtmlEnvolvidosAdv}
                                    </div>
                                </td>
                            </tr>
                            <tr class="collapse well" id="partes-adv-processo-${row.CODIGO_PROCESSO}" aria-expanded="false"></tr>
                            `
                            : ``
                        }                        
                    </table>
                `;
            }
        });

        this.addAction({
            name: 'download_xls',
            title: 'Exportar para XLS',
            icon: 'fas fa-download',
            onValidate: () => true,
            onClick: () => exportarProcessoParado()
        });
    }

    //Sobreescreve o método
    async load() {
        const { loading, notSearch, totalPages, totalCounter } = this.ids;

        loadingStart(loading);

        this.closeActions();

        this.resetSelectedRow();

        const oldLimit = this.limit;

        this.loadLimit();

        if (oldLimit !== this.limit) this.page = 1;

        try {
            let status = $('#filter-situacao-processo').val();
            let period = $('#filter-movimentacao-processo').val();

            let filterProcess = '';

            if(period && status && $('[name=sem-atividades]')) {
                
                if (!$('[name=sem-intimacao]')[0].checked && !$('[name=sem-andamentos]')[0].checked && !$('[name=sem-atividades]')[0].checked) {
                    loadingDestroy(loading);
                    return NajAlert.toastWarning('Você deve marcar uma das opções para efetuar a busca!');
                }

                filterProcess += `status=${status}`;
                filterProcess += `&period=${period}`;

                let withoutActivits  = $('[name=sem-atividades]')[0].checked;
                let withoutProgress  = $('[name=sem-andamentos]')[0].checked;
                let withoutIntimacao = $('[name=sem-intimacao]')[0].checked;
                
                if(withoutActivits)
                    filterProcess += `&withoutActivits=${withoutActivits}`;

                if(withoutProgress)
                    filterProcess += `&withoutProgress=${withoutProgress}`;

                if(withoutIntimacao)
                    filterProcess += `&withoutIntimacao=${withoutIntimacao}`;

            } else {
                if (CONFIG.PROCESSOS.PRC_PARADOS_INTIMACAO == 'NAO' && CONFIG.PROCESSOS.PRC_PARADOS_ANDAMENTOS == 'NAO' && CONFIG.PROCESSOS.PRC_PARADOS_ATIVIDADES == 'NAO') {
                    loadingDestroy(loading);
                    return NajAlert.toastWarning('Você deve marcar uma das opções para efetuar a busca!');
                }

                filterProcess += `status=S`;
                filterProcess += `&period=${CONFIG.PROCESSOS.PRC_PARADOS_PERIODO}`;
                filterProcess += `&withoutActivits=${CONFIG.PROCESSOS.PRC_PARADOS_ATIVIDADES}`;
                filterProcess += `&withoutProgress=${CONFIG.PROCESSOS.PRC_PARADOS_ANDAMENTOS}`;
                filterProcess += `&withoutIntimacao=${CONFIG.PROCESSOS.PRC_PARADOS_INTIMACAO}`;
            }

            //limpa filtros 
            this.filtersForSearch = [];

            let f = false;

            let filters = this.filtersForSearch.concat(this.fixedFilters);

            if (filters) f = '&f=' + this.toBase64(filters);

            const { data } = await api.get(`${this.route}/paginate?${filterProcess}&usuario_id=${idUsuarioLogado}&limit=${this.limit}&page=${this.page}${f || ''}&XDEBUG_SESSION_START`);

            this.data = data;

            this.totalPages = Math.ceil(data.total / data.limite);

            this.notifyPaginator(
                this.page > 1,
                this.page < this.totalPages
            );

            if (data.resultado.length > 0) {
                this.fillDataTable(data.resultado);
            } else {
                fillById(this.id, `
                    <div id="${notSearch}" class="not-search">
                        Nenhum registro encontrado
                    </div>
                `);
            }

            Array.from(
                document.querySelectorAll(`#${this.id} .data-table-row`)
            ).forEach(item => (
                item.addEventListener('click', e => this.onClickRow(item, e))
            ));

            fillById(totalCounter, numberWithCommas(data.total));
            fillById(totalPages, this.totalPages);
        } catch(e) {
            debugger;
            console.log(e);
            NajAlert.toastError('Erro ao efetuar a requisição!');
        }

        this.notifyActions();

        loadingDestroy(loading);
    }

}