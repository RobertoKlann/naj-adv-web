const chat = new Chat();
const NajApi = new Naj();
const app_id_one_signal = 'ebbc160a-a51d-4c13-b7bf-cff2dcfd3fa0';
const tag_inicio_fim_atendimento = 'BOT';
const tag_mensagem_to_cliente_simples = 'ATENDIMENTO, TEXTO_SIMPLES';
const tag_mensagem_to_cliente_editor = 'ATENDIMENTO, EDITOR_MENSAGEM';
const tag_mensagem_coletiva_to_cliente_editor = 'ATENDIMENTO,EDITOR_MENSAGEM,COLETIVA';

let offsetOldMessages = 0;
let limitAtualChat = 20;
let id_chat_current;
let id_atendimento_current;
let id_chat_current_selected;
let id_usuario_current_chat;
let filterDataChat;
let usersNewAtendimento = [];
let objectUsuarioCurrentChat;
let isFilaChatCurrent;
let isFinishChatCurrent;
let currentLimitFinishContacts = 0;
let hasMoreMessagesFinish = true;
let tabContactsSelected;
let forceApeendFinish = false;
let usedFilterNameFinished = false;
let messagesAppendedChatFila = [];
let messagesAppendedChatFinish = [];

//---------------------- Functions -----------------------//
$(document).ready(function () {
    onLoadAtendimento();

    //Evento do click de iniciar o atendimento
    // $('#buttonChangeTypeChatInternal').on('click', function () {
    //     changeTypeChat(true);
    // });
    // $('#buttonChangeTypeChatExternal').on('click', function () {
    //     changeTypeChat(false);
    // });

    //Evento do click de iniciar o atendimento
    $('#buttonIniciarAtendimento').on('click', function () {
        storeChatAtendimento();
    });

    //Evento do click de transferir o atendimento
    $('#buttonTransferirAtendimento').on('click', function () {
        $("#input-nome-pesquisa-dono").val(nomeUsuarioLogado);
        $("#input-nome-pesquisa-receber").val("");
        $("#content-select-ajax-naj").hide();
        $('#modal-transferir-atendimento-chat').modal('show');
    });

    //Evento do click de finalizar o atendimento
    $('#buttonFimAtendimento').on('click', function () {
        updateFinishChatAtendimento();
    });

    //Evento do click no editor avançado
    $('#input-editor-texto').on('click', function () {
        $('#previews-file-editor')[0].innerHTML = '';
        $('.content-butons-chat').hide();
        $('.chat-box').removeClass('content-chat-box-no-full');
        $('.chat-box').addClass('content-chat-box-full');
        $('#content-editor-upload').show();
        $('#content-messages-chat').hide();
        $('#input-text-chat-enviar').hide();
        $('.content-buttons-atendimento').hide();

        chat.createUpdateRascunhoEditorMessage(id_chat_current, null);
        chat.loadMessageRascunhoEditorChat(id_chat_current);
    });

    //Evento do click de exibir o modal anexo do chat
    $('#input-anexo').on('click', function () {
        $('#previews')[0].innerHTML = '';
        $('#content-upload-anexos-chat').show();
        $('.content-butons-chat').hide();
        $('.chat-box').removeClass('content-chat-box-no-full');
        $('.chat-box').addClass('content-chat-box-full');
        $('#content-messages-chat').hide();
        $('#input-text-chat-enviar').hide();
        $('.content-buttons-atendimento').hide();
    });

    //Evento do click de enviar a mensagem
    $('#enviar-mensagem-no-enter').on('click', function () {
        if (!$('#input-text-chat-enviar').val()) {
            return;
        }
        sendMessage(null, tag_mensagem_to_cliente_simples);
    });

    $('#input-text-chat-enviar').keypress(function (event) {
        var keycode = (event.keyCode ? event.keyCode : event.which);

        if (keycode == '13') {
            event.preventDefault();
            if (!$('#input-text-chat-enviar').val()) {
                return;
            }

            chat.createUpdateRascunhoMessage(id_chat_current, null, true);
            sendMessage(null, tag_mensagem_to_cliente_simples);
        } else {
            chat.createUpdateRascunhoMessage(id_chat_current, $('#input-text-chat-enviar').val() + event.key, true);
        }
    });

    $('.card-body .note-editable').keypress(function (event) {
        var keycode = (event.keyCode ? event.keyCode : event.which);

        if (keycode == '13') {
            event.preventDefault();
            if (!$('#input-text-chat-enviar').val()) {
                return;
            }

            chat.createUpdateRascunhoEditorMessage(id_chat_current, null, true);
            sendMessage(null, tag_mensagem_to_cliente_editor);
        } else {
            chat.createUpdateRascunhoEditorMessage(id_chat_current, $('.card-body .note-editable')[0].innerHTML, true);
        }
    });

    $('#icon-trash-rascunho-message-chat').on('click', function () {
        chat.createUpdateRascunhoMessage(id_chat_current, null, true);
        $('#input-text-chat-enviar').val('');
        $('#content-button-rascunho-message-chat').hide();
    });

    $('#icon-trash-rascunho-editor-message-chat').on('click', function () {
        chat.createUpdateRascunhoEditorMessage(id_chat_current, null, true);
        $('.card-body .note-editable')[0].innerHTML = '';
        $('#content-button-rascunho-editor-message-chat').hide();
    });

    $('#content-select-ajax-naj-pessoa-upload-ficha').on('click', function (el) {
        onClickContentSelectAjaxAnexoFichaPessoa(el);
    });

    //Esconde caixa do campo de pesquisa
    $('#content-outside-upload-anexo-ficha-pessoa').on('click', function (el) {
        if (el.target.id == 'nome_cliente' || el.target.id == 'icon-search-nome-pessoa-upload-ficha') {
            return;
        }

        $("#content-select-ajax-naj-pessoa-upload-ficha").hide();
    });

    //Realiza a busca
    $('#nome_pessoa_upload_ficha').on('click', function (element) {
        buscaDadosPessoaFicha(element);
    });

    //Ao esconder o modal de '#modal-manutencao-pessoa' remove a classe 'z-index-100' do modal '#modal-upload-anexo-ficha-pessoa-chat'
    $('#modal-manutencao-pessoa').on('hidden.bs.modal', function () {
        $('#modal-upload-anexo-ficha-pessoa-chat').removeClass('z-index-100');
    });

    //Faz o filtro por nome da tab ENCERRADOS
    $('#filter-name-chat').keyup(function(event) {
        let keycode = (event.keyCode ? event.keyCode : event.which);

        if(keycode == 8 || keycode == 46 && event.target.value == 0) {
            event.preventDefault();
            hasMoreMessagesFinish = true;
            forceApeendFinish = true;
            usedFilterNameFinished = true;

            setTimeout(() => {
                onClickFilterUserChat();
            }, 2000);
        }

        if(event.target.value.length < 3)
            usedFilterNameFinished = false;
        
        event.preventDefault();
        hasMoreMessagesFinish = true;
        forceApeendFinish = true;
        usedFilterNameFinished = true;

        loadingStart('loading-content-scroll-messages-finish');
        setTimeout(() => {
            onClickFilterUserChat();
        }, 2000);
    });

    setInterval(() => {
        let objectSelected = ($('.selected-conversa-chat')[0] ? JSON.parse(atob($('.selected-conversa-chat')[0].getAttribute('key'))) : null);
        id_chat_current_selected = (objectSelected) ? objectSelected.id_chat : null;
        tabContactsSelected = $('.customtab .active')[0].getAttribute('data-link-nav-chat');
        onClickFilterDataChat(filterDataChat.itemListSelected);
    }, 20000);

    setInterval(() => {        
        loadMessageChat();
    }, 15000);
});

