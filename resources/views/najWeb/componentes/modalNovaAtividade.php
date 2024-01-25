<div class="modal fade" id="modal-nova-atividade" tabindex="-1" role="dialog" aria-hidden="true">
    <div id="bloqueio-nova-atividade" class="loader loader-default" data-half></div>
    <div class="modal-dialog modal-nova-tarefa-naj" role="document">
        <div class="modal-content modal-content-shadow-naj">
            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Nova Atividade</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body-naj naj-scrollable" id="content-outside-atividade" style="overflow-y: auto;">
                <form class="form-horizontal needs-validation" novalidate="" id="form-nova-atividade">
                    <div class="form-group row">                        
                        <input type="text" class="form-control d-none" name="codigo_usuario" id="codigo_usuario" required="" readonly>
                        <label for="nome_usuario_criacao" class="col-2 control-label label-center">Usuário</label>
                        <div class="col-10">
                            <div class="input-group">
                                <input type="text" class="form-control" name="nome_usuario_criacao" id="nome_usuario_criacao" required="" readonly>
                            </div>
                        </div>
                    </div>
                    <div id="row_codigo_tarefa" class="form-group row hide">
                        <label for="codigo_tarefa" class="col-2 control-label label-center">Código</label>
                        <div class="col-4">
                            <div class="input-group">
                                <input type="text" id="codigo_tarefa" name="codigo_tarefa" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="input-group-text ml-1 button-editar-pessoas-tarefa" id="btnGroupAddon" onclick="onClickButtonAlterarTarefa();" style="margin-left: -14px !important;"><i class="mdi mdi-open-in-new icone-alterar-dados-tarefa"></i></div>
                    </div>
                    <div class="form-group row">
                        <label for="codigo_divisao" class="col-2 control-label label-center">Divisão</label>
                        <div class="col-10">
                            <select class="form-control" id="codigo_divisao_tarefa" name="codigo_divisao" required="" tabindex="1"></select>
                        </div>
                    </div>

                    <!-- INICIO INPUT CONSULTA CLIENTE -->
                    <div class="form-group row">
                        <label for="codigo_cliente" class="col-2 control-label label-center">Cliente</label>
                        <div class="col-10">
                            <div class="input-group-prepend">
                                <input type="text" class="form-control mr-1" name="codigo_cliente" id="codigo_cliente" required="" style="width: 15% !important;" onchange="onChangeCodigosPessoasTarefa('codigo_cliente', 'nome_cliente');" tabindex="2">
                                <input type="text" class="form-control" name="nome_cliente" id="nome_cliente" required="" onkeypress="getClienteTarefa(this);" placeholder="Pesquisar pessoas pelo nome">
                                <i class="fas fa-search icon-search-input-naj" id="icon-search-cliente" onclick="buscaDadosCliente(this)"></i>
                                <div class="input-group-text ml-1 button-editar-pessoas-tarefa" id="btnGroupAddon" onclick="onClickButtonCadastroPessoaAtividade('codigo_cliente');"><i class="fas fa-edit"></i></div>
                                <div class="input-group content-select-ajax-cliente row" id="content-select-ajax-naj-nova-tarefa">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- FIM INPUT CONSULTA CLIENTE -->

                    <div class="form-group row">
                        <label for="id_tipo_atividade" class="col-2 control-label label-center" style="padding: 7px 0 0 0 !important;">Tipos de Atividade</label>
                        <div class="col-5">
                            <div class="input-group">
                                <select class="form-control" id="id_tipo_atividade" name="id_tipo_atividade" required="" tabindex="3">
                                </select>
                            </div>                            
                        </div>
                        <!-- <div class="input-group-text ml-1 button-editar-pessoas-tarefa" id="btnGroupAddon" onclick="onClickButtonTipoTarefa();" style="margin-left: -14px !important;"><i class="fas fa-edit"></i></div> -->
                    </div>
                    <div class="form-group row">
                        <label for="descricao" class="col-2 control-label label-center">Descrição</label>
                        <div class="col-10">
                            <div class="input-group">
                                <textarea name="descricao" id="descricao" rows="4" tabindex="4"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="data" class="col-2 control-label label-center">Data</label>
                        <div class="col-4">
                            <div class="input-group">
                                <input type="date" id="data" name="data" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="HORA_INICIO" class="col-2 control-label label-center">Hora</label>
                        <div class="col-4">
                            <div class="input-group">
                                <input type="time" id="HORA_INICIO" name="HORA_INICIO" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="tempo" class="col-2 control-label label-center">Tempo</label>
                        <div class="col-4">
                            <div class="input-group">
                                <input type="time" id="tempo" name="tempo" class="form-control">
                            </div>
                        </div>
                    </div>
                    

                </form>
            </div>
            <div class="card-footer-naj">
                <label for="codigo_divisao" class="col-2 control-label label-center" style="margin-left: 6px;"></label>
                <button type="button" id="gravar-tarefa" class="btn btnLightCustom" title="Gravar" onclick="storeAtividade();">
                    <i class="fas fa-save"></i>
                    Gravar
                </button>
                <button type="button" class="btn btnLightCustom" title="Fechar" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>