<div class="modal fade" id="modal-conteudo-movimentacao-processo" role="dialog" aria-hidden="true">
    <div id="bloqueio-modal-conteudo-movimentacao-processo" class="loader loader-default"></div>
    <div class="modal-dialog modal-extra-large" role="document" style="height: 90%;">
        <div class="modal-content modal-content-shadow-naj" style=" height: 100%;">

            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Conteúdo Completo das Movimentações do Processo</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="row m-0" style="width: 100%; position: absolute; top: 48px;">
                <div id="header-modal-conteudo-movimentacao-processo" class="col-12 pl-3 pr-3">
                </div>
            </div>

            <div class="row m-0 border-top conteudo-movimentacao-processo">
                <div id="content-modal-conteudo-movimentacao-processo"  class="col-12 card-body-naj border-right naj-scrollable text-justify pl-0 pr-0 mt-1" style="height: 100%">

                    <div id="datatable-conteudo-movimentacao-processo" class="naj-datatable" style="height: 100%;"></div>

                </div>
            </div>

            <div class="card-footer-naj" style="width: 100%; position: absolute; bottom: 0px;">
                <div class="row">
                    <div class="col-6">
                        <button type="button" id="anteriorConteudoMovimentacaoProcesso" class="btn waves-effect waves-light btn-secondary" onclick="anteriorConteudoMovimentacaoProcesso();" data-toggle="tooltip" data-placement="top" title="Anterior">
                            <i class="fas fa-arrow-circle-left"></i>
                            Anterior
                        </button>
                        <button type="button" id="proximoConteudoMovimentacaoProcesso" class="btn waves-effect waves-light btn-secondary" onclick="proximoConteudoMovimentacaoProcesso();" data-toggle="tooltip" data-placement="top" title="Próximo">
                            <i class="fas fa-arrow-circle-right"></i>
                            Próximo
                        </button>
                        <button type="button" id="btnFecharModalConteudoMovimentacao" class="btn waves-effect waves-light btn-light" onclick="fecharModalConteudoPublicacao()" data-toggle="tooltip" data-placement="top" title="Fechar">
                            <i class="fas fa-times"></i>
                            Fechar
                        </button>&emsp;
                        <span id="paginacao-modal-conteudo-publicacao">
                        </span>
                    </div>
                    <div class="col-6 text-right">
                        <button id="btnMonitorarEdicao" type="button" class="btn btn-info" onclick="">
                            <i class="fas fa-search mr-2"></i>Monitoramento
                        </button>
                        <button id="btnCadastrarTarefa" type="button" class="btn btn-info" onclick="">
                            <i class="fas fa-plus mr-2"></i>Tarefa
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>