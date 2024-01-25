@extends('najWeb.viewBase')

@section('title', 'Upload Logo')

@section('css')
    <link href="{{ env('APP_URL') }}ampleAdmin/assets/libs/dropzone/dist/min/dropzone.min.css" rel="stylesheet">
@endsection

@section('content')

<div id="bloqueio-atualizar-dados" class="loader loader-default" data-half></div>
<div class="row row-content-perfil">
    <div class="col-5 mr-0 pr-0">
        <div class="row content-pai-perfil position-relative content-alterar-perfil">
            <div class="col-12 content-header-perfil">
                <p>ALTERA LOGO DA ADVOCACIA</p>
            </div>
            <div class="col-12 content-body-perfil scrollable">
                <div class="row">
                    <div class="col-12">
                        <div id="loading-anexo-chat" class="loader loader-default" data-half></div>
                        <div class="col-12">
                            <div class="table table-striped files" id="previews">
                                <div id="template" class="file-row">
                                    <div class="row" style="align-items: center;">
                                        <div class="col-5">
                                            <p class="name" data-dz-name></p>
                                            <strong class="error text-danger" data-dz-errormessage></strong>
                                        </div>
                                        <div class="col-3">
                                            <p class="size" data-dz-size></p>
                                        </div>
                                        <div class="col-4">
                                            <button data-dz-remove class="btn btn-danger cancel">
                                                <i class="fas fa-ban mr-1"></i><span>Cancelar</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="_method" value="POST">
                            <input type="hidden" id="token" name="_token" value="{{ csrf_token() }}">
                            <meta name="csrf-token" content="{{ csrf_token() }}" />
                        </div>
                    </div>
                    <div class="col-12" style="position: absolute; bottom: 15%;">
                        <button type="button" class="btn btn-success" onclick="onClickSendAnexoChat();"><i class="fas fa-paper-plane mr-1"></i>Enviar</button>
                        <button type="button" class="btn btn-danger" onclick="onClickCancelarAnexos();"><i class="fas fa-times mr-1"></i>Cancelar</button>
                        <button type="button" class="btn btn-info fileinput-button" style="position: absolute; right: 1%;"><i class="fas fa-paperclip mr-1"></i></i>Anexar Arquivos</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-5 ml-0 pl-0">
        <div class="row content-pai-perfil position-relative content-alterar-senha">
            <div class="col-12 content-header-perfil">
                <p>LOGO DA ADVOCACIA</p>
            </div>
            <div class="col-12 content-body-perfil scrollable content-body-senha">
                <div class="row">
                    <div class="col-12 box-alterar-senha-perfil">
                        <div class="d-flex align-items-center justify-content-center">
                            <img src="C:/dwr/apache24/htdocs/naj-cliente/public/imagens/logo_escritorio.png" alt="logo-cliente" class="dark-logo" style="height: 212px; width: 250px;"/>
                        </div>
                        <div class="mt-4 d-flex align-items-center justify-content-center">
                            <div class="ml-4">
                                <h3 class="font-medium" id="nomeEmpresa">NAJ SISTEMAS EM INFORMÁTICA LTDA</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script src="{{ env('APP_URL') }}ampleAdmin/assets/libs/dropzone/dist/min/dropzone.min.js"></script>
    <script src="{{ env('APP_URL') }}js/perfilUsuario.js"></script>
    <script>
        //Configuração do UPLOAD
        Dropzone.autoDiscover = false;

        Dropzone.prototype.filesize = function(size) {
            var selectedSize = Math.round(size / 1024);
            return "<strong>" + selectedSize + "</strong> KB";
        };

        var previewNode = document.querySelector("#template");
        previewNode.id = "";
        var previewTemplate = previewNode.parentNode.innerHTML;
        previewNode.parentNode.removeChild(previewNode);

        var myDropzone = new Dropzone(document.body, {
            url: `${baseURL}chat/mensagem/anexo`,
            thumbnailWidth: 80,
            thumbnailHeight: 80,
            parallelUploads: 5,
            previewTemplate: previewTemplate,
            autoQueue: false,
            previewsContainer: "#previews",
            clickable: ".fileinput-button",
            dictFileSizeUnits: 'b'
        });

    </script>
@endsection