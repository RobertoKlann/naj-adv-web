<div class="modal fade" id="modal-consulta-novo-atendimento-chat" tabindex="-1" role="dialog" aria-hidden="true">
    <div id="loading-novo-atendimento" class="loader loader-default" data-half></div>
    <div class="modal-dialog modal-extra-large" role="document">
        <div class="modal-content modal-content-shadow-naj">
            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Nova Mensagem</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body-chat-novo-atendimento p-0 wizard-content naj-scrollable" style="overflow-y: auto !important;">
                <div class="tab-wizard wizard-circle">
                    <!-- Step 1 -->
                    <h6>Selecione o(s) Destinatário(s)</h6>
                    <section>
                        <div class="row">
                            <div id="loading-user" class="loader loader-default" data-half></div>
                            <div id="datatable-novo-atendimento-chat-modal" class="naj-datatable" style="height: 63vh !important;"></div>
                        </div>
                    </section>

                    <!-- Step 2 -->
                    <h6>Editar e Enviar</h6>
                    <section>
                        <div class="row mt-4">
                            <div class="col-12" id="content-input-summernote-novo-atendimento">
                                <div id="summernote-novoatendimento" class="input-mensagem-chat"></div>
                            </div>
                            <div class="col-12 ml-3" style="max-width: 97.1%; padding-top: 10px;">
                                <div class="row">
                                    <button type="button" class="btn btn-info btn-rounded fileinput-button-novo-atendimento float-left"><i class="fas fa-paperclip mr-1"></i>Anexar Arquivos</button>
                                </div>
                                <hr style="margin-bottom: 5px !important; margin-top: 5px !important; margin-left: -15px !important; width: 102.9%;">
                                <div class="row mt-2 naj-scrollable" style="overflow-y: auto !important; height: 18vh;">
                                    <div class="table table-striped files" id="previews-file-novo-atendimento" style="overflow-x: hidden; border: none !important;">
                                        <div id="template-novo-atendimento" class="file-row">
                                            <div class="row" style="align-items: center;">
                                                <div class="col-7">
                                                    <p class="name" data-dz-name></p>
                                                    <strong class="error text-danger" data-dz-errormessage></strong>
                                                </div>
                                                <div class="col-2">
                                                    <p class="size" data-dz-size></p>
                                                    <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                                        <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <button data-dz-remove class="btn btn-danger cancel">
                                                        <i class="fas fa-ban mr-1"></i><span>Cancelar</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>