async function loadMessageChat() {
    if (id_chat_current && id_usuario_current_chat && !$('#content-editor-upload').is(":visible") && !$('#content-upload-anexos-chat').is(":visible")) {
        let moveScroll = $('#pololo').scrollTop() + $('#pololo').innerHeight() == $('#pololo')[0].scrollHeight;
        await chat.loadNewMessages({ "id_chat": id_chat_current, "id_usuario_cliente": id_usuario_current_chat }, moveScroll, false, false, false);
    }
}

async function onLoadAtendimento() {
    $('.content-butons-chat').hide();
    $('#content-editor-upload').hide();
    $('#buttonIniciarAtendimento').hide();
    $('#buttonFimAtendimento').hide();
    $('#buttonTransferirAtendimento').hide();
    $('.content-input-mensagem-chat').hide();
    $('#content-upload-anexos-chat').hide();
    $('#content-button-rascunho-message-chat').hide();
    $('#content-button-rascunho-editor-message-chat').hide();

    // $('#buttonChangeTypeChatExternal').hide();

    $('#card-body-editor-chat .card').addClass('card-editor-chat');
    $('.modal-body-chat-novo-atendimento .card').addClass('card-editor-chat-nova-mensagem');

    await onClickFilterDataChat(null, true);
}

async function sendMessage(mensagem = false, tagTipo) {
    loadingStart('loading-message-chat')

    let message = (!mensagem) ? $('#input-text-chat-enviar').val() : mensagem;
    let data_hora = getDataHoraAtual();
    let data = {
        "id_chat": id_chat_current,
        "id_usuario": idUsuarioLogado,
        "conteudo": message.replace(/<div>\s*<\/div>/ig, '').replace(/<div\s*\/>/ig, '').replace(/<p>\s*<\/p>/ig, '').replace(/<p\s*\/>/ig, ''),
        "tipo": 0,
        "data_hora": data_hora,
        "file_size": null,
        "file_path": null,
        "file_type": null,
        "id_atendimento": id_atendimento_current,
        "tag": tagTipo
    };

    let result = await NajApi.postData(`chat/mensagem`, data);
    if (!result || !result.model) {
        NajAlert.toastError('Não foi possível enviar a mensagem, tente novamente mais tarde!');
        loadingDestroy('loading-message-chat')
        return false;
    }

    if (result.model) {
        let sHtmlMessage = chat.newContentNewMessage({"id_mensagem": result.model.id, "nome": nomeUsuarioLogado, "conteudo": message.replace(/<div>\s*<\/div>/ig, '').replace(/<div\s*\/>/ig, '').replace(/<p>\s*<\/p>/ig, '').replace(/<p\s*\/>/ig, ''), "data_hora": data_hora }, true);
        $(`#content-messages-chat`).append(sHtmlMessage);
        loadingDestroy('loading-message-chat')

        //FORMATA A MENSAGEM PARA NÃO ESTOURAR NO ONESIGNAL
        let dataOneSignal = {
            "id_chat": id_chat_current,
            "id_usuario": idUsuarioLogado,
            "conteudo": message.replace(/(<([^>]+)>)/gi, " ").trim().replace(/\s{2,}/g, ' ').substr(0, 1500).replace('-&gt;', '->'),
            "tipo": 0,
            "data_hora": data_hora,
            "file_size": null,
            "file_path": null,
            "file_type": null,
            "id_atendimento": id_atendimento_current,
        };

        await callShootNotificationMessageOneSignal(dataOneSignal, message);

        chat.scrollToBottom();
        chat.cleanInputMessage();

        return true;
    }
}

// function changeTypeChat(isInternal) {
//     if (isInternal) {
//         $('#buttonIniciarAtendimento').hide();
//         $('#buttonFimAtendimento').hide();
//         $('#buttonTransferirAtendimento').hide();
//         $('div.bg-info-user-chat').hide();

//         $('#buttonChangeTypeChatInternal').hide();
//         $('#buttonChangeTypeChatExternal').show();

//         return
//     }

//     $('#buttonIniciarAtendimento').show();
//     $('#buttonFimAtendimento').show();
//     $('#buttonTransferirAtendimento').show();
//     $('div.bg-info-user-chat').show();
//     $('#buttonChangeTypeChatInternal').show();
//     $('#buttonChangeTypeChatExternal').hide();


// }

async function storeChatAtendimento() {
    let data_hora = getDataHoraAtual();
    let data = {
        "id_chat": id_chat_current,
        "id_usuario": idUsuarioLogado,
        "data_hora_inicio": data_hora,
        "data_hora_termino": "",
        "status": 0
    };

    let result = await NajApi.postData(`chat/atendimento`, data);
    if (!result || !result.model) {
        NajAlert.toastError('Não foi possível iniciar o atendimento, este chat pode estar em atendimento já!');
        return;
    }

    if (result.model) {
        id_atendimento_current = result.model.id;
        let data = {
            "id_chat": id_chat_current,
            "id_usuario": idUsuarioLogado,
            "conteudo": `${nomeUsuarioLogado} - Iniciou o atendimento`,
            "tipo": 0,
            "data_hora": data_hora,
            "file_size": 0,
            "file_path": "",
            "id_atendimento": id_atendimento_current,
        };

        result = await NajApi.postData(`chat/mensagem`, data);
        if (!result || !result.model) {
            NajAlert.toastError('Não foi possível enviar a mensagem, tente novamente mais tarde!');
            return;
        }
        if (result.model) {
            let sHtmlMessage = chat.newContentStartMessage({ "nome": nomeUsuarioLogado, "data_hora": data_hora });
            $(`#content-messages-chat`).append(sHtmlMessage);
            chat.scrollToBottom();
            chat.cleanInputMessage();
        }
    }

    $('.chat-box').removeClass('content-chat-box-full');
    $('.chat-box').addClass('content-chat-box-no-full');
    $('.content-butons-chat').show();
    $('#input-text-chat-enviar').show();
    $('#buttonTransferirAtendimento').show();

    // Se for finish vamos setar senhum chat
    if(isFinishChatCurrent) {
        $('#buttonIniciarAtendimento').hide();
        $('#buttonFimAtendimento').hide();
        $('.content-input-mensagem-chat').hide();
        $('.content-butons-chat').hide();
        $('#input-text-chat-enviar').hide();
        $('#buttonTransferirAtendimento').hide();
        $('.chat-box').addClass('content-chat-box-full');
        $('.chat-box').removeClass('content-chat-box-no-full');

        $('.content-message-select-user-chat').show();
        $('#content-messages-chat')[0].innerHTML = '';
        $('.selected-conversa-chat').removeClass('selected-conversa-chat');

        id_chat_current = null;
        id_usuario_current_chat = null;
    } else {
        $('#content-fila').removeClass('active');
        $('#content-todos').removeClass('active');
        $('#content-em-andamento').addClass('active');

        $('a[data-link-nav-chat=todos]').removeClass('active');
        $('a[data-link-nav-chat=fila]').removeClass('active');
        $('a[data-link-nav-chat=andamento]').addClass('active');

        $('#buttonIniciarAtendimento').hide();
        $('#buttonFimAtendimento').show();
        $('.content-input-mensagem-chat').show();
        $('.content-butons-chat').show();
        $('#input-text-chat-enviar').show();
        $('#buttonTransferirAtendimento').show();
        $('.chat-box').removeClass('content-chat-box-full');
        $('.chat-box').addClass('content-chat-box-no-full');

        id_chat_current_selected = id_chat_current;
    }

    hasMoreMessagesFinish = true;
    forceApeendFinish = true;
    await chat.loadContacts();
}

