<div class="modal fade" id="modal-manutencao-conta-virtual" role="dialog" aria-hidden="true">
    <div id="bloqueio-modal-manutencao-conta-virtual" class="loader loader-default"></div>
    <div class="modal-dialog modal-extra-large" role="document">
        <div class="modal-content modal-content-shadow-naj">
            
            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj">Manutenção da Conta Virtual IUGU</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="page-content container-fluid mt-1 mb-1">

                <div class="row justify-content-center">
                    <div class="col-lg-12">
                        <div class="card-body-naj scrollable">
                            <form class="form-horizontal needs-validation" novalidate="" id="form-conta-virtual">
                                <div class="row">
                                    <div class="col-md-12 col-lg-6">
                                        
                                        <div class="form-group row">
                                            <label for="id" class="col-sm-6 col-md-5 col-lg-4 control-label text-right label-center">ID Conta Virtual</label>
                                            <div class="col-sm-4 col-md-3 col-lg-4">
                                                <div class="input-group">
                                                    <input type="number" min="0" class="form-control" name="id" id="id" placeholder="ID..." readonly="">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="account_id" class="col-sm-6 col-md-5 col-lg-4 control-label text-right label-center">ID Conta IUGU</label>
                                            <div class="col-sm-6 col-md-7 col-lg-8">
                                                <div class="input-group">
                                                    <input type="text" maxlength="32" class="form-control" name="account_id" id="account_id" placeholder="ID Conta IUGU..." required="">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="nome" class="col-sm-6 col-md-5 col-lg-4 control-label text-right label-center">Nome Conta</label>
                                            <div class="col-sm-6 col-md-7 col-lg-8">
                                                <div class="input-group">
                                                    <input type="text" maxlength="80" class="form-control" name="nome" id="nome" placeholder="Nome Conta...">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="codigo_especie" class="col-sm-6 col-md-5 col-lg-4 control-label text-right label-center">Pagamento Especie</label>
                                            <div class="col-sm-6 col-md-7 col-lg-8">
                                                <div class="input-group">
                                                    <select class="form-control" name="codigo_especie" id="codigo_especie" required="">
                                                        <option value="" disabled="">--Selecionar--</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group row">
                                            <label for="especie_unidade_finaceira" class="col-sm-6 col-md-5 col-lg-4 control-label text-right label-center">Unidade Financeira Especie</label>
                                            <div class="col-sm-6 col-md-7 col-lg-8">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" name="especie_unidade_finaceira" id="especie_unidade_finaceira" placeholder="Unidade Financeira Da Especie..." readonly="" >
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="codigo_unidade" class="col-sm-6 col-md-5 col-lg-4 control-label text-right label-center">Unidade Financeira</label>
                                            <div class="col-sm-6 col-md-7 col-lg-8">
                                                <div class="input-group">
                                                    <select class="form-control" name="codigo_unidade" id="codigo_unidade" required="">
                                                        <option value="" disabled="">--Selecionar--</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group row">
                                            <label for="live_api_token" class="col-sm-6 col-md-5 col-lg-4 control-label text-right label-center">Live API Token</label>
                                            <div class="col-sm-6 col-md-7 col-lg-8">
                                                <div class="input-group">
                                                    <input type="text" maxlength="65" class="form-control" name="live_api_token" id="live_api_token" placeholder="Live API Token...">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="test_api_token" class="col-sm-6 col-md-5 col-lg-4 control-label text-right label-center">Test API Token</label>
                                            <div class="col-sm-6 col-md-7 col-lg-8">
                                                <div class="input-group">
                                                    <input type="text" maxlength="65" class="form-control" name="test_api_token" id="test_api_token" placeholder="Test API Token...">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="user_token" class="col-sm-6 col-md-5 col-lg-4 control-label text-right label-center">User Token</label>
                                            <div class="col-sm-6 col-md-7 col-lg-8">
                                                <div class="input-group">
                                                    <input type="text" maxlength="32" class="form-control" name="user_token" id="user_token" placeholder="User Token...">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="status" class="col-sm-6 col-md-5 col-lg-4 control-label text-right label-center">Situação</label>
                                            <div class="col-sm-4 col-md-3 col-lg-4">
                                                <div class="input-group">
                                                    <select class="form-control" name="status" id="status" required="">
                                                        <option value="" disabled="">--Selecionar--</option>
                                                        <option value="1">Ativo</option>
                                                        <option value="0">Inativo</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="mora" class="col-sm-6 col-md-5 col-lg-4 control-label text-right label-center">Aplica Mora?</label>
                                            <div class="col-sm-4 col-md-3 col-lg-4">
                                                <div class="input-group">
                                                    <select class="form-control" name="mora" id="mora" required="">
                                                        <option value="" disabled="">--Selecionar--</option>
                                                        <option value="S">Sim</option>
                                                        <option value="N">Não</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 col-lg-6">
                                        <div class="form-group row">
                                            <label for="banco" class="col-sm-6 col-md-5 col-lg-4 control-label text-right label-center">Banco</label>
                                            <div class="col-sm-6 col-md-7 col-lg-8">
                                                <div class="input-group">
                                                    <input type="text" maxlength="15" class="form-control" name="banco" id="banco" placeholder="Banco..." required="">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="agencia" class="col-sm-6 col-md-5 col-lg-4 control-label text-right label-center">Agência</label>
                                            <div class="col-sm-6 col-md-7 col-lg-8">
                                                <div class="input-group">
                                                    <input type="text" maxlength="10" class="form-control" name="agencia" id="agencia" placeholder="Agência..." required="">
                                                </div>
                                            </div>
                                        </div>  

                                        <div class="form-group row">
                                            <label for="tipo_conta" class="col-sm-6 col-md-5 col-lg-4 control-label text-right label-center">Tipo da Conta</label>
                                            <div class="col-sm-6 col-md-5 col-lg-7">
                                                <div class="input-group">
                                                    <select class="form-control" name="tipo_conta" id="tipo_conta" required="">
                                                        <option value="" disabled="">--Selecionar--</option>
                                                        <option value="CC">CC - Conta Corrente</option>
                                                        <option value="CP">CP - Conta Poupança</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="multa" class="col-sm-6 col-md-5 col-lg-4 control-label text-right label-center">Multa Percentual</label>
                                            <div class="col-sm-4 col-md-3 col-lg-4">
                                                <div class="input-group">
                                                    <!--O valor da multa não pode ser superior a 20% do valor da fatura-->
                                                    <input type="text" maxlength="6" min="0" max="20" class="form-control" name="multa" id="multa" placeholder="Percentual...">&emsp;
                                                    <span>
                                                        <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="O valor da multa não pode ser superior a 20% do valor da fatura."></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="desconto_percentual" class="col-sm-6 col-md-5 col-lg-4 control-label text-right label-center">Desconto Percentual</label>
                                            <div class="col-sm-4 col-md-3 col-lg-4">
                                                <div class="input-group">
                                                    <!--O valor do desconto não pode ser superior a 50% do valor da fatura-->
                                                    <input type="text" maxlength="6"  min="0" max="50" class="form-control" name="desconto_percentual" id="desconto_percentual" placeholder="Percentual...">&emsp;
                                                    <span>
                                                        <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="O valor do desconto não pode ser superior a 50% do valor da fatura."></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="valor_comissao_boleto" class="col-sm-6 col-md-5 col-lg-4 control-label text-right label-center">Valor Comissão Boleto</label>
                                            <div class="col-sm-4 col-md-3 col-lg-4">
                                                <div class="input-group">
                                                    <input type="text" maxlength="10" onkeypress="onlynumber()" class="form-control" name="valor_comissao_boleto" id="valor_comissao_boleto" placeholder="R$ 0.00...">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="valor_tarifa_saque" class="col-sm-6 col-md-5 col-lg-4 control-label text-right label-center">Valor Tarifa Saque</label>
                                            <div class="col-sm-4 col-md-3 col-lg-4">
                                                <div class="input-group">
                                                    <input type="text" maxlength="10" onkeypress="onlynumber()" class="form-control" name="valor_tarifa_saque" id="valor_tarifa_saque" placeholder="R$ 0.00...">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group row">
                                            <label for="dias_apos" class="col-sm-6 col-md-5 col-lg-4 control-label text-right label-center">Dias Após Vencimento</label>
                                            <div class="col-sm-4 col-md-3 col-lg-4">
                                                <div class="input-group">
                                                    <input type="text" maxlength="2" onkeypress="onlynumber()" class="form-control" name="dias_apos" id="dias_apos" placeholder="Dias...">&emsp;
                                                    <span>
                                                        <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Dias para pagamento da fatura após a data do Vencimento, mínimo 0 e máximo 30"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group row">
                                            <label for="saque_montante" class="col-sm-6 col-md-5 col-lg-4 control-label text-right label-center">Valor Saque Montante</label>
                                            <div class="col-sm-4 col-md-3 col-lg-4">
                                                <div class="input-group">
                                                    <input type="text" maxlength="10" onkeypress="onlynumber()" class="form-control" name="saque_montante" id="saque_montante" placeholder="R$ 0.00..." required="">&emsp;
                                                    <span>
                                                        <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Valor montante de saldo disponível na plataforma da IUGU para efetuar um saque. INDEPENDENTE do dia da semana ou do valor mínimo para saque."></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="border pt-2 pl-2">
                                            <div class="form-group row">
                                                <label for="saque_semanal" class="col-sm-6 col-md-5 col-lg-4 control-label text-right label-center">Saque Semanal</label>
                                                <div class="col-sm-3 col-md-3 col-lg-3">
                                                        <input type="checkbox" value="1" id="segunda" name="saque_semanal">&emsp;<label for="segunda">Segunda</label></br>
                                                        <input type="checkbox" value="2" id="terca" name="saque_semanal">&emsp;<label for="terca">Terça</label></br>
                                                        <input type="checkbox" value="3" id="quarta" name="saque_semanal">&emsp;<label for="quarta">Quarta</label>
                                                </div>
                                                <div class="col-sm-3 col-md-3 col-lg-3">
                                                        <input type="checkbox" value="4" id="quinta" name="saque_semanal">&emsp;<label for="quinta">Quinta</label></br>
                                                        <input type="checkbox" value="5" id="sexta" name="saque_semanal">&emsp;<label for="sexta">Sexta</label></br>
                                                        <span>
                                                            <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Dia da semana para realizar o saque na plataforma da IUGU. Considera o valor mínimo para saque."></i>
                                                        </span>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="saque_minimo" class="col-sm-6 col-md-5 col-lg-4 control-label text-right label-center">Valor Saque Mínimo</label>
                                                <div class="col-sm-4 col-md-3 col-lg-4">
                                                    <div class="input-group">
                                                        <input type="text" maxlength="10" onkeypress="onlynumber()" class="form-control" name="saque_minimo" id="saque_minimo" placeholder="R$ 0.00..." required="">&emsp;
                                                        <span>
                                                            <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Valor mínimo de saldo disponível na plataforma IUGU no dia da semana marcado para saque."></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

            <div class="card-footer-naj">
                <button type="button" id="gravarContaVirtual" class="btn btnLightCustom" title="Gravar">
                    <i class="fas fa-save"></i>
                    Gravar
                </button>
                <button type="button" class="btn btnLightCustom" onclick="novoRegistro();" title="Novo">
                    <i class="fas fa-plus"></i>
                    Novo
                </button>
                <button type="button" class="btn btnLightCustom" onclick="$('#modal-manutencao-conta-virtual').modal('hide')" title="Fechar">
                    <i class="fas fa-times"></i>
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>