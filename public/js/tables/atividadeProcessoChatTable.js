class AtividadeProcessoChatTable extends Table {

    constructor(codigo_processo) {
        super();
        
        this.target           = 'datatable-atividade-processo-chat';
        this.name             = 'Atividades do Processo';
        this.route            = 'processos/atividades';
        this.key              = ['CODIGO'];
        this.openLoaded       = true;
        this.isItEditable     = false;
        this.isItDestructible = false;;
        this.showTitle        = false;
        this.defaultFilters   = false;

        this.addField({
            name: 'DATA_INICIO',
            title: 'Data e Hora',
            width: 15,
            onLoad: (data, row) =>  {
                return `${row.DATA_INICIO} ${row.HORA_INICIO}`;
            }
        });
        
        this.addField({
            name: 'TEMPO',
            title: 'Tempo',
            width: 10
        });
        
        this.addField({
            name: 'DESCRICAO',
            title: 'Histórico',
            width: 45
        });

        this.addField({
            name: 'NOME_USUARIO',
            title: 'Responsável',
            width: 30
        });

        this.addFixedFilter('CODIGO_PROCESSO', 'I', codigo_processo);
    }

}