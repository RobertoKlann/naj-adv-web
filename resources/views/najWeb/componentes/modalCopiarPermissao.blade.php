<div class="modal fade" id="modal-copiar-permissao-usuario" tabindex="-1" role="dialog" aria-hidden="true">
    <div id="bloqueio-copiar-permissao"></div>
    <div class="modal-dialog modal-copiar-permissao" role="document" id="content-outside">
        <div class="modal-content modal-content-shadow-naj">
            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Copiar Permissões</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body-naj">
                <div class="form-group row">
                    <label for="input-nome-pesquisa-copiar-dono" class="col-sm-4 control-label label-copiar-permissao">Copiando permissões de:</label>
                    <div class="col-lg-8 col-md-8 col-sm-12" style="margin-left: -5%;">
                        <div class="input-group">
                            <input type="text" id="input-nome-pesquisa-copiar-dono" onkeypress="getUsuarios(this);" class="form-control">
                            <span class="ml-1 mt-2">
                                <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Usuário que terá as permissões copiadas"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="form-group row p-0 content-pai-select-ajax-copiar">
                    <div class="col-sm-12">
                        <div class="input-group content-select-ajax-copiar-permissao" id="content-select-ajax-naj-input-nome-pesquisa-copiar-dono">
                            
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="input-nome-pesquisa-copiar-receber" class="col-sm-4 control-label label-copiar-permissao">Para o usuário:</label>
                    <div class="col-lg-8 col-md-8 col-sm-12" style="margin-left: -5%;">
                        <div class="input-group">
                            <input type="text" id="input-nome-pesquisa-copiar-receber" onkeypress="getUsuarios(this);" class="form-control">
                            <span class="ml-1 mt-2">
                                <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Usuário que irá receber as permissões copiadas"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="form-group row p-0 content-pai-select-ajax-copiar">
                    <div class="col-sm-12">
                        <div class="input-group content-select-ajax-copiar-permissao" id="content-select-ajax-naj-input-nome-pesquisa-copiar-receber">
                            
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer-naj" style="height: 25% !important; display: flex; align-items: center;">
                <label for="codigo_divisao" class="col-4 control-label label-center" style="margin-left: -20px;"></label>
                <button type="button" class="btn btnLightCustom" title="Aplicar" onclick="onClickCopiarPermissao();">
                    <i class="fas fa-copy"></i>
                    Aplicar
                </button>
            </div>
        </div>
    </div>
</div>