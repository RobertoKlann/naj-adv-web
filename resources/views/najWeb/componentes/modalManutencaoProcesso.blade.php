<div class="modal fade" id="modal-manutencao-processo" role="dialog" aria-hidden="true">
    <div id="bloqueio-modal-manutencao-processo" class="loader loader-default"></div>
    <div class="modal-dialog modal-extra-large" id="loader-modal-manutencao-processo" role="document" style="height: 90%;">
        <div class="modal-content modal-content-shadow-naj" style="height: 100%;">

            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Manutenção Processo</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="card-body-naj page-content container-fluid naj-scrollable " id="content-modal-processo" style="height: 100%;">
                <div class="row mt-4" style="height: 100%;"> 
                    <div class="col-md-12" style="height: 100%;">
                        
                        <form class="form-horizontal needs-validation" novalidate="" id="form-processo">
                        
                            <div class="form-group row">
                                <label for="codigo_processo" class="col-2 control-label text-right label-center">Código</label>
                                <div class="col-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="CODIGO" id="codigo_processo" placeholder="Código..." required="" readonly="">&emsp;
                                        <i id="externoProcessoModalProcesso" class="font-18 mdi mdi-open-in-new cursor-pointer text-dark mt-1" data-toggle="tooltip" data-placement="top" title="Consultar o processo" data-toggle="tooltip" onclick="onClickFichaProcesso()"></i>
                                    </div>
                                </div>
                                <label for="data_cadastro" class="col-2 control-label text-right label-center">Data Cadastro</label>
                                <div class="col-2">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="DATA_CADASTRO" id="data_cadastro" placeholder="Data Cadastro..." required="" readonly="">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- INICIO INPUT CONSULTA CLIENTE -->
