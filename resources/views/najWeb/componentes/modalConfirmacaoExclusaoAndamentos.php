<div class="modal fade" id="modal-confirmacao-exclusao-andamentos" role="dialog" aria-hidden="true">
    <div id="bloqueio-modal-confirmacao-exclusao-andamentos" class="loader loader-default"></div>
    <div class="modal-dialog" role="document" style="margin-top: 12%;">
        <div class="modal-content modal-content-shadow-naj" style="height: 50%;">

            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Excluir Andamentos do Monitoramento</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="card-body-naj page-content container-fluid mt-4">
                <form class="form-horizontal needs-validation" novalidate="" id="form-confirmacao-exclusao-andamentos">
                    
                    <div>
                         Tem certeza que deseja excluir os andamentos deste monitoramento?
                    </div>
                    
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                </form>
            </div>

            <div class="card-footer-naj">
                <div class="row">
                    <div class="col-3">
                        <!--Div somente para manter o layout do modal-->
                    </div>
                    <div class="col-9">
                        <button type="button" id="confirmarExclusaoAndamentos" class="btn btnLightCustom" title="Confirmar" onclick="ExcluirAndamentosCMP()">
                            <i class="fas fa-save"></i>
                            Confirmar
                        </button>
                        <button type="button" class="btn btnLightCustom" onclick="$('#modal-confirmacao-exclusao-andamentos').modal('hide');" title="Fechar">
                            <i class="fas fa-times"></i>
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>