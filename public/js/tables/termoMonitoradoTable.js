class TermoMonitoradoTable extends Table {

    constructor() {
        super();
        
        this.target         = 'datatable-termo-monitorado';
        this.name           = 'Termos';
        this.route          = `monitoramento/diarios/termos`;
        this.key            = ['id'];
        this.openLoaded     = true;
        this.showTitle      = false;
        this.onEdit     = async function() {
            let avo = this.parentElement.parentElement;
            let key = parseKey(avo.getAttribute('key'));
            sessionStorage.removeItem('@NAJ_WEB/termo_monitorado_key');
            sessionStorage.setItem('@NAJ_WEB/termo_monitorado_key', JSON.stringify(key.id));
            sessionStorage.removeItem('@NAJ_WEB/termo_monitorado_action');
            sessionStorage.setItem('@NAJ_WEB/termo_monitorado_action', 'edit');
            carregaModalManutencaoTermoMonitorado();
            $('#modal-consulta-termo-monitorado').addClass('z-index-100');
        };

        // campos
        this.addField({
            name: 'id',
            title: 'Código',
            width: 20
        });
        
        this.addField({
            name: 'id_monitoramento',
            title: 'ID Monitoramento',
            width: 20
        });
        
        this.addField({
            name: 'termo_pesquisa',
            title: 'Termo Pesquisa',
            width: 20
        });
        
        this.addField({
            name: 'variacoes',
            title: 'Variações',
            width: 20
        });
        
        this.addField({
            name: 'contem',
            title: 'Contêm',
            width: 20
        });
        
        this.addField({
            name: 'nao_contem',
            title: 'Não Contêm',
            width: 20
        });
        
        this.addField({
            name: 'data_inclusao',
            title: 'Data Inclusão',
            width: 20,
            onLoad:(data) => formatDate(data)
        });
        
        this.addField({
            name: 'status',
            title: 'Status',
            width: 20,
            onLoad: data => data == 0 ? "Inativo" : "Ativo"
        });

        this.addAction({
            name: 'Incluir',
            title: 'Incluir',
            icon: 'fas fa-plus',
            onValidate: () => true,
            onClick: async () => {
                sessionStorage.removeItem('@NAJ_WEB/termo_monitorado_action');
                sessionStorage.setItem('@NAJ_WEB/termo_monitorado_action', 'create');
                carregaModalManutencaoTermoMonitorado();
                $('#modal-consulta-termo-monitorado').addClass('z-index-100');
            }    
        });
        
        //Sobreescreve o método onClick do action DESTROY
        TableDefaults.actions.DESTROY.onClick = me =>
        NajAlert.confirm({
            title: 'Atenção',
            text: `Você confirma a exclusão do(s) registro(s) selecionado(s)?`
        }, {
            success: async () => {
                const { loading } = me.ids;
                loadingStart(loading);
                try {
                    let id;
                    let url;
                    let rowsSelecteds;
                    let response;
                    let status;
                    rowsSelecteds = $('.row-selected');
                    for(let i = 0; i < rowsSelecteds.length; i++){
                        status = rowsSelecteds[i].children[9].innerHTML.replace(/\s/g, '');
                        if(status == "Ativo"){
                            //Requisição para remover monitoramento na Escavador
                            id       = rowsSelecteds[i].children[3].innerHTML.replace(/\s/g, '');
                            url      = `${baseURL}escavador/removermonitoramentodiarios/${id}`;
                            response = await najTermo.getData(url);
                        }
                    }
                    //Requisição para excluir os registros no banco de daos
                    let keys = me.getSelectedRows().join(';');
                    const { data } = await api.delete(`${me.route}/many/${keys}`);
                    let total = me.data.resultado.length;
                    let selected = me.getCountSelectedRows();
                    if (me.page > 1 && total - selected <= 0) {
                        me.loadPrevious();
                    } else {
                        me.load();
                    }
                    NajAlert.toastSuccess(data.mensagem);
                } catch(e) {
                    NajAlert.toastError('Erro ao excluir o(s) registro(s)');

                    loadingDestroy(loading);
                }
            }
        });
        
        this.addAction(TableDefaults.actions.DESTROY);
        
    }

}
