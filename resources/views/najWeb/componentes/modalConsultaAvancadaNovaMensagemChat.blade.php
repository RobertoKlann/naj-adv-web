<div class="modal fade" id="modal-consulta-avancada-nova-mensagem-chat" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-extra-large" role="document" style="min-width: 70% !important;">
        <div class="modal-content modal-content-shadow-naj">
            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Consulta Avançada</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body-naj p-0" style="background: whitesmoke;">
                <div id="loading-consulta-avancada" class="loader loader-default" data-half></div>
                <div class="row pl-4 pr-4">
                    <div class="col-lg-12 col-sm-12 page-content container-fluid note-has-grid p-2 mb-2">
                        <ul class="nav nav-pills p-2 bg-white rounded-pill align-items-center ul-tab-financeiro">
                            <li class="nav-item">
                                <a href="#pessoas_aniversariantes" id="link-pessoas-aniversariantes" class="nav-link active rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" onclick="onClickTabPessoasAniversariantes()">
                                <i class="fas fa-gift mr-1"></i>
                                    <span class="">Pessoas/Aniversariantes</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#pessoas_grupos" id="link-pessoas-grupos" class="nav-link active rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" onclick="onClickTabPessoasGrupos()">
                                <i class="fas fa-user mr-1"></i>
                                    <span class="">Pessoas/Grupos</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#areajuridica" id="link-areajuridica" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" onclick="onClickTabAreaJuridica()">
                                <i class="fas fa-balance-scale mr-1"></i>
                                    <span class="">Área Jurídica</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#classe" id="link-classe" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" onclick="onClickTabClasse()">
                                    <i class="fas fa-balance-scale mr-1"></i>
                                    <span class="">Classe</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#comarca" id="link-comarca" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" onclick="onClickTabComarca()">
                                    <i class="fas fa-balance-scale mr-1"></i>
                                    <span class="">Comarca</span>
                                </a>
                            </li>
                        </ul>
                        
                    </div>

                    <div class="col-lg-8 col-sm-12" style="display: flex; align-items: center; justify-content: center;">
                        <div class="tab-content bg-transparent pt-0 pb-0 h-100">
                            <div id="note-full-container" class="naj-scrollable note-has-grid row" style="height: 55vh; overflow-y: auto; overflow-x: hidden;">
                                <div class="tab-pane content-full w-100" id="pessoas_aniversariantes" role="tabpanel">
                                    <div class="content-full single-note-item w-100 pt-0 p-4" style="padding-top: 0 !important;">
                                        <div class="row" id="content-pessoas-aniversariantes">
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane content-full w-100" id="pessoas_grupos" role="tabpanel">
                                    <div class="content-full single-note-item w-100 pt-0 p-4" style="padding-top: 0 !important;">
                                        <div class="row" id="content-pessoas-grupos">
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane content-full w-100" id="areajuridica" role="tabpanel">
                                    <div class="content-full single-note-item w-100 pt-0 p-4" style="padding-top: 0 !important;">
                                        <div class="row" id="content-areajuridica">
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane content-full" id="classe" role="tabpanel" style="width: 100%; height: 100%;">
                                    <div class="content-full single-note-item" style="height: 100%;">
                                        <div class="row" id="content-classe">
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane content-full" id="comarca" role="tabpanel" style="width: 100%; height: 100%;">
                                    <div class="content-full single-note-item" style="height: 100%;">
                                        <div class="row" id="content-comarca">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-12" style="display: flex; align-items: center; justify-content: center;">
                        <div class="row">
                            <div class="col-lg-12 col-md-12">
                                <div class="card border-left border-success cursor-pointer tooltip-naj" data-toggle="tooltip" data-placement="right" title="Pessoas com usuários habilitado com login e senha e já acessaram o aplicativo em um dispositivo.">
                                    <div class="card-body">
                                        <div class="d-flex no-block align-items-center" onclick="onClickFilterUserConsultaAvancadaChat()">
                                            <div>
                                                <span class="text-success display-6"><i class="ti-user"></i></span>
                                            </div>
                                            <div class="ml-auto">
                                                <h2 id="total-habilitados-app">0</h2>
                                                <h6 class="text-success">Habilitados WEB + APP</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12">
                                <div class="card border-left border-info cursor-pointer tooltip-naj" data-toggle="tooltip" data-placement="right" title="Pessoas com usuários habilitados com login e senha mas que não tem um dispositivo vinculado.">
                                    <div class="card-body">
                                        <div class="d-flex no-block align-items-center" onclick="onClickFilterUserConsultaAvancadaChat()">
                                            <div>
                                                <span class="text-info display-6"><i class="ti-user"></i></span>
                                            </div>
                                            <div class="ml-auto">
                                                <h2 id="total-habilitados-sem-device-app">0</h2>
                                                <h6 class="text-info">Habilitados WEB</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12">
                                <div class="card border-left border-warning tooltip-naj" data-toggle="tooltip" data-placement="right" title="Pessoas sem um usuário habilitado com login e senha!.">
                                    <div class="card-body">
                                        <div class="d-flex no-block align-items-center">
                                            <div>
                                                <span class="text-warning display-6"><i class="ti-user"></i></span>
                                            </div>
                                            <div class="ml-auto">
                                                <h2 id="total-nao-habilitados-app">0</h2>
                                                <h6 class="text-warning">Clientes NÃO HABILITADOS</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>