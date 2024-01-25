<div class="modal fade" id="modal-manutencao-processo-area-juridica" role="dialog" aria-hidden="true">
    <div id="bloqueio-modal-manutencao-processo-area-juridica" class="loader loader-default"></div>
    <div class="modal-dialog" role="document" style="margin-top: 15%">
        <div class="modal-content modal-content-shadow-naj" style="height: 65%;">

            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Manutenção Área Jurídica</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="card-body-naj page-content container-fluid mt-4">
                <form class="form-horizontal needs-validation" novalidate="" id="form-processo-area-juridica">
                    <div class="row">
                        <div class="col-12">

                            <div class="form-group row">
                                <label for="codigo_processo_area_juridica" class="col-2 control-label text-right label-center">Código</label>
                                <div class="col-4">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="ID" id="codigo_processo_area_juridica" placeholder="Código..." required="" readonly="">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="nome_processo_area_juridica" class="col-2 control-label text-right label-center">Área Jurídica</label>
                                <div class="col-10">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="AREA" id="nome_area_juridica" placeholder="Área Jurídica..." required="">
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        </div>
                    </div>
                </form>
            </div>

            <div class="card-footer-naj">
                <div class="row">
                    <div class="col-2">
                        <!--Div somente para manter o layout do modal-->
                    </div>
                    <div class="col-10">
                        <button type="button" id="gravarProcessoAreaJuridica" class="btn btnLightCustom" title="Gravar">
                            <i class="fas fa-save"></i>
                            Gravar
                        </button>
                        <button type="button" class="btn btnLightCustom" onclick="novoRegistroProcessoAreaJuridica();" title="Novo">
                            <i class="fas fa-plus"></i>
                            Novo
                        </button>
                        <button type="button" class="btn btnLightCustom" onclick="$('#modal-manutencao-processo-area-juridica').modal('hide');" title="Fechar">
                            <i class="fas fa-times"></i>
                            Fechar
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>