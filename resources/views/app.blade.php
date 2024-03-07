<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="https://eideas.io/red1-fotor-bg-remover-20230614133716.png">
        <title>lift eideas</title>
        @laravelPWA
        @vite('resources/css/app.css')
    </head>
    <body style="background-color:#dbdbdb59 ">
        <div id="app"></div>
        @vite('resources/js/app.js')
    </body>
</html>
<style>
    .swal2-backdrop-show {
        z-index: 1000000;
    }
    * {
        white-space: normal !important;
    }
</style>
