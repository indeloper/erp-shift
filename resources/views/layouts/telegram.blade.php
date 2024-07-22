<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">

    <meta
            name="viewport"
            content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
    >
    <meta
            http-equiv="X-UA-Compatible"
            content="ie=edge"
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
<body class="bg-dark">
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
