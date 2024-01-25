/**
 * Classe base dos arquivos JS.
 * 
 * @author Roberto Klann
 * @since  09/01/2020
 */
class Naj {

    constructor(rotina, table) {
        this.rotina = rotina;
        this.tabela = table;
    }

    async store(url, dados) {
        let dataResponse;
        await axios({
            method : 'post',
            url,
            data   : dados
        })
        .then(response => {
            NajAlert.toast(response.data.mensagem);
            dataResponse = response.data.model;
        }).catch(e => {
            console.log(e);
        });

        if (!this.tabela) return dataResponse;
        this.tabela.page = 1;
        this.tabela.load();

        return dataResponse;
    }

    async update(url, dados) {
        await axios({
            method : 'put',
            url,
            data   : dados
        })
        .then(response => {
            NajAlert.toast(response.data.mensagem);
        }).catch(e => {
            NajAlert.toast("Erro ao alterar o registro, tente novamente mais tarde!");
        });

        if (!this.tabela) return;

        this.tabela.page = 1;
        this.tabela.load();
    }

    async destroy(url) {
        await axios({
            method : 'delete',
            url    : url
        })
        .then(response => {
            NajAlert.toast(response.data.mensagem);
        })
        .catch(error => {
            NajAlert.toast("Não foi possível excluir o registro!");
        });
    }

    async getData(url, responseTypeBlob = false) {
        let dataResponse;
        if(responseTypeBlob) {
            await axios({
                responseType: 'blob',
                method : 'get',
                url    : url
            })
            .then(response => {
                dataResponse = response.data;
            });
        } else {
            await axios({
                method : 'get',
                url    : url
            })
            .then(response => {
                dataResponse = response.data;
            });
        }

        return dataResponse;
    }

    async postData(url, dados) {
        let dataResponse;
        await axios({
            method : 'post',
            url    : url,
            data   : dados
        })
        .then(response => {
            dataResponse = response.data;
        }).catch(e => {
            console.log(e);
        });

        return dataResponse;
    }

    async updateData(url, dados) {
        let dataResponse;
        await axios({
            method : 'put',
            url    : url,
            data   : dados
        })
        .then(response => {
            dataResponse = response.data;
        }).catch(e => {
            console.log(e);
        });

        return dataResponse;
    }

    loadData(formulario, data) {
        let inputs    = $(formulario + ' input'),
            checkboxs = $(formulario + ' input:checkbox'),
            selects   = $(formulario + ' select'),
            textAreas = $(formulario + ' textarea');

        for(var i = 0; i < inputs.length; i++) {
            if(inputs[i].type == "checkbox")
              continue

            if(data[inputs[i].name] || data[inputs[i].name] == 0) //Inputs com valor 0
                inputs[i].value = data[inputs[i].name]
        }

        for(var i = 0; i < selects.length; i++) {
            if(data[selects[i].name])
                selects[i].value = data[selects[i].name]
        }

        for(var i = 0; i < textAreas.length; i++) {
            if(data[textAreas[i].name])
                textAreas[i].value = data[textAreas[i].name]
        }
        
        for(var i = 0; i < checkboxs.length; i++) {
            if(data[checkboxs[i].name]) {
                let values = data[checkboxs[i].name].split(",");
                for(var j = 0; j < values.length; j++) {
                    if(checkboxs[i].value == values[j])
                        checkboxs[i].checked = true
                }
            }
        }
    }

}