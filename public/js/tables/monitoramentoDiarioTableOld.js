class MonitoramentoDiarioTable extends Table {

    constructor() {
        super();
        
        this.target           = 'datatable-monitoramento-diario';
        this.name             = 'Monitoramento Diário';
        this.route            = `monitoramento/diarios`;
        this.key              = ['id'];
        this.openLoaded       = true; //Não carregar dados inicialmente 
        this.isItDestructible = false;
        this.isItEditable     = false;
        this.defaultFilters   = false;
        this.showTitle        = false;

        this.addField({
            name: 'diario',
            title: 'Intimações',
            width: 100,
            onLoad: (data,linha) =>  {
                let result = '';
                if(data != null){
                    result +=  `
                        <table class="table no-wrap user-table">
                            <tbody>
                                <tr>
                                    <td style='width:50%'>
                                        <span class="font-medium">` + linha.diario + `</span><br>
                                        <span class="text-muted">` + linha.estado + ` - ` + linha.tipo_diario + `</span><br>
                                        <span class="text-muted" style="width: 150px">` + linha.intimacao + `<br>
                                            <button type="button" class="btn btn-sm waves-effect waves-light btn-rounded btn-outline-dark" data-toggle="modal" data-target="#intimacao_content1"><i class="fas fa-search"></i> Leia na Íntegra</button>
                                            <span class="badge text-white font-normal badge-pill badge-warning blue-grey-text text-darken-4 mr-2">Nova</span>
                                        </span>
                                    </td>
                                    <td style='width:50%'> 
                                        <span class="font-medium">` + linha.advogado + ` OAB: ` + linha.oab + `</span><br>
                                        Data: <span class="font-medium">` + linha.data + `</span><br>
                                        Página: <span class="font-medium">` + linha.pagina + `</span><br>
                                        Processo: <span class="font-medium">` + linha.processo + `</span><br>
                                        <span class="">Não conseguimos identificar o processo </span><i class="icon-info" data-toggle="tooltip" data-placement="top" title="Localizamos o termo de pesquisa mas não foi possível identificar o processo"></i><br>
                                        <button type="button" class="btn waves-effect waves-light btn-rounded btn-info" data-toggle="tooltip" data-placement="top" title="" data-original-title="Clique para monitorar este processo"><i class="fas fa-plus-circle"></i> Monitorar</button>
                                        <button type="button" class="btn waves-effect waves-light btn-rounded btn-danger" data-toggle="tooltip" data-placement="top" title="" data-original-title="Clique para Cadastrar ou Relacionar a um Processo"><i class="fas fa-plus-circle"></i> Pendente</button>
                                        <button type="button" class="btn waves-effect waves-light btn-rounded btn-secondary" data-toggle="tooltip" data-placement="top" title="" data-original-title="Clique para Descartar esta publicação"><i class="far fa-trash-alt"></i> Descartar</button>
                                    </td>
                                </tr>
                            <tbody>
                        </table>`;
                }
                return result;
            }
        });
        
        this.addAction({
            name: 'obterMovimentacoesAgora',
            title: 'Obter Movimentações Agora',
            icon: 'mdi mdi-flag',
            onValidate: () => true,
            onClick: async () => {
                await obterMovimentacoesAgora();
            }    
        });
        
        this.addAction({
            name: 'descartarTodas',
            title: 'Descartar Todas',
            icon: 'mdi mdi-flag',
            onValidate: () => true,
            onClick: () => {
                console.log('Descartar Todas');
            }    
        });
        
        this.addAction({
            name: 'marcarComoLida',
            title: 'Marcar Como Lida',
            icon: 'mdi mdi-flag',
            onValidate: () => true,
            onClick: () => {
                console.log('Marcar Como Lida');
            }    
        });
        
        this.addAction({
            name: 'termosMonitorados',
            title: 'Termos Monitorados',
            icon: 'mdi mdi-flag',
            onValidate: () => true,
            onClick: () => {
                console.log('Exibe modal de Consulta Termos Monitorados');
                carregaModalConsultaTermos();
            }    
        });
        
    }

}
