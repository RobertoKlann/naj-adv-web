<div class="modal fade" id="modal-excluir-pessoa" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content modal-excluir-pessoa-question">
            <div class="modal-header modal-header-dark">
                <h4><i class="glyphicon glyphicon-question-sign"></i> <b> Atenção !</b></h4>
                <button type="button" class="close" data-dismiss="modal">×</button>                
            </div>
            <div class="modal-body">
                Tem certeza que deseja excluir este registro?
            </div>

            <div class="modal-footer">
                <button type="button" onclick="confirmarExclusao();" class="btn btn-default pull-left">Confirmar</button>
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
            </div>
                            
            <input type="hidden" name="_method" value="POST">
            <input type="hidden" id="token" name="_token" value="{{ csrf_token() }}">
        </div>
    </div>
</div>