async function updateFinishChatAtendimento() {
    sessionStorage.removeItem('@NAJ_WEB/dados_dispositivo_usuario_chat');
    let data_hora = getDataHoraAtual();
    let data = {
        "id": id_atendimento_current,
        "data_hora_termino": data_hora,
        "chat": id_chat_current,
        "status": 1,
        "id_chat": id_chat_current
    };

    let result = await NajApi.updateData(`chat/atendimento/${btoa(JSON.stringify({ "id": id_atendimento_current }))}`, data);
    if (!result || !result.model) {
        NajAlert.toastError('Aguarde alguns segundos ou atualize a página, estamos carregando as mensagens não lidas!');
        return;
    }
    if (result.model) {
        let data = {
            "id_chat": id_chat_current,
            "id_usuario": idUsuarioLogado,
            "conteudo": `${nomeUsuarioLogado} - Encerrou o atendimento`,
            "tipo": 0,
            "data_hora": data_hora,
            "file_size": 0,
            "file_path": "",
            "id_atendimento": id_atendimento_current,
            "tag": tag_inicio_fim_atendimento
        };

        result = await NajApi.postData(`chat/mensagem`, data);
        if (result.model) {
            let sHtmlMessage = chat.newContentFinishMessage({ "nome": nomeUsuarioLogado, "data_hora": data_hora });
            $(`#content-messages-chat`).append(sHtmlMessage);
            chat.scrollToBottom();
            chat.cleanInputMessage();
        }
    }
    
    $('#buttonFimAtendimento').hide();
    $('.content-input-mensagem-chat').hide();
    $('.content-butons-chat').hide();
    $('#input-text-chat-enviar').hide();
    $('#buttonTransferirAtendimento').hide();
    $('.chat-box').removeClass('content-chat-box-no-full');
    $('.chat-box').addClass('content-chat-box-full');

    $('.content-message-select-user-chat').show();
    $('#content-messages-chat')[0].innerHTML = '';
    $('.selected-conversa-chat').removeClass('selected-conversa-chat');

    id_chat_current = null;
    id_usuario_current_chat = null;
    id_chat_current_selected = null;

    chat.appendInfoUsuarioDocumentos(`
        <div class="text-no-process-chat">
            <p>Sem informações...</p>
        </div>
    `);

    chat.appendInfoUsuarioProcesso(`
        <div class="text-no-process-chat">
            <p>Sem informações...</p>
        </div>
    `);

    hasMoreMessagesFinish = true;
    forceApeendFinish = true;
    await chat.loadContacts();
}

async function updateTransferChatAtendimento(id_novo_usuario, nome_novo_usuario) {
    let data_hora = getDataHoraAtual();
    let data = {
        "id": id_atendimento_current,
        "id_usuario": id_novo_usuario,
        "id_chat": id_chat_current
    };

    result = await NajApi.updateData(`chat/atendimento/${btoa(JSON.stringify({ "id": id_atendimento_current }))}`, data);
    if (!result || !result.model) {
        NajAlert.toastError('Não foi possível transferir o atendimento, tente novamente mais tarde!');
        return;
    }
    if (result.model) {
        let data = {
            "id_chat": id_chat_current,
            "id_usuario": idUsuarioLogado,
            "conteudo": `${nomeUsuarioLogado} - Transferiu o atendimento para: ${nome_novo_usuario}`,
            "tipo": 0,
            "data_hora": data_hora,
            "file_size": 0,
            "file_path": ""
        };

        result = await NajApi.postData(`chat/mensagem`, data);
        if (result.model) {
            let sHtmlMessage = chat.newContentTransferConversation({ "conteudo": `${nomeUsuarioLogado} - Transferiu o atendimento para: ${nome_novo_usuario}`, "data_hora": data_hora });
            $(`#content-messages-chat`).append(sHtmlMessage);
            chat.scrollToBottom();
            chat.cleanInputMessage();
        }
    }

    await chat.loadContacts();
    $('#buttonIniciarAtendimento').hide();
    $('#buttonFimAtendimento').hide();
    $('#buttonTransferirAtendimento').hide();
    $('.content-input-mensagem-chat').hide();
    $('.content-butons-chat').hide();
    $('.chat-box').removeClass('content-chat-box-no-full');
    $('.chat-box').addClass('content-chat-box-full');
    $('#modal-transferir-atendimento-chat').modal('hide');
}

function onClickCancelarEditorTexto() {
    $('#content-editor-upload').hide();
    $('.content-butons-chat').show()
    $('#content-messages-chat').show();
    $('#input-text-chat-enviar').show();
    $('.content-buttons-atendimento').show();
    $('.chat-box').removeClass('content-chat-box-full');
    $('.chat-box').addClass('content-chat-box-no-full');

    chat.scrollToBottom();
}

function onClickCancelarAnexos() {
    $('#content-upload-anexos-chat').hide();
    $('.content-butons-chat').show();
    $('.chat-box').removeClass('content-chat-box-full');
    $('.chat-box').addClass('content-chat-box-no-full');
    $('#content-messages-chat').show();
    $('#input-text-chat-enviar').show();
    $('.content-buttons-atendimento').show();

    chat.scrollToBottom();
}

async function onClickButtonMaisMensagemChat() {
    offsetOldMessages = offsetOldMessages + 20;
    await chat.moreMessagesOld({ "id_chat": id_chat_current, "id_usuario_cliente": id_usuario_current_chat }, false);
}

