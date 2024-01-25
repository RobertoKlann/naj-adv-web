/**
 * Classe do Chat de Atendimento
 * 
 * @author Roberto Oswaldo Klann
 */
class Chat {

    constructor() {
        this.messageContainer = document.getElementById('content-messages-chat');
    }

    async loadContacts(parameters = '') {
        //Busca todos os contatos
        const NajClass = new Naj();
        let responseContatos = await NajClass.getData(`chat/mensagens${(parameters != '') ? `?f=${parameters}&limit=${currentLimitFinishContacts}&isReload=true` : `?limit=${currentLimitFinishContacts}`}&isReload=true`);

        let sHtmlTodos     = '',
            sHtmlAndamento = '',
            sHtmlFila      = '';

        //TODOS
        for(var i = 0; i < responseContatos.todos.data.length; i++) {
            if(hasMoreMessagesFinish) {
                sHtmlTodos += this.newItemContact(responseContatos.todos.data[i], null);                
            } else {
                forceApeendFinish = false;
            }
        }

        hasMoreMessagesFinish = responseContatos.todos.hasMoreMessages;

        //EM ANDAMENTO
        for(var i = 0; i < responseContatos.emAndamento.length; i++) {
            sHtmlAndamento += this.newItemContact(responseContatos.emAndamento[i], true);
        }

        //NA FILA
        for(var i = 0; i < responseContatos.naFila.length; i++) {
            sHtmlFila += this.newItemContact(responseContatos.naFila[i], false);
            $('#icone-pendentes').addClass('notify');
        }

        if(responseContatos.naFila.length == 0) {
            $('#icone-pendentes').removeClass('notify');
        }

        this.appendContacts(sHtmlTodos, sHtmlAndamento, sHtmlFila);
        loadingDestroy('loading-content-scroll-messages-finish');

        this.addEventClickItemContacts();
    }

    async appendContacts(sHtmlTodos, sHtmlAndamento, sHtmlFila) {
        let ativo = $('.customtab .active')[0].getAttribute('data-link-nav-chat');
        let nameFilter = $('#filter-name-chat').val();

        //primeiro remove a classe ativa para então colocar novamente
        $('#content-fila').removeClass('active');
        $('#content-todos').removeClass('active');
        $('#content-em-andamento').removeClass('active');

        if(ativo == 'todos') $('#content-todos').addClass('active');
        if(ativo == 'andamento') $('#content-em-andamento').addClass('active');
        if(ativo == 'fila') $('#content-fila').addClass('active');
        
        $('#contacts-andamento')[0].innerHTML = '';
        $('#contacts-pendentes')[0].innerHTML = '';

        if((tabContactsSelected != 'todos' || usedFilterNameFinished || forceApeendFinish) && (hasMoreMessagesFinish || forceApeendFinish)) {
            $('#contacts-finish')[0].innerHTML = '';
            $('#contacts-finish').append(sHtmlTodos);
        }

        $('#contacts-andamento').append(sHtmlAndamento);
        $('#contacts-pendentes').append(sHtmlFila);

        let scroll = document.querySelector('#content-scroll-messages-finish');

        scroll.addEventListener('scroll', () => {
            if (scroll.scrollTop + scroll.clientHeight >= scroll.scrollHeight && hasMoreMessagesFinish) {
                currentLimitFinishContacts += 10;
                this.loadMoreMessagesFinish();
            }
        });

        $('#filter-name-chat').val(nameFilter);
        
        $(`.dropdown-item`).removeClass('item-filter-data-chat-selected');
        $(`#filter-data-${filterDataChat.itemListSelected}`).addClass('item-filter-data-chat-selected');
    }

    async loadInfoUsuario(key) {
        await this.loadInfoUsuarioProcesso(key.id_usuario_cliente);
        await this.loadInfoUsuarioDocumentos(key.id_usuario_cliente);
        await this.loadInfoUsuarioFinanceiro(key.id_usuario_cliente);
    }

    async loadInfoUsuarioDocumentos(id_usuario_cliente) {
        if(!id_usuario_cliente) {
            this.appendInfoUsuarioDocumentos(`
                <div class="text-no-process-chat">
                    <p>Sem informações...</p>
                </div>
            `);
            return;
        }
        const NajClass = new Naj();

        let parameters = btoa(JSON.stringify({id_usuario_cliente}));
        let documentos  = await NajClass.getData(`documentos/show/${parameters}?XDEBUG_SESSION_START`),
            sHtml      = ``;

        if(documentos.data.length < 1) {
            sHtml = `
                <div class="text-no-process-chat">
                    <p>Sem informações...</p>
                </div>
            `;
        }

        for(var i = 0; i < documentos.data.length; i++) {
            sHtml += this.newInfoUsuarioDocumentos(documentos.data[i]);
        }
        
        this.appendInfoUsuarioDocumentos(sHtml);
    }

    async loadInfoUsuarioFinanceiro(id_usuario_cliente) {
        if(!id_usuario_cliente) {
            this.appendInfoUsuarioFinanceiro(`
                <div class="text-no-process-chat">
                    <p>Sem informações...</p>
                </div>
            `);
            return;
        }
        const NajClass = new Naj();

        let parameters = btoa(JSON.stringify({id_usuario_cliente}));
        let processos  = await NajClass.getData(`processos/paginate?f=${parameters}`),
            sHtml      = ``;

        if(processos.resultado.length < 1) {
            sHtml = `
                <div class="text-no-process-chat">
                    <p>Sem informações...</p>
                </div>
            `;
        }

        for(var i = 0; i < processos.resultado.length; i++) {
            sHtml += this.newInfoUsuarioFinanceiro(processos.resultado[i]);
        }
        
        this.appendInfoUsuarioFinanceiro(sHtml);
    }

    async loadInfoUsuarioProcesso(id_usuario_cliente) {
        if(!id_usuario_cliente) {
            this.appendInfoUsuarioProcesso(`
                <div class="text-no-process-chat">
                    <p>Sem informações...</p>
                </div>
            `);
            return;
        }
        const NajClass = new Naj();

        let parameters = btoa(JSON.stringify({id_usuario_cliente}));
        let processos  = await NajClass.getData(`processos/paginate?f=${parameters}`),
            sHtml      = ``;

        if(processos.resultado.length < 1) {
            sHtml = `
                <div class="text-no-process-chat">
                    <p>Sem informações...</p>
                </div>
            `;
        }

        for(var i = 0; i < processos.resultado.length; i++) {
            let anexosProcesso = await NajClass.getData(`processos/anexos/${processos.resultado[i].CODIGO_PROCESSO}`),
                sHtmlAnexos    = '';

            sHtmlAnexos = await this.newInfoUsuarioProcessoAnexo(processos.resultado[i], anexosProcesso);
            sHtml += this.newInfoUsuarioProcesso(processos.resultado[i], sHtmlAnexos);
        }
        
        this.appendInfoUsuarioProcesso(sHtml);
    }

    newInfoUsuarioFinanceiro(processo) {
        return `
            
        `;
    }

