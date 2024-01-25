class PermissaoTable extends Table {

    constructor() {
        super();
        
        this.target       = 'datatable-permissao';
        this.name         = 'Permissões';
        this.route        = `usuarios/permissoes`;
        this.key          = ['id'];
        this.openLoaded   = true;
        this.showTitle    = false;
        this.forceButtonsInTreePoints = true;

        // campos
        this.addField({
            name: 'id',
            title: 'ID',
            width: 5
        });        
        this.addField({
            name: 'divisao_nome',
            title: 'Divisão',
            width: 15,
            onLoad: (val, { codigo_divisao }) => `${codigo_divisao} - ${val}`
        });
        this.addField({
            name: 'modulo',
            title: 'Modulo',
            width: 20
        });
        this.addField({
            name: 'aplicacao',
            title: 'Aplicação',
            width: 10,
            onLoad: (_, row) => row.aplicacao == 'S' ? 'Sim' : 'Não'
        });
        this.addField({
            name: 'acessar',
            title: 'Acessar',
            width: 10,
            onLoad: (_, row) => row.acessar == 'S' ? 'Sim' : 'Não'
        });
        this.addField({
            name: 'pesquisar',
            title: 'Pesquisar',
            width: 10,
            onLoad: (_, row) => row.pesquisar == 'S' ? 'Sim' : 'Não'
        });
        this.addField({
            name: 'incluir',
            title: 'Incluir',
            width: 10,
            onLoad: (_, row) => row.incluir == 'S' ? 'Sim' : 'Não'
        });
        this.addField({
            name: 'alterar',
            title: 'Aplicação',
            width: 10,
            onLoad: (_, row) => row.alterar == 'S' ? 'Sim' : 'Não'
        });
        this.addField({
            name: 'excluir',
            title: 'Excluir',
            width: 10,
            onLoad: (_, row) => row.excluir == 'S' ? 'Sim' : 'Não'
        });

        // ações
        this.addAction({
            name: 'store',
            title: 'Novo Relacionamento',
            icon: 'fas fa-plus',
            onValidate: me => true,
            onClick: () => exibeModalInluirPermissao(this, false)
        });
        this.addAction(TableDefaults.actions.DESTROY);

        this.addFixedFilter('codigo_pessoa', 'I', 1);
    }

}