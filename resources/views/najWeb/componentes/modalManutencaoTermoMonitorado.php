<div class="modal fade" id="modal-manutencao-termo-monitorado" role="dialog" aria-hidden="true">
    <div id="bloqueio-modal-manutencao-termo-monitorado" class="loader loader-default"></div>
    <div class="modal-dialog" role="document" style="min-width: 70%; height: 65%; margin-top: 8%">
        <div class="modal-content modal-content-shadow-naj" style="height: 100%;">

            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Manutenção Termo Monitorado</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="card-body-naj page-content container-fluid mt-4" style="height: 100%;">
                <form class="form-horizontal needs-validation" novalidate="" id="form-termo-monitorado" style="height: 100%;"> 
                    <div class="row" style="height: 100%;">
                        <div class="col-md-12" style="height: 100%;">

                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item"> <a id="guideTermo" class="nav-link active" data-toggle="tab" href="#tabTermo" role="tab" aria-selected="true"><span class="hidden-sm-up"><i class="fas fa-gavel"></i></span> <span class="hidden-xs-down">Termo</span></a> </li>
                                <li class="nav-item"> <a id="guideRegras" class="nav-link" data-toggle="tab" href="#tabRegras" role="tab" aria-selected="false"><span class="hidden-sm-up"><i class="fas fa-cogs"></i></span> <span class="hidden-xs-down">Regras</span></a> </li>
                            </ul>

                            <div class="tab-content tabcontent-border p-4"style="height: 85%">

                                <div class="tab-pane active" id="tabTermo" role="tabpanel">

                                    <div class="row">

                                        <div class="col-6">

                                            <div class="form-group row">
                                                <label for="id" class="col-4 control-label text-right label-center">ID</label>
                                                <div class="col-7">
                                                    <div class="input-group">
                                                        <input type="number" min="0" class="form-control" name="id" id="id" placeholder="ID..." readonly="">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row" id="row_id_monitoramento">
                                                <label for="id_monitoramento" class="col-4 control-label text-right label-center">ID Termo Monitorado</label>
                                                <div class="col-8">
                                                    <div class="input-group">
                                                        <input type="number" min="0" class="form-control" name="id_monitoramento" id="id_monitoramento" placeholder="ID Monitoramento..." readonly="">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="termo_pesquisa" class="col-4 control-label text-right label-center">Termo de Pesquisa</label>
                                                <div class="col-7">
                                                    <div class="input-group">
                                                        <input type="text" maxlength="50" class="form-control" name="termo_pesquisa" id="termo_pesquisa" placeholder="Termo de Pesquisa..." required="">
                                                    </div>
                                                </div>
                                                <span>
                                                    <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Nome completo do ADVOGADO."></i>
                                                </span>
                                            </div>

                                            <div class="form-group row">
                                                <label for="numero_oab" class="col-4 control-label text-right label-center">Número OAB</label>
                                                <div class="col-7">
                                                    <div class="input-group">
                                                        <input type="text" maxlength="6" onkeypress="onlynumber()" class="form-control" name="numero_oab" id="numero_oab" placeholder="Número OAB..." required="">
                                                    </div>
                                                </div>
                                                <span>
                                                    <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Número da OAB do Advogado."></i>
                                                </span>
                                            </div>

                                        </div>

                                        <div class="col-6">

                                            <div class="form-group row">
                                                <label for="letra_oab" class="col-4 control-label text-right label-center">Letra OAB</label>
                                                <div class="col-7">
                                                    <div class="input-group">
                                                        <select class="form-control" name="letra_oab" id="letra_oab">
                                                            <option value="">--Selecionar--</option>
                                                            <option value="D">D</option>
                                                            <option value="A">A</option>
                                                            <option value="B">B</option>
                                                            <option value="E">E</option>
                                                            <option value="N">N</option>
                                                            <option value="P">P</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <span>
                                                    <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Letra da OAB do Advogado (OPCIONAL)."></i>
                                                </span>
                                            </div>

                                            <div class="form-group row">
                                                <label for="uf" class="col-4 control-label text-right label-center">UF</label>
                                                <div class="col-7">
                                                    <div class="input-group">
                                                        <select class="form-control" name="uf" id="uf" required="">
                                                            <option value="" disabled="">--Selecionar--</option>
                                                            <option value="AC">AC</option>
                                                            <option value="AL">AL</option>
                                                            <option value="AP">AP</option>
                                                            <option value="AM">AM</option>
                                                            <option value="BA">BA</option>
                                                            <option value="CE">CE</option>
                                                            <option value="DF">DF</option>
                                                            <option value="ES">ES</option>
                                                            <option value="GO">GO</option>
                                                            <option value="MA">MA</option>
                                                            <option value="MT">MT</option>
                                                            <option value="MS">MS</option>
                                                            <option value="MG">MG</option>
                                                            <option value="PA">PA</option>
                                                            <option value="PB">PB</option>
                                                            <option value="PR">PR</option>
                                                            <option value="PE">PE</option>
                                                            <option value="PI">PI</option>
                                                            <option value="RJ">RJ</option>
                                                            <option value="RN">RN</option>
                                                            <option value="RS">RS</option>
                                                            <option value="RO">RO</option>
                                                            <option value="RR">RR</option>
                                                            <option value="SC">SC</option>
                                                            <option value="SP">SP</option>
                                                            <option value="SE">SE</option>
                                                            <option value="TO">TO</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="status" class="col-4 control-label text-right label-center">Situação</label>
                                                <div class="col-7">
                                                    <div class="input-group">
                                                        <select class="form-control" name="status" id="status" required="">
                                                            <option value="" disabled="">--Selecionar--</option>
                                                            <option value="1">Ativo</option>
                                                            <option value="0">Inativo</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                </div>

                                <div class="tab-pane" id="tabRegras" role="tabpanel">

                                    <div class="form-group row">
                                        <label for="variacoes" class="col-4 control-label text-right label-center">Variações</label>
                                        <div class="col-7">
                                            <div class="input-group">
                                                <select class="form-control select2-hidden-accessible" multiple="" name="variacoes" id="variacoes" style="width: 100%;height: 36px;" data-select2-id="variacoes" tabindex="-1" aria-hidden="true">
                                                </select>
                                            </div>
                                            <h6 class="card-subtitle mt-1">Precione Enter para adicionar a palavra.</h6>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="contem" class="col-4 control-label text-right label-center">Termos que <b>devem</b> conter</label>
                                        <div class="col-7">
                                            <div class="input-group">
                                                <select class="form-control select2-hidden-accessible" multiple="" name="contem" id="contem" style="width: 100%;height: 36px;" data-select2-id="contem" tabindex="-1" aria-hidden="true">
                                                </select>
                                            </div>
                                            <h6 class="card-subtitle mt-1">Precione Enter para adicionar a palavra.</h6>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="nao_contem" class="col-4 control-label text-right label-center">Termos que <b>não devem</b> conter</label>
                                        <div class="col-7">
                                            <div class="input-group">
                                                <select class="form-control select2-hidden-accessible" multiple="" name="nao_contem" id="nao_contem" style="width: 100%;height: 36px;" data-select2-id="nao_contem" tabindex="-1" aria-hidden="true">
                                                </select>
                                            </div>
                                            <h6 class="card-subtitle mt-1">Precione Enter para adicionar a palavra.</h6>
                                        </div>
                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>
                </form>
            </div>

            <div class="card-footer-naj">
                <button type="button" id="gravarTermoMonitorado" class="btn btnLightCustom" title="Gravar">
                    <i class="fas fa-save"></i>
                    Gravar
                </button>
                <button type="button" class="btn btnLightCustom" onclick="novoRegistro();" title="Novo">
                    <i class="fas fa-plus"></i>
                    Novo
                </button>
                <button type="button" class="btn btnLightCustom" onclick="$('#modal-manutencao-termo-monitorado').modal('hide')" title="Fechar">
                    <i class="fas fa-times"></i>
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>