    newInfoUsuarioDocumentos(documento) {
        let sHtml = ``,
            nome  = '',
            id_anexo = 0,
            pessoaCodigo = 0;
        
        let contadorAnexos = 0;
        let identificador  = sessionStorage.getItem('@NAJ_WEB/identificadorEmpresa');

        for(var indice = 0; indice < documento.length; indice++) {

            if(documento[indice].DESCRICAO == 'DIR') {
                continue;
            }

            contadorAnexos++;

            sHtml += `
                <div class="row-content-anexo-processo row row-zebra-memo">
                    <div class="custom-control custom-checkbox col-1" style="margin-right: -15px;">
                        <input key="${btoa(JSON.stringify({"id" : documento[indice].ID, "name": documento[indice].DESCRICAO}))}" type="checkbox" class="custom-control-input" id="processo-anexo-row-${documento[indice].CODIGO}-${indice}" onclick="onClickCheckDocumentos();">
                        <label class="custom-control-label" for="processo-anexo-row-${documento[indice].CODIGO}-${indice}">&nbsp;</label>
                    </div>
                    <div class="p-0 col-8">
                        <i class="fas fa-download mt-1 mr-1 cursor-pointer" title="Baixar anexo" onclick="onClickDownloadAnexoDocumentoPessoaChat('${btoa(JSON.stringify({"id" : documento[indice].ID, "name": documento[indice].DESCRICAO, identificador}))}')"></i>
                        ${(documento[indice].DESCRICAO.length > 25) ? `${documento[indice].DESCRICAO.substr(0, 22)}...` : `${documento[indice].DESCRICAO}`}
                        ${(documento[indice].DESCRICAO.length > 25) ? `<span style="margin-left: -9px !important;">
                            <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="${documento[indice].DESCRICAO}"></i>
                        </span>` : ``}
                    </div>
                    <div class="p-0 col-3">
                        ${this.formarterData(documento[indice].DATA_ARQUIVO, '/')}
                    </div>
                </div>
            `;
            nome = documento[indice].NOME;
            id_anexo = documento[indice].ID;
            pessoaCodigo = documento[indice].CODIGO;
        }

        return `
            <div class="m-0 p-1 d-flex flex-row comment-row row-anexo-documentos">
                <div class="pl-2 comment-text w-100">
                    <div class="font-12">
                        <span class="font-12">
                            <i class="font-18 mdi mdi-open-in-new cursor-pointer text-dark mr-1" title="Ver ficha completa da pessoa" data-toggle="tooltip" onclick="onClickFichaPessoa(${pessoaCodigo});" style="margin-top: 3px;"></i>
                            ${(nome.length > 25) ? `${nome.substr(0, 27)}...` : `${nome}`}
                            ${(nome.length > 25) ? `<span>
                                <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="${nome}"></i>
                            </span>` : ``}
                            ${(nome.length > 25) ? `<br>` : ``}
                            <span class="badge badge-secondary badge-rounded" title="${(contadorAnexos) ? `+${contadorAnexos} ANEXO(S)`  : ``}">${(contadorAnexos) ? `+${contadorAnexos} ANEXOS`  : ``}</span>
                            <span class="action-icons">
                                <a data-toggle="collapse" href="#documentos-chat-${pessoaCodigo}-${id_anexo}" aria-expanded="false" onclick="onClickAnexoDocumentoProcesso(this);">
                                    <i class="fas fa-chevron-circle-right" title="Clique para ver os documentos" data-toggle="tooltip"></i>
                                </a>
                            </span>
                        </span>
                        <div class="collapse mt-1 well" id="documentos-chat-${pessoaCodigo}-${id_anexo}" aria-expanded="false">
                            ${sHtml}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    newInfoUsuarioProcesso(processo, sHtmlAnexos) {
        let sHtmlUltimaAtividade = `
            <div class="font-12 title-items-processo-chat">Atividades:</div>
            <span class="font-12 text-muted">Não há informações.</span>
        `;

        let sHtmlUltimoAndamento = `
            <div class="font-12 title-items-processo-chat">Andamentos:</div>
            <span class="font-12 text-muted">Não há informações.</span>
        `;

        let sHtmlQtdeClientes  = '';
        let sHtmlEnvolvidos    = '';
        let sHtmlAdversarios   = '';
        let sHtmlEnvolvidosAdv = '';

        if(processo.QTDE_ATIVIDADE > 0) {
            sHtmlUltimaAtividade = `
                <div class="font-12 title-items-processo-chat">
                    Atividades:
                    <span class="badge badge-secondary badge-rounded badge-nome-partes-processo cursor-pointer" onclick="onClickQuantidadeAtividadeChat(${processo.CODIGO_PROCESSO})" title="${processo.QTDE_ATIVIDADE} ATIVIDADE(S)">Total ${processo.QTDE_ATIVIDADE}</span>
                    <i class="fas fa-search font-14 cursor-pointer" title="Clique para ver as atividades" onclick="onClickQuantidadeAtividadeChat(${processo.CODIGO_PROCESSO})"></i>
                </div>
                <div class="font-12">
                    <span class="font-12">${processo.ULTIMA_ATIVIDADE_DATA} - ${processo.ULTIMA_ATIVIDADE_DESCRICAO}</span>
                </div>
            `;
        }

        if(processo.QTDE_ANDAMENTO > 0) {
            sHtmlUltimoAndamento = `
                <div class="font-12 title-items-processo-chat">
                    Andamentos:
                    <span class="badge badge-secondary badge-rounded badge-nome-partes-processo cursor-pointer" onclick="onClickQuantidadeAndamentoChat(${processo.CODIGO_PROCESSO})" title="${processo.QTDE_ANDAMENTO} ANDAMENTO(S)">Total ${processo.QTDE_ANDAMENTO}</span>
                    <i class="fas fa-search font-14 cursor-pointer" title="Clique para ver os andamentos" onclick="onClickQuantidadeAndamentoChat(${processo.CODIGO_PROCESSO})"></i>
                </div>
                <div class="font-12">
                    <span class="font-12">${processo.ULTIMO_ANDAMENTO_DATA} - ${processo.ULTIMO_ANDAMENTO_DESCRICAO}</span>
                </div>
            `;
        }

        if(processo.QTDE_CLIENTES) {
            sHtmlQtdeClientes = `<span class="badge badge-secondary badge-rounded badge-nome-partes-processo" title="+${processo.QTDE_CLIENTES} ENVOLVIDO(S)">+${processo.QTDE_CLIENTES} ENVOLVIDO(S)</span>`;
            sHtmlEnvolvidos   = `
                <span class="action-icons">
                    <a data-toggle="collapse" href="#partes-processo-${processo.CODIGO_PROCESSO}" data-key-processo="${processo.CODIGO_PROCESSO}" aria-expanded="false" onclick="onClickEnvolvidosProcesso(${processo.CODIGO_PROCESSO}, this);">
                        <i class="fas fa-chevron-circle-right" title="Clique para ver os envolvidos" data-toggle="tooltip"></i>
                    </a>
                </span>
            `;
        }

        if(processo.QTDE_ADVERSARIOS) {
            sHtmlAdversarios   = `<span class="badge badge-secondary badge-rounded badge-nome-partes-processo" title="+${processo.QTDE_ADVERSARIOS} ENVOLVIDO(S)">+${processo.QTDE_ADVERSARIOS} ENVOLVIDO(S)</span>`;
            sHtmlEnvolvidosAdv = `
                <span class="action-icons">
                    <a data-toggle="collapse" href="#partes-adv-processo-${processo.CODIGO_PROCESSO}" data-key-processo="${processo.CODIGO_PROCESSO}" aria-expanded="false" onclick="onClickEnvolvidosProcessoAdv(${processo.CODIGO_PROCESSO}, this);">
                        <i class="fas fa-chevron-circle-right" title="Clique para ver os envolvidos" data-toggle="tooltip"></i>
                    </a>
                </span>
            `;
        }

        return `
            <div class="m-0 p-1 d-flex flex-row comment-row row-anexo-processo">
                <div class="pl-2 comment-text w-100">
                    <div class="font-12 title-items-processo-chat">Informações do Processo:</div>
                    <div class="font-12 text-medium text-muted">
                        <div>
                            <span class="font-12">Código: ${processo.CODIGO_PROCESSO} <i class="font-18 mdi mdi-open-in-new cursor-pointer text-dark icone-codigo-processo-chat" title="Ver ficha do processo" data-toggle="tooltip" onclick="onClickFichaProcesso(${processo.CODIGO_PROCESSO});"></i></span>
                            <span class="font-12 ml-4">${processo.GRAU_JURISDICAO}</span>
                            ${(processo.SITUACAO == "ENCERRADO") ? `<span class="badge badge-danger badge-rounded badge-status-processo-chat" title="Baixado">Baixado</span>` : ``}
                        </div>
                        ${(processo.NUMERO_PROCESSO_NEW) ? `<div>Número: ${processo.NUMERO_PROCESSO_NEW}</div>` : ``}
                        ${(processo.CLASSE) ? `<div>${processo.CLASSE}</div>` : ``}
                        ${(processo.CARTORIO && processo.COMARCA && processo.COMARCA_UF) ? `<div>${processo.CARTORIO} - ${processo.COMARCA} (${processo.COMARCA_UF})</div>` : ``}                        
                    </div>
                    <div class="font-12 title-items-processo-chat">Envolvidos:</div>
                    <div class="font-12">
                        <i class="font-18 mdi mdi-open-in-new cursor-pointer text-dark" title="Ver ficha do envolvido" data-toggle="tooltip" onclick="onClickFichaEnvolvido(${processo.CODIGO_CLIENTE});"></i>
                        <span class="font-12">
                            ${(processo.NOME_CLIENTE.length > 32) ? `${processo.NOME_CLIENTE.substr(0, 30)}...` : `${processo.NOME_CLIENTE}`}
                            ${(processo.NOME_CLIENTE.length > 32) ? `<span>
                                <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="${processo.NOME_CLIENTE}"></i>
                            </span>` : ``}
                            <small><span class="text-muted">(${processo.QUALIFICA_CLIENTE}) </span></small>
                            ${(processo.NOME_CLIENTE.length > 32) ? `<br>` : ``}
                            ${sHtmlQtdeClientes}
                            ${sHtmlEnvolvidos}
                        </span>
                        <div class="collapse mt-1 well" id="partes-processo-${processo.CODIGO_PROCESSO}" aria-expanded="false"></div>
                        <span class="font-12">
                            <div class="font-12">
                                <i class="font-18 mdi mdi-open-in-new cursor-pointer text-dark" title="Ver ficha do envolvido" data-toggle="tooltip" onclick="onClickFichaEnvolvido(${processo.CODIGO_ADVERSARIO});"></i>
                                ${processo.NOME_ADVERSARIO} 
                                <small><span class="text-muted">(${processo.QUALIFICA_ADVERSARIO})</span></small>
                            </div>
                            ${sHtmlAdversarios}
                            ${sHtmlEnvolvidosAdv}
                        </span>
                        <div class="collapse mt-1 well" id="partes-adv-processo-${processo.CODIGO_PROCESSO}" aria-expanded="false"></div>
                    </div>
                    ${sHtmlUltimoAndamento}
                    ${sHtmlUltimaAtividade}
                    ${sHtmlAnexos}
                </div>
            </div>
        `;
    }

    async newInfoUsuarioProcessoAnexo(processo, anexos) {
        let sHtmlAnexos = '';
        let sHtmlAnexosAriaExpanded = '';
        let sHtmlAnexosSemInformacao = '<span class="font-12 text-muted">Não há informações.</span>';
        let contadorAnexos = 0;
        let identificador = sessionStorage.getItem('@NAJ_WEB/identificadorEmpresa');

        for(var indice = 0; indice < anexos.length; indice++) {

            if(anexos[indice].NOME_ARQUIVO == 'DIR' || !anexos[indice].DESCRICAO || !anexos[indice].ID)
                continue

            contadorAnexos++;

            let hashFile = btoa(JSON.stringify({"id" : anexos[indice].ID, "name": anexos[indice].DESCRICAO.replace('–', '-'), identificador}))

            sHtmlAnexos += `
                <div class="row-content-anexo-processo row row-zebra-memo">
                    <div class="custom-control custom-checkbox col-1" style="margin-right: -15px;">
                        <input key="${hashFile}" type="checkbox" class="custom-control-input" id="processo-anexo-${anexos[indice].CODIGO_PROCESSO}-row-${indice}" onclick="onClickCheckAnexoProcesso();">
                        <label class="custom-control-label" for="processo-anexo-${anexos[indice].CODIGO_PROCESSO}-row-${indice}">&nbsp;</label>                        
                    </div>
                    <div class="p-0 col-8 input-group">
                        <i class="fas fa-download mt-1 mr-1 cursor-pointer" title="Baixar anexo" onclick="onClickDownloadAnexoProcessoChat('${hashFile}')"></i>
                        <span>${(anexos[indice].DESCRICAO.length > 25) ? `${anexos[indice].DESCRICAO.substr(0, 22)}...` : `${anexos[indice].DESCRICAO}`}</span>&emsp;
                        ${(anexos[indice].DESCRICAO.length > 25) ? `<span style="margin-left: -9px !important;">
                            <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="${anexos[indice].DESCRICAO}"></i>
                        </span>` : ``}
                        
                    </div>
                    <div class="p-0 col-3">
                        ${this.formarterData(anexos[indice].DATA_ARQUIVO, '/')}
                    </div>
                </div>
            `;
        }

        if(anexos.length > 0) {
            sHtmlAnexosSemInformacao = '';
            sHtmlAnexosAriaExpanded = `
                <span class="action-icons">
                    <a data-toggle="collapse" href="#processo-${processo.CODIGO_PROCESSO}-anexo" data-key-processo-anexo="${processo.CODIGO_PROCESSO}" aria-expanded="false" onclick="onClickAnexoProcesso(this);">
                        <i class="fas fa-chevron-circle-right" title="Clique para ver os anexos" data-toggle="tooltip"></i>
                    </a>
                </span>
            `;
        }

        return `
            <div class="font-12 title-items-processo-chat">
                Documentos:
                <span class="badge badge-secondary badge-rounded badge-documentos-processo-chat" title="${(anexos.length) ? `+${contadorAnexos} ANEXO(S)`  : ``}">${(anexos.length) ? `+${contadorAnexos} ANEXO(S)`  : ``}</span>
                ${sHtmlAnexosAriaExpanded}
            </div>
            ${sHtmlAnexosSemInformacao}
            <div class="font-12 collapse well" id="processo-${processo.CODIGO_PROCESSO}-anexo">
                ${sHtmlAnexos}
            </div>
        `;
    }

    appendInfoUsuarioDocumentos(sHtml) {
        $('#info-documentos-user')[0].innerHTML = ``;
        $('#info-documentos-user')[0].innerHTML = `
            <div class="comment-widgets pb-0 mb-0">
                ${sHtml}
            </div>
            <div id="button-encaminhar-documento" style="display: none;">
                <button type="button" class="btn btn-info ml-1" style="width: 15vw !important; margin-left: 4px !important;" onclick="shareAnexos('row-anexo-documentos');"><i class="fas fa-share mr-2"></i>Encaminhar<span class="ml-1" id="contador-check-documentos">(0)</span></button>
                <button type="button" class="btn btn-danger" style="width: 8vw !important;" onclick="onClickCancelarEncaminharAnexos('row-anexo-documentos');"><i class="fas fa-times mr-2"></i>Cancelar </button>
            </div>
        `;
        $('.fa-info-circle').tooltip('update');
    }

    appendInfoUsuarioFinanceiro(sHtml) {
        $('#info-financeiro-user')[0].innerHTML = ``;
        $('#info-financeiro-user')[0].innerHTML = `
            <div class="comment-widgets pb-0 mb-0">
                ${sHtml}
            </div>
        `;
    }

    appendInfoUsuarioProcesso(sHtml) {
        $('#info-processos-user')[0].innerHTML = ``;
        $('#info-processos-user')[0].innerHTML = `
            <div class="comment-widgets pb-0 mb-0">
                ${sHtml}
            </div>
            <div id="button-encaminhar-anexo-processo" style="display: none;">
                <button type="button" id="" class="btn btn-info ml-1" style="width: 15vw !important; margin-left: 4px !important;" onclick="shareAnexos('row-anexo-processo');"><i class="fas fa-share mr-2"></i>Encaminhar<span class="ml-1" id="contador-check-processo">(0)</span></button>
                <button type="button" class="btn btn-danger" style="width: 8vw !important;" onclick="onClickCancelarEncaminharAnexos('row-anexo-processo');"><i class="fas fa-times mr-2"></i>Cancelar </button>
            </div>
        `;

        $('.fa-info-circle').tooltip('update');
    }

    async startChat(key, moveScrollView = true, useLoading = true, loadInfoUser = true, loadRascunho = true) {
        if(useLoading) {
            loadingStart('loading-message-chat');
            loadingStart('loading-contacts-chat-second');
        }

        const NajClass = new Naj();

        let parameters = btoa(JSON.stringify({"limit" : limitAtualChat, "id_usuario_chat": key.id_usuario_cliente, 'updateStatus': !isFilaChatCurrent && !isFinishChatCurrent}));
        let responseChat = await NajClass.getData(`chat/mensagem/publico/${key.id_chat}?f=${parameters}`);
        let sHtml = "";

        if(!responseChat.isLastPage) {
            sHtml = `
                <div class="row chat-item-mostrar-mais-messages" onclick="onClickButtonMaisMensagemChat();">
                    <p class="text-button-mais-mensagem-chat">Mostrar mais mensagem...</p>
                </div>
            `;
        }

        //se veio dados do dispositivo, utilizado para enviar push para o dispositivo
        if(responseChat.dados_dispositivos && responseChat.dados_dispositivos.naj.length > 0) {
            sessionStorage.setItem('@NAJ_WEB/dados_dispositivo_usuario_chat', btoa(JSON.stringify({'dados': responseChat.dados_dispositivos.naj})));
        } else {
            sessionStorage.removeItem('@NAJ_WEB/dados_dispositivo_usuario_chat');
        }

        this.hideContentEditorMensagemChat();
        this.hideContentAnexoChat();

        messagesAppendedChatFila = [];
        messagesAppendedChatFinish = [];
        for(var i = 0; i < responseChat.data.length; i++) {
            if(!this.isBeginConversation(responseChat.data[i].conteudo) && !this.isFinishConversation(responseChat.data[i].conteudo) && !this.isTransferConversation(responseChat.data[i].conteudo) && responseChat.data[i].tipo_conteudo == '0') {
                let isEu = idUsuarioLogado == responseChat.data[i].id_usuario_mensagem;
                sHtml += this.newContentNewMessage(responseChat.data[i], isEu);
            } else if(responseChat.data[i].tipo_conteudo == 1) {
                let isEu = idUsuarioLogado == responseChat.data[i].id_usuario_mensagem;
                sHtml += this.newContentAnexo(responseChat.data[i], isEu);
            } else if(this.isBeginConversation(responseChat.data[i].conteudo)) {
                sHtml += this.newContentStartMessage(responseChat.data[i]);
            } else if(this.isTransferConversation(responseChat.data[i].conteudo)) {
                sHtml += this.newContentTransferConversation(responseChat.data[i]);
            } else {
                sHtml += this.newContentFinishMessage(responseChat.data[i]);
            }
            id_atendimento_current = responseChat.data[i].id_atendimento;

            if(isFilaChatCurrent)
                messagesAppendedChatFila.push(responseChat.data[i].id_mensagem);

            if(isFinishChatCurrent)
                messagesAppendedChatFinish.push(responseChat.data[i].id_mensagem);
        }

        this.appendMessagesInChat(sHtml);
        this.loadOthersInfoChat(key, moveScrollView, loadRascunho, useLoading, loadInfoUser);
    }

    async loadNewMessages(key, moveScrollView = true, useLoading = true, loadInfoUser = true, loadRascunho = true) {
        if(useLoading) {
            loadingStart('loading-message-chat');
            loadingStart('loading-contacts-chat-second');
        }

        const NajClass = new Naj();
        const parameters = btoa(JSON.stringify({"limit" : limitAtualChat, "id_usuario_chat": key.id_usuario_cliente, 'updateStatus': !isFilaChatCurrent && !isFinishChatCurrent}));
        const responseChat = await NajClass.getData(`chat/mensagem/new/${key.id_chat}?f=${parameters}`);

        //se veio dados do dispositivo, utilizado para enviar push para o dispositivo
        if(responseChat.dados_dispositivos && responseChat.dados_dispositivos.naj.length > 0) {
            sessionStorage.setItem('@NAJ_WEB/dados_dispositivo_usuario_chat', btoa(JSON.stringify({'dados': responseChat.dados_dispositivos.naj})));
        } else {
            sessionStorage.removeItem('@NAJ_WEB/dados_dispositivo_usuario_chat');
        }

        this.hideContentEditorMensagemChat();
        this.hideContentAnexoChat();
        this.updateStatusMessagesChat(responseChat.messagesReadCurrentChat);

        for(var i = 0; i < responseChat.data.length; i++) {

            if(!isFilaChatCurrent && !isFinishChatCurrent) {
                if(!this.isBeginConversation(responseChat.data[i].conteudo) && !this.isFinishConversation(responseChat.data[i].conteudo) && !this.isTransferConversation(responseChat.data[i].conteudo) && responseChat.data[i].tipo_conteudo == '0') {
                    let isEu = idUsuarioLogado == responseChat.data[i].id_usuario_mensagem;
                    $('#content-messages-chat').append(this.newContentNewMessage(responseChat.data[i], isEu));
                } else if(responseChat.data[i].tipo_conteudo == 1) {
                    let isEu = idUsuarioLogado == responseChat.data[i].id_usuario_mensagem;
                    $('#content-messages-chat').append(this.newContentAnexo(responseChat.data[i], isEu));
                } else if(this.isBeginConversation(responseChat.data[i].conteudo)) {
                    $('#content-messages-chat').append(this.newContentStartMessage(responseChat.data[i]));
                } else if(this.isTransferConversation(responseChat.data[i].conteudo)) {
                    $('#content-messages-chat').append(this.newContentTransferConversation(responseChat.data[i]));
                } else {
                    $('#content-messages-chat').append(this.newContentFinishMessage(responseChat.data[i]));
                }
            } else {

                //Validando se é a tab FILA e se já foi adicionado no CHAT a mensagem
                if(messagesAppendedChatFila.indexOf(responseChat.data[i].id_mensagem) < 0 && isFilaChatCurrent) {
                    if(!this.isBeginConversation(responseChat.data[i].conteudo) && !this.isFinishConversation(responseChat.data[i].conteudo) && !this.isTransferConversation(responseChat.data[i].conteudo) && responseChat.data[i].tipo_conteudo == '0') {
                        let isEu = idUsuarioLogado == responseChat.data[i].id_usuario_mensagem;
                        $('#content-messages-chat').append(this.newContentNewMessage(responseChat.data[i], isEu));
                    } else if(responseChat.data[i].tipo_conteudo == 1) {
                        let isEu = idUsuarioLogado == responseChat.data[i].id_usuario_mensagem;
                        $('#content-messages-chat').append(this.newContentAnexo(responseChat.data[i], isEu));
                    } else if(this.isBeginConversation(responseChat.data[i].conteudo)) {
                        $('#content-messages-chat').append(this.newContentStartMessage(responseChat.data[i]));
                    } else if(this.isTransferConversation(responseChat.data[i].conteudo)) {
                        $('#content-messages-chat').append(this.newContentTransferConversation(responseChat.data[i]));
                    } else {
                        $('#content-messages-chat').append(this.newContentFinishMessage(responseChat.data[i]));
                    }

                    if(isFilaChatCurrent)
                        messagesAppendedChatFila.push(responseChat.data[i].id_mensagem);
                }

                //Validando se é a tab ENCERRADOS e se já foi adicionado no CHAT a mensagem
                if(messagesAppendedChatFinish.indexOf(responseChat.data[i].id_mensagem) < 0 && isFinishChatCurrent) {
                    if(!this.isBeginConversation(responseChat.data[i].conteudo) && !this.isFinishConversation(responseChat.data[i].conteudo) && !this.isTransferConversation(responseChat.data[i].conteudo) && responseChat.data[i].tipo_conteudo == '0') {
                        let isEu = idUsuarioLogado == responseChat.data[i].id_usuario_mensagem;
                        $('#content-messages-chat').append(this.newContentNewMessage(responseChat.data[i], isEu));
                    } else if(responseChat.data[i].tipo_conteudo == 1) {
                        let isEu = idUsuarioLogado == responseChat.data[i].id_usuario_mensagem;
                        $('#content-messages-chat').append(this.newContentAnexo(responseChat.data[i], isEu));
                    } else if(this.isBeginConversation(responseChat.data[i].conteudo)) {
                        $('#content-messages-chat').append(this.newContentStartMessage(responseChat.data[i]));
                    } else if(this.isTransferConversation(responseChat.data[i].conteudo)) {
                        $('#content-messages-chat').append(this.newContentTransferConversation(responseChat.data[i]));
                    } else {
                        $('#content-messages-chat').append(this.newContentFinishMessage(responseChat.data[i]));
                    }

                    if(isFinishChatCurrent)
                        messagesAppendedChatFinish.push(responseChat.data[i].id_mensagem);
                }
            }
            
            id_atendimento_current = responseChat.data[i].id_atendimento;
        }

        this.loadOthersInfoChat(key, moveScrollView, loadRascunho, useLoading, loadInfoUser);
    }

    async moreMessagesOld(key, moveScrollView = false, useLoading = true, loadInfoUser = true, loadRascunho = true) {
        if(useLoading) {
            loadingStart('loading-message-chat');
            loadingStart('loading-contacts-chat-second');
        }

        const NajClass = new Naj();
        const parameters = btoa(JSON.stringify({"offset" : offsetOldMessages, "id_usuario_chat": key.id_usuario_cliente, 'updateStatus': !isFilaChatCurrent && !isFinishChatCurrent}));
        const responseChat = await NajClass.getData(`chat/mensagem/old/${key.id_chat}?f=${parameters}`);

        //se veio dados do dispositivo, utilizado para enviar push para o dispositivo
        if(responseChat.dados_dispositivos && responseChat.dados_dispositivos.naj.length > 0) {
            sessionStorage.setItem('@NAJ_WEB/dados_dispositivo_usuario_chat', btoa(JSON.stringify({'dados': responseChat.dados_dispositivos.naj})));
        } else {
            sessionStorage.removeItem('@NAJ_WEB/dados_dispositivo_usuario_chat');
        }

        let totalHeight = 0;

        this.hideContentEditorMensagemChat();
        this.hideContentAnexoChat();

        for(var i = 0; i < responseChat.data.length; i++) {
            totalHeight += $('#content-messages-chat').children('li').first()[0].offsetHeight;
            if(!this.isBeginConversation(responseChat.data[i].conteudo) && !this.isFinishConversation(responseChat.data[i].conteudo) && !this.isTransferConversation(responseChat.data[i].conteudo) && responseChat.data[i].tipo_conteudo == '0') {
                let isEu = idUsuarioLogado == responseChat.data[i].id_usuario_mensagem;
                $(this.newContentNewMessage(responseChat.data[i], isEu)).insertBefore($('#content-messages-chat').children('li').first());
            } else if(responseChat.data[i].tipo_conteudo == 1) {
                let isEu = idUsuarioLogado == responseChat.data[i].id_usuario_mensagem;
                $(this.newContentAnexo(responseChat.data[i], isEu)).insertBefore($('#content-messages-chat').children('li').first());
            } else if(this.isBeginConversation(responseChat.data[i].conteudo)) {
                $(this.newContentStartMessage(responseChat.data[i])).insertBefore($('#content-messages-chat').children('li').first());
            } else if(this.isTransferConversation(responseChat.data[i].conteudo)) {
                $(this.newContentTransferConversation(responseChat.data[i])).insertBefore($('#content-messages-chat').children('li').first());
            } else {
                $(this.newContentFinishMessage(responseChat.data[i])).insertBefore($('#content-messages-chat').children('li').first());
            }

            id_atendimento_current = responseChat.data[i].id_atendimento;
        }

        document.getElementById('pololo').scrollTop = totalHeight;

        this.loadOthersInfoChat(key, moveScrollView, loadRascunho, useLoading, loadInfoUser);
    }

    async loadOthersInfoChat(key, moveScrollView, loadRascunho, useLoading, loadInfoUser) {
        $('.content-message-select-user-chat').hide();

        if(moveScrollView) {
            chat.scrollToBottom();
        }

        //Carrega as informações do RASCUNHO da mensagem
        if(loadRascunho) {
            await this.createUpdateRascunhoMessage(key.id_chat, null);
            await this.loadMessageRascunhoChat(key.id_chat);
        }

        $('.icon-download-chat').tooltip('update');
        $('.icon-upload-anexo-ficha-pessoa-chat').tooltip('update');

        loadingDestroy('loading-message-chat');
        loadingDestroy('loading-contacts-chat-second');
        
        //Carrega as informações do usuário, PROCESSOS, CADASTRO E RELACIONAMENTOS
        if(loadInfoUser) {
            if(useLoading) {
                loadingStart('loading-info-user-chat');
                loadingStart('loading-buttons-atendimento');
                loadingStart('loading-contacts-chat-second');
            }

            await this.loadInfoUsuario(key);
            loadingDestroy('loading-info-user-chat');
            loadingDestroy('loading-contacts-chat-second');
            loadingDestroy('loading-buttons-atendimento');
        }
    }

    newContentAnexo(fileUpload, isOdd) {
        let spanIconStatusMessage = `<span class="iconesStatusMessage" id="status-message-${fileUpload.id_mensagem}"><i class="mdi mdi-check-all ml-1"></i></span>`;

        //Verificar o status que veio
        if(fileUpload.status == 1) {
            spanIconStatusMessage = `<span class="iconesStatusMessage" id="status-message-${fileUpload.id_mensagem}"><i class="mdi mdi-check-all ml-1"></i></span>`;
        } else if(fileUpload.status == 2) {
            spanIconStatusMessage = `<span class="iconesStatusMessageSuccess" id="status-message-${fileUpload.id_mensagem}"><i class="mdi mdi-check-all ml-1"></i></span>`;
        } else if(fileUpload.status == 0) {
            spanIconStatusMessage = `<span class="iconesStatusMessage" id="status-message-${fileUpload.id_mensagem}"><i class="mdi mdi-check ml-1"></i></span>`;
        }

        let data_hora = '';

        if(fileUpload.data_hora.split(' ')[0] == getDataAtual()) {
            data_hora = `Hoje ${this.formaterDataInHora(fileUpload.data_hora)}`;
        } else {
            data_hora = `${this.convertDataHora(fileUpload.data_hora)}`;
        }

        let classOdd = 'color-odd-naj';

        if(!isOdd && (fileUpload.usuario_tipo_id != 3)) {
            classOdd = 'color-no-odd-naj-no-usuario-cliente-message';
        } else if(!isOdd && (fileUpload.usuario_tipo_id == 3)) {
            classOdd = 'color-no-odd-naj-usuario-cliente-message';
        }

        let extensao = fileUpload.conteudo.split('.')[1];
        let titleIconDownload = fileUpload.file_type == 2 ? 'Baixar áudio para ouvir' : 'Baixar';
        let fileNameAttachment = ''

        if (fileUpload.file_type != 2) {
            const icon = (fileUpload.file_type == 0) ? '<i class="icon-anexo-chat fas fa-image"></i>' : '<i class="icon-anexo-chat fas fa-file"></i>'
            fileNameAttachment = `
                <p class="mb-0 text-chat-messages" style="margin-top: 4px; word-break: break-word;">${icon} ${fileUpload.conteudo}</p>
            ` 
        }

        return `
            <li class="${(!isOdd) ? 'no-odd-chat-naj' : 'odd-chat-naj odd '} chat-item">
                <div class="chat-content">
                    <div class="box bg-light-success p-2 ${classOdd}" style="max-width: 100%;">
                        <h5 class="font-medium m-0">${fileUpload.nome}</h5>
                        <div class="mt-2 content-info-anexo-chat">
                            <div class="m-0 d-flex">
                                ${fileNameAttachment}
                                <i id="btn-download-${fileUpload.id_mensagem}" onclick="onClickDownloadAnexoChat(${fileUpload.id_mensagem}, '${fileUpload.conteudo}', ${fileUpload.file_type});" class="icon-anexo-chat fas fa-download icon-download-chat" data-toggle="tooltip" title="${titleIconDownload}"></i>
                                ${(fileUpload.usuario_tipo_id == 3 && fileUpload.file_type == 2)
                                    ?
                                    `<i class="icon-anexo-chat fas fa-paperclip icon-upload-anexo-ficha-pessoa-chat" onclick="onClickUploadAnexoFichaPessoaChat('${extensao}', ${fileUpload.file_size}, ${fileUpload.id_mensagem}, '${fileUpload.conteudo}');" data-toggle="tooltip" title="Anexar na ficha da pessoa"></i>`
                                    :
                                    ``
                                }
                                ${
                                    (fileUpload.file_type == 2) ?
                                    `<audio id="audio-${fileUpload.id_mensagem}" controls style="height: 30px;">
                                        <source id="source-${fileUpload.id_mensagem}" src="" type="audio/${extensao}"/>
                                    </audio>`
                                    : ''
                                }
                                ${(fileUpload.usuario_tipo_id == 3 && fileUpload.file_type != 2)
                                    ?
                                    `<i class="icon-anexo-chat fas fa-paperclip icon-upload-anexo-ficha-pessoa-chat" onclick="onClickUploadAnexoFichaPessoaChat('${extensao}', ${fileUpload.file_size}, ${fileUpload.id_mensagem}, '${fileUpload.conteudo}');" data-toggle="tooltip" title="Anexar na ficha da pessoa"></i>`
                                    :
                                    ``
                                }
                            </div>
                        </div>
                        <div class="m-0">
                            <div class="m-0 content-size-anexo-chat">${this.formaterSizeAnexo(fileUpload.file_size)}</div>
                            <div class="chat-time m-0 ${(!isOdd) ? ' ajuste-hora-anexo-chat' : ''}">${data_hora}${(!isOdd) ? '' : spanIconStatusMessage}</div>
                        </div>
                    </div>
                </div>
            </li>
        `;
    }

    appendMessagesInChat(sHtml) {
        $('#content-messages-chat')[0].innerHTML = `${sHtml}`;

        $('.icon-download-chat').tooltip('update');
        $('.icon-upload-anexo-ficha-pessoa-chat').tooltip('update');
    }

    newContentStartMessage(message) {
        if(!message) return;
        return `
            <li class="row chat-item-inicio-fim-conversa">
                <p class="text-header-inicio-atendimento">${message.nome} - Iniciou atendimento</p>
                <p class="text-info-inicio-atendimento">${this.convertDataHora(message.data_hora)}</p>
            </li>
        `;
    }

    newContentFinishMessage(message) {
        if(!message) return;
        return `
            <li class="row mt-4 chat-item-inicio-fim-conversa">
                <p class="text-header-fim-atendimento">${message.nome} - Encerrou atendimento</p>
                <p class="text-info-fim-atendimento">${this.convertDataHora(message.data_hora)}</p>
            </li>
        `;
    }

    newContentTransferConversation(message) {
        if(!message) return;
        return `
            <li class="row mt-4 chat-item-inicio-fim-conversa">
                <p class="text-header-transfer-atendimento">${message.conteudo}</p>
                <p class="text-info-transfer-atendimento">${this.convertDataHora(message.data_hora)}</p>
            </li>
        `;
    }

    newContentNewMessage(message, isOdd) {
        if(!message) return;

        let spanIconStatusMessage = `<span class="iconesStatusMessage" id="status-message-${message.id_mensagem}"><i class="mdi mdi-check-all ml-1"></i></span>`;
        //Verificar o status que veio
        if(message.status == 1) {
            spanIconStatusMessage = `<span class="iconesStatusMessage" id="status-message-${message.id_mensagem}"><i class="mdi mdi-check-all ml-1"></i></span>`;
        } else if(message.status == 2) {
            spanIconStatusMessage = `<span class="iconesStatusMessageSuccess" id="status-message-${message.id_mensagem}"><i class="mdi mdi-check-all ml-1"></i></span>`;
        } else if(message.status == 0) {
            spanIconStatusMessage = `<span class="iconesStatusMessage" id="status-message-${message.id_mensagem}"><i class="mdi mdi-check ml-1"></i></span>`;
        }

        let data_hora = '';

        if(message.data_hora.split(' ')[0] == getDataAtual()) {
            data_hora = `Hoje ${this.formaterDataInHora(message.data_hora)}`;
        } else {
            data_hora = `${this.convertDataHora(message.data_hora)}`;
        }

        let classOdd = 'color-odd-naj';

        if(!isOdd && (message.usuario_tipo_id != 3)) {
            classOdd = 'color-no-odd-naj-no-usuario-cliente-message';
        } else if(!isOdd && (message.usuario_tipo_id == 3)) {
            classOdd = 'color-no-odd-naj-usuario-cliente-message';
        }

        let conteudo;
        if(message.conteudo.search('http') > -1) {
            if(message.conteudo.search('<a href="') > -1) {
                conteudo = message.conteudo;
                //conteudo = message.conteudo.replace(/((http:|https:)[^\s]+[\w])/g, '<a href="$1" target="_blank" style="word-wrap: break-word;"><i class="icone-link-chat fas fa-link mr-1"></i> $1</a>');
            } else {
                conteudo = message.conteudo.replace(/((http:|https:)[^\s]+[\w])/g, '<a href="$1" target="_blank" style="word-wrap: break-word;"><i class="icone-link-chat fas fa-link mr-1"></i> $1</a>');
            }
        } else {
            conteudo = message.conteudo.replace(/((http:|https:)[^\s]+[\w])/g, '<a href="$1" target="_blank" style="word-wrap: break-word;"><i class="icone-link-chat fas fa-link mr-1"></i>$1</a>');
        }

        return `
            <li class="${(!isOdd) ? 'no-odd-chat-naj' : 'odd-chat-naj odd '} chat-item">
                <div class="chat-content">
                    <div class="box bg-light-success p-2 ${classOdd}" style="max-width: 100%;">
                        <h5 class="font-medium m-0">${message.nome}</h5>
                        <div class="yiyiyiyiyi">
                            <span class="mb-0 text-chat-messages" style="word-wrap: break-word;">${conteudo}</span>                            
                        </div>
                        <div class="chat-time m-0">${data_hora}${(!isOdd) ? '' : spanIconStatusMessage}</div>
                    </div>
                </div>
            </li>
        `;
    }

    newItemContact(item, status_atendimento = null) {
        let classSelected = (item.id_chat == id_chat_current_selected) ? 'selected-conversa-chat' : '';
        let mensagem = item.ultima_mensagem.replace(/(<([^>]+)>)/ig,"");
        let data_hora = '';

        if(mensagem.length > 40) {
            mensagem = `${mensagem.substr(0, 37)} ...`;
        }

        if(item.data_hora.split(' ')[0] == getDataAtual()) {
            data_hora = `Hoje ${this.formaterDataInHora(item.data_hora)}`;
        } else {
            data_hora = `${this.convertDataHora(item.data_hora)}`;
        }

        return `
            <a key="${btoa(JSON.stringify({'id_chat' : item.id_chat, 'id_usuario_cliente': item.id_usuario_cliente, 'id_usuario_atendimento': item.id_usuario_atendimento}))}" href="javascript:void(0)" class="message-item ${classSelected}" data-chat-status="${status_atendimento}" data-user-chat="${btoa(JSON.stringify({"id_usuario": item.id_usuario_cliente, "nome": item.cliente, "apelido": item.apelido}))}">
                <span class="user-img"> <img src="${appUrl}imagens/user.png" alt="user" class="rounded-circle"></span>
                <div class="mail-contnet">
                    <h5 class="message-title weight-500">${item.cliente}</h5>
                    <div class="d-flex">
                        <span class="mail-desc" style="margin-right: 8%;">${(item.id_usuario_cliente == idUsuarioLogado) ? `Você: ${mensagem}` : mensagem}</span>
                        
                    </div>
                    <div class="d-flex">
                        <span class="text-horario-chat">${data_hora}</span>
                        ${(item.qtde_novas > 0) ? `<span class="badge badge-success text-white font-normal badge-pill float-right" style="right: 20px; position: absolute;">${item.qtde_novas}</span>` : ``}
                        ${(item.meu_usuario == 1 && status_atendimento) ? `<i class="fas fa-star text-warning float-right" style="right: 3px; position: absolute;"></i>` : ``}
                    </div>
                </div>
            </a>
        `;
    }

    async createUpdateRascunhoMessage(id_chat, message, mensagem_digitada = false) {
        let rascunhoChat = JSON.parse(localStorage.getItem('@NAJWEB/rascunho_chat'));

        if(!rascunhoChat) {
            rascunhoChat = [];
            rascunhoChat.push({id_chat, message});
        } else {
            let newChat = true;
            for(var i = 0; i < rascunhoChat.length; i++) {
                if(rascunhoChat[i].id_chat == id_chat) {
                    newChat = false;

                    //Se for digitada no chat atualiza o que ta no rascunho
                    if(mensagem_digitada) {
                        rascunhoChat[i].message = message;
                    } else {
                        rascunhoChat[i].message = rascunhoChat[i].message;
                    }
                    break;
                }
            }

            if(newChat) {
                rascunhoChat.push({id_chat, message});
            }            
        }

        localStorage.setItem('@NAJWEB/rascunho_chat', JSON.stringify(rascunhoChat));
    }

    async loadMessageRascunhoChat(id_chat) {
        let rascunhoChat = JSON.parse(localStorage.getItem('@NAJWEB/rascunho_chat'));

        for(var i = 0; i < rascunhoChat.length; i++) {
            if(rascunhoChat[i].id_chat == id_chat) {
                $('#input-text-chat-enviar').val(rascunhoChat[i].message);

                //Se tiver mensagem exibe o BADGE do rascunho
                if(rascunhoChat[i].message) {
                    $('#content-button-rascunho-message-chat').show();
                } else {
                    $('#content-button-rascunho-message-chat').hide();
                }
                
                break;
            } else {
                $('#content-button-rascunho-message-chat').hide();
            }
        }
    }

    async createUpdateRascunhoEditorMessage(id_chat, message, mensagem_digitada = false) {
        let rascunhoChat = JSON.parse(localStorage.getItem('@NAJWEB/rascunho_chat_editor'));

        if(!rascunhoChat) {
            rascunhoChat = [];
            rascunhoChat.push({id_chat, message});
        } else {
            let newChat = true;
            for(var i = 0; i < rascunhoChat.length; i++) {
                if(rascunhoChat[i].id_chat == id_chat) {
                    newChat = false;

                    //Se for digitada no chat atualiza o que ta no rascunho
                    if(mensagem_digitada) {
                        rascunhoChat[i].message = message;
                    } else {
                        rascunhoChat[i].message = rascunhoChat[i].message;
                    }
                    break;
                }
            }

            if(newChat) {
                rascunhoChat.push({id_chat, message});
            }            
        }

        localStorage.setItem('@NAJWEB/rascunho_chat_editor', JSON.stringify(rascunhoChat));
    }

    async loadMessageRascunhoEditorChat(id_chat) {
        let rascunhoChat = JSON.parse(localStorage.getItem('@NAJWEB/rascunho_chat_editor'));

        for(var i = 0; i < rascunhoChat.length; i++) {
            if(rascunhoChat[i].id_chat == id_chat) {
                $('.card-body .note-editable')[0].innerHTML = rascunhoChat[i].message;

                //Se tiver mensagem exibe o BADGE do rascunho
                if(rascunhoChat[i].message) {
                    $('#content-button-rascunho-editor-message-chat').show();
                } else {
                    $('#content-button-rascunho-editor-message-chat').hide();
                }
                
                break;
            } else {
                $('#content-button-rascunho-editor-message-chat').hide();
            }
        }
    }

    async loadMoreMessagesFinish() {
        let nameFilter = $('#filter-name-chat').val(),
        parameters = '';

        //Se não tiver nome informado não adiciona nada ao filtro
        if (!nameFilter) {
            parameters = btoa(JSON.stringify({ "data_inicial": filterDataChat.data_inicial, "data_final": filterDataChat.data_final }));
        } else {
            parameters = btoa(JSON.stringify({ "nome_usuario_cliente": nameFilter, "data_inicial": filterDataChat.data_inicial, "data_final": filterDataChat.data_final }));
        }

        const NajClass = new Naj();
        const responseContatos = await NajClass.getData(`chat/mensagens/finish${(parameters != '') ? `?f=${parameters}&limit=${currentLimitFinishContacts}` : `?limit=${currentLimitFinishContacts}`}`);

        if(responseContatos.hasMoreMessages == 0) {
            return hasMoreMessagesFinish = false;
        } else {
            hasMoreMessagesFinish = true;
        }

        responseContatos.data.forEach((item, index) => {
            let newItemFinish = this.newItemContact(item, null);
            $('#contacts-finish').append(newItemFinish);
        });

        this.addEventClickItemContacts();
    }

    addEventClickItemContacts() {
        //removendo todos os eventos primeiramente
        $('.message-item').unbind();

        //Evento do click de carregar conversa
        $('.message-item').on('click', function() {
            let key = JSON.parse(atob(this.getAttribute('key')));

            let bLoadInfoUser   = (this.getAttribute('data-chat-status') == 'null') ? false : true;
            isFilaChatCurrent   = this.getAttribute('data-chat-status') == 'false';
            isFinishChatCurrent = this.getAttribute('data-chat-status') == 'null';

            chat.startChat(key, true, true, bLoadInfoUser);
            chat.showButtonsChat(this.getAttribute('data-chat-status'), (key.id_usuario_atendimento != idUsuarioLogado));

            //Setando váriaveis que são utilizadas durante o chat
            id_chat_current          = key.id_chat;
            id_usuario_current_chat  = key.id_usuario_cliente;
            limitAtualChat           = 20;
            objectUsuarioCurrentChat = JSON.parse(atob(this.getAttribute('data-user-chat')));

            let objectSelected = ($('.selected-conversa-chat')[0] ? $('.selected-conversa-chat')[0] : null);
            if(objectSelected) {
                objectSelected.className = "message-item";
            }

            //Adicionando classe CSS para dar um destaque
            this.className = "message-item selected-conversa-chat";
        });
    }

    updateStatusMessagesChat(messagesId) {
        for(var i = 0; i < messagesId.length; i++) {
            let element = $(`#status-message-${messagesId[i].id_mensagem}`);

            element.removeClass('iconesStatusMessage');
            element.addClass('iconesStatusMessageSuccess');
        }
    }

    showButtonsChat(status_atendimento, is_only_visualizar = false) {
        if(is_only_visualizar && status_atendimento == "true") {
            $('#buttonIniciarAtendimento').hide();
            $('#buttonFimAtendimento').hide();
            $('.content-input-mensagem-chat').hide();
            $('#buttonTransferirAtendimento').hide();
            $('.text-header-historico-mensagem').hide();
            $('.content-butons-chat').hide();

            $('.chat-box').removeClass('content-chat-box-no-full');
            $('.chat-box').addClass('content-chat-box-full');
            return;
        }

        if(status_atendimento == "null") {
            $('#buttonIniciarAtendimento').show();
            $('#buttonFimAtendimento').hide();
            $('#buttonTransferirAtendimento').hide();
            $('.content-input-mensagem-chat').hide();
            $('.text-header-historico-mensagem').show();
            $('.content-butons-chat').hide();

            $('.chat-box').removeClass('content-chat-box-no-full');
            $('.chat-box').addClass('content-chat-box-full');
        } else if(status_atendimento == "true") {
            $('#buttonIniciarAtendimento').hide();
            $('#buttonFimAtendimento').show();
            $('.content-input-mensagem-chat').show();
            $('#buttonTransferirAtendimento').show();
            $('.text-header-historico-mensagem').hide();
            $('.content-butons-chat').show();

            $('.chat-box').removeClass('content-chat-box-full');
            $('.chat-box').addClass('content-chat-box-no-full');
        } else {
            $('#buttonIniciarAtendimento').show();
            $('#buttonFimAtendimento').hide();
            $('.content-input-mensagem-chat').hide();
            $('.text-header-historico-mensagem').hide();
            $('.content-butons-chat').hide();
            $('.content-buttons-atendimento').show();

            $('.chat-box').addClass('content-chat-box-no-full');
            $('.chat-box').removeClass('content-chat-box-full');
        }
    }

    cleanInputMessage() {
        $('.input-mensagem-chat').val("");
    }

    isBeginConversation(message) {
        if(!message) return false;
        return message.search('Iniciou') > -1;
    }

    isFinishConversation(message) {
        if(!message) return false;
        return message.search('Encerrou') > -1;
    }

    isTransferConversation(message) {
        if(!message) return false;
        return message.search('Transferiu') > -1;
    }

    scrollToBottom() {
		this.messageContainer
			.lastElementChild
			.scrollIntoView({
				behavior: 'smooth'
			});
	}

    formaterDataInHora(value) {
        let hora = value.split(' ');

        if(!hora[1]) return '';

        return hora[1].substr(0, 5);
    }

    formarterData(value, typeDivisor = '-') {
        let data = value.split('-');

        if(!data[1]) return '';

        return `${data[2]}${typeDivisor}${data[1]}${typeDivisor}${data[0]}`;
    }

    convertDataHora(value) {
        let hora = value.split(' ');

        if(!hora[1]) return '';

        hora = hora[1].substr(0, 5);

        let data = value.split(' ')[0];
        let dia  = data.substr(8);
        let mes  = data.substr(5, 2);
        let ano  = data.substr(0, 4);

        return `${dia} ${this.convertMes(mes)}, ${ano}, ${hora}`;
    }

    convertMes(mes) {
        switch(mes) {
            case '01':
                return 'Janeiro';
            case '02':
                return 'Fevereiro';
            case '03':
                return 'Março';
            case '04':
                return 'Abril';
            case '05':
                return 'Maio';
            case '06':
                return 'Junho';
            case '07':
                return 'Julho';
            case '08':
                return 'Agosto';
            case '09':
                return 'Setembro';
            case '10':
                return 'Outubro';
            case '11':
                return 'Novembro';
            default: 
                return 'Dezembro';
        }
    }

    formaterSizeAnexo(size) {
        return `${Math.round(size / 1024)}KB` ;
    }

    hideContentEditorMensagemChat() {
        $('#content-editor-upload').hide();
        $('.content-butons-chat').show()
        $('#content-messages-chat').show();
        // $('#input-text-chat-enviar').show();
        $('.content-buttons-atendimento').show();
        $('.chat-box').removeClass('content-chat-box-full');
        $('.chat-box').addClass('content-chat-box-no-full');
    }

    hideContentAnexoChat() {
        $('#content-upload-anexos-chat').hide();
        $('.content-butons-chat').show();
        $('.chat-box').removeClass('content-chat-box-full');
        $('.chat-box').addClass('content-chat-box-no-full');
        $('#content-messages-chat').show();
        // $('#input-text-chat-enviar').show();
        $('.content-buttons-atendimento').show();
    }

}