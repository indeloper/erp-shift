<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="utf-8"/>
    <link rel="apple-touch-icon" sizes="76x76" href="{{ mix('img/apple-icon.png') }}">
    <link rel="icon" type="image/ico" href="{{ mix('img/favicon.ico') }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <title>@yield('title')</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no'
          name='viewport'/>
    <!--     Fonts and icons     -->
    <link href="{{ mix('css/font-awesome.min.css') }}" rel="stylesheet"/>
    <link href="{{ mix('css/v4-shims.min.css') }}" rel="stylesheet"/>
    <link href="{{ mix('css/google_fonts.css') }}" rel="stylesheet"/>

    <link href="{{ mix('css/pe-icon-7-stroke.css') }}" rel="stylesheet"/>
    <!-- CSS Files -->
    <link href="{{ mix('css/bootstrap.min.css') }}" rel="stylesheet"/>
    <link href="{{ mix('css/balloon.css') }}" rel="stylesheet"/>
    <link href="{{ mix('css/light-bootstrap-dashboard.css') }}" rel="stylesheet"/>

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link href="{{ mix('css/demo.css') }}" rel="stylesheet"/>
    <link href="{{ mix('css/additionally.css') }}" rel="stylesheet"/>
    <link href="{{ mix('css/tech.css') }}" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700&display=swap" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css?family=Montserrat:300,300i,400,400i,500,500i,600,700,800,900&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="{{ mix('css/index.css') }}">
    <link rel="stylesheet" href="{{ mix('css/emojione.css') }}">
    <link rel="stylesheet" href="{{ mix('css/emojionearea.min.css') }}">
    <link rel="stylesheet" href="{{ mix('css/main.css') }}">
    <!-- editable table -->
    <link href="https://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootswatch/4.1.3/darkly/bootstrap.min.css"> -->

    <!-- lightgalleryjs.com  -->
    <link type="text/css" rel="stylesheet" href="{{ asset('css/lightgallery/lightgallery.css')}}"/>
    <link type="text/css" rel="stylesheet" href="{{ asset('css/lightgallery/lg-thumbnail.css')}}"/>
    <link type="text/css" rel="stylesheet" href="{{ asset('css/lightgallery/lg-zoom.css')}}"/>
    <link type="text/css" rel="stylesheet" href="{{ asset('css/lightgallery/lg-rotate.css')}}"/>
    <link type="text/css" rel="stylesheet" href="{{ asset('css/lightgallery/lightgallery-bundle.css')}}"/>


    <meta name="csrf-token" content="{{ csrf_token() }}"/>


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

