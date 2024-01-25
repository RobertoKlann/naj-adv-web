<div class="modal fade" id="modal-area-transferencia" role="dialog" aria-hidden="true">
    <div id="bloqueio-area-transferencia" class="loader loader-default"></div>
    <div class="modal-dialog" role="document" style="margin-top: 15%">
        <div class="modal-content modal-content-shadow-naj" style="height: 80%;">

            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Área Transferência</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="card-body-naj page-content container-fluid mt-4">
                <form class="form-horizontal needs-validation" novalidate="" id="form-area-transferencia">
                    <div class="row">
                        <div class="col-12">

                            <div class="form-group row">
                                <label for="area_transferencia" class="col-3 control-label text-right label-center">Texto</label>
                                <div class="col-9">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="area_transferencia" id="area_transferencia" placeholder="Área Transferência..." readonly="">
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
                        <button type="button" class="btn btnLightCustom" onclick="$('#modal-area-transferencia').modal('hide');" title="Fechar">
                            <i class="fas fa-times"></i>
                            Fechar
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>