<div class="modal fade" id="modal-manutencao-monitoramento-processo-tribunal" role="dialog" aria-hidden="true">
    <div id="bloqueio-modal-manutencao-monitoramento-processo-tribunal" class="loader loader-default"></div>
    <div class="modal-dialog" role="document" style="margin-top: 12%;">
        <div class="modal-content modal-content-shadow-naj" style="height: 70%;">

            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Monitoramento Processo Tribunal</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="card-body-naj page-content container-fluid mt-4">
                <form class="form-horizontal needs-validation" novalidate="" id="form-processo-tribunal">

                    <div id="linhaIdMTP" class="form-group row" hidden="">
                        <label for="id_processo_tribunal" class="col-3 control-label text-right label-center">Código</label>
                        <div class="col-8">
                            <div class="input-group">
                                <input type="text" class="form-control" name="id" id="id_processo_tribunal" placeholder="Código..." required="">
                            </div>
                        </div>
                    </div>
                    
                    <div id="linhaCodigoProcessoMTP" class="form-group row">
                        <label for="codigo_processo_mpt" class="col-3 control-label text-right label-center">Código Processo</label>
                        <div class="col-8">
                            <div class="input-group">
                                <input type="text" class="form-control" name="codigo_processo" id="codigo_processo_mpt" placeholder="Código Processo..." required="">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="numero_cnj" class="col-3 control-label text-right label-center">CNJ</label>
                        <div class="col-8">
                            <div class="input-group">
                                <input type="text" class="form-control" name="numero_cnj" id="numero_cnj" placeholder="CNJ..." readonly="" required="">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="abrangencia" class="col-3 control-label text-right label-center">Abragência</label>
                        <div class="col-8">
                            <div class="input-group">
                                <select class="form-control" name="abrangencia" id="abrangencia">
                                    <option value="0">1 e 2 Instância</option>
                                    <option value="1">STJ</option>
                                    <option value="2">STF</option>
                                    <option value="3">TST</option>
                                    <option value="4">TSE</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="status_monitora_processo_tribunal" class="col-3 control-label text-right label-center">Situação</label>
                        <div class="col-8">
                            <div class="input-group">
                                <select class="form-control" name="status" id="status_monitora_processo_tribunal" required="">
                                    <option value="" selected="" disabled="">-- Selecionar --</option>
                                    <option value="A">Monitoramento Ativo</option>
                                    <option value="B">Monitoramento Baixado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="frequencia_mes" class="col-3 control-label text-right label-center">Busca Mensais</label>
                        <div class="col-8">
                            <div class="row border pt-2 ml-0 input-group" id="quadro_dias_mes">
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
                    
                    <div class="form-group row">
                        <label for="frequencia" class="col-3 control-label text-right label-center">Busca Semanais</label>
                        <div class="col-8">
                            <div class="row border pt-2 ml-0 input-group" id="quadro_dias_semana">
                                <div class="col-6">
                                    <input type="checkbox" value="1" id="segunda" name="frequencia"> <label for="segunda">Segunda</label><br>
                                    <input type="checkbox" value="2" id="terca" name="frequencia"> <label for="terca">Terça</label><br>
                                    <input type="checkbox" value="3" id="quarta" name="frequencia"> <label for="quarta">Quarta</label>
                                </div>
                                <div class="col-6">
                                    <input type="checkbox" value="4" id="quinta" name="frequencia"> <label for="quinta">Quinta</label><br>
                                    <input type="checkbox" value="5" id="sexta" name="frequencia"> <label for="sexta">Sexta</label><br>
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
                        <button type="button" id="gravarMonitoramentoProcessoTribunal" class="btn btnLightCustom" title="Confirmar">
                            <i class="fas fa-save"></i>
                            Confirmar
                        </button>
                        <button type="button" class="btn btnLightCustom" onclick="$('#modal-manutencao-monitoramento-processo-tribunal').modal('hide');" title="Fechar">
                            <i class="fas fa-times"></i>
                            Fechar
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>