<!--                            <div class="form-group row mb-0">
                                <label for="codigo_cliente" class="col-2 control-label text-right label-center">Cliente</label>
                                <div class="col-8 pr-0">
                                    <div class="input-group-prepend">
                                        <input type="text" onkeypress="onlynumber()" class="col-2 form-control mr-1" name="CODIGO_CLIENTE" id="codigo_cliente" required="" onchange="getShow('codigo_cliente', 'nome_cliente', 'pessoas');">
                                        <input type="text" class="form-control" name="nome_cliente" id="nome_cliente" required="" filled_field="content-select-ajax-naj-cliente" onkeypress="getPessoas(this);">
                                        <i class="fas fa-search icon-search-input-naj cursor-pointer" onclick="getPessoas($('#nome_cliente')[0], false)"></i>
                                        <div class="input-group-text ml-1 button-editar-pessoa" onclick="carregaModalManutencaoProcessoPessoaEdicao('codigo_cliente');"><i class="fas fa-edit"></i></div>
                                    </div>
                                </div>
                                <div class="col-2 pl-0">
                                    <div class="input-group">
                                        <select class="form-control" name="QUALIFICA_CLIENTE" id="qualifica_cliente_cliente" required="">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <label for="codigo_cliente" class="col-2 control-label text-right label-center"></label>
                                <div class="col-8 pr-0">
                                    <div class="input-group-prepend" id="content-select-ajax-naj-cliente" style="">
                                    </div>
                                </div>
                            </div>-->
                            <!-- FIM INPUT CONSULTA CLIENTE -->
                            
                            <!-- INICIO INPUT CONSULTA CLIENTE -->
                            <div class="form-group row mb-0">
                                <label for="codigo_cliente" class="col-2 control-label text-right label-center">Cliente</label>
                                <div class="col-8 pr-0">
                                    <div class="input-group-prepend">
                                        <input type="text" onkeypress="onlynumber()" class="form-control mr-1" name="CODIGO_CLIENTE" id="codigo_cliente_processo" required="" tabindex="1" style="width: 15% !important;" onchange="getShow('codigo_cliente_processo', 'nome_cliente_processo', 'pessoas');">
                                        <input type="text" class="form-control" name="nome_cliente" id="nome_cliente_processo" required="" filled_field="content-select-ajax-naj-cliente" onkeypress="getPessoas(this);">
                                        <i class="fas fa-search icon-search-input-naj cursor-pointer" onclick="getPessoas($('#nome_cliente_processo')[0], false)"></i>
                                        <div class="input-group-text ml-1 button-editar-pessoa" onclick="carregaModalManutencaoProcessoPessoaEdicao('codigo_cliente_processo');"><i class="fas fa-edit"></i></div>
                                    </div>
                                </div>
                                <div class="col-2 pl-0">
                                    <div class="input-group">
                                        <select class="form-control" name="QUALIFICA_CLIENTE" id="qualifica_cliente_cliente" required="" tabindex="2">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row p-0 m-0">
                                <label for="" class="control-label text-right label-center mb-0" style="width: 22.45% !important;"></label>
                                <div class="col-9">
                                    <div class="input-group row content-select-ajax-naj mt-0" id="content-select-ajax-naj-cliente">
                                    </div>
                                </div>
                            </div>
                            <!-- FIM INPUT CONSULTA CLIENTE -->
                            
                            <!-- INICIO INPUT CONSULTA ADVERSÁRIO -->
                            <div class="form-group row mb-0">
                                <label for="codigo_adversario" class="col-2 control-label text-right label-center">Parte Contrária</label>
                                <div class="col-8 pr-0">
                                    <div class="input-group-prepend">
                                        <input type="text" onkeypress="onlynumber()" class="form-control mr-1" name="CODIGO_ADVERSARIO" id="codigo_adversario" required="" tabindex="3" style="width: 15% !important;" onchange="getShow('codigo_adversario', 'nome_adversario', 'pessoas');">
                                        <input type="text" class="form-control" name="nome_adversario" id="nome_adversario" required="" filled_field="content-select-ajax-naj-adversario" onkeypress="getPessoas(this);">
                                        <i class="fas fa-search icon-search-input-naj cursor-pointer" onclick="getPessoas($('#nome_adversario')[0], false)"></i>
                                        <div class="input-group-text ml-1 button-editar-pessoa" onclick="carregaModalManutencaoProcessoPessoaEdicao('codigo_adversario');"><i class="fas fa-edit"></i></div>
                                    </div>
                                </div>
                                <div class="col-2 pl-0">
                                    <div class="input-group">
                                        <select class="form-control" name="QUALIFICA_ADVERSARIO" id="qualifica_cliente_adversario" required="" tabindex="4">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row p-0 m-0">
                                <label for="" class="control-label label-center mb-0" style="width: 22.45% !important;"></label>
                                <div class="col-9">
                                    <div class="input-group row content-select-ajax-naj mt-0" id="content-select-ajax-naj-adversario">
                                    </div>
                                </div>
                            </div>
                            <!-- FIM INPUT CONSULTA PARTE CONTRÁRIA -->
                            
                            <!-- INICIO INPUT CONSULTA ADVOGADO -->
                            <div class="form-group row mb-0">
                                <label for="codigo_adv_cliente" class="col-2 control-label text-right label-center">Advodago do Cliente</label>
                                <div class="col-8 pr-0">
                                    <div class="input-group-prepend">
                                        <input type="text" onkeypress="onlynumber()" class="form-control mr-1" name="CODIGO_ADV_CLIENTE" id="codigo_adv_cliente" required="" tabindex="5" style="width: 15% !important;" onchange="getShow('codigo_adv_cliente', 'nome_adv_cliente', 'pessoas');">
                                        <input type="text" class="form-control" name="nome_adv_cliente" id="nome_adv_cliente" required="" filled_field="content-select-ajax-naj-adv-cliente" onkeypress="getPessoas(this);">
                                        <i class="fas fa-search icon-search-input-naj cursor-pointer" onclick="getPessoas($('#nome_adv_cliente')[0], false)"></i>
                                        <div class="input-group-text ml-1 button-editar-pessoa" onclick="carregaModalManutencaoProcessoPessoaEdicao('codigo_adv_cliente');"><i class="fas fa-edit"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row p-0 m-0">
                                <label for="" class="control-label label-center mb-0" style="width: 22.45% !important;"></label>
                                <div class="col-9">
                                    <div class="input-group row content-select-ajax-naj mt-0" id="content-select-ajax-naj-adv-cliente">
                                    </div>
                                </div>
                            </div>
                            <!-- FIM INPUT CONSULTA ADVOGADO -->
                            
                            <div class="form-group row">
                                <label for="numero_processo_new" class="col-2 control-label text-right label-center">Numeração CNJ</label>
                                <div class="col-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="NUMERO_PROCESSO_NEW" id="numero_processo_new" placeholder="Numeração CNJ..." required="">
                                    </div>
                                </div>
                                <label for="grau_jurisdicao" class="col-2 control-label text-right label-center">Grau de Jurisdição</label>
                                <div class="col-3">
                                    <div class="input-group">
                                        <select class="form-control" name="GRAU_JURISDICAO" id="grau_jurisdicao" required="" tabindex="6">
                                            <option value="" disabled="" selected="">-- Selecionar --</option>
                                            <option value="1º Instância">1º Instância</option>
                                            <option value="2º Instância">2º Instância</option>
                                            <option value="SUPERIOR">Superior</option>
                                        </select>
                                        <div style="width:9%"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label for="data_distribuicao" class="col-2 control-label text-right label-center">Data Distribuição</label>
                                <div class="col-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="DATA_DISTRIBUICAO" id="data_distribuicao" placeholder="Data Distribuição..." tabindex="7">
                                    </div>
                                </div>
                                <label for="valor_causa" class="col-2 control-label text-right label-center">Valor da Causa</label>
                                <div class="col-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="VALOR_CAUSA" id="valor_causa" placeholder="Valor da Causa..." tabindex="8">
                                        <div style="width:9%"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label for="id_area_juridica" class="col-2 control-label text-right label-center">Área Jurídica</label>
                                <div class="col-3">
                                    <div class="input-group">
                                        <select class="form-control" name="ID_AREA_JURIDICA" id="id_area_juridica" required="" tabindex="9">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- INICIO INPUT CONSULTA CLASSE -->
                            <div class="form-group row mb-0">
                                <label for="codigo_classe" class="col-2 control-label text-right label-center">Classe da Ação</label>
                                <div class="col-8 pr-0">
                                    <div class="input-group-prepend">
                                        <input type="text" onkeypress="onlynumber()" class="form-control mr-1" name="CODIGO_CLASSE" id="codigo_classe" tabindex="10" style="width: 15% !important;" onchange="getShow('codigo_classe', 'nome_classe', 'processos/classe', 'CLASSE');">
                                        <input type="text" class="form-control" name="CLASSE" id="nome_classe" filled_field="content-select-ajax-naj-classe" onkeypress="getClasses(this);">
                                        <i class="fas fa-search icon-search-input-naj cursor-pointer" onclick="getPessoas($('#nome_classe')[0], false)"></i>
                                        <div class="input-group-text ml-1 button-editar-pessoa" onclick="carregaModalManutencaoProcessoClasseEdicao();"><i class="fas fa-edit"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row p-0 m-0">
                                <label for="" class="control-label text-right label-center mb-0" style="width: 22.45% !important;"></label>
                                <div class="col-9">
                                    <div class="input-group row content-select-ajax-naj mt-0" id="content-select-ajax-naj-classe">
                                    </div>
                                </div>
                            </div>
                            <!-- FIM INPUT CONSULTA CLASSE -->
                            
                            <!-- INICIO INPUT CONSULTA COMARCA -->
                            <div class="form-group row mb-0">
                                <label for="codigo_comarca" class="col-2 control-label text-right label-center">Comarca</label>
                                <div class="col-8 pr-0">
                                    <div class="input-group-prepend">
                                        <input type="text" onkeypress="onlynumber()" class="form-control mr-1" name="CODIGO_COMARCA" id="codigo_comarca" tabindex="11" style="width: 15% !important;" onchange="getShow('codigo_comarca', 'nome_comarca', 'processos/comarca', 'COMARCA');">
                                        <input type="text" class="form-control" name="nome_comarca" id="nome_comarca" filled_field="content-select-ajax-naj-comarca" onkeypress="getComarcas(this);">
                                        <i class="fas fa-search icon-search-input-naj cursor-pointer" onclick="getPessoas($('#nome_comarca')[0], false)"></i>
                                        <div class="input-group-text ml-1 button-editar-pessoa" onclick="carregaModalManutencaoProcessoComarcaEdicao();"><i class="fas fa-edit"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row p-0 m-0">
                                <label for="" class="control-label text-right label-center mb-0" style="width: 22.45% !important;"></label>
                                <div class="col-9">
                                    <div class="input-group row content-select-ajax-naj mt-0" id="content-select-ajax-naj-comarca">
                                    </div>
                                </div>
                            </div>
                            <!-- FIM INPUT CONSULTA COMARCA -->
                            
                            <!-- INICIO INPUT CONSULTA CARTORIO -->
                            <div class="form-group row mb-0">
                                <label for="codigo_cartorio" class="col-2 control-label text-right label-center">Cartório</label>
                                <div class="col-8 pr-0">
                                    <div class="input-group-prepend">
                                        <input type="text" onkeypress="onlynumber()" class="form-control mr-1" name="CODIGO_CARTORIO" id="codigo_cartorio" tabindex="12" style="width: 15% !important;" onchange="getShow('codigo_cartorio', 'nome_cartorio', 'processos/cartorio', 'CARTORIO');">
                                        <input type="text" class="form-control" name="CARTORIO" id="nome_cartorio" filled_field="content-select-ajax-naj-cartorio" onkeypress="getCartorios(this);">
                                        <i class="fas fa-search icon-search-input-naj cursor-pointer" onclick="getPessoas($('#nome_cartorio')[0], false)"></i>
                                        <div class="input-group-text ml-1 button-editar-pessoa" onclick="carregaModalManutencaoProcessoCartorioEdicao();"><i class="fas fa-edit"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row p-0 m-0">
                                <label for="" class="control-label text-right label-center mb-0" style="width: 22.45% !important;"></label>
                                <div class="col-9">
                                    <div class="input-group row content-select-ajax-naj mt-0" id="content-select-ajax-naj-cartorio">
                                    </div>
                                </div>
                            </div>
                            <!-- FIM INPUT CONSULTA CARTORIO -->
                            
                            <div class="form-group row">
                                <label for="pedidos_processo" class="col-2 control-label text-right label-center">Pedidos Processo</label>
                                <div class="col-8 pr-0">
                                    <div class="input-group">
                                        <textarea id="w3review" rows="4" cols=""class="form-control" name="PEDIDOS_PROCESSO" id="pedidos_processo" tabindex="13" placeholder="Pedidos Processo..."></textarea>
                                        <div style="width:5.2%"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label for="codigo_divisao_processo" class="col-2 control-label text-right label-center">Divisão</label>
                                <div class="col-5">
                                    <div class="input-group">
                                        <select class="form-control" name="CODIGO_DIVISAO" id="codigo_divisao_processo" tabindex="14" required="">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label for="codigo_situacao" class="col-2 control-label text-right label-center">Situação</label>
                                <div class="col-5">
                                    <div class="input-group">
                                        <select class="form-control" name="CODIGO_SITUACAO" id="codigo_situacao" tabindex="15" required="">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                        </form>
                        
                    </div>
                </div>
            </div>

            <div class="card-footer-naj">
                <div class="row">
                    <div class="col-2">
                        <!--Div somente para manter o layout do modal-->
                    </div>
                    <div class="col-10">
                        <button type="button" id="gravarProcesso" class="btn btnLightCustom" title="Gravar">
                        <i class="fas fa-save"></i>
                        Gravar
                        </button>
                        <button type="button" class="btn btnLightCustom" onclick="$('#modal-manutencao-processo').modal('hide'); " title="Fechar">
                            <i class="fas fa-times"></i>
                            Fechar
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>