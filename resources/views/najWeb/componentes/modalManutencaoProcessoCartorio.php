<div class="modal fade" id="modal-manutencao-processo-cartorio" role="dialog" aria-hidden="true">
    <div id="bloqueio-modal-manutencao-processo-cartorio" class="loader loader-default"></div>
    <div class="modal-dialog" role="document" style="margin-top: 15%">
        <div class="modal-content modal-content-shadow-naj" style="height: 65%;">

            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Manutenção Cartório</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="card-body-naj page-content container-fluid mt-4">
                <form class="form-horizontal needs-validation" novalidate="" id="form-processo-cartorio">
                    <div class="row">
                        <div class="col-12">

                            <div class="form-group row">
                                <label for="codigo_processo_cartorio" class="col-2 control-label text-right label-center">Código</label>
                                <div class="col-4">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="CODIGO" id="codigo_processo_cartorio" placeholder="Código..." required="" readonly="">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="nome_processo_cartorio" class="col-2 control-label text-right label-center">Cartório</label>
                                <div class="col-10">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="CARTORIO" id="nome_processo_cartorio" placeholder="Cartório..." required="">
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
                        <button type="button" id="gravarProcessoCartorio" class="btn btnLightCustom" title="Gravar">
                            <i class="fas fa-save"></i>
                            Gravar
                        </button>
                        <button type="button" class="btn btnLightCustom" onclick="novoRegistroProcessoCartorio();" title="Novo">
                            <i class="fas fa-plus"></i>
                            Novo
                        </button>
                        <button type="button" class="btn btnLightCustom" onclick="$('#modal-manutencao-processo-cartorio').modal('hide');" title="Fechar">
                            <i class="fas fa-times"></i>
                            Fechar
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>