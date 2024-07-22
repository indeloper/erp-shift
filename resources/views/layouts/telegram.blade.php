<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">

    <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
    >
    <meta
            http-equiv="X-UA-Compatible"
            content="ie=edge"
    >

    <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
            crossorigin="anonymous"
    >

    <title>@yield('title', 'TEST')</title>

    @routes


    @if(config('telegram-webapp.enabled'))
        <script
                id="telegram-webapp-script"
                src="{{config('telegram-webapp.webAppScriptLocation')}}"
        ></script>
    @endif

</head>
<body>
<div class="container">
    @yield('content')
</div>
@stack('scripts')
<script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"
></script>
@stack('scripts_after')
</body>
</html>
