<div class="modal fade" id="modal-nova-tarefa-processo" tabindex="-1" role="dialog" aria-hidden="true">
    <div id="bloqueio-nova-tarefa-processo" class="loader loader-default" data-half></div>
    <div class="modal-dialog modal-nova-tarefa-naj" role="document">
        <div class="modal-content modal-content-shadow-naj">
            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Nova Tarefa</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body-naj naj-scrollable" id="content-outside-tarefa" style="overflow-y: auto;">
                <form class="form-horizontal needs-validation" novalidate="" id="form-nova-tarefa-processo">
                    <div class="form-group row">                        
                        <input type="text" class="form-control d-none" name="codigo_usuario_criacao" id="codigo_usuario_criacao" required="" readonly>
                        <label for="nome_usuario_criacao" class="col-2 control-label label-center">Usuário</label>
                        <div class="col-10">
                            <div class="input-group">
                                <input type="text" class="form-control" name="nome_usuario_criacao" id="nome_usuario_criacao" required="" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="data_hora_criacao" class="col-2 control-label label-center">Data/Hora</label>
                        <div class="col-4">
                            <div class="input-group">
                                <input type="datetime-local" id="data_hora_criacao" name="data_hora_criacao" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                    <div id="row_codigo_tarefa" class="form-group row">
                        <label for="codigo_tarefa" class="col-2 control-label label-center">Código</label>
                        <div class="col-4">
                            <div class="input-group">
                                <input type="text" id="codigo_tarefa" name="codigo_tarefa" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="input-group-text ml-1 button-editar-pessoas-tarefa" id="btnGroupAddon" onclick="onClickButtonAlterarTarefa();" style="margin-left: -14px !important;"><i class="font-18 mdi mdi-open-in-new cursor-pointer text-dark mt-1"></i></div>
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
                                <div class="input-group-text ml-1 button-editar-pessoas-tarefa" id="btnGroupAddon" onclick="onClickButtonCadastroPessoa('codigo_cliente');"><i class="fas fa-edit"></i></div>
                                <div class="input-group content-select-ajax-cliente row" id="content-select-ajax-naj-nova-tarefa">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- FIM INPUT CONSULTA CLIENTE -->

                    <div class="form-group row">
                        <label for="id_tipo" class="col-2 control-label label-center" style="padding: 7px 0 0 0 !important;">Tipos de Tarefa</label>
                        <div class="col-5">
                            <div class="input-group">
                                <select class="form-control" id="id_tipo" name="id_tipo" required="" tabindex="3">
                                </select>
                            </div>                            
                        </div>
                        <div class="input-group-text ml-1 button-editar-pessoas-tarefa" id="btnGroupAddon" onclick="onClickButtonTipoTarefa();" style="margin-left: -14px !important;"><i class="fas fa-edit"></i></div>
                    </div>
                    <div class="form-group row">
                        <label for="descricao" class="col-2 control-label label-center">Descrição</label>
                        <div class="col-10">
                            <div class="input-group">
                                <textarea name="descricao" id="descricao" rows="4" tabindex="4"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- INICIO INPUT CONSULTA SUPERVISOR -->
                    <div class="form-group row">
                        <label for="codigo_supervisor" class="col-2 control-label label-center">Supervisor</label>
                        <div class="col-10">
                            <div class="input-group-prepend">
                                <input type="text" class="form-control mr-1" name="codigo_supervisor" id="codigo_supervisor" required="" style="width: 15% !important;" onchange="onChangeCodigosPessoasTarefa('codigo_supervisor', 'nome_supervisor');" tabindex="5">
                                <input type="text" class="form-control" name="nome_supervisor" id="nome_supervisor" required="" onkeypress="getSupervisorTarefa(this);" placeholder="Pesquisar pessoas pelo nome">
                                <i class="fas fa-search icon-search-input-naj" id="icon-search-supervisor" onclick="buscaDadosSupervisor(this)"></i>
                                <div class="input-group-text ml-1 button-editar-pessoas-tarefa" id="btnGroupAddon" onclick="onClickButtonCadastroPessoa('codigo_supervisor');"><i class="fas fa-edit"></i></div>
                                <div class="input-group content-select-ajax-cliente" id="content-select-ajax-naj-nova-tarefa-supervisor"></div>
                            </div>
                        </div>
                    </div>
                    <!-- FIM INPUT CONSULTA SUPERVISOR -->

                    <!-- INICIO INPUT CONSULTA RESPONSAVEL -->
                    <div class="form-group row">
                        <label for="codigo_responsavel" class="col-2 control-label label-center">Responsável</label>
                        <div class="col-10">
                            <div class="input-group-prepend">
                                <input type="text" class="form-control mr-1" name="codigo_responsavel" id="codigo_responsavel" required="" style="width: 15% !important;" onchange="onChangeCodigosPessoasTarefa('codigo_responsavel', 'nome_responsavel');" tabindex="6">
                                <input type="text" class="form-control" name="nome_responsavel" id="nome_responsavel" required="" onkeypress="getResponsavelTarefa(this);" placeholder="Pesquisar pessoas pelo nome">
                                <i class="fas fa-search icon-search-input-naj" id="icon-search-responsavel" onclick="buscaDadosResponsavel(this)"></i>
                                <div class="input-group-text ml-1 button-editar-pessoas-tarefa" id="btnGroupAddon" onclick="onClickButtonCadastroPessoa('codigo_responsavel');"><i class="fas fa-edit"></i></div>
                                <div class="input-group content-select-ajax-cliente" id="content-select-ajax-naj-nova-tarefa-responsavel"></div>
                            </div>
                        </div>
                    </div>
                    <!-- FIM INPUT CONSULTA RESPONSAVEL -->

                    <div class="form-group row">
                        <label for="id_situacao" class="col-2 control-label label-center">Situação</label>
                        <div class="col-5">
                            <div class="input-group">
                                <input type="text" class="form-control" name="id_situacao" id="id_situacao" required="" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="id_prioridade" class="col-2 control-label label-center" style="padding: 7px 0 0 0 !important;">Prioridade da Tarefa</label>
                        <div class="col-5">
                            <div class="input-group">
                                <select class="form-control" id="id_prioridade" name="id_prioridade" required="" tabindex="7">
                                </select>
                            </div>
                        </div>
                        <div class="input-group-text ml-1 button-editar-pessoas-tarefa" id="btnGroupAddon" onclick="onClickButtonPrioridadeTarefa();" style="margin-left: -14px !important;"><i class="fas fa-edit"></i></div>
                    </div>
                    <div class="form-group row">
                        <label for="data_prazo_interno" class="col-2 pl-0 control-label label-center">Prazo Interno</label>
                        <div class="col-5">
                            <div class="input-group">
                                <input type="datetime-local" id="data_prazo_interno" name="data_prazo_interno" class="form-control" tabindex="8">
                            </div>
                        </div>
                        <div class="input-group-text ml-1 button-editar-pessoas-tarefa" id="btnGroupAddon" onclick="onClickButtonLimparPrazoInterno();" style="margin-left: -14px !important;"><i class="fas fa-times"></i></div>
                    </div>
                    <div class="form-group row">
                        <label for="data_prazo_fatal" class="col-2 control-label label-center">Prazo Fatal</label>
                        <div class="col-5">
                            <div class="input-group">
                                <input type="datetime-local" id="data_prazo_fatal" name="data_prazo_fatal" class="form-control" tabindex="9">
                            </div>
                        </div>
                        <div class="input-group-text ml-1 button-editar-pessoas-tarefa" id="btnGroupAddon" onclick="onClickButtonLimparPrazoFatal();" style="margin-left: -14px !important;"><i class="fas fa-times"></i></div>
                    </div>
                </form>
            </div>
            <div class="card-footer-naj">
                <label for="codigo_divisao" class="col-2 control-label label-center" style="margin-left: 6px;"></label>
                <button type="button" id="gravar-tarefa" class="btn btnLightCustom" title="Gravar" onclick="storeTarefa();">
                    <i class="fas fa-save"></i>
                    Gravar
                </button>
                <button type="button" id="nova-tarefa" class="btn btnLightCustom" title="Nova Tarefa" onclick="carregaModalNovaTarefaProcesso();">
                    <i class="fas fa-plus"></i>
                    Nova
                </button>
                <button type="button" class="btn btnLightCustom" title="Fechar" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>