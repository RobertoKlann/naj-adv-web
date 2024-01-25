<div class="modal fade" id="modal-manutencao-pessoa-contato" role="dialog" aria-hidden="true">
    <div id="bloqueio-modal-manutencao-pessoa-contato" class="loader loader-default"></div>
    <div class="modal-dialog" role="document" style="min-width: 50%; margin-top: 15%">
        <div class="modal-content modal-content-shadow-naj" style="height: 65%;">

            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj" id="titulo-modal-manutencao-pessoa-contato">Contatos da Pessoa</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class=" page-content container-fluid mt-4" style="height: auto; padding-right: 15px; padding-left: 15px; margin-right: auto; margin-left: auto; width: 100%;">
                <form class="form-horizontal needs-validation" novalidate="" id="form-pessoa-contato">
                    <div class="row">
                        <div class="col-12">

                            <div class="form-group row d-none">
                                <label for="codigo_contato" class="col-3 control-label text-right label-center">Código</label>
                                <div class="col-2">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="CODIGO" id="codigo_contato" placeholder="Código..." required="" readonly="">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group row d-none">
                                <div class="col-2">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="codigo_pessoa" id="codigo_pessoa" placeholder="Código Pessoa..." required="" readonly="">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label for="contato_de" class="col-3 control-label text-right label-center">Contato de</label>
                                <div class="col-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="NOME" id="contato_de" placeholder="Contato de..." readonly="">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label for="pessoa" class="col-3 control-label text-right">Pessoa de Contato</label>
                                <div class="col-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="PESSOA" id="pessoa" placeholder="Pessoa de Contato..." required="">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="tipo_contato" class="col-3 control-label text-right label-center">Tipo</label>
                                <div class="col-8">
                                    <div class="input-group">
                                        <select class="form-control" name="TIPO" id="tipo_contato" required="">
                                            <option value="" selected="" disabled="">--Selecionar--</option>
                                            <option>Fone Trabalho</option>
                                            <option>Fone Residencial</option>
                                            <option>Fone Comercial</option>
                                            <option>Fone Fax</option>
                                            <option>Fone Celular</option>
                                            <option>Fone Celular WhatsApp</option>
                                            <option>Fone Celular Comercial</option>
                                            <option>Fone Celular Particular</option>
                                            <option>Fone p/ Recados</option>
                                            <option>E-Mail</option>
                                            <option>E-Mail Particular</option>
                                            <option>E-Mail Comercial</option>
                                            <option>E-Mail Trabalho</option>
                                            <option>Página Web</option>
                                            <option>Página Web Particular</option>
                                            <option>Página Web Comercial</option>
                                            <option>Referência</option>
                                            <option>Referência Comercial</option>
                                            <option>Responsável</option>
                                            <option>Skype</option>
                                            <option>Outros</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label for="contato" class="col-3 control-label text-right label-center">Contato</label>
                                <div class="col-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="CONTATO" id="contato" placeholder="Contato..." required="">
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        </div>
                    </div>
                </form>
            </div>

            <div class="card-footer-naj" style="height: 20%">
                <button type="button" id="gravarPessoaContato" class="btn btnLightCustom" title="Gravar">
                    <i class="fas fa-save"></i>
                    Gravar
                </button>
                <button type="button" class="btn btnLightCustom" onclick="novoRegistroPessoaContato();" title="Novo">
                    <i class="fas fa-plus"></i>
                    Novo
                </button>
                <button type="button" class="btn btnLightCustom" onclick="$('#modal-manutencao-pessoa-contato').modal('hide');" title="Fechar">
                    <i class="fas fa-times"></i>
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>