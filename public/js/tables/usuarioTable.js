//Sobreescrevemos o filtro para definir ele como DEFAULT
TableDefaults.filters.CARRY = { id: 'C', title: 'Contenha', isDefault: true };

class UsuarioTable extends Table {

    constructor() {
        super();
        
        this.target     = 'datatable-usuarios';
        this.name       = 'Usuários';
        this.route      = `usuarios`;
        this.key        = ['pessoa_codigo', 'id'];
        this.openLoaded = true;
        this.showTitle  = false;
        this.onEdit     = function() {
            let avo = this.parentElement.parentElement;
            let key = parseKey(avo.getAttribute('key'));

            sessionStorage.removeItem('@NAJ_WEB/usuario_key');
            sessionStorage.setItem('@NAJ_WEB/usuario_key', JSON.stringify(key.id));
            window.location.href = `${baseURL}usuarios/edit`;
        };

        // campos
        this.addField({
            name: 'id',
            title: 'Código',
            width: 5,
            onLoad: (data, row) =>  {
                return `${row.id}`;
            },
            filterOptions: {
                afterValidate: ({ col, ...rest }) => ({ ...rest, col: 'usuarios.id' })
            }
        });
        this.addField({
            name: 'status',
            title: 'Status',
            width: 5,
            onLoad: (_, row) => row.status === 'A' ? 'Ativo' : 'Baixado'
        });
        this.addField({
            name: 'nome',
            title: 'Nome',
            width: 25,
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
            name: 'login',
            title: 'Login',
            width: 23
        });
        this.addField({
            name: 'email_recuperacao',
            title: 'E-mail',
            width: 23.5
        });
        this.addField({
            name: 'cpf',
            title: 'CPF',
            width: 8.5
        });
        this.addField({
            name: 'tipo',
            title: 'Tipo',
            width: 10,
            onLoad: (val, { usuario_tipo_id }) => `${val}`
        });
        

        //Ação de Novo Usuário
        this.addAction({
            name: 'store_usuario',
            title: 'Novo Usuário',
            icon: 'fas fa-plus',
            onValidate: () => true,
            onClick: () => window.location.href = `${baseURL}usuarios/create`
        });

        //Ação de Liberar Usuário para o APP
        this.addAction({
            name: 'codigo_acesso',
            title: 'Usuário App',
            icon: 'fas fa-unlock-alt',
            onValidate: () => {
                let hasPermission = perm('SenhaServicosAoCliente')

                if(tipoUsuarioLogado != '0' && tipoUsuarioLogado != '1' && !hasPermission)
                    return false;

                return true
            },
            onClick: () => exibeModalCodigoAcessoUsuario(this)
        });

        //Ação de copiar perfil
        this.addAction({
            name: 'copiar_perfil',
            title: 'Copiar Permissões',
            icon: 'fas fa-copy',
            onValidate: () => true,
            onClick: () => exibeModalCopiarPermissao(this)
        });

        this.addAction({
            name: 'dashboard_all',
            title: 'Estatística em Geral',
            icon: 'fas fa-chart-bar',
            onValidate: () => true,
            onClick: () => window.location.href = `${baseURL}usuarios/dashboards/geral`
        });

        this.addAction({
            name: 'dashboard_by_user',
            title: 'Estatística por Usuário',
            icon: 'fas fa-chart-bar',
            onValidate: () => true,
            onClick: () => window.location.href = `${baseURL}usuarios/dashboards/usuarios`
        });

        this.addAction({
            name: 'dashboard_by_client',
            title: 'Estatística por Cliente',
            icon: 'fas fa-chart-bar',
            onValidate: () => true,
            onClick: () => window.location.href = `${baseURL}usuarios/dashboards/cliente`
        });

        this.addAction({
            name: 'dashboard_by_device',
            title: 'Estatística por Dispositivo',
            icon: 'fas fa-chart-bar',
            onValidate: () => true,
            onClick: () => window.location.href = `${baseURL}usuarios/dashboards/dispositivo`
        });

        //Se for o SUPERVISOR logado então mostra o usuário dele na consulta, se não esconde
        if(tipoUsuarioLogado == 0) {
            this.addFixedFilter('usuario_tipo_id', 'B', 0, 4);
        } else {
            this.addFixedFilter('usuario_tipo_id', 'B', 1, 4);
        }
    }

}