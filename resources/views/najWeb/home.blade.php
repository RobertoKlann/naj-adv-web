@extends('najWeb.viewBase')

@section('title', 'Atendimento')

@section('css')
    <link href="{{ env('APP_URL') }}ampleAdmin/assets/libs/summernote/dist/summernote-bs4.css" rel="stylesheet">
    <link href="{{ env('APP_URL') }}ampleAdmin/assets/libs/dropzone/dist/min/dropzone.min.css" rel="stylesheet">
    <link href="{{ env('APP_URL') }}ampleAdmin/assets/libs/jquery-steps/steps.css" rel="stylesheet">
    <link href="{{ env('APP_URL') }}css/tarefaChat.css" rel="stylesheet">

    <style>
        .form-group {
            margin-bottom: 7px;
        }
    </style>
@endsection

@section('active-layer', 'home')

@section('content')
<div class="row height-100">
    <div id="loading-pai" class="loader loader-default" data-half></div>
    <div class="col-3 pl-4 bg-light" style="background-color: white !important;">
        <div id="loading-contacts-chat" class="loader loader-default" data-half></div>
        <div id="loading-contacts-chat-second" class="loader loader-default" data-half></div>
        <div class="row">
            <div class="col-12 pl-0 pr-0">
                <ul class="nav nav-tabs customtab" role="tablist" style="font-size: 12px;">
                    <li class="nav-item" style="width: 34%;"><a class="nav-link active pl-3 pr-1 pt-3 pb-3" data-toggle="tab" data-link-nav-chat="todos" href="#content-todos" role="tab"><span class="hidden-sm-up"><i class="fas fa-check"></i></span> <span class="hidden-xs-down">Concluídos</span></a></li>
                    <li class="nav-item" style="width: 38%;"><a class="nav-link pl-2 pr-1 pt-3 pb-3" data-toggle="tab" data-link-nav-chat="andamento" href="#content-em-andamento" role="tab"><span class="hidden-sm-up"><i class="fas fa-play"></i></span> <span class="hidden-xs-down">Em Andamento</span></a></li>
                    <li class="nav-item" style="width: 28%;">
                        <a class="nav-link pl-2 pr-1 pt-3 pb-3" data-toggle="tab" data-link-nav-chat="fila" href="#content-fila" role="tab">
                        <div class="" id="icone-pendentes" style="left: -75px; top: 16px;">
                            <span class="heartbit"></span>
                            <span class="point"></span>
                        </div>
                        <span class="hidden-xs-down ml-2">Pendentes</span>
                        </a>
                    </li>
                </ul>
                <div class="tab-content" id="content-list-contatos">
                    <div class="tab-pane data-table-content" id="content-todos" role="tabpanel" style="height:calc(100vh - 160px); overflow-y: hidden;">
                        <div class="row m-0 pt-1 pb-2 mb-dropdown-item-divider">
                            <div class="input-group-prepend pl-2 pr-1" style="width: 90% !important;">
                                <input type="text" id="filter-name-chat" class="form-control" placeholder="Pesquisar por cliente" title="Pesquisar por cliente">
                                <i class="fas fa-search" onclick="onClickFilterUserChat();" style="cursor: pointer; margin-top: 10px; margin-left: -20px; z-index: 1;"></i>
                            </div>
                            <div class="btn-group dropright show pl-0"  style="width: 5% !important;">
                                <button type="button" class="btn btn-light btn-light-atendimento-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-filter" act="1"></i></button>
                                <div class="content-dropbox-filter-chat-time dropdown-menu pb-0">
                                    <a class="dropdown-item mb-dropdown-item-divider" href="#" id="filter-data-atual" onclick="onClickFilterDateChat();" style="margin-top: -10px;"><i class="far fa-calendar-alt mr-2"></i>Mês Atual</a>
                                    <a class="dropdown-item mb-dropdown-item-divider" href="#" id="filter-data-7" onclick="onClickFilterDateChat(7);"><i class="far fa-calendar-alt mr-2"></i>Últimos 7 Dias</a>
                                    <a class="dropdown-item mb-dropdown-item-divider" href="#" id="filter-data-15" onclick="onClickFilterDateChat(15);"><i class="far fa-calendar-alt mr-2"></i>Últimos 15 Dias</a>
                                    <a class="dropdown-item mb-dropdown-item-divider" href="#" id="filter-data-30" onclick="onClickFilterDateChat(30);"><i class="far fa-calendar-alt mr-2"></i>Últimos 30 Dias</a>
                                    <a class="dropdown-item mb-dropdown-item-divider" href="#" id="filter-data-60" onclick="onClickFilterDateChat(60);"><i class="far fa-calendar-alt mr-2"></i>Últimos 60 Dias</a>
                                    <a class="dropdown-item" href="#" id="filter-data-all" onclick="onClickFilterDateChat(null, true);"><i class="far fa-calendar-alt mr-2"></i>Todos</a>
                                </div>
                            </div>
                        </div>
                        <ul class="mailbox list-style-none naj-scrollable" id="content-scroll-messages-finish" style="height: 69vh; overflow-y: auto;">
                            <div id="loading-content-scroll-messages-finish" class="loader loader-default" data-half style="margin-top: 29.5%;"></div>
                            <li>
                                <div class="message-center chat-scroll" id="contacts-finish">
                                    
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-pane data-table-content naj-scrollable" id="content-em-andamento" role="tabpanel" style="height:calc(100vh - 160px);">
                        <ul class="mailbox list-style-none">
                            <li>
                                <div class="message-center chat-scroll" id="contacts-andamento">
                                    
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-pane data-table-content naj-scrollable" id="content-fila" role="tabpanel" style="height:calc(100vh - 160px);">
                        <ul class="mailbox list-style-none">
                            <li>
                                <div class="message-center chat-scroll" id="contacts-pendentes">
                                    
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 bg-content-messages pr-0 pl-0">
        <div class="row bg-light content-buttons-atendimento">
            <div id="loading-buttons-atendimento" class="loader loader-default" data-half style="max-height: 9.5%;"></div>
            <!-- <button type="button" id="buttonChangeTypeChatInternal" class="btn btn-light btn-rounded mr-2"><i class="fas fa-exchange-alt mr-2"></i>Chat Interno</button>
            <button type="button" id="buttonChangeTypeChatExternal" class="btn btn-light btn-rounded mr-2"><i class="fas fa-exchange-alt mr-2"></i>Chat Externo</button> -->
            <button type="button" id="buttonIniciarAtendimento" class="btn btn-success btn-rounded mr-2"><i class="fas fa-play mr-2"></i>Iniciar Atendimento</button>
            <button type="button" id="buttonFimAtendimento" class="btn btn-danger btn-rounded mr-2"><i class="fas fa-check mr-2"></i>Encerrar Atendimento</button>
            <button type="button" id="buttonTransferirAtendimento" class="btn btn-light btn-rounded ml-2 text-info"><i class="fas fa-share-square mr-2"></i>Transferir Atendimento</button>
            <div class="btn-group dropleft show pl-0" style="width: 3% !important; right: 7px; position: absolute; background-color: #f1f1f1 !important;">
                <button type="button" class="btn btn-light btn-light-atendimento-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-v" act="1"></i></button>
                <div class="dropdown-menu pb-0">
                    <a class="dropdown-item" href="#" id="action-novo-atendimento-chat"><i class="fas fa-plus mr-2"></i>Nova Mensagem</a>
                    <a class="dropdown-item" href="#" id="action-nova-tarefa-chat" onclick="onClickNovaTarefa();"><i class="fas fa-plus mr-2"></i>Nova Tarefa</a>
                </div>
            </div>
        </div>
        <div class="chat-box data-table-content naj-scrollable content-chat-box-full" style="overflow-x: hidden;" id="pololo">
            <div id="loading-message-chat" class="loader loader-default" data-half></div>
            <div id="loading-upload-chat" class="loader loader-default" data-half></div>
            <div class="content-message-select-user-chat">
                <p class="text-message-select-user-chat">Selecione uma conversa ao lado para ver suas mensagens...</p>
            </div>
            <div class="mail-compose bg-white w-100 content-chat-box-full" id="content-editor-upload" style="height: calc(100vh - 110px) !important;">
                <div class="card-header bg-info row">
                    <div class="col-11">
                        <h4 class="mb-0 text-white">Editor de Mensagem</h4>
                    </div>
                    <div class="col-1">
                        <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded" onclick="onClickCancelarEditorTexto();" style="right: 2%; position: absolute; cursor: pointer; border-color: #3695bf !important; margin-top: -8px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body naj-scrollable" id="card-body-editor-chat" style="height: 82%;">
                    <div id="loading-anexo-chat-editor" class="loader loader-default" data-half></div>
                    <form>
                        <div id="content-button-rascunho-editor-message-chat">
                            <span class="font-10 badge badge-danger" title="Rascunho da mensagem">RASCUNHO</span><i class="fas fa-trash ml-1 cursor-pointer" id="icon-trash-rascunho-editor-message-chat"></i>
                        </div>
                        <div id="summernote" class="input-mensagem-chat"></div>
                        <button type="button" class="btn btn-success" onclick="onClickSendAnexoEditor();"><i class="far fa-paper-plane"></i> Enviar</button>
                        <button type="button" class="btn btn-danger" onclick="onClickCancelarEditorTexto();">Cancelar</button>
                        <button type="button" class="btn btn-info fileinput-button-editor float-right"><i class="fas fa-paperclip mr-1"></i></i>Anexar Arquivos</button>
                        <hr style="margin-bottom: 5px !important; margin-top: 5px !important;">
                        <h4 class="mb-0">Anexos</h4>
                        <div class="table table-striped files" id="previews-file-editor">
                            <div id="template-editor" class="file-row">
                                <div class="row" style="align-items: center;">
                                    <div class="col-7">
                                        <p class="name" data-dz-name></p>
                                        <strong class="error text-danger" data-dz-errormessage></strong>
                                    </div>
                                    <div class="col-2">
                                        <p class="size" data-dz-size></p>
                                    </div>
                                    <div class="col-3">
                                        <button data-dz-remove class="btn btn-danger cancel">
                                            <i class="fas fa-ban mr-1"></i><span>Cancelar</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="mail-compose bg-white w-100" id="content-upload-anexos-chat" style="overflow: hidden !important; height: 100%;">
                <div class="card-header bg-info row">
                    <div class="col-11">
                        <h4 class="mb-0 text-white">Anexos</h4>
                    </div>
                    <div class="col-1">
                        <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded" onclick="onClickCancelarAnexos();" style="right: 2%; position: absolute; cursor: pointer; border-color: #3695bf !important; margin-top: -8px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body naj-scrollable" style="overflow-x: hidden !important; height: 82%;">
                    <div id="loading-anexo-chat" class="loader loader-default" data-half></div>
                    <div class="col-12">
                        <div class="table table-striped files" id="previews">
                            <div id="template" class="file-row">
                                <div class="row" style="align-items: center;">
                                    <div class="col-7">
                                        <p class="name" data-dz-name></p>
                                        <strong class="error text-danger" data-dz-errormessage></strong>
                                    </div>
                                    <div class="col-2">
                                        <p class="size" data-dz-size></p>
                                    </div>
                                    <div class="col-3">
                                        <button data-dz-remove class="btn btn-danger cancel">
                                            <i class="fas fa-ban mr-1"></i><span>Cancelar</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="_method" value="POST">
                        <input type="hidden" id="token" name="_token" value="{{ csrf_token() }}">
                        <meta name="csrf-token" content="{{ csrf_token() }}" />
                    </div>
                </div>
                <div class="card-footer-naj">
                    <div style="position: fixed; bottom: 10px; width: 50%;">
                        <button type="button" class="btn btn-success" onclick="onClickSendAnexoChat();"><i class="fas fa-paper-plane mr-1"></i>Enviar</button>
                        <button type="button" class="btn btn-danger" onclick="onClickCancelarAnexos();"><i class="fas fa-times mr-1"></i>Cancelar</button>
                        <button type="button" class="btn btn-info fileinput-button" style="margin-right: 5%; float: right;"><i class="fas fa-paperclip mr-1"></i></i>Anexar Arquivos</button>
                    </div>
                </div>
            </div>
            <ul class="chat-list" id="content-messages-chat"></ul>
        </div>
        <div class="bg-light content-butons-chat">
            <div class="btn-group dropup show pl-0 content-input-mensagem-chat" style="width: 3% !important; left: 1px; position: absolute; background-color: #f1f1f1 !important;">
                <button type="button" class="btn btn-light btn-light-atendimento-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-v" act="1"></i></button>
                <div class="dropdown-menu pb-0" style="position: absolute; transform: translate3d(99px, 0px, 0px); top: 0px; left: 0px; will-change: transform;">
                    <a class="dropdown-item mb-dropdown-item-divider" id="input-editor-texto" href="#"><i class="far fa-file-word mr-2"></i>Editor de Mensagem</a>
                    <a class="dropdown-item mb-dropdown-item-divider" id="input-anexo" href="#"><i class="fas fa-paperclip mr-2"></i>Enviar um Anexo</a>
                    <a class="dropdown-item" id="enviar-mensagem-no-enter" href="#"><i class="fas fa-paper-plane mr-2"></i>Enviar Mensagem (Enter)</a>
                </div>
            </div>
            <textarea name="" id="input-text-chat-enviar" class="input-mensagem-chat content-input-mensagem-chat" wrap="hard" placeholder="Digite sua mensagem"></textarea>
            <div id="content-button-rascunho-message-chat">
                <span class="font-10 badge badge-danger" title="Rascunho da mensagem">RASCUNHO</span><i class="fas fa-trash ml-1 cursor-pointer" id="icon-trash-rascunho-message-chat"></i>
            </div>
        </div>
    </div>
    <div class="col-3 bg-light bg-info-user-chat">
        <div id="loading-info-user-chat" class="loader loader-default" data-half></div>
        <div class="row pl-0 pt-0 pb-0">            
            <div class="col-12 pl-0 pr-0 pl-0">
                <ul class="nav nav-tabs customtab" role="tablist" style="font-size: 12px;">
                    <li class="nav-item" style="width: 28%;"><a class="nav-link active pl-2 pr-1 pt-3 pb-3" data-toggle="tab" href="#info-processos-user" role="tab"><span class="hidden-sm-up"><i class="fas fa-gavel"></i></span> <span class="hidden-xs-down">Processos</span></a> </li>
                    <li class="nav-item" style="width: 38%;"><a class="nav-link pl-3 pr-1 pt-3 pb-3" data-toggle="tab" href="#info-documentos-user" role="tab"><span class="hidden-sm-up"><i class="fas fa-users"></i></span> <span class="hidden-xs-down">Pessoa(s)</span></a> </li>
                    <li class="nav-item" style="width: 34%;"><a class="nav-link pl-2 pl-2 pr-1 pt-3 pb-3" data-toggle="tab" href="#info-financeiro-user" role="tab"><span class="hidden-sm-up"><i class="fas fa-dollar-sign"></i></span> <span class="hidden-xs-down">Financeiro</span></a> </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active tab-pane-info-adicional-user-chat data-table-content naj-scrollable mr-3" id="info-processos-user" role="tabpanel">
                        <div class="text-no-process-chat m-2">
                            <p>Selecione uma conversa na lista de contatos para ver os processos.</p>
                        </div>
                    </div>
                    <div class="tab-pane tab-pane-info-adicional-user-chat data-table-content naj-scrollable mr-3" id="info-financeiro-user" role="tabpanel">
                        <div class="text-no-process-chat m-2">
                            <p>Selecione uma conversa na lista de contatos para ver o financeiro.</p>
                        </div>
                    </div>
                    <div class="tab-pane tab-pane-info-adicional-user-chat data-table-content naj-scrollable mr-3" id="info-documentos-user" role="tabpanel">
                        <div class="text-no-process-chat m-2">
                            <p>Selecione uma conversa na lista de contatos para ver os documentos.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    @component('najWeb.componentes.modalConsultaNovoAtendimentoChat')
    @endcomponent

    @component('najWeb.componentes.modalTransferirAtendimentoChat')
    @endcomponent

    @component('najWeb.componentes.modalAnexoChat')
    @endcomponent

    @component('najWeb.componentes.modalUploadAnexoFichaPessoaChat')
    @endcomponent

    @component('najWeb.componentes.modalNovaTarefaChat')
    @endcomponent

    @component('najWeb.componentes.modalTipoTarefa')
    @endcomponent

    @component('najWeb.componentes.modalPrioridadeTarefa')
    @endcomponent

    @component('najWeb.componentes.modalManutencaoPessoa')
    @endcomponent

    @component('najWeb.componentes.modalManutencaoPessoaContato')
    @endcomponent

    @component('najWeb.componentes.modalConsultaAtividadeProcessoChat')
    @endcomponent

    @component('najWeb.componentes.modalConsultaAndamentoProcessoChat')
    @endcomponent

    @component('najWeb.componentes.modalConsultaAvancadaNovaMensagemChat')
    @endcomponent

