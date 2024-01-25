//Sobreescrevemos o filtro para definir ele como DEFAULT
TableDefaults.filters.CARRY = { id: 'C', title: 'Contenha', isDefault: true };

class AtendimentoChatTable extends Table {

    constructor() {
        super();
        
        this.target         = 'datatable-novo-atendimento-chat-modal';
        this.name           = 'Usuários';
        this.route          = `usuarios`;
        this.key            = ['id'];
        this.openLoaded     = true;
        this.showTitle      = false;
        this.isItEditable   = false;
        this.defaultFilters = false;
        this.titleIconDefaultConsult = 'Nova Mensagem';

        // campos
        this.addField({
            name: 'id',
            title: 'Código',
            width: 5
        });
        this.addField({
            name: 'status',
            title: 'Status',
            width: 8,
            onLoad: (_, row) => row.status === 'A' ? 'Ativo' : 'Baixado'
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
            name: 'apelido',
            title: 'Apelido',
            width: 30
        });
        this.addField({
            name: 'cpf',
            title: 'CPF',
            width: 20
        });

        //Ação de copiar perfil
        this.addAction({
            name: 'check_all',
            title: 'Marcar todos usuários',
            icon: 'fas fa-check',
            onValidate: () => true,
            onClick: () => onClickCheckAllUsuariosMensagem()
        });

        //Ação de seleção avançada
        this.addAction({
            name: 'selecao_avancada',
            title: 'Seleção Avançada',
            icon: 'fas fa-search',
            onValidate: () => true,
            onClick: () => onClickSelecaoAvancada()
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
        let keyNewAtendimento = JSON.parse(atob(row.getAttribute('key')));

        if(classList.indexOf(classSelected) >= 0) {
            
            if(!ctrlKey) {
                usersNewAtendimento = [];
                return;
            }

            for(var i = 0; i < usersNewAtendimento.length; i++) {
                if (usersNewAtendimento[i].id === keyNewAtendimento.id) {
                    usersNewAtendimento.splice(i, 1);
                    i--;
                }
            }
        } else {
            if(!ctrlKey) {
                usersNewAtendimento = [];
            }
            usersNewAtendimento.push(keyNewAtendimento);
        }
    }

}