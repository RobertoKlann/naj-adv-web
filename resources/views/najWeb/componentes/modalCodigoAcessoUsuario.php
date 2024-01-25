<div class="modal fade" id="modal-codigo-acesso-usuario" tabindex="-1" role="dialog" aria-hidden="true">
    <div id="loading-codigo" class="loader loader-default" data-half></div>
    <div class="modal-dialog modal-extra-large" role="document">
        <div class="modal-content modal-content-shadow-naj">
            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Liberação do Usúario ao App</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="page-content container-fluid containerLiberarAcesso">
                <ul class="nav nav-tabs manage-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active recuperacao">
                            <span class="hidden-sm-up">
                                <h4><i class="ti-lock"></i></h4>
                            </span>
                            <span class="d-none d-md-block">Autenticação</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link cadastro">
                            <span class="hidden-sm-up">
                                <h4><i class="icon-notebook"></i></h4>
                            </span>
                            <span class="d-none d-md-block">Dados cadastrais</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link finalizar">
                            <span class="hidden-sm-up">
                                <h4><i class="ti-check-box"></i></h4>
                            </span>
                            <span class="d-none d-md-block">Relacionamento</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link dispositivos">
                            <span class="hidden-sm-up">
                                <h4><i class="ti-receipt"></i></h4>
                            </span>
                            <span class="d-none d-md-block">Dispositivos</span>
                        </a>
                    </li>
                </ul>
                <!-- Tab panes -->
                <div class="tab-content tab-content-full">
                    <div class="tab-pane active content-full" id="recuperacao" role="tabpanel">
                        <div class="bg-light content-full">
                            <div class="user-box-wrapper p-4 d-flex no-block justify-content-center align-items-center">
                                <div class="user-box" style="margin-top: 5%;">
                                    <div id="loginform">
                                        <div class="logo">
                                            <h5 class="font-medium mb-3">Informe o cpf do usuário</h5>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <form class="form-horizontal mt-3" id="formCodigoAcesso" method="post">
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i id="iconCodigoAcesso" class="fas fa-check"></i></span>
                                                        </div>
                                                        <input type="text" name="codigo_acesso" id="codigo_acesso" class="form-control form-control-lg mascaracpf" aria-describedby="basic-addon1">
                                                    </div>
                                                    <div class="form-group text-center">
                                                        <div class="col-xs-12 pb-3">
                                                            <button class="btn btn-block btn-lg btn-info" type="submit">Validar</button>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="_method" value="POST">
                                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                </form>
                                            </div>
                                            <div class="col-12" id="divResultadoUsuario">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center p-2 content-footer-custom">
                                <div class="ml-auto">
                                    <button class="btn btn-info text-white btn-rounded py-2 px-3" id="proximoAcesso" disabled onclick="onClickAvancar('recuperacao', 'cadastro');">Próximo <i class="ti-arrow-right ml-2"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane content-full" id="cadastro" role="tabpanel">
                        <div class="bg-light content-full">
                            <form class="form-horizontal tab-content-custom ml-2" id="formCadastroPessoa">
                                <div class="row">
                                    <div class="col-sm-12 col-md-6 col-lg-6 p-4" style="padding-right: 0px !important; padding-bottom: 0px !important">
                                        <div class="form-group row">
                                            <label for="nome" class="col-sm-3 control-label label-center">Nome</label>
                                            <div class="col-sm-8">
                                                <div class="input-group">
                                                    <input type="text" name="nome" class="form-control">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="apelido" class="col-sm-3 control-label label-center">Apelido</label>
                                            <div class="col-sm-5">
                                                <div class="input-group">
                                                    <input type="text" name="apelido" class="form-control">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="cpf" class="col-sm-3 control-label label-center">CPF</label>
                                            <div class="col-sm-5">
                                                <div class="input-group">
                                                    <input type="text" name="cpf" class="form-control mascaracpf" onkeypress="return onlynumber();">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="login" class="col-sm-3 control-label label-center">Login</label>
                                            <div class="col-sm-5">
                                                <div class="input-group">
                                                    <input type="text" name="login" id="login" class="form-control">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="password" class="col-sm-3 control-label label-center">Senha</label>
                                            <div class="col-sm-5">
                                                <div class="input-group">
                                                    <input type="password" name="password" class="form-control">
                                                </div>
                                            </div>
                                        </div>                                        
                                    </div>
                                    <div class="col-sm-12 col-md-6 col-lg-6 p-4" style="padding-left: 0px !important; padding-bottom: 0px !important">
                                        <div class="form-group row">
                                            <label for="email_recuperacao" class="col-sm-3 control-label label-center">Email Rec.</label>
                                            <div class="col-sm-5">
                                                <div class="input-group">
                                                    <input type="email" name="email_recuperacao" class="form-control" placeholder="example@gmail.com">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="mobile_recuperacao" class="col-sm-3 control-label label-center">Mobile Rec.</label>
                                            <div class="col-sm-4">
                                                <div class="input-group">
                                                    <input type="text" name="mobile_recuperacao" class="form-control mascaracelular" maxlength="16" onkeypress="return onlynumber();">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="usuario_tipo_id" class="col-sm-3 control-label label-center">Tipo</label>
                                            <div class="col-sm-5">
                                                <div class="input-group">
                                                    <select class="form-control" id="usuario_tipo_id" name="usuario_tipo_id" disabled>
                                                        <option value="1">Administrador</option>
                                                        <option value="2">Usuário</option>
                                                        <option value="3" selected>Cliente</option>
                                                        <option value="4">Parceiro</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="status" class="col-sm-3 control-label label-center">Status</label>
                                            <div class="col-sm-4">
                                                <div class="input-group">
                                                    <select id="statusUser" name="status" class="form-control" disabled>
                                                        <option value="A" selected>Ativo</option>
                                                        <option value="B">Baixado</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="data_inclusao" class="col-sm-3 control-label label-center">Data Inclusão</label>
                                            <div class="col-sm-5">
                                                <div class="input-group">
                                                    <input type="date" name="data_inclusao" class="form-control" value="{{date('d-m-Y')}}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="_method" value="POST">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                </div>
                            </form>
                            <div class="d-flex align-items-center p-2 content-footer-custom">
                                <p class="mt-2" style="font-weight: 500;">Atualize os dados se necessário.</p>
                                <div class="ml-auto">                                
                                    <button class="btn btn-info text-white btn-rounded py-2 px-3" onclick="onClickVoltar('cadastro', 'recuperacao');"><i class="ti-arrow-left mr-2"></i>Voltar</button>
                                    <button class="btn btn-info text-white btn-rounded py-2 px-3" onclick="onClickAvancarUsuario();">Próximo <i class="ti-arrow-right ml-2"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tab-pane content-full" id="finalizar" role="tabpanel">
                        <div class="bg-light content-full">
                            <div class="tab-pane-header-custom-fixed">
                                <div class="form-group row p-3">
                                    <label for="pessoa" class="col-sm-1 control-label label-center">Pesquisar</label>
                                    <div class="col-sm-8">
                                        <div class="input-group">
                                            <input class="form-control" name="pessoa" id="input-nome-pesquisa" style="width: 500px !important;" onkeypress="getPessoaRelacionamento(this);">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row p-0 m-0">
                                    <label for="select" class="col-sm-1 control-label label-center"></label>
                                    <div class="col-sm-8">
                                        <div class="input-group content-select-ajax-relacionamento-codigo-acesso" id="content-select-ajax-naj">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-3 tab-content-custom-finalizar">
                                <div class="table-responsive">
                                    <table class="table text-muted mb-0 no-wrap recent-table font-light tables-dispositivo" id="table-relacionamento">
                                        <thead>
                                            <tr class="text-uppercase">
                                                <th class="border-0"></th>
                                                <th class="border-0">Código</th>
                                                <th class="border-0">Nome</th>
                                                <th class="border-0">CPF/CNPJ</th>
                                                <th class="border-0">Cidade</th>
                                                <th class="border-0">Permissões</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-table-relacionamento">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="d-flex align-items-center p-2 content-footer-custom">
                                <div class="ml-auto">
                                <button class="btn btn-info text-white btn-rounded py-2 px-3" onclick="onClickVoltar('finalizar', 'cadastro');"><i class="ti-arrow-left mr-2"></i> Voltar</button>
                                <button class="btn btn-info text-white btn-rounded py-2 px-3" onclick="onClickAvancar('finalizar', 'dispositivos');">Próximo <i class="ti-arrow-right ml-2"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane content-full" id="dispositivos" role="tabpanel">
                        <div class="bg-light content-full">
                            <div class="p-3 tab-content-custom bt-switch">
                                <div class="table-responsive">
                                    <table class="table text-muted mb-0 no-wrap recent-table font-light tables-dispositivo">
                                        <thead>
                                            <tr class="text-uppercase">
                                                <th class="border-0">ID</th>
                                                <th class="border-0">Modelo</th>
                                                <th class="border-0">Versão SO</th>
                                                <th class="border-0">Ativo</th>
                                            </tr>
                                        </thead>
                                        <tbody id="table-content-dispositivos">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="d-flex align-items-center p-2 content-footer-custom">
                                <div class="ml-auto">
                                <button class="btn btn-info text-white btn-rounded py-2 px-3" onclick="onClickVoltar('dispositivos', 'finalizar');"><i class="ti-arrow-left mr-2"></i> Voltar</button>
                                    <button class="btn btn-info text-white btn-rounded py-2 px-3" onclick="onClickFinalizarCodigoAcesso();">Finalizar <i class="far fa-paper-plane"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>