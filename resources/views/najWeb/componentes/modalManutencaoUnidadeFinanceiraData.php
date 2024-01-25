<div class="modal fade" id="modal-manutencao-unidade-financeira-data" role="dialog" aria-hidden="true">
    <div id="bloqueio-modal-manutencao-unidade-financeira-data" class="loader loader-default"></div>
    <div class="modal-dialog modal-extra-large" role="document">
        <div class="modal-content modal-content-shadow-naj">
            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Altera Data Registro Unidade Financeira</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="page-content container-fluid mt-1 mb-1">

                <div class="row justify-content-center">
                    <div class="col-lg-12">
                        <div class="scrollable p-2">
                            <form class="form-horizontal needs-validation m-2" novalidate="" id="form-manutencao-unidade-financeira-data">
                                <div class="row">
                                    <div class="col-md-12">
                                        
                                        <div class="form-group row">
                                            <label for="valor_saque" class="col-sm-12 col-md-4 control-label text-right label-center">Data</label>
                                            <div class="col-sm-12 col-md-4">
                                                <div class="input-group"><input type="text" maxlength="10" class="form-control" name="unidade-financeira-data" id="unidade-financeira-data" placeholder="Data..." required="">&emsp;
                                                    <span>
                                                        <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Data do registro da Unidade Financeira."></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer-naj">
                <button type="submit" id="gravarContaVirtual" class="btn btnLightCustom" onclick="alteraUnidadeFinanceiraData()" title="Solicitar Saque">
                    <i class="fas fa-save"></i>
                    Alterar
                </button>
                <button type="button" class="btn btnLightCustom" onclick="$('#modal-manutencao-unidade-financeira-data').modal('hide')" title="Fechar">
                    <i class="fas fa-times"></i>
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>