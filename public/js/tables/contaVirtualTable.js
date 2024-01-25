class ContaVirtualTable extends Table {

    constructor() {
        super();
        
        this.target     = 'datatable-conta-virtual';
        this.name       = 'Conta Virtual';
        this.route      = `contavirtual`;
        this.key        = ['id'];
        this.openLoaded = true;
        this.showTitle  = false;
        this.onEdit     = async function() {
            //Antes de carregar a tela de manutenção iremos verificar se as naturezas 
            //de taxas foram definidas, caso contrário não exibiremos a tela de manutenção 
            if(!await verificaNaturezaFinanceira()){
                return;
            }
            let avo = this.parentElement.parentElement;
            let key = parseKey(avo.getAttribute('key'));
            sessionStorage.removeItem('@NAJ_WEB/conta_virtual_key');
            sessionStorage.setItem('@NAJ_WEB/conta_virtual_key', JSON.stringify(key.id));
            sessionStorage.removeItem('@NAJ_WEB/conta_virtual_action');
            sessionStorage.setItem('@NAJ_WEB/conta_virtual_action', 'edit');
            carregaModalManutencaoContaVirtual();
        };

        // campos
        this.addField({
            name: 'id',
            title: 'Código',
            width: 12.5,
            filterOptions: {
                afterValidate: ({ col, ...rest }) => ({ ...rest, col: 'boleto_cv.id' })
            }
        });
        this.addField({
            name: 'nome',
            title: 'Nome',
            width: 12.5,
            isDefault: true,
            filterOptions: {
                //Não utilizado constante para o contenha pois foi necessário para definir ele como default
                options: [TableDefaults.filters.EQUAL, TableDefaults.filters.CARRY],
                onTypeText: [FieldMask.onlyNumber],
                onAdd: (val, val2, op) => {
                    if (op !== 'B') return parseInt(val) > 0;

                    return parseInt(val) > 0 && (parseInt(val2) > parseInt(val));
                }
            }
        });
        this.addField({
            name: 'especie_descricao',
            title: 'Espécie',
            width: 12.5
        });
        this.addField({
            name: 'unidade_descricao',
            title: 'Unidade',
            width: 12.5
        });
        this.addField({
            name: 'account_id',
            title: 'Account Id',
            width: 30
        });
        this.addField({
            name: 'banco',
            title: 'Banco',
            width: 12.5,
        });
        this.addField({
            name: 'agencia',
            title: 'Agência',
            width: 12.5,
        });
        this.addField({
            name: 'tipo_conta',
            title: 'Tipo Conta',
            width: 12.5,
            onLoad: tipo => String(tipo) === 'CC' ? 'Conta Corrente' : 'Conta Poupança'
        });

        this.addAction({
            name: 'Incluir',
            title: 'Incluir',
            icon: 'fas fa-plus',
            onValidate: () => true,
            onClick: async () => {
                //Antes de carregar a tela de manutenção iremos verificar se as naturezas 
                //de taxas foram definidas, caso contrário não exibiremos a tela de manutenção 
                if(!await verificaNaturezaFinanceira()){
                    return;
                }
                sessionStorage.removeItem('@NAJ_WEB/conta_virtual_action');
                sessionStorage.setItem('@NAJ_WEB/conta_virtual_action', 'create');
                carregaModalManutencaoContaVirtual();
            }    
        });
        
        this.addAction(TableDefaults.actions.DESTROY);
        
    }

}
