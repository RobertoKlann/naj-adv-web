class DispositivoTable extends Table {

    constructor() {
        super();

        let user = JSON.parse(sessionStorage.getItem('@NAJ_WEB/usuario'))
        
        this.target       = 'datatable-dispositivos';
        this.name         = `Dispositivos de: ${user.nome}`;
        this.route        = `usuarios/dispositivos`;
        this.key          = ['id'];
        this.openLoaded   = true;
        this.showTitle    = true;
        this.iconDefaultConsult = 'fas fa-hand-point-down';
        this.onEdit       = function() {
            var avo = this.parentElement.parentElement;
            let key = parseKey(avo.getAttribute('key'));

            NajAlert.confirm({
                title: 'Atenção',
                text: `Você confirma a alteração do status do dispositivo?`
            }, {
                success: async () => {
                    let chave = btoa(JSON.stringify({
                        "id": key.id,
                        "usuario_id": sessionStorage.getItem('@NAJ_WEB/usuario_key')
                    }));

                    let dados = {
                        'ativo': (avo.getElementsByClassName('data-table-item')[5].textContent.trim() == "Ativo") ? 'N' : 'S',
                        'modelo': avo.getElementsByClassName('data-table-item')[3].textContent.trim(),
                        'versao_so': avo.getElementsByClassName('data-table-item')[4].textContent.trim()
                    };
        
                    try {
                        if(!chave) {                            
                            NajAlert.toastSuccess("Não foi possível alterar o registro, tente novamente mais tarde.");
                            return;
                        }
        
                        await naj.update(`dispositivos/${chave}?XDEBUG_SESSION_START`, dados);
                        tableDispositivos.load();
                    } catch(e) {
                        NajAlert.toastError('Erro ao alterar o registro');
                    }
                }
            });
        };

        // campos
        this.addField({
            name: 'id',
            title: 'Código',
            width: 15
        });
        this.addField({
            name: 'modelo',
            title: 'Modelo',
            width: 40
        });
        this.addField({
            name: 'versao_so',
            title: 'Versão SO',
            width: 25
        });
        this.addField({
            name: 'ativo',
            title: 'Status',
            width: 20,
            onLoad: (_, row) => row.ativo == 'S' ? 'Ativo' : 'Inativo'
        });

        this.addFixedFilter('usuario_id', 'I', sessionStorage.getItem('@NAJ_WEB/usuario_key'));
    }

}