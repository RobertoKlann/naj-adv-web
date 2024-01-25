<div class="modal fade" id="modal-manutencao-processo-classe" role="dialog" aria-hidden="true">
    <div id="bloqueio-modal-manutencao-processo-classe" class="loader loader-default"></div>
    <div class="modal-dialog" role="document" style="margin-top: 15%">
        <div class="modal-content modal-content-shadow-naj" style="height: 70%;">

            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Manutenção Classe da Ação</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="card-body-naj page-content container-fluid mt-4">
                <form class="form-horizontal needs-validation" novalidate="" id="form-processo-classe">
                    <div class="row">
                        <div class="col-12">

                            <div class="form-group row">
                                <label for="codigo_processo_classe" class="col-2 control-label text-right label-center">Código</label>
                                <div class="col-4">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="CODIGO" id="codigo_processo_classe" placeholder="Código..." required="" readonly="">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="nome_processo_classe" class="col-2 control-label text-right label-center">Classe</label>
                                <div class="col-10">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="CLASSE" id="nome_processo_classe" placeholder="Classe..." required="">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="tipo_processo_classe" class="col-2 control-label text-right label-center">Tipo</label>
                                <div class="col-5">
                                    <div class="input-group">
                                        <select class="form-control" name="TIPO" id="tipo_processo_classe" required="">
                                            <option value="" selected="" disabled="">--Selecionar--</option>
                                            <option value="J">Judicial</option>
                                            <option value="A">Amigável</option>
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
                        <button type="button" id="gravarProcessoClasse" class="btn btnLightCustom" title="Gravar">
                            <i class="fas fa-save"></i>
                            Gravar
                        </button>
                        <button type="button" class="btn btnLightCustom" onclick="novoRegistroProcessoClasse();" title="Novo">
                            <i class="fas fa-plus"></i>
                            Novo
                        </button>
                        <button type="button" class="btn btnLightCustom" onclick="$('#modal-manutencao-processo-classe').modal('hide');" title="Fechar">
                            <i class="fas fa-times"></i>
                            Fechar
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>