//Sobreescrevemos o filtro para definir ele como DEFAULT
TableDefaults.filters.CARRY = { id: 'C', title: 'Contenha', isDefault: true };

class NpsRelacionamentoUsuariosTable extends Table {

    constructor() {
        super();
        
        this.target     = 'datatable-nps-relacionamento-usuarios';
        this.name       = 'Usuários';
        this.route      = `pesquisa/usuarios`;
        this.key        = ['pessoa_codigo', 'id'];
        this.openLoaded = true;
        this.isItEditable     = false;
        this.isItDestructible = true;
        this.showTitle        = false;

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
            name: 'nome',
            title: 'Nome',
            width: 45,
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
            name: 'email_recuperacao',
            title: 'E-mail',
            width: 25
        });

        this.addField({
            name: 'mobile_recuperacao',
            title: 'Número Móvel',
            width: 13,
            onLoad: (data, row) =>  {
                if(!row.mobile_recuperacao) return `<span class="ml-4 mascaracelular">Sem informação</span>`;

                const phoneDDD = row.mobile_recuperacao.substr(0, 2);
                const phoneFinal = row.mobile_recuperacao.substr(7);
                const phoneInitial = row.mobile_recuperacao.substr(2, 5);

                return `<span class="ml-4">(${phoneDDD}) ${phoneInitial}-${phoneFinal}</span>`;
            },
        });

        this.addField({
            name: 'ultima_pesquisa',
            title: 'Última Pesquisa',
            width: 12,
            onLoad: (_, row) => {
                if(!row.ultima_pesquisa) return `<span class="ml-4 mascaracelular">Sem informação</span>`;

                return `<span class="ml-4">${formatDate(row.ultima_pesquisa.split(' ')[0])}</span>`;
            }
        });

        //Ação de Novo Usuário
        this.addAction({
            name: 'check_all',
            title: 'Marcar todos usuários',
            icon: 'fas fa-check',
            onValidate: () => true,
            onClick: () => onClickCheckAllUsersNps()
        });

        this.addFixedFilter('usuario_tipo_id', 'I', 3);
    }

    onClickRow(row, ctrlKey) {
        if(!this.isItDestructible && !this.isItEditable){
            return;
        }
        
        const classSelected = 'row-selected';
        const classList = Array.from(row.classList);

        this.closeActions();

        if (classList.indexOf(classSelected) >= 0) {
            if (!ctrlKey) {
                byClass('row-selected').forEach(cls => {
                    cls.classList.remove('row-selected');

                    cls.querySelector('input[type=checkbox]').checked = false;
                });
            }

            row.classList.remove('row-selected');

            row.querySelector('input[type=checkbox]').checked = false;
        } else {
            if (!ctrlKey) {
                byClass('row-selected').forEach(cls => {
                    cls.classList.remove('row-selected');

                    cls.querySelector('input[type=checkbox]').checked = false;
                });
            }

            row.classList.add('row-selected');

            row.querySelector('input[type=checkbox]').checked = true;
        }

        this.resetSelectedRow();

        byClass(classSelected).forEach(el => this.addSelectedRow(
            el.getAttribute('key')
        ));

        this.notifyActions();

        //TRATAMENTO ADICIONAL
        let keyRelNps = JSON.parse(atob(row.getAttribute('key')));

        if(classList.indexOf(classSelected) >= 0) {
            
            if(!ctrlKey) {
                usersRelNps = [];
                return;
            }

            for(var i = 0; i < usersRelNps.length; i++) {
                if (usersRelNps[i].id === keyRelNps.id) {
                    usersRelNps.splice(i, 1);
                    i--;
                }
            }
        } else {
            if(!ctrlKey) {
                usersRelNps = [];
            }
            usersRelNps.push(keyRelNps);
        }
    }

}