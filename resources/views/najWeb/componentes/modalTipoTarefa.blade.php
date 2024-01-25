<div class="modal fade" id="modal-novo-tipo-tarefa" tabindex="-1" role="dialog" aria-hidden="true">
    <div id="bloqueio-novo-tipo-tarefa" class="loader loader-default" data-half></div>
    <div class="modal-dialog modal-novo-tipo-tarefa" role="document">
        <div class="modal-content modal-content-shadow-naj">
            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj" id="headerTipoTarefa">Novo Tipo de Tarefa</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body-naj scrollable" style="height: 10vh !important;">
                <form class="form-horizontal needs-validation" novalidate="" id="form-novo-tipo-tarefa">
                    <input type="hidden" id="is-alterar-tipo-tarefa" class="form-control">

                    <div class="form-group row">
                        <label for="tipo" class="col-3 control-label label-center text-right">Tipo de Tarefa</label>
                        <div class="col-9">
                            <div class="input-group">
                                <input type="text" id="TIPO" name="TIPO" class="form-control">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer-naj">
                <label for="codigo_divisao" class="col-4 control-label label-center" style="margin-left: -35px;"></label>
                <button type="button" id="gravar-tarefa" class="btn btnLightCustom" title="Gravar" onclick="storeUpdateTipoTarefa();">
                    <i class="fas fa-save"></i>
                    Gravar
                </button>
                <button type="button" class="btn btnLightCustom" title="Novo" onclick="onClickNovoTipoTarefa();">
                    <i class="fas fa-plus"></i>
                    Novo
                </button>
                <button type="button" id="excluir-tipo-tarefa" class="btn btnLightCustom" title="Excluir" data-dismiss="modal" onclick="onClickExcluirTipoTarefa();">
                    <i class="fas fa-trash"></i>
                    Excluir
                </button>
                <button type="button" class="btn btnLightCustom" title="Fechar" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>