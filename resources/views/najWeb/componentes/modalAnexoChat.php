<div class="modal fade" id="modal-anexo-chat" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-medium-naj" style="margin-top: 10%;">
        <div class="modal-content modal-content-shadow-naj">
            <div class="modal-header">
                <h4><i class="glyphicon glyphicon-question-sign"></i> Anexos</h4>
                <button type="button" class="close" data-dismiss="modal">×</button>                
            </div>
            <div class="modal-body data-table-content naj-scrollable" style="max-height: 40vh;">
                <form action="chat/mensagem/anexo" method="post">
                    <div class="table table-striped files" id="previews">
                        <div id="template" class="file-row">
                            <div class="row" style="align-items: center;" id="content-upload-anexos">
                                <!-- <div class="col-2">
                                    <span class="preview"><img data-dz-thumbnail /></span>
                                </div> -->
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
                                    <!-- <button class="btn btn-primary start">
                                        <i class="glyphicon glyphicon-upload"></i><span>Start</span>
                                    </button> -->
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
                </form>
            </div>
            <div class="modal-footer modal-footer-naj">
                <button type="button" class="btn btn-success" onclick="onClickEnviarAnexos();"><i class="fas fa-paper-plane mr-1"></i>Enviar</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times mr-1"></i>Cancelar</button>
                <button type="button" class="btn btn-info fileinput-button float-right"><i class="fas fa-paperclip mr-1"></i></i>Anexar Arquivos</button>
            </div>
        </div>
    </div>
</div>