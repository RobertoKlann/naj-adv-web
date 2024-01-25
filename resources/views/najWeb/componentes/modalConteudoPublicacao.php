<div class="modal fade" id="modal-conteudo-publicacao" role="dialog" aria-hidden="true">
    <div id="bloqueio-modal-conteudo-publicacao" class="loader loader-default"></div>
    <div class="modal-dialog" role="document" style="min-width: 90%; height: 90%;">
        <div class="modal-content modal-content-shadow-naj" style=" height: 100%;">
            
            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Conteúdo completo publicação</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="row m-0" style="width: 100%; position: absolute; top: 48px;">
                <div id="header-modal-conteudo-publicacao-left" class="col-8 pl-3 pr-3">
                </div>
                <div id="header-modal-conteudo-publicacao-right" class="col-4 pl-3 pr-3">
                </div>
            </div>
            
            <div class="row m-0 border-top"  style="width: 100%; position: absolute; top: 135px;">
                <div class="col-8 text-center border-right">
                    Dados da publicação
                </div>
                <div id="titulo-superior-coluna-direita-modal-conteudo-publicacao" class="col-4 text-center">
                    Envolvidos
                </div>
            </div>
            
            <div class="row m-0 border-top conteudo-publicacao">
                
                <div id="content-modal-conteudo-publicacao"  class="col-8 card-body-naj border-right naj-scrollable text-justify pl-3 pr-3" style="height: 100%">
                </div>
                
                <div class="col-4 card-body-naj p-0 naj-scrollable" style="height: 100%">
                    <div id="processos-semelhantes-modal-conteudo-publicacao" class="naj-scrollable pl-3 pr-3" style="display: none;">
                    </div>
                    <div id="titulo-inferior-coluna-direita-modal-conteudo-publicacao" class="border-top border-bottom text-center" style="display: none;">Envolvidos</div>
                    <div id="envolvidos-modal-conteudo-publicacao" class="naj-scrollable pl-3 pr-3 pt-1">
                    </div>
                </div>
                
            </div>
            
            <div class="row"  style="width: 100%; position: absolute; bottom: 69px;">
                <div class="col-8">
                </div>
                <div class="col-4">
                    <div class="d-flex justify-content-center">
                        <div id="button-vincular-processo-a-publicacao" class="text-center">
                            <button type="button" class="btn btn-info ml-1" onclick="vincularProcessoPublicacao();"><i class="fas fa-share mr-2"></i>Vincular</button>
                            <button type="button" class="btn btn-danger mr-1" onclick="onClickCancelarProcessoSemelhante();"><i class="fas fa-times mr-2"></i>Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-footer-naj" style="width: 100%; position: absolute; bottom: 0px;">
                <div class="row">
                    <div class="col-5">
                        <button type="button" id="anteriorConteudoPublicacao" class="btn waves-effect waves-light btn-secondary" onclick="anteriorConteudoPublicacao();" data-toggle="tooltip" data-placement="top" title="Anterior">
                            <i class="fas fa-arrow-circle-left"></i>
                            Anterior
                        </button>
                        <button type="button" id="proximoConteudoPublicacao" class="btn waves-effect waves-light btn-secondary" onclick="proximoConteudoPublicacao();" data-toggle="tooltip" data-placement="top" title="Próximo">
                            <i class="fas fa-arrow-circle-right"></i>
                            Próximo
                        </button>                       
                        <button type="button" id="btnFecharModalConteudoPublicacao" class="btn waves-effect waves-light btn-light" onclick="fecharModalConteudoPublicacao()" data-toggle="tooltip" data-placement="top" title="Fechar">
                            <i class="fas fa-times"></i>
                            Fechar
                        </button>&emsp;
                        <span id="paginacao-modal-conteudo-publicacao">
                        </span>
                    </div>
                    <div id="footer-modal-conteudo-publicacao-right" class="col-7 text-right">
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>