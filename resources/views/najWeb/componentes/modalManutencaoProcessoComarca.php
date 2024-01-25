<div class="modal fade" id="modal-manutencao-processo-comarca" role="dialog" aria-hidden="true">
    <div id="bloqueio-modal-manutencao-processo-comarca" class="loader loader-default"></div>
    <div class="modal-dialog" role="document" style="margin-top: 15%">
        <div class="modal-content modal-content-shadow-naj" style="height: 65%;">

            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Manutenção Comarca</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="card-body-naj page-content container-fluid mt-4">
                <form class="form-horizontal needs-validation" novalidate="" id="form-processo-comarca">
                    <div class="row">
                        <div class="col-12">

                            <div class="form-group row">
                                <label for="codigo_processo_comarca" class="col-2 control-label text-right label-center">Código</label>
                                <div class="col-4">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="CODIGO" id="codigo_processo_comarca" placeholder="Código..." required="" readonly="">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="nome_processo_comarca" class="col-2 control-label text-right label-center">Comarca</label>
                                <div class="col-10">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="COMARCA" id="nome_processo_comarca" placeholder="Comarca..." required="">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="uf_processo_comarca" class="col-2 control-label text-right label-center">Estado</label>
                                <div class="col-5">
                                    <div class="input-group">
                                        <select class="form-control" name="UF" id="uf_processo_comarca" required="">
                                            <option value="" selected="" disabled="">--Selecionar--</option>
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
                        <button type="button" id="gravarProcessoComarca" class="btn btnLightCustom" title="Gravar">
                            <i class="fas fa-save"></i>
                            Gravar
                        </button>
                        <button type="button" class="btn btnLightCustom" onclick="novoRegistroProcessoComarca();" title="Novo">
                            <i class="fas fa-plus"></i>
                            Novo
                        </button>
                        <button type="button" class="btn btnLightCustom" onclick="$('#modal-manutencao-processo-comarca').modal('hide');" title="Fechar">
                            <i class="fas fa-times"></i>
                            Fechar
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>