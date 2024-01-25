class AtividadeTable extends Table {

    constructor() {
        super();
        
        this.target           = 'datatable-atividades';
        this.name             = 'Atividades';
        this.route            = `atividades`;
        this.key              = ['codigo'];
        this.openLoaded       = false; //Não carregar dados inicialmente 
        this.isItDestructible = false;
        this.isItEditable     = false;
        this.defaultFilters   = false;
        this.showTitle        = false;

        //Ação de Novo Usuário
        this.addAction({
            name: 'store_atividade',
            title: 'Nova Tarefa',
            icon: 'fas fa-plus',
            onValidate: () => true,
            onClick: () => exibeModalAtividade(this)
        });

        this.onEdit           = async function() {
            // let registro = new Object();
            // registro.id   = this.getAttribute('id_registro');
            // registro.data = this.getAttribute('data_registro');
            // let registro_json = JSON.stringify(registro);
            // sessionStorage.setItem('registro_json', registro_json);
            // carregaModalManutencaoUnidadeFinanceiraData(registro);
        };
        
        this.addField({
            name: 'data_hora_inicio',
            title: 'Data/Hora Inicio',
            width: 10,
            onLoad: (data, row) =>  {
                const data_atual = getDataAtual();
                let mesAtual           = data_atual.split('-')[1] - 1;
                let mesInicio          = row.DATA_INICIO.split('/')[1] - 1;
                let data_atual_moment  = moment([data_atual.split('-')[0], mesAtual, data_atual.split('-')[2]]);
                let data_inicio_moment = moment([row.DATA_INICIO.split('/')[2], mesInicio, row.DATA_INICIO.split('/')[0]]);
                
                const days_difference = data_atual_moment.diff(data_inicio_moment, 'days');

                if(days_difference > 30) 
                    return `
                        <table style="margin: 5px 0 0 20px;">
                            <tr>
                                <td>${row.DATA_INICIO} ${row.HORA_INICIO}</td>
                            </tr>
                        </table>
                    `;

                let string_days = 'Hoje';
                if(days_difference > 0) {
                    string_days = `Há ${days_difference} dias`;
                }

                return `
                    <table style="margin: 5px 0 0 20px;">
                        <tr>
                            <td>${row.DATA_INICIO} ${row.HORA_INICIO}</td>
                        </tr>
                        <tr>
                            <td><span class="mt-1 mb-2 badge badge-warning badge-rounded badge-informacoes-processo">${string_days}</span></td>
                        </tr>
                    </table>
                    
                `;
            }
        });
        
        this.addField({
            name: 'TEMPO',
            title: 'Tempo',
            width: 5,
            onLoad: (data, row) =>  {
                if (!row.TEMPO)
                    return `00:00:00`

                return `
                    <span>${row.TEMPO}</span>
                `
            }
        });
        
        this.addField({
            name: 'DESCRICAO',
            title: 'Histórico',
            width: 40,
            onLoad: (data, row) =>  {
                return `
                    <span style="word-break: break-word;">${row.DESCRICAO}</span>
                `;
            }
        });

        this.addField({
            name: 'outras_informacao',
            title: 'Outras Informações',
            width: 25,
            onLoad: (data, row) =>  {
                let html = ''

                if(!row.NUMERO_PROCESSO_NEW && !row.CARTORIO && !row.COMARCA) {
                    html = `
                        <table>
                            <tr>
                                <td class="td-nome-parte-cliente">${row.PESSOA_CLIENTE_NOME} (Cliente)</td>
                            </tr>
                        </table>
                    `
                } else {
                    html = `
                        <table style="width: 100%;">
                            <tr>
                                <td>
                                    <table style="width: 100%;">
                                        <tr>
                                            <td class="td-nome-parte-cliente">${row.NOME_CLIENTE} (${row.QUALIFICA_CLIENTE})</td>
                                        </tr>
                                        ${(row.NOME_ADVERSARIO)
                                            ?
                                            `<tr>
                                                <td>${row.NOME_ADVERSARIO} (${row.QUALIFICA_ADVERSARIO})</td>
                                            </tr>
                                            `
                                            : ``
                                        }
                                        ${(row.NUMERO_PROCESSO_NEW)
                                            ?
                                            `<tr>
                                                <td>${row.NUMERO_PROCESSO_NEW}</td>
                                            </tr>
                                            `
                                            : ``
                                        }
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
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <table style="width: 100%;">
                                        <tr>
                                            <td>
                                            ${(row.VALOR_CAUSA > 0)
                                                ?
                                                `<span>Valor Ação: <span class="weight-700">${convertIntToMoney(row.VALOR_CAUSA)}</span></span>`
                                                :
                                                `<span>Valor Ação: <span class="">0,00</span></span>`
                                            }
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="width: 50%;">
                                                <span>Grau de Risco: <span class="${row.DESCRICAO_RISCO ? 'weight-700' : ''}">${row.DESCRICAO_RISCO || '-'}</span></span>
                                            </td>
                                            <td>
                                                ${(row.VALOR_RISCO > 0)
                                                    ?
                                                    `<span>Valor Risco: <span class="weight-700">${convertIntToMoney(row.VALOR_RISCO)}</span></span>`
                                                    :
                                                    `<span>Valor Risco: <span class="">0,00</span></span>`
                                                }
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    `
                }

                return html
            }
        });

        this.addField({
            name: 'outras_informacao',
            title: '',
            width: 20,
            onLoad: (data, row) =>  {
                return `
                    <table class="row-informacoes-processo">
                        <tr>
                            <td class="weight-500 text-dark">Responsável:</td>
                        </tr>
                        <tr>
                            <td>${row.NOME_USUARIO}</td>
                        </tr>
                    </table>
                `;
            }
        });
    }
}