async function onClickSendAnexoEditor() {
    loadingStart('loading-anexo-chat-editor');

    //Se foi escrito algo
    if ($("#summernote").summernote('code')) {
        let successMessage = await sendMessage($("#summernote").summernote('code'), tag_mensagem_to_cliente_editor);

        if(!successMessage) return false;
    }

    let successFiles = await sendAnexos(myDropzoneEditor);

    if(!successFiles) return false;

    $('#content-editor-upload').hide();
    $('.content-butons-chat').show()
    $('#content-messages-chat').show();
    $('#input-text-chat-enviar').show();
    $('.content-buttons-atendimento').show();
    $('.chat-box').removeClass('content-chat-box-full');
    $('.chat-box').addClass('content-chat-box-no-full');
    $('#previews-file-editor')[0].innerHTML = '';
    myDropzoneEditor.files = [];

    chat.createUpdateRascunhoEditorMessage(id_chat_current, null, true);
    chat.scrollToBottom();

    loadingDestroy('loading-anexo-chat-editor');
}

async function onClickSendAnexoChat() {
    loadingStart('loading-anexo-chat');
    await sendAnexos(myDropzone);

    $('#content-upload-anexos-chat').hide();
    $('.content-butons-chat').show();
    $('.chat-box').removeClass('content-chat-box-full');
    $('.chat-box').addClass('content-chat-box-no-full');
    $('#content-messages-chat').show();
    $('#input-text-chat-enviar').show();
    $('.content-buttons-atendimento').show();
    $('#previews')[0].innerHTML = '';
    myDropzone.files = [];

    chat.scrollToBottom();
    loadingDestroy('loading-anexo-chat');
}

async function loadAnexoNovoAtendimento() {
    let filesUpload = [];
    let data_hora = getDataHoraAtual();
    let identificador = sessionStorage.getItem('@NAJ_WEB/identificadorEmpresa');

    if (dropzoneNewAtendimento.files.length < 1) {
        return [];
    }

    for (var i = 0; i < dropzoneNewAtendimento.files.length; i++) {
        let parseFile = await toBase64(dropzoneNewAtendimento.files[i]);

        filesUpload.push({
            'name_file': dropzoneNewAtendimento.files[i].name,
            'arquivo': parseFile,
            'id_cliente': identificador,
            'nome': nomeUsuarioLogado,
            'data_hora': data_hora,
            'tipo': 1,
            'conteudo': dropzoneNewAtendimento.files[i].name,
            'id_usuario': idUsuarioLogado,
            'id_chat': '',
            'file_size': dropzoneNewAtendimento.files[i].size,
            'file_path': ''
        });
    }

    return filesUpload;
}

async function sendAnexos(dropzone) {
    let filesUpload = [];
    let data_hora = getDataHoraAtual();
    let identificador = sessionStorage.getItem('@NAJ_WEB/identificadorEmpresa');

    if (dropzone.files.length < 1) {
        return true;
    }

    for (var i = 0; i < dropzone.files.length; i++) {
        let parseFile = await toBase64(dropzone.files[i]);
        let is_image = dropzone.files[i].type.search('image');
        let is_audio = dropzone.files[i].type.search('audio');        
        let file_type = (is_image > -1) ? 0 : 1;

        if(is_audio > -1) {
            file_type = 2;
        } else if(is_image > -1) {
            file_type = 0;
        } else {
            file_type = 1;
        }

        filesUpload.push({
            'name_file': dropzone.files[i].name,
            'arquivo': parseFile,
            'id_cliente': identificador,
            'nome': nomeUsuarioLogado,
            'data_hora': data_hora,
            'tipo': 1,
            'conteudo': dropzone.files[i].name,
            'id_usuario': idUsuarioLogado,
            'id_chat': id_chat_current,
            'file_size': dropzone.files[i].size,
            'file_path': '',
            'file_type': file_type,
            'tag' : tag_mensagem_to_cliente_simples
        });
    }

    let result = await NajApi.postData(`chat/mensagem/anexo`, { 'files': filesUpload, 'id_atendimento': id_atendimento_current });

    if (result && result.status_code && result.status_code == 200) {
        result.data.forEach((item) => {
            let anexo = {
                "id_mensagem": item.id,
                "status": item.status,
                "data_hora": item.data_hora,
                "usuario_tipo_id": tipoUsuarioLogado,
                "conteudo": item.conteudo,
                "file_type": item.file_type,
                "nome": nomeUsuarioLogado,
                "file_size": item.file_size
            };
            $(`#content-messages-chat`).append(chat.newContentAnexo(anexo, true));
        });

        await callShootNotificationMessageOneSignal({'id_chat' : id_chat_current}, '', true);

        return true;
    } else {
        loadingDestroy('loading-anexo-chat');
        NajAlert.toastWarning(result.mensagem);

        return false;
    }
}

async function shareAnexos(content_inputs) {
    let filesUpload = [];
    data_hora = getDataHoraAtual();
    identificador = sessionStorage.getItem('@NAJ_WEB/identificadorEmpresa');
    files = $(`.${content_inputs} :checked`);

    if (files.length == 0) {
        return;
    }

    for (var i = 0; i < files.length; i++) {
        let key = JSON.parse(atob(files[i].getAttribute('key')));

        if (!key.id || !key.name) {
            NajAlert.toastWarning("Não foi possivel encaminhar os anexos, recarregue a página e tente novamente!");
        }

        let pasta = (content_inputs == 'row-anexo-documentos') ? 'pessoa_anexos' : 'prc_anexos';

        filesUpload.push({
            'id_file': key.id,
            'id_cliente': identificador,
            'nome': nomeUsuarioLogado,
            'data_hora': data_hora,
            'tipo': 1,
            'conteudo': nameFile = key.name,
            'id_usuario': idUsuarioLogado,
            'id_chat': id_chat_current,
            'file_size': '',
            'file_path': '',
            pasta
        });
    }

    result = await NajApi.postData(`chat/mensagem/shareAnexo?XDEBUG_SESSION_START`, { 'files': filesUpload, 'id_atendimento': id_atendimento_current });

    if (result.status_code == 200) {
        await chat.loadNewMessages({ "id_chat": id_chat_current, "id_usuario_cliente": id_usuario_current_chat }, false);
    } else {
        loadingDestroy('loading-anexo-chat');
        NajAlert.toastWarning(result.mensagem);
    }
}

async function onClickFilterDateChat(dias = 'atual', allChats = false) {
    hasMoreMessagesFinish = true;
    forceApeendFinish = true;
    usedFilterNameFinished = true;
    await onClickFilterDataChat(dias, allChats);
}

async function onClickFilterUserChat() {
    let nameFilter = $('#filter-name-chat').val(),
        parameters = '';

    //Se não tiver nome informado não adiciona nada ao filtro
    if (!nameFilter) {
        parameters = btoa(JSON.stringify({ "data_inicial": filterDataChat.data_inicial, "data_final": filterDataChat.data_final }));
    } else {
        parameters = btoa(JSON.stringify({ "nome_usuario_cliente": nameFilter, "data_inicial": filterDataChat.data_inicial, "data_final": filterDataChat.data_final }));
    }

    await chat.loadContacts(parameters);
}

