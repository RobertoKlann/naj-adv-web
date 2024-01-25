class UnidadeFinanceiraExtratoTable extends Table {

    constructor() {
        super();

        this.target           = 'datatable-unidade-financeira-extrato';
        this.name             = 'Unidade Financeira Extrato 2';
        this.route            = `unidadefinanceiraextrato`;
        this.key              = ['id'];
        this.openLoaded       = false; //Não carregar dados inicialmente 
        this.isItDestructible = false;
        this.isItEditable     = false;
        this.defaultFilters   = false;
        this.showTitle        = false;
        this.onEdit           = async function() {
            let registro = new Object();
            registro.id   = this.getAttribute('id_registro');
            registro.data = this.getAttribute('data_registro');
            let registro_json = JSON.stringify(registro);
            sessionStorage.setItem('registro_json', registro_json);
            carregaModalManutencaoUnidadeFinanceiraData(registro);
        };

        // campos
        this.addField({
            name: 'DATA',
            title: 'Data',
            width: 10,
            onLoad: (data,linha) =>  {
                let result = '';
                if(data != null){
                    result += '<span id="data-' + linha.ID + '" class="pl-1">' + formatDate(data) + '</span>';
                    if(linha.status_saque != null){
                        result += `<i id_registro='${linha.ID}' data_registro='${data}' class="fas fa-edit btn-onedit" title="Alterar"></i>`;
                    }
                    //Obtêm id da conta virtual
//                    let account_id = sessionStorage.getItem("account_id");
                    //Verifica se o extrato financeiro é de conta virtual   
//                    if(account_id  != "null" && account_id != null){
                        //Verifica se tem data de liberação
//                        if(linha.data_liberacao != null){
                            //Verifica se a data da liberação é maior ou igual a data corrente
//                            if(linha.data_liberacao >= getDateProperties().fullDate){
//                                if(table.data.resultado.length > 0 ){
                                    //Obtêm o saldo disponivel
                                    //let saldoDisponivelValor = convertMoneyToFloat($('#saldoDisponivelValor').html());
//                                    let periodo = $('#filter-tipo-periodo').val();
//                                    if(periodo == 1){
//                                        if(table.data.resultado[0].SALDO_ATUAL != saldoDisponivelValor){
//                                            result += '</br><span class="badge badge-warning p-1 m-1 badge-pendente">pendente</span>';
//                                        }
//                                    }else if(periodo == 2){
//                                        if(table.data.resultado[0].SALDO_ATUAL_CONCILIACAO != saldoDisponivelValor){
//                                            result += '</br><span class="badge badge-warning p-1 m-1 badge-pendente">pendente</span>';
//                                        }
//                                    }
//                                }
//                            }
//                        }
//                    }
                }
                return result;
            }
        });
        this.addField({
            name: 'HISTORICO',
            title: 'Histórico',
            width: 60,
            onLoad: (historico,linha) =>  {
                let result = '<span>' + historico;
                if(linha.tipo_pagamento != null){
                    if(linha.tipo_pagamento == 'B'){
                        result += '</br><span class="badge badge-info p-1 m-1">Boleto</span>';
                    } else{
                        result += '</br><span class="badge badge-primary p-1 m-1">Cartão de Crédito</span>';
                    }
                }
                if(linha.status_saque != null){
                    if(linha.status_saque == 0){
                        result += '</br><span class="badge badge-danger p-1 m-1">Pendente</span>';
                    } else if (linha.status_saque == 1){
                        result += '</br><span class="badge badge-secondary p-1 m-1">Em Processamento</span>';
                    } else if (linha.status_saque == 2){
                        result += '</br><span class="badge badge-warning p-1 m-1">Saque</span>';
                    } else if (linha.status_saque == 3){
                        result += '</br><span class="badge badge-danger p-1 m-1">rejeitado</span>';
                    }
                }
                return result + '</span>';
            }
        });
        this.addField({
            name: 'VALOR_ENTRADA',
            title: 'Valor Entrada',
            width: 10,
            onLoad: data => formatter.format(data).replace('R$','')
        });
        this.addField({
            name: 'VALOR_SAIDA',
            title: 'Valor Saida',
            width: 10,
            onLoad: data => formatter.format(data).replace('R$','')
        });
        this.addField({
            name: 'SALDO_ATUAL',
            title: 'Saldo Atual',
            width: 10,
            onLoad: data => formatter.format(data).replace('R$','')
        });
        
        this.addAction({
            name: 'verificarBoletosPendentes',
            title: 'Verificar Boletos Pendentes',
            icon: 'fas fa-barcode',
            onValidate: () => {
                let account_id = sessionStorage.getItem("account_id");
                if(account_id == 'null' || account_id == null){
                    return false
                }else{
                    return true
                }
            },
            onClick: () => {
                verificarBoletos(0);
            }    
        });
        this.addAction({
            name: 'verificarBoletosCancelados',
            title: 'Verificar Boletos Cancelados',
            icon: 'fas fa-barcode',
            onValidate: () => {
                let account_id = sessionStorage.getItem("account_id");
                if(account_id == 'null' || account_id == null){
                    return false
                }else{
                    return true
                }
            },
            onClick: () => {
                verificarBoletos(2);
            }    
        });
        this.addAction({
            name: 'verificarBoletosExpirados',
            title: 'Verificar Boletos Expirados',
            icon: 'fas fa-barcode',
            onValidate: () => {
                let account_id = sessionStorage.getItem("account_id");
                if(account_id == 'null' || account_id == null){
                    return false
                }else{
                    return true
                }
            },
            onClick: () => {
                verificarBoletos(4);
            }    
        });
        this.addAction({
            name: 'verificaSaldoDisponivelIUGU',
            title: 'Verificar Saldo Disponível',
            icon: 'fas fa-piggy-bank',
            onValidate: () => {
                let account_id = sessionStorage.getItem("account_id");
                if(account_id == 'null' || account_id == null){
                    return false
                }else{
                    return true
                }
            },
            onClick: () => {
                verificaSaldoDisponivelIUGU();
            }    
        });
        this.addAction({
            name: 'realizarSaque',
            title: 'Realizar Saque',
            icon: 'far fa-money-bill-alt',
            onValidate: () => {
                let account_id = sessionStorage.getItem("account_id");
                if(account_id == 'null' || account_id == null){
                    return false
                }else{
                    return true
                }
            },
            onClick: async () => {
                loadingStart();
                if(!await verificaSaqueArquivoTemporario()){
                    loadingDestroy();
                    return;
                }
                if(!await verificaSaldoBD(sessionStorage.getItem("account_id"))){
                    loadingDestroy();
                    return;
                }
                loadingDestroy();
                exibeModalSaque();
            }    
        });
    }
}
