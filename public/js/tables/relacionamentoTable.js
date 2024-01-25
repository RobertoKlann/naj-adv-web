class RelacionamentoTable extends Table {

    constructor(hasActionDestroy = true) {
        super();
        
        let user = JSON.parse(sessionStorage.getItem('@NAJ_WEB/usuario'));

        this.target       = 'datatable-relacionamentos';
        this.name         = `Relacionamentos de: ${user.nome}`;
        this.route        = 'usuarios/relacionamentos';
        this.key          = ['usuario_id', 'pessoa_codigo'];
        this.openLoaded   = true;
        this.isItEditable = true;
        this.showTitle    = true;

        this.onEdit     = function() {
            const avo = this.parentElement.parentElement
            const key = parseKey(avo.getAttribute('key'))

            loadDadosAlteracaoRelacionamentoPessoaUsuario(key)
        };

        // campos
        this.addField({
            name: 'pessoa_codigo',
            title: 'Código',
            width: 10
        });
        this.addField({
            name: 'nome',
            title: 'Nome',
            width: 40
        });
        this.addField({
            name: 'contas_pagar',
            title: 'Pagar',
            width: 10,
            onLoad: (data, row) =>  {
                let bChecked = (row.contas_pagar == 'S') ? 'Sim' : 'Não';

                return `${bChecked}`;
            },
        });

        this.addField({
            name: 'contas_receber',
            title: 'Receber',
            width: 10,
            onLoad: (data, row) =>  {
                let bChecked = (row.contas_receber == 'S') ? 'Sim' : 'Não';

                return `${bChecked}`;
            },
        });

        this.addField({
            name: 'atividades',
            title: 'Atividades',
            width: 10,
            onLoad: (data, row) =>  {
                let bChecked = (row.atividades == 'S') ? 'Sim' : 'Não';

                return `${bChecked}`;
            },
        });

        this.addField({
            name: 'processos',
            title: 'Processos',
            width: 10,
            onLoad: (data, row) =>  {
                let bChecked = (row.processos == 'S') ? 'Sim' : 'Não';

                return `${bChecked}`;
            },
        });

        this.addField({
            name: 'agenda',
            title: 'Agenda',
            width: 10,
            onLoad: (data, row) =>  {
                let bChecked = (row.agenda == 'S') ? 'Sim' : 'Não';

                return `${bChecked}`;
            },
        });

        //Ação de INCLUIR UM NOVO RELACIONAMENTO
        this.addAction({
            name: 'novo_relacionamento',
            title: 'Novo Relacionamento',
            icon: 'fas fa-plus',
            onValidate: () => true,
            onClick: () => exibeModalNovoRelacionamento()
        });

        if(hasActionDestroy) {
            this.addAction({
                name: 'destroy',
                title: 'Excluir',
                icon: 'fas fa-trash',
                onValidate: me => me.getCountSelectedRows() >= 1,
                onClick: me => {
                    NajAlert.confirm({
                        title: 'Atenção',
                        text: `Você confirma a exclusão do(s) registro(s) selecionado(s)?`
                    }, {
                        success: async () => {
                            loadingStart();
                            try {
                                let keys = me.getSelectedRows().join(';')
    
                                const { data } = await api.delete(`${baseURL}usuarios/relacionamentos/many/${keys}`)
    
                                let total = me.data.resultado.length
                                let selected = me.getCountSelectedRows()
    
                                if (me.page > 1 && total - selected <= 0) {
                                    loadingDestroy()
                                    me.loadPrevious()
                                } else {
                                    loadingDestroy()
                                    me.load()
                                }
    
                                NajAlert.toast(data.mensagem)
                            } catch(e) {
                                NajAlert.toast('Erro ao excluir o(s) registro(s)')
    
                                loadingDestroy()
                            }
                        }
                    })
                }
            });
        }

        let usuario = JSON.parse(sessionStorage.getItem('@NAJ_WEB/usuario_key'))

        //Filtro fixo do usuário
        if(usuario.id) {
            this.addFixedFilter('usuario_id', 'I', usuario.id)
        } else if(usuario || usuario == 0) {
            this.addFixedFilter('usuario_id', 'I', usuario)
        }
    }

}