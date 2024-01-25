class ConteudoMovimentacoesProcessoTable extends Table {

    constructor() {
        super();
        
        this.target           = 'datatable-conteudo-movimentacao-processo';
        this.name             = 'Conteudo Movimentacao Processo';
        this.route            = ``;
        this.key              = ['id'];
        this.openLoaded       = false; //Não carregar dados inicialmente 
        this.isItDestructible = true;
        this.isItEditable     = false;
        this.defaultFilters   = false;
        this.showTitle        = false;
        this.limit            = 10;
        //Precisamos sobreescrver o onEdit se não irá executar o onEdit da classe mãe
        this.onEdit           = function() {
        };

        this.addField({
            name: 'data',
            title: 'Data Andamento',
            width: 10,
            onLoad:(data) => `&nbsp;` + formatDate(data)
            
        });
        
        this.addField({
            name: 'conteudo',
            title: 'Descrição no Tribunal',
            width: 45,
            onLoad:(data,linha) => {
                let result = '';
                let instancia = {
                    'PRIMEIRO_GRAU' : {
                        'label':'1° Instância',
                        'class':'info',
                    },
                    'SEGUNDO_GRAU': {
                        'label':'2° Instância',
                        'class':'warning',
                    },
                    'DESCONHECIDA': {
                        'label':'1° Instância',
                        'class':'info',
                    },
                    'SUPERIOR' : {
                        'label':'Superior',
                        'class':'danger',
                    }
                }
                let novo = linha.lido == 'N' ? `<span class="badge text-white blue-grey-text text-darken-4 font-normal badge-pill badge-warning">Novo</span>` : ``;
                result = `
                    <span class="row">
                        <span class="col-12">
                            <span class="badge text-white blue-grey-text text-darken-4 font-normal badge-pill badge-${instancia[linha.instancia].class}">${instancia[linha.instancia].label}</span>
                            <i class="onClickVerProcessoSiteTribunal tooltip-naj font-16 mdi mdi-open-in-new cursor-pointer text-dark" data-toggle="tooltip" data-placement="top" title="Ver processo no site do tribunal"></i>
                            ${novo}
                        </span>
                        <span class="col-12">${data}</span>
                    </span>
                `;
                return result;
            }
            
        });
        
        this.addField({
            name: 'TRADUCAO_ANDAMENTO',
            title: 'Descrição Simplificada',
            width: 40,
            onLoad:(data, linha) => {
                let result = '';
                data = data ? data : "";
                result = `
                    <span class="row">
                        <span class="col-12 text-justify">${data}</span>
                        <span class="col-12">
                            <button type="button" id="${linha.id}" class="btn btn-sm waves-effect waves-light btn-rounded btn-outline-secondary btnComentarAndamento">
                                <i class="fas fa-edit mr-2"></i>
                                Comentar
                            </button>
                        </span>
                    </span> 
                `;
                return result;
            }
            
        });
        
        this.addAction({
            name: 'marcarTodosComoLido',
            title: 'Marcar Todos Como Lido',
            icon: 'mdi mdi-flag remove-btn-icon',
            onValidate: () => true,
            onClick: async () => {
                await marcaTodosComoLidosCMP();
            }    
        });
        
        this.addAction({
            name: 'excluirAndamentos1instancia',
            title: 'Excluir andamentos 1º Inst...',
            icon: 'fas fa-trash remove-btn-icon',
            onValidate: () => true,
            onClick: async () => {
                await sweetAlertExcluirAndamentos(1);
            }    
        });
        
        this.addAction({
            name: 'excluirAndamentos2instancia',
            title: 'Excluir andamentos 2º Inst...',
            icon: 'fas fa-trash remove-btn-icon',
            onValidate: () => true,
            onClick: async () => {
                await sweetAlertExcluirAndamentos(2);
            }    
        });
        
    }
    
    //Sobreescreve o método load do Table
    async load() {
        const { loading, notSearch, totalPages, totalCounter } = this.ids;

        loadingStart(loading);

        this.closeActions();

        this.resetSelectedRow();

        const oldLimit = this.limit;

        this.loadLimit();

        if (oldLimit !== this.limit) this.page = 1;

        try {
            let f = false;

            let filters = this.filtersForSearch.concat(this.fixedFilters);

            if (filters) f = '&f=' + this.toBase64(filters);

            this.totalPages = Math.ceil(this.data.total / this.data.limite);

            this.notifyPaginator(
                this.page > 1,
                this.page < this.totalPages
            );

            if (this.data.resultado.length > 0) {
                this.fillDataTable(this.data.resultado);
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

            fillById(totalCounter, numberWithCommas(this.data.total));
            fillById(totalPages, this.totalPages);
        } catch(e) {
            NajAlert.toastError('Erro ao efetuar a requisição!');
        }

        this.notifyActions();

        loadingDestroy(loading);
    }
    
    //Sobreescreve o método loadPrevious do Table
    loadPrevious() {
        this.page--;

        this.loadCurrentData();
    }

    //Sobreescreve o método loadNext do Table
    loadNext() {
        this.page++;

        this.loadCurrentData();
    }
    
    //Sobreescreve o método notifyPaginator do Table
    notifyPaginator(prev, next) {
        const { currentPage, previousPage, nextPage, fisrtPage, lastPage } = this.ids;

        recreateElement(byId(previousPage));
        recreateElement(byId(nextPage));
        recreateElement(byId(fisrtPage));
        recreateElement(byId(lastPage));

        if (prev) {
            addEventById(previousPage, 'click', () => this.loadPrevious());

            if (this.page > 1) {
                addEventById(fisrtPage, 'click', () => {
                    this.page = 1;

                    this.loadCurrentData();
                });
            }
        }

        if (next) {
            addEventById(nextPage, 'click', () => this.loadNext());

            if (this.page !== this.totalPages) {
                addEventById(lastPage, 'click', () => {
                    this.page = this.totalPages;

                    this.loadCurrentData();
                });
            }
        }

        // montando as páginas
        this.loadBoxPagination();

        fillById(currentPage, this.page);
    }
    
    //Sobreescreve o método loadBoxPagination do Table
    loadBoxPagination() {
        const { paginationPages } = this.ids;

        let pages = [];
        let pagesQuantity = this.totalPages < 5 ? this.totalPages : 5;
        let limitPageLeft = Math.floor(pagesQuantity / 2);

        if (limitPageLeft <= 0) limitPageLeft = 1;

        let limitPageRight = this.totalPages - this.page;
        let current = this.page;

        if (current > limitPageLeft && limitPageRight >= limitPageLeft) { // meio
            current -= limitPageLeft + 1;
        } else if (current <= limitPageLeft) { // esquerda
            current = 0;
        } else { // direita
            current = current - pagesQuantity + limitPageRight;
        }

        for (let p = 1; p <= pagesQuantity; p++) {
            pages.push(current + p);
        }

        fillById(
            paginationPages,
            pages.reduce((acm, page) => acm + `
                <button
                    title="Página ${page}"
                    page="${page}"
                    class="btn-page ${this.page === page && 'current-page' || ''}"
                >
                    ${page}
                </button>
            `, '')
        );

        addEventByClass('btn-page', 'click', ({ target }) => {
            const toPage = parseInt(target.getAttribute('page'));

            if (this.page !== toPage && toPage <= this.totalPages) {
                this.page = toPage;

                this.loadCurrentData();
            }
        });
    }
    
    /** 
     * Carrega dados correntes na tabela
     */
    async loadCurrentData(){
        //Indice final
        let end             = this.page * 10;
        //Indice inicial
        let start           = end - 10 > 0 ? end - 10 : 0;
        //Todas as movimentações
        let movimentacoes   = getRegistroSelecionadoMT().movimentacoes;
        //Seta o resultado com o range das movimentações que serão exibidas no datatable
        this.data.resultado = movimentacoes.slice(start,end);
        //Seta o total
        this.data.total     = movimentacoes.length;
        //Seta página
        this.data.pagina    = this.page;
        //Seta limite de registros exibidos
        this.limit          = 10;   
        //Carrega dados na tabela
        this.load();
        if(this.data.resultado.length > 0){
            await verificaRegistrosLidosCMP();
        }
    }
    
}
