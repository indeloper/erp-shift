<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="utf-8"/>
    <link
        rel="apple-touch-icon"
        sizes="76x76"
        href="{{ asset('img/apple-icon.png') }}"
    >
    <link
        rel="icon"
        type="image/ico"
        href="{{ asset('img/favicon.ico') }}"
    >
    <meta
        http-equiv="X-UA-Compatible"
        content="IE=edge,chrome=1"
    />
    <title>@yield('title')</title>

    @routes

    @vite(['resources/assets/css/app.css', 'resources/assets/js/app.js'])


    <meta
        content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no'
        name='viewport'
    />
    <!--     Fonts and icons     -->

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    />


    @yield('css_top')
    <style media="screen">
        @media (min-width: 1025px) {
            .sidebar-m {
                padding-bottom: 50px !important;
                display: flex;
                flex-direction: column;
            }
        }

        .sidebar-m {
            overflow-x: hidden !important;
        }

        .nav-m {
            float: none;
            display: block;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            padding-left: 0;
            margin-bottom: 0;
            list-style: none;
        }

        .sidebar .nav-m .nav-item .nav-link {
            color: #FFFFFF;
            margin: 5px 15px 0px 10px;
            opacity: .86;
            border-radius: 4px;
            text-transform: uppercase;
            line-height: 30px;
            font-size: 12px;
            font-weight: 600;
            padding: 10px 15px;
            white-space: nowrap;
        }

        .sidebar .nav-m a, .table > tbody > tr .td-actions .btn {
            -webkit-transition: all 150ms ease-in;
            -moz-transition: all 150ms ease-in;
            -o-transition: all 150ms ease-in;
            -ms-transition: all 150ms ease-in;
            transition: all 150ms ease-in;
        }

        .sidebar .nav-m .nav-item .nav-link i {
            font-size: 28px;
            margin-right: 15px;
            width: 30px;
            text-align: center;
            vertical-align: middle;
            float: left;
        }

        .sidebar .sidebar-wrapper .nav-m .nav-link p {
            margin: 0;
            line-height: 30px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            white-space: nowrap;
            position: relative;
            color: #FFFFFF;
            -webkit-transform: translate3d(0px, 0, 0);
            -moz-transform: translate3d(0px, 0, 0);
            -o-transform: translate3d(0px, 0, 0);
            -ms-transform: translate3d(0px, 0, 0);
            transform: translate3d(0px, 0, 0);
            display: block;
            height: auto;
            opacity: 1;
        }

        .sidebar .nav-m .nav-item .nav-link:hover {
            background: rgba(255, 255, 255, 0.13);
            opacity: 1;
        }

    </style>
</head>

<body
    @if(Session::has('sidebar_mini') && Session::get('sidebar_mini')) class="sidebar-mini dx-viewport"
    @else class="dx-viewport" @endif>
<div class="wrapper">
    <div
        class="sidebar"
        @if (config("app.env") == "local")
            data-color="red"
        @elseif (config("app.env") == "remote-dev")
            data-color="orange"
        @else
            data-color="purple"
        @endif

        data-image="{{ asset('img/full-screen-image-2.jpg') }}"
    >
        <!-- data-color="purple | blue | green | orange | red" -->

        <div class="logo">
            <a
                class="simple-text logo-mini"
                href="{{ route('tasks::index') }}"
            >
                <img
                    src="{{ mix('img/logo-mini.png') }}"
                    width="30"
                >
            </a>
            <a
                class="simple-text logo-normal"
                href="{{ route('tasks::index') }}"
            >
                <img
                    src="{{ asset('img/logo-normal.png') }}"
                    width="85"
                >
            </a>
        </div>

        @include('layouts.shared.menu')


    </div>
    <div class="main-panel">

        @include('layouts.shared.header')

        <div class="content">
            <div class="container-fluid pd-0-360">
                @yield('content')

                @include('support.support_modal')
            </div>
        </div>

        @include('layouts.shared.footer')

    </div>
</div>
</body>
<!-- DevExtreme themes -->
@if (
        Request::is('project-object-documents')
        || Request::is('objects')
        || Request::is('projects')
        || Request::is('building/tech_acc/technic*')
        || Request::is('building/tech_acc/fuel*')
        || Request::is('timesheet/*')
        || Request::is('materials/operations/all')
    )
    <link
        rel="stylesheet"
        href="{{ asset('css/devextreme/dx.sk.generic.light.css')}}"
    >
    <link
        rel="stylesheet"
        href="{{ asset('css/devextreme/dx.generic.light.css')}}"
    >
@else
    <link
        rel="stylesheet"
        href="{{ asset('css/devextreme/dx.material.blue.light.compact.css')}}"
    >
@endif



<!-- DevExtreme library -->
<script src="https://unpkg.com/devextreme-quill@1.5.16/dist/dx-quill.min.js"></script>


<meta
    name="csrf-token"
    content="{{ csrf_token() }}"
/>


<script type="module">
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    ELEMENT.locale(ELEMENT.lang.ruRU);

    DevExpress.localization.locale(navigator.language);
    DevExpress.config({
        editorStylingMode: 'outlined',
        forceIsoDateParsing: true,
    });

    function iOS() {
        return [
                'iPad Simulator',
                'iPhone Simulator',
                'iPod Simulator',
                'iPad',
                'iPhone',
                'iPod',
            ].includes(navigator.platform)
            // iPad on iOS 13 detection
            || (navigator.userAgent.includes('Mac') && 'ontouchend' in document);
    }

    if (iOS()) {
        ELEMENT.Select.computed.readonly = function () {
            // trade-off for IE input readonly problem: https://github.com/ElemeFE/element/issues/10403
            const isIE = !this.$isServer && !Number.isNaN(Number(document.documentMode));

            return !(this.filterable || this.multiple || !isIE) && !this.visible;
        };
    }

    setInterval(function () {
        var csrfToken = $('[name="csrf-token"]').val();
        $.ajax({
            url: '{{ route('get-new-csrf') }}',
            type: 'get',
        }).done(function (data) {
            csrfToken = data;
        });
    }, 60 * 60 * 1000);

    $(document).on('submit', 'form', function (event) {
        var form = $(event.currentTarget);
        if (form.hasClass('axios')) {
            return;
        }
        if (form.find('[type="file"]').length > 0) {

            var show_swal = false;

            form.find('[type="file"]').each(function () {
                if ($(this)[0].files.length > 0) {
                    show_swal = true;
                }
            });
            if (show_swal) {
                swal.fire({
                    title: 'Идет загрузка',
                    html:
                        '<div class="fa-4x">\n' +
                        '<i class="fa fa-spinner fa-spin" style="width: auto"></i>\n' +
                        '</div>',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showConfirmButton: false,
                });
            }
        }
    });

    $('input').attr('autocomplete', 'off');

    $(document).ready(function () {
        $('a[href$="mat_acc/operations"]').each(function () {
            const cookies = document.cookie.split(';').filter(el => el.indexOf('opsource') !== -1);
            if (cookies.length > 0) {
                $(this).attr('href', decodeURIComponent(cookies[0].split('=')[1]));
            }
        });
    });
</script>

@yield('js_footer')
@stack('js_footer')
@include('sections.yandex_metrika')

</html>