async function onClickFilterDataChat(dias = 'atual', allChats = false) {
    let parameters = '',
        dataAtual = getDataAtual(),
        mesAtual = dataAtual.split('-')[1],
        anoAtual = dataAtual.split('-')[0],
        nameFilter = $('#filter-name-chat').val();

    if(allChats) {
        filterDataChat = { "data_inicial": `0001-01-01 00:00:01`, "data_final": `9999-01-01 23:59:59`, "itemListSelected": 'all' };
        parameters = btoa(JSON.stringify({ "nome_usuario_cliente": nameFilter, "data_inicial": `0001-01-01 00:00:01`, "data_final": `9999-01-01 23:59:59` }));
    }else if (dias == 'atual') {
        let primeiroDiaMes = new Date(parseFloat(anoAtual), parseFloat(mesAtual) - 1, 1).getDate(),
            ultimoDiaMes = new Date(parseFloat(anoAtual), parseFloat(mesAtual), 0).getDate();

        primeiroDiaMes = (primeiroDiaMes < 10) ? `0${primeiroDiaMes}` : primeiroDiaMes;
        ultimoDiaMes = (ultimoDiaMes < 10) ? `0${ultimoDiaMes}` : ultimoDiaMes;
        filterDataChat = { "data_inicial": `${anoAtual}-${mesAtual}-${primeiroDiaMes} 00:00:01`, "data_final": `${anoAtual}-${mesAtual}-${ultimoDiaMes} 23:59:59`, "itemListSelected": dias };
        parameters = btoa(JSON.stringify({ "nome_usuario_cliente": nameFilter, "data_inicial": `${anoAtual}-${mesAtual}-${primeiroDiaMes} 00:00:01`, "data_final": `${anoAtual}-${mesAtual}-${ultimoDiaMes} 23:59:59` }));
    } else {
        dataAtual = new Date();
        dataAtual.setDate(dataAtual.getDate() - dias);
        let monthTratado = ((dataAtual.getMonth() + 1) < 10) ? `0${dataAtual.getMonth() + 1}` : dataAtual.getMonth() + 1;
        let diaTratado = (dataAtual.getDate() < 10) ? `0${dataAtual.getDate()}` : `${dataAtual.getDate()}`;
        filterDataChat = { "data_inicial": `${dataAtual.getFullYear()}-${monthTratado}-${diaTratado} 00:00:01`, "data_final": `${getDataAtual()} 23:59:59`, "itemListSelected": dias }
        parameters = btoa(JSON.stringify({ "nome_usuario_cliente": nameFilter, "data_inicial": `${dataAtual.getFullYear()}-${monthTratado}-${diaTratado} 00:00:01`, "data_final": `${getDataAtual()} 23:59:59` }));
    }

    await chat.loadContacts(parameters);
}

function onClickCheckAnexoProcesso() {
    let inputs = $('#info-processos-user .row-content-anexo-processo .custom-checkbox input'),
        bControl = false;

    $('#contador-check-processo')[0].innerHTML = `(${$('#info-processos-user .row-content-anexo-processo .custom-checkbox :checked').length})`;
    for (var i = 0; i < inputs.length; i++) {

        if (inputs[i].checked) {
            bControl = true;
            break;
        } else {
            bControl = false;
        }
    }

    if (bControl) {
        $('#button-encaminhar-anexo-processo').slideDown(300);
    } else {
        $('#button-encaminhar-anexo-processo').slideUp(300);
    }
}

function onClickCheckDocumentos() {
    let inputs = $('#info-documentos-user .row-content-anexo-processo .custom-checkbox input'),
        bControl = false;

    $('#contador-check-documentos')[0].innerHTML = `(${$('#info-documentos-user .row-content-anexo-processo .custom-checkbox :checked').length})`;

    for (var i = 0; i < inputs.length; i++) {

        if (inputs[i].checked) {
            bControl = true;
            break;
        } else {
            bControl = false;
        }
    }

    if (bControl) {
        $('#button-encaminhar-documento').slideDown(300);
    } else {
        $('#button-encaminhar-documento').slideUp(300);
    }
}

async function onClickEnvolvidosProcesso(codigo, el) {
    let parameters = btoa(JSON.stringify({ codigo })),
        envolvidos = await NajApi.getData(`processos/partes/cliente/${parameters}`),
        sHtml = '';

    if (el.children) {
        let className = el.children.item(0).className;

        if (className == 'fas fa-chevron-circle-down') {
            el.children.item(0).className = 'fas fa-chevron-circle-right';
            return;
        }
        el.children.item(0).className = 'fas fa-chevron-circle-down';
    }

    for (var indice = 0; indice < envolvidos.length; indice++) {
        sHtml += `
            <div class="font-12 row-zebra-memo">
                <i class="font-18 mdi mdi-open-in-new cursor-pointer text-dark" title="Ver ficha do envolvido" data-toggle="tooltip" onclick="onClickFichaEnvolvido(${envolvidos[indice].CODIGO});"></i>
                <span>
                    ${(envolvidos[indice].NOME.length > 32) ? `${envolvidos[indice].NOME.substr(0, 30)}...` : `${envolvidos[indice].NOME}`}
                    ${(envolvidos[indice].NOME.length > 32) ? `<span>
                        <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="${envolvidos[indice].NOME}"></i>
                    </span>` : ``}
                </span>
                <small><span class="text-muted">(${envolvidos[indice].QUALIFICACAO}) </span></small>
            </div>
        `;
    }

    $(`#partes-processo-${codigo}`)[0].innerHTML = sHtml;
    $('.fa-info-circle').tooltip('update');
}

async function onClickEnvolvidosProcessoAdv(codigo, el) {
    let parameters = btoa(JSON.stringify({ codigo })),
        envolvidos = await NajApi.getData(`processos/partes/adversaria/${parameters}`),
        sHtml = '';

    if (el.children) {
        let className = el.children.item(0).className;

        if (className == 'fas fa-chevron-circle-down') {
            el.children.item(0).className = 'fas fa-chevron-circle-right';
            return;
        }
        el.children.item(0).className = 'fas fa-chevron-circle-down';
    }

    for (var indice = 0; indice < envolvidos.length; indice++) {
        sHtml += `
            <div class="font-12 row-zebra-memo">
                <i class="font-18 mdi mdi-open-in-new cursor-pointer text-dark" title="Ver ficha do envolvido" data-toggle="tooltip" onclick="onClickFichaEnvolvido(${envolvidos[indice].CODIGO});"></i>
                <span>
                    ${(envolvidos[indice].NOME.length > 32) ? `${envolvidos[indice].NOME.substr(0, 30)}...` : `${envolvidos[indice].NOME}`}
                    ${(envolvidos[indice].NOME.length > 32) ? `<span>
                        <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="${envolvidos[indice].NOME}"></i>
                    </span>` : ``}
                </span>
                <small><span class="text-muted">(${envolvidos[indice].QUALIFICACAO}) </span></small>
            </div>
        `;
    }

    $(`#partes-adv-processo-${codigo}`)[0].innerHTML = sHtml;
    $('.fa-info-circle').tooltip('update');
}