<body @if(Session::has('sidebar_mini')) @if(Session::get('sidebar_mini')) class="sidebar-mini" @endif @endif>
<div class="wrapper">
    <div class="sidebar"
         @if (config("app.env") == "local")
             data-color="red"
         @elseif (config("app.env") == "local_dev")
             data-color="orange"
         @else
             data-color="purple"
         @endif

         data-image="{{ mix('img/full-screen-image-2.jpg') }}">
        <!-- data-color="purple | blue | green | orange | red" -->

        <div class="logo">
            <a class="simple-text logo-mini" href="{{ route('tasks::index') }}">
                <img src="{{ mix('img/logo-mini.png') }}" width="30">
            </a>
            <a class="simple-text logo-normal" href="{{ route('tasks::index') }}">
                <img src="{{ mix('img/logo-normal.png') }}" width="85">
            </a>
        </div>

        @include('layouts.shared.menu')



    </div>
    <div class="main-panel">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg ">
            <div class="container-fluid">
                <div class="navbar-wrapper">
                    <div class="navbar-minimize">
                        <button id="minimizeSidebar"
                                class="btn btn-warning btn-fill btn-round btn-icon d-none d-lg-block">
                            <i class="fa fa-ellipsis-v visible-on-sidebar-regular"></i>
                            <i class="fa fa-navicon visible-on-sidebar-mini"></i>
                        </button>
                    </div>
                    <a class="navbar-brand" href=@yield('url')>@yield('title')</a>
                </div>
                <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse"
                        aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-bar burger-lines"></span>
                    <span class="navbar-toggler-bar burger-lines"></span>
                    <span class="navbar-toggler-bar burger-lines"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-end">
                    <ul class="nav navbar-nav mr-auto">
                    </ul>
                    <ul class="navbar-nav">
                        <li class="dropdown nav-item">
                            <a id="messages" target="_blank" href="{{ route('messages::index') }}" class="nav-link">
                                <i class="nc-icon nc-chat-round"></i>
                                <span class="d-lg-none">Диалоги</span>
                                <span-message-count v-bind:messages="messages"></span-message-count>
                            </a>
                        </li>
                        <li class="dropdown nav-item">
                            <a id="main" href="{{ route('notifications::index') }}" class="nav-link">
                                <i class="nc-icon nc-bell-55"></i>
                                <span class="d-lg-none">Оповещения</span>
                                <span-notify v-bind:notify="notify"></span-notify>
                            </a>
                        </li>
                        <li class="dropdown nav-item support-letter">
                            <a href="#" onclick="modalWork()" data-original-title="Написать в тех. поддержку"
                               class="nav-link">
                                <i class="nc-icon nc-send"></i>
                                <span class="d-lg-none">Написать в тех. поддержку</span>
                            </a>
                        </li>
                        <li class="dropdown nav-item">
                            <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                                {{ Auth::user()->last_name }} {{ Auth::user()->first_name }} {{ Auth::user()->patronymic }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <a href="{{ route('users::card', Auth::user()->id) }}" class="dropdown-item">
                                    <i class="nc-icon nc-single-02"></i> Профиль
                                </a>
                                <!-- <a id="messages" target="_blank" href="{{ route('messages::index') }}" class="dropdown-item" style="position:relative">
                                        <i class="nc-icon nc-chat-round"></i>
                                        <span>Диалоги</span>
                                        <span-message-count v-bind:messages="messages" style="top:5px"></span-message-count>
                                    </a> -->
                                <a href="{{ route('logout') }}" class="dropdown-item text-exit">
                                    <i class="nc-icon nc-button-power"></i> Выйти
                                </a>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- End Navbar -->

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
<!--   Core JS Files   -->
<script src="{{ mix('js/core/jquery.3.2.1.min.js') }}" type="text/javascript"></script>
<script src="{{ mix('js/jquery.table.js') }}" type="text/javascript"></script>
<script src="{{ mix('js/core/popper.min.js') }}" type="text/javascript"></script>
<script src="{{ mix('js/core/bootstrap.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/common.js') }}"></script>

<script src="{{ mix('js/plugins/bootstable.js') }}"></script>
<!--  Plugin for Switches, full documentation here: http://www.jque.re/plugins/version3/bootstrap.switch/ -->
<script src="{{ mix('js/plugins/bootstrap-switch.js') }}"></script>
<!--  Google Maps Plugin    -->
<!-- <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?YOUR_KEY_HERE"></script> -->
<!--  Chartist Plugin  -->
<script src="{{ mix('js/plugins/chartist.min.js') }}"></script>
<!--  Notifications Plugin    -->
<script src="{{ mix('js/plugins/bootstrap-notify.js') }}"></script>
<!--  jVector Map  -->
<script src="{{ mix('js/plugins/jquery-jvectormap.js') }}" type="text/javascript"></script>
<!--  Plugin for Date Time Picker and Full Calendar Plugin-->
<script src="{{ mix('js/plugins/moment.min.js') }}"></script>
<script>moment.locale('ru');</script>
<!--  DatetimePicker   -->
<script src="{{ mix('js/plugins/bootstrap-datetimepicker.js') }}"></script>
<!--  Sweet Alert  -->
<script src="{{ mix('js/plugins/sweetalert2.all.min.js') }}" type="text/javascript"></script>
<!--  Tags Input  -->
<script src="{{ mix('js/plugins/bootstrap-tagsinput.js') }}" type="text/javascript"></script>
<!--  Sliders  -->
<script src="{{ mix('js/plugins/nouislider.js') }}" type="text/javascript"></script>
<!--  Bootstrap Select  -->
<script src="{{ mix('js/plugins/bootstrap-selectpicker.js') }}" type="text/javascript"></script>
<!--  jQueryValidate  -->
<script src="{{ mix('js/plugins/jquery.validate.min.js') }}" type="text/javascript"></script>
<!--  Plugin for the Wizard, full documentation here: https://github.com/VinceG/twitter-bootstrap-wizard -->
<script src="{{ mix('js/plugins/jquery.bootstrap-wizard.js') }}"></script>
<!--  Bootstrap Table Plugin -->
<script src="{{ mix('js/plugins/bootstrap-table.js') }}"></script>
<!--  DataTable Plugin -->
<script src="{{ mix('js/plugins/jquery.dataTables.min.js') }}"></script>
<!--  Full Calendar   -->
<script src="{{ mix('js/plugins/fullcalendar.min.js') }}"></script>
<!-- Control Center for Now Ui Dashboard: parallax effects, scripts for the example pages etc -->
<script src="{{ mix('js/light-bootstrap-dashboard.js') }}" type="text/javascript"></script>
<!-- Select2 Essentials and Localization -->
<script src="{{ asset('js/select2.js') }}" type="text/javascript"></script>
<!-- Light Dashboard DEMO methods, don't include it in your project! -->
<script src="{{ mix('js/demo.js') }}"></script>
<script src="{{ mix('js/plugins/bootstrap-table-mobile.js') }}"></script>
<!--Main scripts-->
<script src="{{ mix('js/modal-window.js') }}"></script>
<!-- Validation-form-ru-locale -->
<script src="{{ mix('js/form-validation.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/plugins/jquery.mask.min.js') }}"></script>
<!-- AutoNumeric -->
<script src="{{ asset('js/plugins/autonumeric.min.js') }}"></script>

<!-- Fix Multi Submit -->
<script src="{{ mix('js/fixMultiSubmit.js') }}"></script>

<!-- Vue and Pusher to get fast Notifications -->

{{--<script src="https://js.pusher.com/4.4/pusher.min.js"></script>--}}
<script src="{{ asset('js/plugins/pusher_and_vue.js') }}"></script>

<!-- import JavaScript -->
<script src="{{ asset('js/vue.js') }}"></script>
<script src="{{ asset('js/elementui.js') }}"></script>

<!-- fix select search-->
<script src="{{ mix('js/vue-router.js') }}"></script>
<script src="{{ mix('js/plugins/vee-validate.js') }}" type="text/javascript"></script>
<script src="{{ mix('js/validation-rules.js') }}" type="text/javascript"></script>
<!--  -->

<script src="{{ mix('js/axios.min.js') }}"></script>
<script src="{{ asset('js/ru-RU.js') }}"></script>

{{-- <!-- import FontAwesome (fresh version of icons) -->
<script src="{{ asset('js/92abca6220.js') }}"></script> --}}
<!-- import Lodash -->
<script src="{{ asset('js/plugins/lodash.js') }}"></script>
<script src="{{ asset('js/plugins/download.js') }}"></script>


<script src="{{ asset('js/plugins/emojione.js') }}"></script>
<script src="{{ asset('js/plugins/emojionearea.min.js') }}"></script>


<!-- DevExtreme themes -->
@if (
        Request::is('project-object-documents')
        || Request::is('objects')
        || Request::is('building/tech_acc/technic*')
        || Request::is('building/tech_acc/fuel*')
        || Request::is('timesheet/*')
        || Request::is('materials/operations/all')
    )
    <link rel="stylesheet" href="{{ asset('css/devextreme/dx.sk.generic.light.css')}}">
    <link rel="stylesheet" href="{{ asset('css/devextreme/dx.generic.light.css')}}">
@else
    <link rel="stylesheet" href="{{ asset('css/devextreme/dx.material.blue.light.compact.css')}}">
@endif



<!-- DevExtreme library -->
<script src="https://unpkg.com/devextreme-quill@1.5.16/dist/dx-quill.min.js"></script>
<script type="text/javascript" src="{{ asset('js/devextreme/dx.all.js')}}"></script>
<script type="text/javascript" src="{{ asset('js/devextreme/dx.messages.ru.js')}}"></script>

<!-- SKG DevExtreme-inherited -->
<script type="text/javascript" src="{{ asset('js/devextreme/skgDataGrid.js')}}"></script>

<!-- DevExtreme default-settings -->
<script type="text/javascript" src="{{ asset('js/devextreme/default-settings.js')}}"></script>

<!-- lightgalleryjs.com -->
<script type="text/javascript" src="{{ asset('js/lightgallery/lightgallery.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('js/lightgallery/lg-thumbnail.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('js/lightgallery/lg-zoom.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('js/lightgallery/lg-rotate.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('js/lightgallery/lg-video.min.js')}}"></script>

<script type="text/javascript" src="{{ asset('js/layout.js')}}"></script>

<!-- END lightgalleryjs.com -->


<meta name="csrf-token" content="{{ csrf_token() }}"/>


<script>
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    ELEMENT.locale(ELEMENT.lang.ruRU);

    DevExpress.localization.locale(navigator.language);
    DevExpress.config({
        editorStylingMode: "outlined",
        forceIsoDateParsing: true
    })

    function iOS() {
        return [
                'iPad Simulator',
                'iPhone Simulator',
                'iPod Simulator',
                'iPad',
                'iPhone',
                'iPod'
            ].includes(navigator.platform)
            // iPad on iOS 13 detection
            || (navigator.userAgent.includes("Mac") && "ontouchend" in document)
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
            type: 'get'
        }).done(function (data) {
            csrfToken = data;
        });
    }, 60 * 60 * 1000);

    Vue.component('span-notify', {
        props: ['notify'],
        template: '<span class="notification" v-if="notify">@{{ notify }}</span>'
    });

    let notify = new Vue({
        el: '#main',

        data: {
            notify: {{ $notifications }}
        }
    });

    $(document).on('submit', 'form', function (event) {
        var form = $(event.currentTarget);
        if (form.hasClass('axios')) return;
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
                })
            }
        }
    });

    $('input').attr('autocomplete', 'off');

    var pusher = new Pusher('13460ea482ed2f33ee58', {
        cluster: 'eu',
        forceTLS: true
    });

    var channel = pusher.subscribe('{{config('app.env')}}' + '.App.User.' + {{ Auth::user()->id }});
    channel.bind('App\\Events\\NotificationCreated', function (response) {
        notify.notify = response['notifications'];
    });

    Vue.component('span-message-count', {
        props: ['messages'],
        template: '<span class="notification" v-if="messages">@{{ messages }}</span>'
    });

    var message_notifications = new Vue({
        el: '#messages',
        data: {
            messages: {{ $messages }}
        }
    });

    channel.bind('message-stored', function (data) {
        // thread not currently opened => create notification
        $.ajax({
            url: '{{ route('messages::message_info') }}',
            type: 'POST',
            data: {
                _token: CSRF_TOKEN,
                message_id: data.messageData.message_id
            },
            dataType: 'JSON',
            success: function (response) {
                var message = '<strong>' + response.sender_name + ': </strong><br>' + response.text + (response.text.length > 50 ? '...' : '') + '<br><a href="' + response.thread_url + '" class="text-right">Просмотреть</a>';
                var thread_subject = response.thread_subject;

                // notify user
                message_notifications.$notify({
                    dangerouslyUseHTMLString: true,
                    message: 'Новое сообщение' + (thread_subject ? ' из чата ' + thread_subject : '') + '. ' + '<br>' + message,
                    type: 'info',
                    duration: 5000
                });
            }
        });

        message_notifications.messages = data.messagesCount;
    });

    channel.bind('message-deleted', function (data) {
        // -1 because event overtake message deleting
        message_notifications.messages = data.messagesCount - 1;
    });

    $(document).ready(function () {
        $('a[href$="mat_acc/operations"]').each(function () {
            const cookies = document.cookie.split(';').filter(el => el.indexOf('opsource') !== -1);
            if (cookies.length > 0) {
                $(this).attr("href", decodeURIComponent(cookies[0].split('=')[1]));
            }
        });
    });
</script>

@yield('js_footer')
@stack('js_footer')
@include('sections.yandex_metrika')

</html>
