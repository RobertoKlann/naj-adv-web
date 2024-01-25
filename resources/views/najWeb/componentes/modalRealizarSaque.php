<div class="modal fade" id="modal-realizar-saque" role="dialog" aria-hidden="true">
    <div id="bloqueio-modal-realizar-saque" class="loader loader-default"></div>
    <div class="modal-dialog modal-extra-large" role="document">
        <div class="modal-content modal-content-shadow-naj">
            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Realizar Saque da Conta Virtual IUGU</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="page-content container-fluid mt-1 mb-1">

                <div class="row justify-content-center">
                    <div class="col-lg-12">
                        <div class="scrollable p-2">
                            <form class="form-horizontal needs-validation m-2" novalidate="" id="form-realizar-saque">
                                <div class="row">
                                    <div class="col-md-12">
                                        
                                        <div class="form-group row">
                                            <label for="saldo_disponivel_para_saque" class="col-sm-12 col-md-4 control-label text-right label-center">Saldo Disponível</label>
                                            <div class="col-sm-12 col-md-4">
                                                <div class="input-group">
                                                    <input type="text" maxlength="32" class="form-control" name="saldo_disponivel_para_saque" id="saldo_disponivel_para_saque" placeholder="Saldo Disponível..." readonly="">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="valor_saque" class="col-sm-12 col-md-4 control-label text-right label-center">Valor Saque</label>
                                            <div class="col-sm-12 col-md-4">
                                                <div class="input-group">
                                                    <!--O valor mínimo para saque na conta virtual é de R$ 5,00 reais.-->
                                                    <input type="text" maxlength="10" class="form-control" name="valor_saque" id="valor_saque" placeholder="Valor Saque..." onkeypress="onlynumber();" required="">&emsp;
                                                    <span>
                                                        <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="O valor mínimo para saque na conta virtual é de R$ 5,00 reais."></i>
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
                <button type="submit" id="gravarContaVirtual" class="btn btnLightCustom" onclick="realizarSaque()" title="Solicitar Saque">
                    <i class="fas fa-save"></i>
                    Solicitar Saque
                </button>
                <button type="reset" class="btn btnLightCustom" onclick="limpaFormularioRealizarSaque();" title="Limpar Formulário">
                    <i class="fas fa-sync-alt"></i>
                    Limpar
                </button>
                <button type="button" class="btn btnLightCustom" onclick="$('#modal-realizar-saque').modal('hide')" title="Fechar">
                    <i class="fas fa-times"></i>
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>