function onClickCancelarEncaminharAnexos(content_inputs) {
    let files = $(`.${content_inputs} :checked`);

    for (var i = 0; i < files.length; i++) {
        files[i].checked = false;
    }

    $('#button-encaminhar-documento').slideUp(300);
    $('#button-encaminhar-anexo-processo').slideUp(300);
}

function onClickAnexoProcesso(el) {
    if (el.children) {
        let className = el.children.item(0).className;

        if (className == 'fas fa-chevron-circle-down') {
            el.children.item(0).className = 'fas fa-chevron-circle-right';
            return;
        }
        el.children.item(0).className = 'fas fa-chevron-circle-down';
    }
}

function onClickAnexoDocumentoProcesso(el) {
    if (el.children) {
        let className = el.children.item(0).className;

        if (className == 'fas fa-chevron-circle-down') {
            el.children.item(0).className = 'fas fa-chevron-circle-right';
            return;
        }
        el.children.item(0).className = 'fas fa-chevron-circle-down';
    }
}

function convertDataURIToBinary(dataURI) {
    var BASE64_MARKER = ';base64,';
    var base64Index = dataURI.indexOf(BASE64_MARKER) + BASE64_MARKER.length;
    var base64 = dataURI.substring(base64Index);
    var raw = window.atob(base64);
    var rawLength = raw.length;
    var array = new Uint8Array(new ArrayBuffer(rawLength));

    for(i = 0; i < rawLength; i++) {
        array[i] = raw.charCodeAt(i);
    }
    
    return array;
}

async function onClickDownloadAnexoChat(id_message, arquivoName, fileType) {
    loadingStart('loading-upload-chat');
    identificador = sessionStorage.getItem('@NAJ_WEB/identificadorEmpresa');
    let parametros = btoa(JSON.stringify({ id_message, identificador }));
    let result = await NajApi.getData(`chat/mensagem/download/${parametros}`, true);

    if(fileType == 2 && result) {
        let reader = new FileReader();
        reader.readAsDataURL(result);
        reader.onloadend = () => {
            let base64data = reader.result;

            let extensao = arquivoName.split('.')[1];
            let binary = convertDataURIToBinary(base64data);
            let blob = new Blob([binary], {type : `audio/${extensao}`});
            let blobUrl = URL.createObjectURL(blob);

            $(`#source-${id_message}`).attr("src", blobUrl);

            let audio = $(`#audio-${id_message}`);
            audio[0].pause();
            audio[0].load(); //suspends and restores all audio element
            audio[0].oncanplaythrough =  audio[0].play();

            $(`#btn-download-${id_message}`).attr('disabled', true);

            loadingDestroy('loading-upload-chat');
        }

        return;
    }

    
    if (result) {
        const url = URL.createObjectURL(result);

        // Create a new anchor element
        const a = document.createElement('a');

        // Set the href and download attributes for the anchor element
        // You can optionally set other attributes like `title`, etc
        // Especially, if the anchor element will be attached to the DOM
        a.href = url;
        a.download = arquivoName || 'download';

        // Click handler that releases the object URL after the element has been clicked
        // This is required for one-off downloads of the blob content
        const clickHandler = () => {
            setTimeout(() => {
                URL.revokeObjectURL(url);
                this.removeEventListener('click', clickHandler);
            }, 150);
        };

        // Add the click event listener on the anchor element
        // Comment out this line if you don't want a one-off download of the blob content
        a.addEventListener('click', clickHandler, false);

        // Programmatically trigger a click on the anchor element
        // Useful if you want the download to happen automatically
        // Without attaching the anchor element to the DOM
        // Comment out this line if you don't want an automatic download of the blob content
        a.click();

        // Return the anchor element
        // Useful if you want a reference to the element
        // in order to attach it to the DOM or use it in some other way
        loadingDestroy('loading-upload-chat');
    }

    loadingDestroy('loading-upload-chat');
}

function onClickUploadAnexoFichaPessoaChat(ext, size, id_mensagem_anexo, nome_file) {
    $('#modal-upload-anexo-ficha-pessoa-chat').modal('show');
    $('#nome_pessoa_upload_ficha').val('');
    $('#codigo_pessoa_upload_ficha').val('');
    $('#nome_aquivo_upload').val(nome_file);
    $('#extensao').val(ext);
    $('#file_size').val(size);
    $('#id_mensagem_anexo').val(id_mensagem_anexo);
}

async function buscaDadosPessoaFicha(element) {
    if (!element.target || element.target.value == 0) {
        let content = `
            <div class="input-group col-12 p-0 m-0 content-table-input-ajax"></div>
            <div class="input-group col-12 content-footer-table-input-ajax">
                <div class="row">
                    <button type="button" class="btn btnLightCustom" title="Gravar" style="margin: 4px 0 5px 5px;" onclick="onClickNovaPessoaAnexoFichaPessoa();"><i class="fas fa-plus"></i> Nova Pessoa</button>
                    <span class="span-contador-registros-table-input-ajax">Exibindo 0 registro(s)</span>
                </div>
            </div>
        `;

        updateListaDataPessoaAnexoFicha(content);
    } else {
        if (element.target.value.length < 3) {
            return;
        }

        setTimeout(async function () {
            result = await searchDataPessoaAnexoFicha(element.target.value, false);
            updateListaDataPessoaAnexoFicha(result);
        }, 500);
    }
}

async function searchDataPessoaAnexoFicha(value) {
    let parameters = btoa(JSON.stringify({ 'usuario_id': id_usuario_current_chat, 'nome': value }));
    let response = await NajApi.getData(`${baseURL}pessoas/grupoClienteRelacionadas/${parameters}`);
    let content = '';
    let rows = '';

    if (response.data.length > 0) {
        for (var i = 0; i < response.data.length; i++) {
            let cpfCnpj = (response.data[i].cpf) ? response.data[i].cpf : response.data[i].cnpj;
            rows += `
                <div class="row row-full content-rows-permissao" style="flex-wrap: nowrap !important;">
                    <div class="col-sm-1 col-icon"><i class="far fa-hand-pointer"></i></div>
                    <div class="col-sm-0 col-codigo" style="display: none;">${response.data[i].pessoa_codigo}</div>
                    <div class="col-sm-5 col-name">${response.data[i].nome}</div>
                    <div class="col-sm-3 col-cpf">${cpfCnpj}</div>
                    <div class="col-sm-3 col-cidade-tarefa">${(response.data[i].cidade == null) ? '' : response.data[i].cidade}</div>
                </div>
            `;
        }

        content += `
            <div class="input-group col-12 p-0 m-0 naj-scrollable content-table-input-ajax">
                ${rows}
            </div>
            <div class="input-group col-12 content-footer-table-input-ajax">
                <div class="row">
                    <button type="button" class="btn btnLightCustom" title="Gravar" style="margin: 4px 0 5px 5px;" onclick="onClickNovaPessoaAnexoFichaPessoa();"><i class="fas fa-plus"></i> Nova Pessoa</button>
                    <span class="span-contador-registros-table-input-ajax">Exibindo ${response.data.length} registro(s)</span>
                </div>
            </div>
        `;
    } else {
        content += '<p class="text-center">Nenhum registro encontrado...</p>';
    }

    return content;
}

