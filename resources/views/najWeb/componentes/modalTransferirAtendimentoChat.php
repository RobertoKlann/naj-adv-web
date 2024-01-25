<div class="modal fade" id="modal-transferir-atendimento-chat" tabindex="-1" role="dialog" aria-hidden="true">
    <div id="bloqueio-transferir-permissao"></div>
    <div class="modal-dialog modal-copiar-permissao" role="document" id="content-outside">
        <div class="modal-content modal-content-shadow-naj">
            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Transferência de Atendimento</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body-naj">
                <div class="form-group row">
                    <label for="input-nome-pesquisa-dono" class="col-sm-2 control-label label-transferir-atendimento">Transferindo de:</label>
                    <div class="col-8">
                        <div class="input-group">
                            <input type="text" id="input-nome-pesquisa-dono" onkeypress="getUsuarios(this);" class="form-control" disabled>
                            <span class="ml-1 mt-2">
                                <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Usuário que está transferindo o atendimento"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="input-nome-pesquisa-receber" class="col-sm-2 control-label label-transferir-atendimento">Para o usuário:</label>
                    <div class="col-8">
                        <div class="input-group">
                            <input type="text" id="input-nome-pesquisa-receber" onkeypress="getUsuarios(this);" class="form-control">
                            <span class="ml-1 mt-2">
                                <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Usuário que irá dar sequência no atendimento"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="form-group row mb-0">
                    <label class="col-sm-2 control-label"></label>
                    <div class="col-8">
                        <div class="content-select-ajax-padrao" id="content-select-ajax-naj-input-nome-pesquisa-transferir"></div>
                    </div>
                </div>                
            </div>
            <div class="card-footer-naj" style="height: 20% !important; display: flex; align-items: center;">
                <label for="codigo_divisao" class="col-2 control-label label-transferir-atendimento" style="margin-left: 10px;"></label>
                <button class="btn btnLightCustom" title="Transferir" onclick="onClickTransferirAtendimento();"><i class="fas fa-share-square"></i>  Transferir</button>
            </div>
        </div>
    </div>
</div>