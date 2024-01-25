class PesquisaNpsUsuariosTable extends Table {

    constructor() {
        super();
        
        this.target           = 'datatable-pesquisa-nps-usuarios';
        this.name             = `Pesquisa: ${sessionStorage.getItem('@NAJ_WEB/nps/descricao')}`;
        this.route            = 'pesquisa/nps/usuarios';
        this.key              = ['id'];
        this.openLoaded       = true;
        this.isItEditable     = false;
        this.isItDestructible = false;
        this.showTitle        = true;

        // campos
        this.addField({
            name: 'id',
            title: 'Código',
            width: 5,
            onLoad: (_, row) => {
                return `<span class="ml-4">${row.id}</span>`;
            }
        });

        this.addField({
            name: 'usuarioId',
            title: 'Usuário',
            width: 20,
            onLoad: (_, row) => {
                return row.nomeUsuario;
            }
        });

        this.addField({
            name: 'data_hora_inclusao',
            title: 'Inclusão',
            width: 10,
            onLoad: (_, row) => {
                return `<span class="ml-3">${formatDate(row.data_hora_inclusao.split(' ')[0])}</span>`;
            }
        });

        this.addField({
            name: 'data_hora_exibicao',
            title: 'Exibição',
            width: 10,
            onLoad: (_, row) => {
                if(!row.data_hora_exibicao) return;

                return `<span class="ml-3">${formatDate(row.data_hora_exibicao.split(' ')[0])}</span>`;
            }
        });

        this.addField({
            name: 'nota',
            title: 'Nota',
            width: 10,
            onLoad: (_, row) => {
                if(!row.nota) return;

                if([0, 1, 2, 3, 4, 5, 6].indexOf(row.nota) > -1) {
                    if(row.rangeMax == 5) {
                        if([0, 1, 2].indexOf(row.nota) > -1)
                            return `<span class="ml-4 btn btn-danger btn-circle">${row.nota}</span>`;

                        if([3].indexOf(row.nota) > -1)
                            return `<span class="ml-4 btn btn-warning btn-circle" style="color: #fff !important;">${row.nota}</span>`;

                        if([4, 5].indexOf(row.nota) > -1)
                            return `<span class="ml-4 btn btn-success btn-circle">${row.nota}</span>`;
                    }

                    return `<span class="ml-4 btn btn-danger btn-circle">${row.nota}</span>`;
                }

                if([7, 8].indexOf(row.nota) > -1) {
                    return `<span class="ml-4 btn btn-warning btn-circle" style="color: #fff !important;">${row.nota}</span>`;
                }

                return `<span class="ml-4 btn btn-success btn-circle">${row.nota}</span>`;
            }
        });

        this.addField({
            name: 'motivo',
            title: 'Motivo',
            width: 20
        });

        this.addField({
            name: 'status',
            title: 'Situação',
            width: 15,
            onLoad: (_, row) => {
                if(!row.status) return;
                
                if(row.status == 'P') {
                    return `<span class="badge badge-warning badge-rounded badge-status-nps">Pendente</span>`
                }

                if(row.status == 'N') {
                    return `<span class="badge badge-danger badge-rounded badge-status-nps">Não Respondida</span>`
                }

                return `<span class="badge badge-success badge-rounded badge-status-nps">Respondida</span>`
            }
        });

        this.addField({
            name: 'device',
            title: 'Dispositivo',
            width: 10
        });

        //Ação de Nova pesquisa NPS
        this.addAction({
            name: 'store_pesquisa_usuarios',
            title: 'Incluir Participantes',
            icon: 'fas fa-plus',
            onValidate: () => true,
            onClick: () => openModalNewNpsUsuarios()
        });

        const key = JSON.parse(sessionStorage.getItem('@NAJ_WEB/nps'));

        if(!key)
            return window.location.href = `${baseURL}pesquisa/nps/create`;

        this.addFixedFilter('id_pesquisa', 'I', key);
    }

    makeSkeleton() {
        const actionsIn = this.getActionsIn();
        const actionsOut = this.getActionsOut();
        let defaultFilters = '';

        if(this.defaultFilters){
            defaultFilters = `<div style="display: flex;">
                        <div style="display: flex; align-items: center;">
                            <span>Pesquisar em&nbsp;</span>
                        </div>
                        <select id="${this.ids.filterColumn}">
                            ${this.getFilterColumns()}
                        </select>
                        <div style="display: flex; align-items: center;">
                            <span>&nbsp;que&nbsp;</span>&nbsp;
                        </div>
                        <select id="${this.ids.filterOption}"></select>&nbsp;

                        <div id="${this.ids.filterValueContainer}" style="display: flex;"></div>

                        <div id="${this.ids.filterValue2Container}" style="display: flex;">
                            <div style="display: flex; align-items: center;">&nbsp;&nbsp;e&nbsp;&nbsp;</div>
                            <input id="${this.ids.filterValue2}" type="text" />
                        </div>

                        &nbsp;&nbsp;

                        <button id=${this.ids.filterButton} title="Adicionar filtro" class="btn btnCustom" style="background: #2f323e !important;">
                            <i class="fas fa-plus btn-icon"></i>
                        </button>

                        <div style="display: flex; align-items: center;">
                            <div class="y-separator"></div>
                        </div>

                        <button id="${this.ids.searchButton}" class="btn btnCustom" style="background: #2f323e !important;">
                            <i class="fas fa-search btn-icon"></i>&nbsp;&nbsp;
                            Pesquisar
                        </button>
                    </div>

                            <div class="data-table-filter-list" id="${this.ids.filterList}"></div>`;
        }

        fillById(this.target, `
            ${this.showTitle && `
                <div class="page-breadcrumb border-bottom">
                    <div class="row">
                        <div class="col-lg-3 col-md-4 col-xs-12 align-self-center">
                            <h5 class="font-medium text-uppercase mb-0">${this.name}</h5>
                </div>
                        <div class="col-lg-9 col-md-8 col-xs-12 align-self-center">
                            <nav aria-label="breadcrumb" class="mt-2 float-md-right float-left">
                                <ol class="breadcrumb mb-0 justify-content-end p-0">
                                    <li class="breadcrumb-item">&nbsp;</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            ` || ''}

            <div class="datatable-body" id="${this.ids.container}">
                <div id="${this.ids.loading}" class="loader loader-default" data-half></div>

                <div class="data-table-filter">
                    
                    ${defaultFilters}
                    
                </div>

                <div class="data-table-footer">
                    <div>
                        <div class="actions-container" act="1">
                            <div class="actions-in" act="1">
                                <button id="${this.ids.actionsInButton}" class="btn btnCustom action-in-button btn-action-default" act="1">
                                    <i class="fas fa-ellipsis-v btn-icon" act="1"></i>
                                </button>
                                <ul act="1" id="list-actions-default" class="actions-in-list" style="display: none;">${actionsIn}</ul>
                            </div>
                            <div class="actions-out">
                                ${actionsOut}
                            </div>

                            <div class="y-separator"></div>
                        </div>

                        <div class="pagination-container">
                            <button id="${this.ids.fisrtPage}" title="Primeira página" class="btn btnCustom action-in-button">
                                <i class="fas fa-caret-left fa-2x btn-icon"></i>
                            </button>

                            <button id="${this.ids.previousPage}" title="Página anterior" class="btn btnCustom action-in-button">
                                <i class="fas fa-angle-left fa-2x btn-icon"></i>
                            </button>

                            <div id="${this.ids.paginationPages}"></div>

                            <button id="${this.ids.nextPage}" title="Próxima página" class="btn btnCustom action-in-button">
                                <i class="fas fa-angle-right fa-2x btn-icon"></i>
                            </button>

                            <button id="${this.ids.lastPage}" title="Última página" class="btn btnCustom action-in-button">
                                <i class="fas fa-caret-right fa-2x btn-icon"></i>
                            </button>
                        </div>

                        <div class="y-separator"></div>

                        <div class="pages-container">
                            Página&nbsp;
                            <span id="${this.ids.currentPage}">1</span>
                            &nbsp; de &nbsp;
                            <span id="${this.ids.totalPages}">1</span>
                        </div>
                    </div>
                    <div>
                        Exibir
                        &nbsp;
                        <select id="${this.ids.perPage}">
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100" selected>100</option>
                        </select>
                        &nbsp;
                        registros de
                        &nbsp;
                        <span id="${this.ids.total}">0</span>
                        &nbsp;&nbsp;
                    </div>
                </div>

                <div class="data-table-header">
                    ${this.getHeaders()}
                </div>
                <div class="data-table-content naj-scrollable" id="${this.id}">
                    <div id="${this.ids.notSearch}" class="not-search">Nenhuma busca realizada</div>
                </div>
            </div>
        `);

        this.verifyHasAction(actionsIn, actionsOut);

        if(this.defaultFilters){
            this.loadFilterOptions();
            this.loadFilterValue();
            this.verifyBetweenFilter();

            byId(this.id).style.overflow = 'overlay';
        }
        
        this.notifyActions();
        
        if(this.defaultFilters){

            addEventById(this.ids.filterColumn, 'change', () => this.onChangeFilterColumn());
            addEventById(this.ids.filterOption, 'change', () => this.onChangeFilterOption());
            addEventById(this.ids.filterButton, 'click', () => {
                this.applyMasksEvents();

                this.addValidateFilter();
            });
        
            addEventById(this.ids.searchButton, 'click', () => this.onSearch());
            addEventById(this.ids.container, 'click', e => e.target.getAttribute('act') != 1 && this.closeActions());
            addEventById(this.ids.actionsInButton, 'click', () => {
                const list = document.querySelector(`#${this.ids.container} .actions-in-list`);

                list.classList.toggle('action-in-open');
            });
        } else if(this.forceButtonsInTreePoints) {
            addEventById(this.ids.actionsInButton, 'click', () => {
                const list = document.querySelector(`#${this.ids.container} .actions-in-list`);
    
                list.classList.toggle('action-in-open');
            });
        }
    }

}