<div class="modal fade" id="modal-upload-anexo-ficha-pessoa-chat" tabindex="-1" role="dialog" aria-hidden="true">
    <div id="bloqueio-nova-upload-anexo-ficha-pessoa-chat" class="loader loader-default" data-half></div>
    <div class="modal-dialog modal-nova-tarefa-naj" role="document" style="margin-top: 10%;">
        <div class="modal-content modal-content-shadow-naj">
            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Anexar arquivos em pessoa</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body-naj" id="content-outside-upload-anexo-ficha-pessoa" style="max-height: 30vh;">
                <form class="form-horizontal needs-validation" novalidate="" id="form-upload-anexo-ficha-pessoa-chat">
                    <input type="hidden" id="extensao">
                    <input type="hidden" id="file_size">
                    <input type="hidden" id="id_mensagem_anexo">
                    
                    <!-- INICIO INPUT CONSULTA -->
                    <div class="form-group row">
                        <label for="codigo_pessoa_upload_ficha" class="col-2 control-label label-center">Pessoa</label>
                        <div class="col-10">
                            <div class="input-group-prepend">
                                <input type="text" class="form-control mr-1" name="codigo_pessoa_upload_ficha" id="codigo_pessoa_upload_ficha" required="" style="width: 15% !important;" onchange="onChangeCodigosPessoasUploadFicha();">
                                <input type="text" class="form-control" name="nome_pessoa_upload_ficha" id="nome_pessoa_upload_ficha" required="" onkeypress="getPessoaUploadAnexoFicha(this);" placeholder="Pesquisar pessoas pelo nome">
                                <i class="fas fa-search icon-search-input-naj" id="icon-search-nome-pessoa-upload-ficha" onclick="buscaDadosPessoaFicha(this)"></i>
                                <div class="input-group-text ml-1 button-editar-pessoas-tarefa" id="btnGroupAddon" onclick="onClickButtonCadastroPessoaFicha();"><i class="fas fa-edit"></i></div>
                                <div class="input-group content-select-ajax-cliente" id="content-select-ajax-naj-pessoa-upload-ficha"></div>
                            </div>
                        </div>
                    </div>
                    <!-- FIM INPUT CONSULTA -->

                    <div class="form-group row">
                        <label for="pasta_salvar_arquivo" class="col-2 control-label label-center">Salvar em</label>
                        <div class="col-10" style="max-width: 78.6% !important;">
                            <div class="input-group">
                                <i class="fas fa-folder-open icon-folder-anexo-naj"></i>
                                <input class="form-control" type="text" name="pasta_salvar_arquivo" id="pasta_salvar_arquivo" value="ANEXOS DAS MENSAGENS" / style="padding-left: 40px; text-decoration: underline;" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="nome_aquivo_upload" class="col-2 pl-0 control-label label-center">Nome do Arquivo</label>
                        <div class="col-10" style="max-width: 78.6% !important;">
                            <div class="input-group">
                                <input class="form-control" type="text" name="nome_aquivo_upload" id="nome_aquivo_upload" />
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer-naj">
                <label for="codigo_pessoa_upload_ficha" class="col-2 control-label label-center"></label>
                <button type="button" id="anexar-aquivo" class="btn btnLightCustom" title="Gravar" onclick="storeAnexarArquivoFichaPessoa();">
                    <i class="fas fa-paperclip"></i>
                    Anexar
                </button>
                <button type="button" class="btn btnLightCustom" title="Fechar" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>