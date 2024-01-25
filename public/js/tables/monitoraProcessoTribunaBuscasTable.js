class MonitoraProcessoTribunalBuscasTable extends Table {

    constructor() {
        super();
        
        this.target           = 'datatable-monitora-processo-tribunal-buscas';
        this.name             = 'Monitora Processo Tribunal Buscas';
        this.route            = `monitoraprocessotribunalbusca`;
        this.key              = ['id'];
        this.openLoaded       = false; //Não carregar dados inicialmente 
        this.isItDestructible = false;
        this.isItEditable     = false;
        this.defaultFilters   = false;
        this.showTitle        = false;
        this.limit            = 10;
        //Precisamos sobreescrver o onEdit se não irá executar o onEdit da classe mãe
        this.onEdit           = function() {
        };

        this.addField({
            name: 'id',
            title: 'ID',
            width: 10,
            onLoad:(data) => `<span class="m-auto">${data}</span>` 
            
        });
        
        this.addField({
            name: 'data_hora',
            title: 'Data Hora',
            width: 15,
            onLoad:(data) => `&nbsp;${formatDate(data.substr(0,10))} ${data.substr(11,10)}`
            
        });
        
        this.addField({
            name: 'status',
            title: 'Status',
            width: 15,
            onLoad:(data, linha) => {
                let result = '';
                if(data != null){
                    let status_msgs         = ['PENDENTE', 'SUCESSO', 'ERRO'];
                    let status_labels       = ['warning', 'success', 'danger'];
                    let status_ultima_busca = data ? status_msgs[data]   : "";
                    let status_label        = data ? status_labels[data] : "";
                    result =`<span class="m-auto">
                                <span class="tooltip-naj badge text-white blue-grey-text text-darken-4 font-normal badge-pill badge-${status_label}" data-toggle="tooltip" data-placement="top" title="${linha.status_mensagem}">${status_ultima_busca}</span>
                            </span>`; 
                }
                return result;
            }
        });
        
        this.addField({
            name: 'status_mensagem',
            title: 'Mensagem',
            width: 55,
            onLoad:(data) => `&nbsp;` + data
        });
        
    }
    
}