function updateListaDataPessoaAnexoFicha(data) {
    $(`#content-select-ajax-naj-pessoa-upload-ficha`)[0].innerHTML = "";
    $(`#content-select-ajax-naj-pessoa-upload-ficha`).append(data);
    $(`#content-select-ajax-naj-pessoa-upload-ficha`).show();
}

async function onChangeCodigosPessoasUploadFicha() {
    const pessoa_codigo = $(`#codigo_pessoa_upload_ficha`).val();
    const usuario_id = id_usuario_current_chat;

    if (!pessoa_codigo) {
        $(`#nome_pessoa_upload_ficha`).val('');
        return;
    }

    let dados = await NajApi.getData(`pessoas/grupoClienteRelacionadas/codigo/${btoa(JSON.stringify({ pessoa_codigo, usuario_id }))}?XDEBUG_SESSION_START`);

    if (!dados.data[0] || !dados.data[0].NOME) return;

    $(`#nome_pessoa_upload_ficha`).val(dados.data[0].NOME);
}

async function getPessoaUploadAnexoFicha(element) {
    if (element.value.length < 3) {
        return;
    }

    setTimeout(async function () {
        result = await searchDataPessoaAnexoFicha(element.value);
        updateListaDataPessoaAnexoFicha(result);
    }, 500);
}

function onClickContentSelectAjaxAnexoFichaPessoa(el) {
    var pai = el.target.parentElement;

    if (!pai.getElementsByClassName('col-codigo')[0]) return;

    let codigoPessoa = pai.getElementsByClassName('col-codigo')[0].textContent;
    let nomePessoa = pai.getElementsByClassName('col-name')[0].textContent;

    $(`#codigo_pessoa_upload_ficha`).val(codigoPessoa);
    $(`#nome_pessoa_upload_ficha`).val(nomePessoa);

    $(`#content-select-ajax-naj-pessoa-upload-ficha`).hide();
}

async function storeAnexarArquivoFichaPessoa() {
    loadingStart('bloqueio-nova-upload-anexo-ficha-pessoa-chat');

    const id_mensagem_anexo = $('#id_mensagem_anexo').val();
    const extensao = $('#extensao').val();

    let name_file = $('#nome_aquivo_upload').val();
    let identificador = sessionStorage.getItem('@NAJ_WEB/identificadorEmpresa');

    //Removendo extensão caso o usuário tente colocar
    name_file = name_file.split('.')[0];
    name_file_com_ext = `${name_file}.${extensao}`;

    const dados = {
        'codigo_pessoa': $('#codigo_pessoa_upload_ficha').val(),
        'descricao': name_file_com_ext,
        'nome_arquivo': name_file_com_ext,
        'file_size': $('#file_size').val(),
        'data_arquivo': getDataAtual(),
        'id_cliente': identificador,
        'id_mesangem': id_mensagem_anexo
    };

    if (!dados.nome_arquivo) {
        loadingDestroy('bloqueio-nova-upload-anexo-ficha-pessoa-chat');
        return NajAlert.toastWarning(`Você deve informar o nome do arquivo!`);
    }

    if (!dados.codigo_pessoa) {
        loadingDestroy('bloqueio-nova-upload-anexo-ficha-pessoa-chat');
        return NajAlert.toastWarning(`Você deve informar o codigo da pessoa!`);
    }

    if (!dados.id_mesangem) {
        loadingDestroy('bloqueio-nova-upload-anexo-ficha-pessoa-chat');
        return NajAlert.toastWarning(`Ops, algo inesperado aconteceu, recarregue a pagina e tente novamente!`);
    }

    const result = await NajApi.postData(`pessoas/anexos`, dados);

    if (!result) {
        loadingDestroy('bloqueio-nova-upload-anexo-ficha-pessoa-chat');
        NajAlert.toastError('Não foi possível fazer o upload do anexo, nome já existente!');
        return;
    }

    if (result.mensagem) {
        NajAlert.toastSuccess(result.mensagem);
        $('#modal-upload-anexo-ficha-pessoa-chat').modal('hide');
        chat.loadInfoUsuario({ "id_usuario_cliente": id_usuario_current_chat });
    }

    loadingDestroy('bloqueio-nova-upload-anexo-ficha-pessoa-chat');
}

async function onClickButtonCadastroPessoaFicha() {
    let codigoCliente = $(`#codigo_pessoa_upload_ficha`).val();

    if (!codigoCliente) {
        NajAlert.toastWarning(`Você deve informar o código da pessoa para utilizar essa ação!`);
        return;
    }

    sessionStorage.setItem('@NAJ_WEB/pessoa_action', 'edit')
    let response = await NajApi.getData(`${baseURL}pessoas/show/${btoa(JSON.stringify({ CODIGO: codigoCliente }))}`);

    await carregaOptionsSelect(`pessoas/divisao`, 'codigo_divisao', false, 'data', false, 1);
    await carregaOptionsSelect(`pessoas/grupopessoa`, 'codigo_grupo', false, 'data', false);

    sessionStorage.setItem('@NAJ_WEB/codigo_pessoa', codigoCliente);

    if (response.CNPJ == "" || response.CNPJ == null) {
        //Esconde o label e o campo do CNPJ
        $('#form-pessoa #label_cnpj').hide();
        $('#form-pessoa #cnpj').hide();
    } else if (response.CEP == "" || response.CEP == null) {
        //Esconde o label e o campo do CEP
        $('#form-pessoa #label_cpf').hide();
        $('#form-pessoa #cpf').hide();
    }

    NajApi.loadData('#form-pessoa', response);

    $('#modal-manutencao-pessoa').modal('show');
    $('#modal-upload-anexo-ficha-pessoa-chat').addClass('z-index-100');
}

async function onClickDownloadAnexoProcessoChat(key) {
    loadingStart('loading-pai');
    let parameters = JSON.parse(atob(key));
    let result = await NajApi.getData(`processos/anexos/download/${key}?XDEBUG_SESSION_START`, true);

    if (result) {
        const url = URL.createObjectURL(result);
        const a = document.createElement('a');

        a.href = url;
        a.download = parameters.name || 'download';

        const clickHandler = () => {
            setTimeout(() => {
                URL.revokeObjectURL(url);
                this.removeEventListener('click', clickHandler);
            }, 150);
        };

        a.addEventListener('click', clickHandler, false);
        a.click();

        loadingDestroy('loading-pai');
    }

    loadingDestroy('loading-pai');
}

