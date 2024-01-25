<div class="modal fade" id="modal-manutencao-pessoa" role="dialog" aria-hidden="true">
    <div id="bloqueio-modal-manutencao-pessoa" class="loader loader-default"></div>
    <div class="modal-dialog" role="document" style="min-width: 65%; height: 80%; margin-top: 4.5%">
        <div class="modal-content modal-content-shadow-naj" style="height: 100%;">

            <div class="modal-header modal-header-naj">
                <p class="titulo-modal-naj" id="titulo-modal-manutencao-pessoa">Manutenção Pessoa</p>
                <button type="button" data-dismiss="modal" class="btn btn-info btn-rounded btnClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="card-body-naj page-content container-fluid pt-2" style="height: 100%;">
                <div class="row" style="height: 100%;"> 
                    <div class="col-md-12" style="height: 100%;">

                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item"> <a id="guidePessoa" class="nav-link cursor-pointer active" onclick="changeTabManutencaoPessoa(1)" role="tab" aria-selected="true"><span class="hidden-sm-up"><i class="fas fa-user"></"></i></span> <span class="hidden-xs-down">Pessoa</span></a></li>
                            <li class="nav-item"> <a id="guideContatos" class="nav-link cursor-pointer" onclick="changeTabManutencaoPessoa(2)" role="tab" aria-selected="false"><span class="hidden-sm-up"><i class="fas fa-phone"></i></span> <span class="hidden-xs-down">Contatos</span></a></li>
                        </ul>

                        <div class="tab-content tabcontent-border" style="height: 88%;">

                            <div class="tab-pane p-3 active" id="tabPessoa" role="tabpanel" style="height: 100%;">
                                <form class="form-horizontal needs-validation" novalidate="" id="form-pessoa" style="height: 100%;">
                                    
                                    <div class="form-group row">
                                        <label for="codigo" class="col-2 control-label text-right label-center">Código</label>
                                        <div class="col-4">
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="CODIGO" id="codigo" placeholder="Código..." required="" readonly="">&emsp;
                                                <i class="font-18 mdi mdi-open-in-new cursor-pointer text-dark mt-1" title="Ver ficha da pessoa." data-toggle="tooltip" onclick="abreExternoCadastroPessoa();"></i>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group row">
                                        <label for="nome" class="col-2 control-label text-right label-center">Nome</label>
                                        <div class="col-10">
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="NOME" id="nome" placeholder="Nome..." required="">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="tipo" class="col-2 control-label text-right label-center">Tipo</label>
                                        <div class="col-4">
                                            <div class="input-group">
                                                <select class="form-control" name="TIPO" id="tipo" required="">
                                                    <option value="F">Física</option>
                                                    <option value="J">Juridíca</option>
                                                </select>
                                            </div>
                                        </div>
                                        <label id="label_cpf" for="cpf" class="col-1 control-label text-right label-center">CPF</label>
                                        <label id="label_cnpj" for="cnpj" class="col-1 control-label text-right label-center">CNPJ</label>
                                        <div class="col-5">
                                            <div class="input-group">
                                                <input type="text" maxlength="" class="form-control cpf" name="CPF" id="cpf" placeholder="CPF...">
                                                <input type="text" maxlength="" class="form-control cnpj" name="CNPJ" id="cnpj" placeholder="CNPJ...">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group row">
                                        <label for="codigo_divisao" class="col-2 control-label text-right label-center">Divisão</label>
                                        <div class="col-4">
                                            <div class="input-group">
                                                <select class="form-control" name="CODIGO_DIVISAO" id="codigo_divisao" required="">
                                                </select>
                                            </div>
                                        </div>
                                        <label for="codigo_grupo" class="col-1 control-label text-right label-center">Grupo</label>
                                        <div class="col-5">
                                            <div class="input-group">
                                                <select class="form-control" name="CODIGO_GRUPO" id="codigo_grupo" required="">
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="endereco_tipo" class="col-2 control-label text-right label-center">Endereço</label>
                                        <div class="col-3">
                                            <div class="input-group">
                                                <select class="form-control" name="ENDERECO_TIPO" id="endereco_tipo">
                                                    <option value="" selected="" disabled="">--Selecionar--</option>
                                                    <option>Acampamento</option>
                                                    <option>Acesso</option>
                                                    <option>Adro</option>
                                                    <option>Aeroporto</option>
                                                    <option>Alameda</option>
                                                    <option>Alto</option>
                                                    <option>Antiga Estrada</option>
                                                    <option>Área</option>
                                                    <option>Área Especial</option>
                                                    <option>Área Verde</option>
                                                    <option>Artéria</option>
                                                    <option>Atalho</option>
                                                    <option>Avenida</option>
                                                    <option>Avenida Contorno</option>
                                                    <option>Avenida Marginal</option>
                                                    <option>Avenida Marginal Direita</option>
                                                    <option>Avenida Marginal Esquerda</option>
                                                    <option>Avenida Marginal Norte</option>
                                                    <option>Avenida Perimetral</option>
                                                    <option>Avenida Velha</option>
                                                    <option>Baixa</option>
                                                    <option>Balão</option>
                                                    <option>Beco</option>
                                                    <option>Belvedere</option>
                                                    <option>Bloco</option>
                                                    <option>Blocos</option>
                                                    <option>Bosque</option>
                                                    <option>Boulevard</option>
                                                    <option>Bulevar</option>
                                                    <option>Buraco</option>
                                                    <option>Cais</option>
                                                    <option>Calçada</option>
                                                    <option>Calçadão</option>
                                                    <option>Caminho</option>
                                                    <option>Caminho de Servidão</option>
                                                    <option>Campo</option>
                                                    <option>Campus</option>
                                                    <option>Canal</option>
                                                    <option>Chácara</option>
                                                    <option>Ciclovia</option>
                                                    <option>Circular</option>
                                                    <option>Colônia</option>
                                                    <option>Complexo Viário</option>
                                                    <option>Comunidade</option>
                                                    <option>Condomínio</option>
                                                    <option>Condomínio Residencial</option>
                                                    <option>Conjunto</option>
                                                    <option>Conjunto Mutirão</option>
                                                    <option>Contorno</option>
                                                    <option>Corredor</option>
                                                    <option>Córrego</option>
                                                    <option>Descida</option>
                                                    <option>Desvio</option>
                                                    <option>Distrito</option>
                                                    <option>Eixo</option>
                                                    <option>Eixo Industrial</option>
                                                    <option>Eixo Principal</option>
                                                    <option>Elevada</option>
                                                    <option>Entrada Particular</option>
                                                    <option>Entre Quadra</option>
                                                    <option>Escada</option>
                                                    <option>Escadaria</option>
                                                    <option>Esplanada</option>
                                                    <option>Estação</option>
                                                    <option>Estacionamento</option>
                                                    <option>Estádio</option>
                                                    <option>Estrada</option>
                                                    <option>Estrada Antiga</option>
                                                    <option>Estrada de Ferro</option>
                                                    <option>Estrada de Ligação</option>
                                                    <option>Estrada de Servidão</option>
                                                    <option>Estrada Estadual</option>
                                                    <option>Estrada Intermunicipal</option>
                                                    <option>Estrada Municipal</option>
                                                    <option>Estrada Particular</option>
                                                    <option>Estrada Velha</option>
                                                    <option>Estrada Vicinal</option>
                                                    <option>Favela</option>
                                                    <option>Fazenda</option>
                                                    <option>Feira</option>
                                                    <option>Ferrovia</option>
                                                    <option>Fonte</option>
                                                    <option>Forte</option>
                                                    <option>Galeria</option>
                                                    <option>Granja</option>
                                                    <option>Ilha</option>
                                                    <option>Jardim</option>
                                                    <option>Jardinete</option>
                                                    <option>Ladeira</option>
                                                    <option>Lago</option>
                                                    <option>Lagoa</option>
                                                    <option>Largo</option>
                                                    <option>Localidade</option>
                                                    <option>Loteamento</option>
                                                    <option>Margem</option>
                                                    <option>Marina</option>
                                                    <option>Módulo</option>
                                                    <option>Monte</option>
                                                    <option>Morro</option>
                                                    <option>Núcleo</option>
                                                    <option>Núcleo Habitacional</option>
                                                    <option>Núcleo Rural</option>
                                                    <option>Outeiro</option>
                                                    <option>Parada</option>
                                                    <option>Paralela</option>
                                                    <option>Parque</option>
                                                    <option>Parque Municipal</option>
                                                    <option>Parque Residencial</option>
                                                    <option>Passagem</option>
                                                    <option>Passagem de Pedestres</option>
                                                    <option>Passagem Subterrânea</option>
                                                    <option>Passarela</option>
                                                    <option>Passeio</option>
                                                    <option>Passeio Público</option>
                                                    <option>Pátio</option>
                                                    <option>Ponta</option>
                                                    <option>Ponte</option>
                                                    <option>Porto</option>
                                                    <option>Praça</option>
                                                    <option>Praça de Esportes</option>
                                                    <option>Praia</option>
                                                    <option>Prolongamento</option>
                                                    <option>Quadra</option>
                                                    <option>Quinta</option>
                                                    <option>Ramal</option>
                                                    <option>Rampa</option>
                                                    <option>Recanto</option>
                                                    <option>Residencial</option>
                                                    <option>Reta</option>
                                                    <option>Retiro</option>
                                                    <option>Retorno</option>
                                                    <option>Rodo Anel</option>
                                                    <option>Rodovia</option>
                                                    <option>Rotatória</option>
                                                    <option>Rótula</option>
                                                    <option>Rua</option>
                                                    <option>Rua de Ligação</option>
                                                    <option>Rua de Pedestre</option>
                                                    <option>Rua Particular</option>
                                                    <option>Rua Principal</option>
                                                    <option>Rua Projetada</option>
                                                    <option>Rua Velha</option>
                                                    <option>Ruela</option>
                                                    <option>Servidão</option>
                                                    <option>Setor</option>
                                                    <option>Sítio</option>
                                                    <option>Subida</option>
                                                    <option>Terminal</option>
                                                    <option>Travessa</option>
                                                    <option>Travessa Particular</option>
                                                    <option>Trecho</option>
                                                    <option>Trevo</option>
                                                    <option>Túnel</option>
                                                    <option>Unidade</option>
                                                    <option>Vala</option>
                                                    <option>Vale</option>
                                                    <option>Vereda</option>
                                                    <option>Via</option>
                                                    <option>Via Coletora</option>
                                                    <option>Via Costeira</option>
                                                    <option>Via de Acesso</option>
                                                    <option>Via de Pedestre</option>
                                                    <option>Via de Pedestres</option>
                                                    <option>Via Expressa</option>
                                                    <option>Via Lateral</option>
                                                    <option>Via Litoranea</option>
                                                    <option>Via Local</option>
                                                    <option>Via Pedestre</option>
                                                    <option>Via Principal</option>
                                                    <option>Viaduto</option>
                                                    <option>Viela</option>
                                                    <option>Vila</option>
                                                    <option>Zigue-Zague</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="input-group">
                                                <input type="text" maxlength="" class="form-control" name="ENDERECO" id="endereco" placeholder="Endereço...">
                                            </div>
                                        </div>
                                        <label for="numero" class="col-1 control-label text-right label-center">Número</label>
                                        <div class="col-2">
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="NUMERO" id="numero" placeholder="Número...">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="bairro" class="col-2 control-label text-right label-center">Bairro</label>
                                        <div class="col-4">
                                            <div class="input-group">
                                                <input type="text" maxlength="" class="form-control" name="BAIRRO" id="bairro" placeholder="Bairro...">
                                            </div>
                                        </div>
                                        <label for="complemento" class="col-2 control-label text-right label-center">Complemento</label>
                                        <div class="col-4">
                                            <div class="input-group">
                                                <input type="text" maxlength="" class="form-control" name="COMPLEMENTO" id="complemento" placeholder="Complemento...">
                                            </div>
                                        </div>
                                    </div>     

                                    <div class="form-group row">
                                        <label for="cidade" class="col-2 control-label text-right label-center">Cidade</label>
                                        <div class="col-5">
                                            <div class="input-group">
                                                <input type="text" maxlength="" class="form-control" name="CIDADE" id="cidade" placeholder="Cidade..." required="">
                                            </div>
                                        </div>
                                        <label for="uf_pessoa" class="col-1 control-label text-right label-center">Estado</label>
                                        <div class="col-4">
                                            <div class="input-group">
                                                <select class="form-control" name="UF" id="uf_pessoa" required="">
                                                    <option value="" selected="" disabled="">--Selecionar--</option>
                                                    <option value="AC">AC</option>
                                                    <option value="AL">AL</option>
                                                    <option value="AP">AP</option>
                                                    <option value="AM">AM</option>
                                                    <option value="BA">BA</option>
                                                    <option value="CE">CE</option>
                                                    <option value="DF">DF</option>
                                                    <option value="ES">ES</option>
                                                    <option value="GO">GO</option>
                                                    <option value="MA">MA</option>
                                                    <option value="MT">MT</option>
                                                    <option value="MS">MS</option>
                                                    <option value="MG">MG</option>
                                                    <option value="PA">PA</option>
                                                    <option value="PB">PB</option>
                                                    <option value="PR">PR</option>
                                                    <option value="PE">PE</option>
                                                    <option value="PI">PI</option>
                                                    <option value="RJ">RJ</option>
                                                    <option value="RN">RN</option>
                                                    <option value="RS">RS</option>
                                                    <option value="RO">RO</option>
                                                    <option value="RR">RR</option>
                                                    <option value="SC">SC</option>
                                                    <option value="SP">SP</option>
                                                    <option value="SE">SE</option>
                                                    <option value="TO">TO</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div> 

                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">  
                                    
                                </form>
                            </div>

                            <div class="tab-pane" id="tabContatos" role="tabpanel" style="height: 100%;">
                                <div id="datatable-pessoa-contato" class="naj-datatable" style="height: 100%;"></div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>

            <div class="card-footer-naj">
                <div class="row">
                    <div class="col-2">
                        <!--Div somente para manter o layout do modal-->
                    </div>
                    <div class="col-10">
                        <button type="button" id="gravarPessoa" class="btn btnLightCustom" title="Gravar">
                            <i class="fas fa-save"></i>
                            Gravar
                        </button>
                        <button type="button" class="btn btnLightCustom" onclick="novoRegistroPessoa();" title="Novo">
                            <i class="fas fa-plus"></i>
                            Novo
                        </button>
                        <button type="button" class="btn btnLightCustom" onclick="$('#modal-manutencao-pessoa').modal('hide'); " title="Fechar">
                            <i class="fas fa-times"></i>
                            Fechar
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>