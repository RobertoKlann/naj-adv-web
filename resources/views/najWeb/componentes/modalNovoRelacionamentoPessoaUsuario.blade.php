<div class="modal fade" id="modal-novo-relacionamento-usuario-pessoa" tabindex="-1" role="dialog" aria-hidden="true">
    <div id="bloqueio-copiar-permissao"></div>
    <div class="modal-dialog content-outside-modal-novo-relacionamento" role="document" id="content-outside-novo-rel" style="max-width: 70%; top: 20%;">
        <div class="modal-content modal-content-shadow-naj">
            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Novo Relacionamento</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body-naj body-modal-novo-relacionamento-pessoa-usuario">
                <form id="form-novo-relacionamento-pessoa-usuario">
                    <div class="form-group row" style="margin-bottom: 0 !important;">
                        <label for="input-nome-pesquisa" class="col-sm-3 control-label label-copiar-permissao">Pesquisa pessoas: </label>
                        <div class="col-lg-9 col-md-9 col-sm-12 div-input-nome-pesquisa-rel-usuario">
                            <div class="input-group">
                                <input type="text" id="input-nome-pesquisa" onkeypress="getPessoaRelacionamento(this);" class="form-control">
                                <span class="ml-1 mt-2">
                                    <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Pessoa que será relacionada ao usuário."></i>
                                </span>
                            </div>
                        </div>
                    </div>
                
                    <div class="form-group row p-0 m-0">
                        <label for="select" class="col-sm-1 control-label label-center"></label>
                        <div class="content-pai-select-ajax-naj-rel-usuarios">
                            <div class="input-group content-select-ajax-rel" id="content-select-ajax-naj-relacionamento-usuarios">
                                
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group row p-0 m-0">
                        <div class="col-lg-12" id="titulo-modulos-permitidos-novo-relacionamento-usuario">
                            <p>Módulos Permitidos</p>
                        </div>
                        <div class="row-checks-relacionamento-usuario">
                            <!-- <div class="col-sm-3 col-md-6 col-lg-2 custom-control custom-checkbox">
                                &emsp;
                            </div> -->
                            <div class="col-sm-3 col-md-6 col-lg-2 p-0 custom-control custom-checkbox input-check-rel-user-receber-pagar">
                                <input class="custom-control-input" type="checkbox" value="1" id="contas_receber" name="contas_receber">
                                &emsp;
                                <label class="custom-control-label" for="contas_receber">Contas Receber</label></br>
                            </div>
                            <div class="col-sm-3 col-md-6 col-lg-2 p-0 custom-control custom-checkbox input-check-rel-user-receber-pagar">
                                <input class="custom-control-input" type="checkbox" value="1" id="contas_pagar" name="contas_pagar">
                                &emsp;
                                <label class="custom-control-label" for="contas_pagar">Contas Pagar</label></br>
                            </div>
                            <div class="col-sm-3 col-md-6 col-lg-2 custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" value="1" id="agenda" name="agenda">
                                &emsp;
                                <label class="custom-control-label" for="agenda">Agenda</label></br>
                            </div>
                            <div class="col-sm-3 col-md-6 col-lg-2 custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" value="1" id="atividades" name="atividades">
                                &emsp;
                                <label class="custom-control-label" for="atividades">Atividades</label></br>
                            </div>
                            <div class="col-sm-3 col-md-6 col-lg-2 custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" value="1" id="processos" name="processos">
                                &emsp;
                                <label class="custom-control-label" for="processos">Processos</label></br>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer-naj" style="display: flex; align-items: center; justify-content: center;">
                <button type="button" class="btn btnLightCustom" title="Gravar" onclick="onClickGravarNovoRelacionamento();">
                    <i class="fas fa-save"></i>
                    Gravar
                </button>
            </div>
        </div>
    </div>
</div>