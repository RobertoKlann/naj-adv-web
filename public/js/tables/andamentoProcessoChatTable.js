class AndamentoProcessoChatTable extends Table {

    constructor(codigo_processo) {
        super();
        
        this.target           = 'datatable-andamento-processo-chat';
        this.name             = 'Andamentos do Processo';
        this.route            = 'processos/andamento';
        this.key              = ['ID'];
        this.openLoaded       = true;
        this.isItEditable     = false;
        this.isItDestructible = false;;
        this.showTitle        = false;
        this.defaultFilters   = false;

        // campos
        this.addField({
            name: 'DATA',
            title: 'Data Andamento',
            width: 15
        });

        this.addField({
            name: 'DESCRICAO_ANDAMENTO',
            title: 'Descrição no Tribunal',
            width: 50
        });

        this.addField({
            name: 'TRADUCAO_ANDAMENTO',
            title: 'Descrição Simplificada',
            width: 35,
            onLoad: (data, row) =>  {
                if(row.TRADUCAO_ANDAMENTO != null) {
                    return row.TRADUCAO_ANDAMENTO;
                }

                return `-`;
            }
        });

        this.addFixedFilter('CODIGO_PROCESSO', 'I', codigo_processo);
    }

}