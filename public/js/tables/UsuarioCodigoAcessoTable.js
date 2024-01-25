class UsuarioCodigoAcessoTable extends Table {

    constructor() {
        super();
        
        this.target     = 'datatable-usuarios-codigoacesso-modal';
        this.name       = 'Usuários';
        this.route      = `usuarios`;
        this.key        = ['pessoa_codigo', 'id'];
        this.openLoaded = true;
        this.onEdit     = function() {
            let avo = this.parentElement.parentElement;
            let key = parseKey(avo.getAttribute('key'));

            sessionStorage.removeItem('@NAJ_WEB/usuario_key');
            sessionStorage.setItem('@NAJ_WEB/usuario_key', JSON.stringify(key.id));
            window.location.href = '/naj/usuarios/edit';
        };

        // campos
        this.addField({
            name: 'id',
            title: 'Código',
            width: 5,
            filterOptions: {
                afterValidate: ({ col, ...rest }) => ({ ...rest, col: 'usuarios.id' })
            }
        });
        this.addField({
            name: 'nome',
            title: 'Nome',
            width: 25
        });
        this.addField({
            name: 'cpf',
            title: 'CPF',
            width: 15
        });
        this.addField({
            name: 'cpf',
            title: 'CPF',
            width: 15
        });
        this.addField({
            name: 'cidade',
            title: 'Cidade',
            width: 15
        });

        this.addAction({
            name: 'store_usuario',
            title: 'Novo Usuário',
            icon: 'fas fa-plus',
            onValidate: () => true,
            onClick: () => window.location.href = '/naj/usuarios/create'
        });

        this.addAction({
            name: 'codigo_acesso',
            title: 'Código Acesso',
            icon: 'fas fa-unlock-alt',
            onValidate: () => true,
            onClick: () => exibeModalCodigoAcessoUsuario(this)
        });
    }

}