async function onClickDownloadAnexoDocumentoPessoaChat(key) {
    loadingStart('loading-pai');
    let parameters = JSON.parse(atob(key));
    let result = await NajApi.getData(`documentos/download/${key}?XDEBUG_SESSION_START`, true);

    if (result) {
        const url = URL.createObjectURL(result);
        const a = document.createElement('a');

        a.href = url;
        a.download = parameters.name || 'download';

        const clickHandler = () => {
            setTimeout(() => {
                URL.revokeObjectURL(url);
                this.removeEventListener('click', clickHandler);
            }, 150);
        };

        a.addEventListener('click', clickHandler, false);
        a.click();

        loadingDestroy('loading-pai');
    }

    loadingDestroy('loading-pai');
}

async function onClickNovaPessoaAnexoFichaPessoa() {
    sessionStorage.setItem('@NAJ_WEB/pessoa_action', 'create');

    await carregaModalManutencaoPessoa();
}

function onClickQuantidadeAtividadeChat(prc_codigo) {
    tableAtividadesProcessoChat = new AtividadeProcessoChatTable(prc_codigo);
    tableAtividadesProcessoChat.render();

    $('#modal-consulta-atividade-processo-chat').modal('show');
}

function onClickQuantidadeAndamentoChat(prc_codigo) {
    tableAndamentoProcessoChat = new AndamentoProcessoChatTable(prc_codigo);
    tableAndamentoProcessoChat.render();

    $('#modal-consulta-andamento-processo-chat').modal('show');
}

async function callShootNotificationMessageOneSignal(chat_mensagem, message, is_anexo) {
    //Se não tiver dados do dispositivo já volta
    if(!sessionStorage.getItem('@NAJ_WEB/dados_dispositivo_usuario_chat')) return;

    const dados_device = JSON.parse(atob(sessionStorage.getItem('@NAJ_WEB/dados_dispositivo_usuario_chat')));
    const nome_empresa = sessionStorage.getItem('@NAJ_WEB/nomeEmpresa');
    const identificador = sessionStorage.getItem('@NAJ_WEB/identificadorEmpresa');

    let include_player_ids = [];
    let dados;

    for (var i = 0; i < dados_device.dados.length; i++) {
        include_player_ids.push(dados_device.dados[i].one_signal_id);
    }

    if(dados_device.dados.one_signal_id) {
        include_player_ids.push(dados_device.dados.one_signal_id);
    }

    if (include_player_ids.length == 0) return;

    if(!chat_mensagem || !chat_mensagem.id_chat) return;

    const android_group = `${identificador}${chat_mensagem.id_chat}`;

    if (is_anexo) {
        dados = {
            "app_id": app_id_one_signal,
            "headings": {
                "en": `${nome_empresa}`
            },
            "contents": {
                "en": `Arquivo enviado`
            },
            "small_icon": "ic_stat_onesignal_default",
            "android_group": android_group,
            "data": {
                "action": "@ACT/reload_messages",
                "message": {
                    "nome": `${nomeUsuarioLogado}`,
                    "apelido": `${apelidoUsuarioLogado}`,
                    "id_cliente": `${identificador}`,
                    "id_usuario_receber": `${objectUsuarioCurrentChat.id_usuario}`
                },
            },
            "include_player_ids": include_player_ids
        };
    } else {
        dados = {
            "app_id": app_id_one_signal,
            "headings": {
                "en": `${nome_empresa}`
            },
            "contents": {
                "en": `${message.replace(/(<([^>]+)>)/gi, " ").trim().replace(/\s{2,}/g, ' ').substr(0, 1500).replace('-&gt;', '->')}`
            },
            "small_icon": "ic_stat_onesignal_default",
            "android_group": android_group,
            "data": {
                "action": "@ACT/new_message",
                "message": {
                    ...chat_mensagem,
                    "id_cliente": `${identificador}`,
                    "nome": `${nomeUsuarioLogado}`,
                    "apelido": `${apelidoUsuarioLogado}`,
                    "id_usuario_receber": `${objectUsuarioCurrentChat.id_usuario}`
                }
            },
            "include_player_ids": include_player_ids
        };
    }

    axios({
        method: 'post',
        url: 'https://onesignal.com/api/v1/notifications',
        data: dados
    })
        .then(response => {
            console.clear();
            console.log('Pusher de mensagem enviado com sucesso!');
        }).catch(e => {
            NajAlert.toastError("Ops, não foi possível enviar o pusher da mensagem!");
        });
}

async function loadNotifyUsers(result, mensagem, files, data_hora) {
    let usuariosDevice = [];

    await result.data_response.map(async (index, item) => {
        usuariosDevice.push(index.usuario);
    });

    let devices = await NajApi.getData(`usuarios/dispositivos/in/${btoa(JSON.stringify({'usuarios': usuariosDevice}))}`);

    if(devices.naj && devices.naj.length > 0) {
        for(let i = 0; i < devices.naj.length; i++) {
            for(let j = 0; j < result.data_response.length; j++) {
                if(devices.naj[i].usuario_id == result.data_response[j].usuario) {
                    if(!devices.naj[i] || !devices.naj[i].dispositivo_id) continue;
                    sessionStorage.setItem('@NAJ_WEB/dados_dispositivo_usuario_chat', btoa(JSON.stringify({'dados': devices.naj[i]})));
                    objectUsuarioCurrentChat = {'nome': result.data_response[j].nome, 'apelido': result.data_response[j].apelido, 'id_usuario': result.data_response[j].usuario};
                    //FORMATA A MENSAGEM PARA NÃO ESTOURAR NO ONESIGNAL
                    let data = {
                        "id_chat": result.data_response[j].chat,
                        "id_usuario": idUsuarioLogado,
                        "conteudo": mensagem.replace(/(<([^>]+)>)/gi, " ").trim().replace(/\s{2,}/g, ' ').substr(0, 1500).replace('-&gt;', '->'),
                        "tipo": (files.length == 0) ? 0 : 1,
                        "data_hora": data_hora,
                        "file_type": null
                    };
                    
                    await callShootNotificationMessageOneSignal(data, mensagem);
                }
            }
        }
    }
}

function onClickFichaEnvolvido(codigo) {
    window.open(`${najAntigoUrl}?idform=pessoas&pessoaid=${codigo}`);
}

function onClickFichaProcesso(codigo) {
    window.open(`${najAntigoUrl}?idform=processos&processoid=${codigo}`);
}

function onClickFichaPessoa(codigo) {
    window.open(`${najAntigoUrl}?idform=pessoas&pessoaid=${codigo}`);
}

function toBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();

        reader.readAsDataURL(file);
        reader.onload = () => resolve(reader.result),
            reader.onerror = error => reject(error)
    });
}