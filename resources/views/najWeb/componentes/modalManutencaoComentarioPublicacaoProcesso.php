<div class="modal fade" id="modal-manutencao-comentario-publicacao-processo" role="dialog" aria-hidden="true">
    <div id="bloqueio-modal-manutencao-comentario-publicacao-processo" class="loader loader-default"></div>
    <div class="modal-dialog" role="document" style="min-width: 70%; margin-top: 3%;">
        <div class="modal-content modal-content-shadow-naj" style="height: 50%;">

            <div class="modal-header modal-header-naj">
                <p id="tituloAndamentoProcessual" class="titulo-modal-naj">Novo Andamento Processual</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="card-body-naj page-content container-fluid mt-4">
                <form class="form-horizontal needs-validation" novalidate="" id="form-comentario-publicacao-processo">
                    <div class="row">
                        <div class="col-12">

                            <div class="form-group row" id="row_id_andamento">
                                <label for="id_andamento" class="col-2 control-label text-right label-center">Código</label>
                                <div class="col-2">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="ID" id="id_andamento" placeholder="Código..." required="" readonly="">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label for="data_andamento" class="col-2 control-label text-right label-center">Data Andamento</label>
                                <div class="col-2">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="DATA" id="data_andamento" placeholder="Data..." readonly="">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="DESCRICAO_ANDAMENTO" class="col-2 control-label text-right label-center">Publicação</label>
                                <div class="col-10">
                                    <div class="input-group">
                                        <textarea id="DESCRICAO_ANDAMENTO" name="DESCRICAO_ANDAMENTO" rows="5" cols="200" readonly=""></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="TRADUCAO_ANDAMENTO" class="col-2 control-label text-right label-center">Descrição Simplificada</label>
                                <div class="col-10">
                                    <div class="input-group">
                                        <textarea id="TRADUCAO_ANDAMENTO" name="TRADUCAO_ANDAMENTO" rows="5" cols="200" required=""></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="adicionar_atividade" class="col-2 control-label text-right label-center">Adicionar Atividade</label>
                                <div class="col-1">
                                    <div class="input-group">
                                        <div class="bt-switch" id="bt-switch_adicionar_atividade">
                                            <input id="adicionar_atividade" name="adicionar_atividade" type="checkbox" data-size="small"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-9" id="alerta_atividade">
                                    
                                </div>
                            </div>

                        </div>
                    </div>

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                </form>

                <form class="form-horizontal needs-validation" novalidate="" id="form-atividade-publicacao-processo">
                    <div id="quadro_atividades">

                        <div class="form-group row" hidden="">
                            <label for="CODIGO" class="col-2 control-label text-right label-center">ID Atividade</label>
                            <div class="col-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="CODIGO" id="CODIGO" placeholder="ID Atividade..." required="" readonly="">
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="DATA" class="col-2 control-label text-right label-center">Data/Hora</label>
                            <div class="col-4">
                                <div class="input-group">
                                    <input type="datetime-local" id="DATA" name="DATA" class="form-control" placeholder="Data/Hora..." required="">
                                </div>
                            </div>
                            <label for="TEMPO" class="control-label text-right label-center">Tempo Total</label>
                            <div class="col-2">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="TEMPO" id="TEMPO" placeholder="00:00:00" required="">
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="ID_TIPO_ATIVIDADE" class="col-2 control-label text-right label-center">Tipos de Atividades</label>
                            <div class="col-4">
                                <div class="input-group">
                                    <select class="form-control" name="ID_TIPO_ATIVIDADE" id="ID_TIPO_ATIVIDADE" required="">
                                        <option value="" selected="" disabled="">--Selecionar--</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-4" style="padding-left: 0px">
                                <div class="input-group">
                                    <input type="checkbox" value="5" id="ENVIAR" name="ENVIAR" style="margin-top: 10px">&emsp;
                                    <label for="ENVIAR" class="control-label text-right label-center">Disponível ao Cliente</label>
                                </div>
                            </div>
                        </div>

                    </div>

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                </form>
            </div>

            <div class="card-footer-naj">
                <div class="row">
                    <div class="col-2">
                        <!--Div somente para manter o layout do modal-->
                    </div>
                    <div class="col-10">
                        <button type="button" id="gravarComentarioPublicacaoProcesso" class="btn btnLightCustom" title="Gravar">
                            <i class="fas fa-save"></i>
                            Gravar
                        </button>
                        <button type="button" class="btn btnLightCustom" onclick="$('#modal-manutencao-comentario-publicacao-processo').modal('hide');" title="Fechar">
                            <i class="fas fa-times"></i>
                            Fechar
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>