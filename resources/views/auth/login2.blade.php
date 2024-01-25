<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <input type="hidden" name="_csrf_token" id="_csrf_token" value="{{ csrf_token() }}">

    redirecionando...

    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    
    <script>
        window.onload = function() {
            const csrf_token = byId('_csrf_token');


        }

        function byId(id) {
            return document.getElementById(id);
        }
    </script>
</body>
</html>