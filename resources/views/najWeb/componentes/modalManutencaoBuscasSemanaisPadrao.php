<div class="modal fade" id="modal-manutencao-buscas-padrao" role="dialog" aria-hidden="true">
    <div id="bloqueio-modal-manutencao-buscas-padrao" class="loader loader-default"></div>
    <div class="modal-dialog" role="document" style="margin-top: 12%;">
        <div class="modal-content modal-content-shadow-naj" style="height: 70%;">

            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Buscas Padrão</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="card-body-naj page-content container-fluid mt-4">
                <form class="form-horizontal needs-validation" novalidate="" id="form-buscas-padrao">

                    <!-- FORM BUSCA MENSAL PADRÃO-->
                    <div class="form-group row">
                        <label for="buscas_mensal_padrao" class="col-3 control-label text-right label-center">Mensal</label>
                        <div class="col-9">
                            <div class="input-group">
                                <div class="bt-switch" id="">
                                    <input id="buscas_mensal_padrao" name="buscas_mensal_padrao" type="checkbox" data-size="small"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="frequencia_mes" class="col-3 control-label text-right label-center"></label>
                        <div class="col-8">
                            <div class="row border pt-2 ml-0 input-group">
                                <div class="col-6">
                                    <input type="checkbox" value="01" id="dia1" name="frequencia_mes"> <label for="dia1">Dia 1</label><br>
                                </div>
                                <div class="col-6">
                                    <input type="checkbox" value="15" id="dia15" name="frequencia_mes"> <label for="dia15">Dia 15</label><br>
                                    <span>
                                        <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Dias do mês que serão realizadas buscas por movimentações nos tribunais."></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- FORM BUSCA SEMANAIS PADRÃO-->
                    <div class="form-group row">
                        <label for="buscas_semanal_padrao" class="col-3 control-label text-right label-center">Semanal</label>
                        <div class="col-9">
                            <div class="input-group">
                                <div class="bt-switch" id="">
                                    <input id="buscas_semanal_padrao" name="buscas_semanal_padrao" type="checkbox" data-size="small"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="frequencia" class="col-3 control-label text-right label-center"></label>
                        <div class="col-8">
                            <div class="row border pt-2 ml-0 input-group">
                                <div class="col-6">
                                    <input type="checkbox" value="1" id="seg" name="frequencia"> <label for="seg">Segunda</label><br>
                                    <input type="checkbox" value="2" id="ter" name="frequencia"> <label for="ter">Terça</label><br>
                                    <input type="checkbox" value="3" id="qua" name="frequencia"> <label for="qua">Quarta</label>
                                </div>
                                <div class="col-6">
                                    <input type="checkbox" value="4" id="qui" name="frequencia"> <label for="qui">Quinta</label><br>
                                    <input type="checkbox" value="5" id="sex" name="frequencia"> <label for="sex">Sexta</label><br>
                                    <span>
                                        <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Dias da semana que serão realizadas buscas por movimentações nos tribunais."></i>
                                    </span>
                                </div>
                            </div>
                        </div>
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
                        <button type="button" id="gravarBuscasPadrao" class="btn btnLightCustom" title="Confirmar">
                            <i class="fas fa-save"></i>
                            Confirmar
                        </button>
                        <button type="button" class="btn btnLightCustom" onclick="$('#modal-manutencao-buscas-padrao').modal('hide');" title="Fechar">
                            <i class="fas fa-times"></i>
                            Fechar
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>