class PesquisaNpsTable extends Table {

    constructor() {
        super();
        
        this.target           = 'datatable-pesquisa-nps';
        this.name             = 'Pesquisa Nps';
        this.route            = 'pesquisa/nps';
        this.key              = ['id'];
        this.openLoaded       = true;
        this.isItEditable     = true;
        this.isItDestructible = false;
        this.showTitle        = false;

        this.onEdit = function() {
            let avo = this.parentElement.parentElement;
            let key = parseKey(avo.getAttribute('key'));

            sessionStorage.removeItem('@NAJ_WEB/nps_key');
            sessionStorage.setItem('@NAJ_WEB/nps', JSON.stringify(key.id));
            sessionStorage.setItem('@NAJ_WEB/nps/descricao', JSON.stringify(avo.children[2].innerText));

            window.location.href = `${baseURL}pesquisa/nps/edit`;
        };

        this.addField({
            name: 'id',
            title: 'Código',
            width: 5,
            onLoad: (_, row) => {
                return `<span class="ml-4">${row.id}</span>`;
            }
        });

        this.addField({
            name: 'data_hora_inclusao',
            title: 'Inclusão',
            width: 10,
            onLoad: (_, row) => {
                return `<span class="ml-4">${formatDate(row.data_hora_inclusao.split(' ')[0])}</span>`;
            }
        });

        this.addField({
            name: 'descricao',
            title: 'Descrição',
            width: 15
        });

        this.addField({
            name: 'pergunta',
            title: 'Pergunta',
            width: 39.5
        });

        this.addField({
            name: 'quantidade_participante',
            title: 'Participantes',
            width: 8,
            onLoad: (_, row) => {
                return `<span class="ml-6">${row.quantidade_participante}</span>`;
            }
        });

        this.addField({
            name: 'quantidade_pendente',
            title: 'Pendente',
            width: 7.5,
            onLoad: (_, row) => {
                return `<span class="ml-6">${row.quantidade_pendente}</span>`;
            }
        });

        this.addField({
            name: 'quantidade_respondido',
            title: 'Respondido',
            width: 7.5,
            onLoad: (_, row) => {
                return `<span class="ml-6">${row.quantidade_respondido}</span>`;
            }
        });

        this.addField({
            name: 'quantidade_recusado',
            title: 'Recusado',
            width: 7.5,
            onLoad: (_, row) => {
                return `<span class="ml-6">${row.quantidade_recusado}</span>`;
            }
        });

        //Ação de Nova pesquisa NPS
        this.addAction({
            name: 'store_pesquisa',
            title: 'Nova Pesquisa',
            icon: 'fas fa-plus',
            onValidate: () => true,
            onClick: () => window.location.href = `${baseURL}pesquisa/nps/create`
        });
    }

}