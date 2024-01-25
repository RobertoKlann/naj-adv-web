<div class="modal fade" id="modal-manutencao-quota-de-buscas" role="dialog" aria-hidden="true">
    <div id="bloqueio-modal-manutencao-quota-de-buscas" class="loader loader-default"></div>
    <div class="modal-dialog" role="document" style="margin-top: 15%; min-width: 50% !important;">
        <div class="modal-content modal-content-shadow-naj" style="height: 40%;">

            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Quota de Buscas</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="card-body-naj page-content container-fluid mt-4">
                <form class="form-horizontal needs-validation" novalidate="" id="form-quota-de-buscas">
                    <div class="row">
                        <div class="col-12">

                            <div class="form-group row">
                                <label for="quota-de-buscas" class="col-4 control-label text-right label-center">Quota</label>
                                <div class="col-4 pr-0">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="quota-de-buscas" id="quota-de-buscas" placeholder="Quotas..." required="">&emsp;
                                    </div>
                                </div>
                                <div class="col-1 pl-0">
                                    <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="right" title="" data-original-title="Quota de monitoramentos ativos permitidos"></i>
                                </div>
                            </div>

                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        </div>
                    </div>
                </form>
            </div>

            <div class="card-footer-naj">
                <div class="row">
                    <div class="col-4">
                        <!--Div somente para manter o layout do modal-->
                    </div>
                    <div class="col-8">
                        <button type="button" id="gravarQuotaDeBuscas" class="btn btnLightCustom" title="Gravar">
                            <i class="fas fa-save"></i>
                            Gravar
                        </button>
                        <button type="button" class="btn btnLightCustom" onclick="$('#modal-manutencao-quota-de-buscas').modal('hide');" title="Fechar">
                            <i class="fas fa-times"></i>
                            Fechar
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>