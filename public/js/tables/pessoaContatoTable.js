class PessoaContatoTable extends Table {

    constructor() {
        super();
        
        this.target     = 'datatable-pessoa-contato';
        this.name       = 'Pessoa Contato';
        this.route      = `pessoa/contato`;
        this.key        = ['CODIGO'];
        this.openLoaded = true;
        this.showTitle  = false;
        this.onEdit     = async function() {
            //Obtêm o id do registro selecionado
            let avo = this.parentElement.parentElement;
            let key = parseKey(avo.getAttribute('key'));
            sessionStorage.removeItem('@NAJ_WEB/pessoa_contato_key');
            sessionStorage.setItem('@NAJ_WEB/pessoa_contato_key', JSON.stringify(key.CODIGO));
            sessionStorage.removeItem('@NAJ_WEB/pessoa_contato_action');
            sessionStorage.setItem('@NAJ_WEB/pessoa_contato_action', 'edit');
            carregaModalManutencaoPessoaContato();
            $('#modal-manutencao-pessoa').addClass('z-index-100');
        };

        this.addField({
            name: 'PESSOA',
            title: 'Pessoa de Contato',
            width: 40,
        });
        
        this.addField({
            name: 'TIPO',
            title: 'Tipo',
            width: 20
        });
        
        this.addField({
            name: 'CONTATO',
            title: 'Contato',
            width: 40
        });
        
        this.addAction({
            name: 'Incluir',
            title: 'Incluir',
            icon: 'fas fa-plus',
            onValidate: () => true,
            onClick: async () => {
                sessionStorage.removeItem('@NAJ_WEB/pessoa_contato_action');
                sessionStorage.setItem('@NAJ_WEB/pessoa_contato_action', 'create');
                carregaModalManutencaoPessoaContato();
                $('#modal-manutencao-pessoa').addClass('z-index-100');
            }    
        });
        
        this.addAction(TableDefaults.actions.DESTROY);
        
    }

}