@endsection
@section('scripts')
    <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/bootstrap-switch/dist/js/bootstrap-switch.min.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/select2/dist/js/select2.full.min.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/select2/dist/js/select2.min.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/dist/js/pages/forms/select2/select2.init.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/dist/js/pages/email/email.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/jquery-steps/build/jquery.steps.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/summernote/dist/summernote-bs4.min.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/dropzone/dist/min/dropzone.min.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/dist/js/custom.js"></script>
    <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/jquery-validation/dist/jquery.validate.min.js"></script>
    <script src="{{ env('APP_URL') }}js/tables/pessoaContatoTable.js"></script>
    <script src="{{ env('APP_URL') }}js/pessoa.js"></script>
    <script src="{{ env('APP_URL') }}js/pessoaContato.js"></script>
    <script src="{{ env('APP_URL') }}js/tables/atendimentoChatTable.js"></script>
    <script src="{{ env('APP_URL') }}js/Chat.js"></script>
    <script src="{{ env('APP_URL') }}js/atendimento.js"></script>
    <script src="{{ env('APP_URL') }}js/consultaNovaMensagemChat.js"></script>
    <script src="{{ env('APP_URL') }}js/tarefaChat.js"></script>
    <script src="{{ env('APP_URL') }}js/transferirAtendimentoChat.js"></script>
    <script src="{{ env('APP_URL') }}js/tables/atividadeProcessoChatTable.js"></script>
    <script src="{{ env('APP_URL') }}js/tables/andamentoProcessoChatTable.js"></script>
    
    <script>

        //Configurando os steps do novo atendimento
        $(".tab-wizard").steps({
            headerTag: "h6",
            bodyTag: "section",
            transitionEffect: "fade",
            titleTemplate: '<span class="step">#index#</span> #title#',
            labels: {
                previous: "Anterior",
                next: "Próximo",
                finish: "Encaminhar"
            },
            onFinished: async function(event, currentIndex) {
                loadingStart('loading-novo-atendimento');
                
                debugger;
                let aux = [];
                let aux2 = [];

                usersNewAtendimento.forEach((item, key) => {
                    if (typeof item == 'object')
                        aux.push(item);
                });

                aux.forEach((item, key) => {
                    if (aux2.includes(item.id))
                        aux.splice(key, 1);

                    aux2.push(item.id);
                });

                let files    = await loadAnexoNovoAtendimento();
                let mensagem = $("#summernote-novoatendimento").summernote('code');
                let data     = {
                    "mensagem" : mensagem.replace(/<div>\s*<\/div>/ig, '').replace(/<div\s*\/>/ig, '').replace(/<p>\s*<\/p>/ig, '').replace(/<p\s*\/>/ig, ''),
                    "usuarios" : aux,
                    "data_hora": getDataHoraAtual(),
                    "data"     : getDataAtual(),
                    "files"    : files,
                    "tag": tag_mensagem_coletiva_to_cliente_editor
                };

                if(usersNewAtendimento.length == 0) {
                    NajAlert.toastWarning('Você deve selecionar ao menos uma pessoa para receber a mensagem!');
                    loadingDestroy('loading-novo-atendimento');
                    return;
                }

                if(!mensagem && dropzoneNewAtendimento.files.length < 1) {
                    NajAlert.toastWarning('Você deve digitar uma mensagem ou adicionar um anexo para ser enviado!');
                    loadingDestroy('loading-novo-atendimento');
                    return;
                }

                let result = await NajApi.postData(`chat/novo/atendimento`, data);

                if(result.hasAtendimento.length > 0) {
                    
                    if(result.data_response && result.data_response.length > 0) {
                        loadNotifyUsers(result, mensagem, files, data.data_hora);
                    }
                    
                    if(result.hasAtendimento.length > 1) {

                        if(usersNewAtendimento.length == result.hasAtendimento.length) {
                            NajAlert.toastSuccess(`Mensagem não enviada, o(s) usuário(s) selecionados já estão sendo atendidos!`);
                            $('#previews-file-novo-atendimento')[0].innerHTML = '';
                            dropzoneNewAtendimento.files = [];
                            $('#modal-consulta-novo-atendimento-chat').modal('hide');
                            loadingDestroy('loading-novo-atendimento');
                            return;
                        }

                        let nomeUsuarios = [];
                        for(var i = 0; i < result.hasAtendimento.length; i++) {
                            nomeUsuarios.push(result.hasAtendimento[i].nome);
                        }
                        NajAlert.toastSuccess(`Mensagem enviada com sucesso, menos para o(s) usuário(s) ${nomeUsuarios.join(', ')} que já estão sendo atendidos!`);
                    } else {
                        if(usersNewAtendimento.length > 1) {
                            let nomeUsuarios = [];
                            for(var i = 0; i < result.hasAtendimento.length; i++) {
                                nomeUsuarios.push(result.hasAtendimento[i].nome);
                            }

                            NajAlert.toastSuccess(`Mensagem enviada com sucesso, menos para o(s) usuário(s) ${nomeUsuarios.join(', ')} que já estão sendo atendidos!`);
                        } else {
                            NajAlert.toastError(`Mensagem não enviada, o usuário selecionado já está sendo atendido!`);
                        }
                    }
                } else {
                    if(result.data_response && result.data_response.length > 0) {
                        loadNotifyUsers(result, mensagem, files, data.data_hora);
                    }
                    
                    NajAlert.toastSuccess('Mensagem enviada com sucesso!');
                }

                await chat.loadContacts();
                $('#previews-file-novo-atendimento')[0].innerHTML = '';
                dropzoneNewAtendimento.files = [];
                $('#modal-consulta-novo-atendimento-chat').modal('hide');
                loadingDestroy('loading-novo-atendimento');
            }
        });

        //Configuração do Editor de Texto
        $('#summernote').summernote({
            tabsize: 4,
            height: 250,
            callbacks: {
                onPaste: function (e) {
                    var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                    e.preventDefault();
                    document.execCommand('insertText', false, bufferText);
                }
            }
        });

        //Configuração do Editor de Texto
        $('#summernote-novoatendimento').summernote({
            tabsize: 4,
            height: 150,
            callbacks: {
                onPaste: function (e) {
                    var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                    e.preventDefault();
                    document.execCommand('insertText', false, bufferText);
                }
            },
            disableResizeEditor: true
        });

        //Configuração do UPLOAD
        Dropzone.autoDiscover = false;

        Dropzone.prototype.filesize = function(size) {
            var selectedSize = Math.round(size / 1024);
            return "<strong>" + selectedSize + "</strong> KB";
        };

        var previewNode = document.querySelector("#template");
        previewNode.id = "";
        var previewTemplate = previewNode.parentNode.innerHTML;
        previewNode.parentNode.removeChild(previewNode);

        var myDropzone = new Dropzone(document.body, {
            url: `${baseURL}chat/mensagem/anexo`,
            thumbnailWidth: 80,
            thumbnailHeight: 80,
            parallelUploads: 5,
            previewTemplate: previewTemplate,
            autoQueue: false,
            previewsContainer: "#previews",
            clickable: ".fileinput-button",
            dictFileSizeUnits: 'b'
        });

        var previewNodeEditor = document.querySelector("#template-editor");
        previewNodeEditor.id = "";
        var previewTemplateEditor = previewNodeEditor.parentNode.innerHTML;
        previewNodeEditor.parentNode.removeChild(previewNodeEditor);

        var myDropzoneEditor = new Dropzone('#previews-file-editor', {
            url: `${baseURL}chat/mensagem/anexo`,
            thumbnailWidth: 80,
            thumbnailHeight: 80,
            parallelUploads: 5,
            previewTemplate: previewTemplateEditor,
            autoQueue: false,
            previewsContainer: "#previews-file-editor",
            clickable: ".fileinput-button-editor"
        });

        //Configurando o anexo novo atendimento
        var previewNodeNewAtendimento = document.querySelector("#template-novo-atendimento");
        previewNodeNewAtendimento.id = "";
        var previewTemplateNewAtendimento = previewNodeNewAtendimento.parentNode.innerHTML;
        previewNodeNewAtendimento.parentNode.removeChild(previewNodeNewAtendimento);

        var dropzoneNewAtendimento = new Dropzone('#previews-file-novo-atendimento', {
            url: `${baseURL}chat/mensagem/anexo`,
            thumbnailWidth: 80,
            thumbnailHeight: 80,
            parallelUploads: 5,
            previewTemplate: previewTemplateNewAtendimento,
            autoQueue: false,
            previewsContainer: "#previews-file-novo-atendimento",
            clickable: ".fileinput-button-novo-atendimento"
        });
        
    </script>
@endsection