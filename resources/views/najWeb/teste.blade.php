<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.js"></script>
    <script>

        $(document).ready(function() {
            const request = {
                accepts: 'application/json', 
                url, 'http://192.168.0.106/auth/login',
                type: 'get', 
                xhrFields: {
                    withCredentials: true,
                },
            };

            const url = 'http://192.168.0.106/auth/login';

            document.getElementById('iframeNaj').contentWindow.postMessage(JSON.stringify({request}), url);
            
            //Verifica se o formulário foi submetido
            $('#loginform').submit(function(e) {
                /*
                debugger;
                e.preventDefault();

                /*api = axios.create({
                    headers: {
                        'Content-Type' : 'application/json',
                        'Accept'        : 'application/json'
                    }
                });

                let dados = {
                    "login": $('#login').val(),
                    "password": $('#password').val()
                };
                validaLogin(dados);*/
            });

        });

        async function validaLogin(dados) {
            let result = await api.post('http://192.168.0.106/auth/login?is_nelson=1', dados);

            $('.formulario').hide();
            $('.iframeNaj').show();
            $('.iframeNaj').append('<iframe id="iframeNaj" src="http://192.168.0.106/naj/testeIframe" style="min-width: 100%; min-height: 96vh;"></iframe>');
            // console.log(result);
        }
    </script>
</head>
<body>

    <!--<div class="formulario">
        <form class="form-horizontal mt-3" id="loginform">
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fas fa-user"></i></span>
                </div>
                <input type="text" name="login" id="login" class="form-control form-control-lg" placeholder="Login" aria-label="Login" aria-describedby="basic-addon1">
            </div>
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon2"><i class="fas fa-key"></i></span>
                </div>
                <input type="password" name="password" id="password" class="form-control form-control-lg" placeholder="Senha" aria-label="Password" aria-describedby="basic-addon1">
            </div>
            <div class="form-group text-center">
                <div class="col-xs-12 pb-3">
                    <button class="btn btn-block btn-lg btn-entrar" type="submit">Entrar</button>
                </div>
            </div>
    
            <input type="hidden" name="_method" value="POST">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
        </form>
    </div>-->

    <div class="iframeNaj">

    </div>

    <iframe id="iframeNaj" src="http://192.168.0.106/auth/login" style="min-width: 100%; min-height: 96vh;"></iframe>
    
</body>
</html>