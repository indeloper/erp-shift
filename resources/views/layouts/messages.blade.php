<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="utf-8" />
    <link rel="apple-touch-icon" sizes="76x76" href="{{ mix('img/apple-icon.png') }}">
    <link rel="icon" type="image/ico" href="{{ mix('img/favicon.ico') }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>@yield('title')</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
    <!--     Fonts and icons     -->
    <link href="{{ mix('css/font-awesome.min.css') }}" rel="stylesheet" />
    <link href="{{ mix('css/v4-shims.min.css') }}" rel="stylesheet" />
    <link href="{{ mix('css/google_fonts.css') }}" rel="stylesheet" />

    <link href="{{ mix('css/pe-icon-7-stroke.css') }}" rel="stylesheet" />
    <!-- CSS Files -->
    <link href="{{ mix('css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ mix('css/light-bootstrap-dashboard.css') }}" rel="stylesheet" />
    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link href="{{ mix('css/demo.css') }}" rel="stylesheet" />
    <link href="{{ mix('css/additionally.css') }}" rel="stylesheet" />
    <link href="{{ mix('css/messages.css') }}" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ mix('css/index.css') }}">
    <link rel="stylesheet" href="{{ mix('css/emojione.css') }}">
    <link rel="stylesheet" href="{{ mix('css/emojionearea.min.css') }}">

    @include('sections.yandex_metrika')

    @yield('css_top')
    <style media="screen">

    html {
        overflow: hidden;
        width: 100%;
    }

    body {
        height: 100%;
        position: fixed;
        /* prevent overscroll bounce*/
        overflow-y: scroll;
        -webkit-overflow-scrolling: touch;
        /* iOS velocity scrolling */
        width: 100%;
    }

    .sidebar {
        width: 30%;
        height: 100vh
        }

    .sidebar .sidebar-wrapper {
        width: 100%
    }

    .main-panel {
        width: calc(100% - 30%);
    }

    @media (min-width: 992px){
        .sidebar-mini .main-panel {
            width: calc(100% );
            margin-left: 80px;
        }

        .sidebar-mini .sidebar {
            width: 0;
        }

        .sidebar-mini .sidebar, .sidebar-mini .main-panel {
            overflow: hidden;
        }
    }

    .sidebar .nav .nav-item .nav-link {
        color: none;
        margin: none;
        opacity: .86;
        border-radius: 4px;
        text-transform: uppercase;
        line-height: 30px;
        font-size: 12px;
        font-weight: 600;
        padding: none;
        white-space: nowrap;
    }

    .nav {
        display: flex!important;
    }

    .nav-item {
        width: 100%
    }

    .sidebar .nav .nav-item .nav-link {
        text-transform: none;
        padding: 0;
    }

    .contacts-item, .dialogs-item {
        width: 50%!important;
    }

    .nav-link {
        display: block!important;
        padding: .5rem 1rem!important;
    }

    .sidebar .nav .nav-item .nav-link {
        margin:0
    }

    .sidebar .sidebar-wrapper {
        padding-bottom: 0;
    }

    .card-footer.chat-form {
        bottom:0;
        width: 100%;
        z-index: 100;
        background-color: white;
    }

    body {
        overflow: hidden;
    }

    .card-header {

    }

    .card-body.chat-content {
        margin-top: 120px;
        overflow: hidden;
        outline: none;
        height: auto;
        overflow-y: auto;
        padding-bottom: 30px;
    }

    .tab-content>.active.second-tab-flex {
        display: flex;
        flex-direction: column;
    }

    .chat-header {
        width: 100%;
    }

    /* sidebar */

    @media (max-width: 991px){
        .main-panel {
            width: 100%!important;
        }

        .sidebar {
            width: 310px;
            z-index: 1;
            right: -50px !important;
        }

        .nav.nav-mobile-menu {
            display: none!important;
        }

        .logo {
            display: none
        }

        .navbar {
            position: fixed;
            left: 0;
            top:0
        }

        .card-body.chat-content {
            margin-bottom: 90px;
        }
    }

    .s-mnr-50 {
        margin-right: 50px!important;
    }

    .content {
        height: 100vh;
    }

    .navbar {
        position: absolute;
        width: 100%;
        z-index: 100;
        box-shadow: 5px 0px 10px rgba(199, 199, 199, 0.5);
    }

    @media(max-width:350px){
        .sidebar {
            width: 290px;
        }
    }

    /* @media (max-width: 767.9px){
        .content {
            height: calc(100vh - 80px);
        }
    } */

    .wrapper,
    .main-panel {
        background-color: rgb(246, 246, 246);
    }

    /* .close-layer {
        height: 100vh!important;
    } */
    </style>
</head>

<body @if(Session::has('sidebar_mini')) @if(Session::get('sidebar_mini')) class="sidebar-mini" @endif @endif>
    <div class="wrapper">
        <div class="sidebar"

            style="box-shadow: 5px 0px 10px rgba(199, 199, 199, 0.5);"
            data-color="white">
        <!-- data-color="purple | blue | green | orange | red" -->
            <div class="sidebar-wrapper" style="overflow-x: hidden; border-right: 1px solid #dbdbdb">
                <ul class="nav">
                    <li class="nav-item">
                        <div class="row">
                            <div class="col-md-12" style="height:100vh;padding-right: 0;">
                                <div class="card chat-card" style="border: 0;border-radius: 0;margin-bottom:0">
                                    <div class="card-body" style="padding: 15px 35px 10px 20px;">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <form action="{{ route('messages::index') }}">
                                                        <input class="form-control" type="text" value="{{ Request::get('search') }}" name="search" placeholder="Поиск">
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12" style="padding:0">
                                                <ul class="nav nav-pills pull-right" id="myTab3" role="tablist">
                                                    <li class="nav-item contacts-item">
                                                        <a class="nav-link " id="home-tab3" data-toggle="tab" href="#contacts" role="tab" aria-controls="home" aria-selected="false">Архивные диалоги</a>
                                                    </li>
                                                    <li class="nav-item dialogs-item">
                                                        <a class="nav-link active" id="profile-tab3" data-toggle="tab" href="#dialogs" role="tab" aria-controls="profile" aria-selected="true">Диалоги</a>
                                                    </li>
                                                </ul>
                                                <div class="clearfix"></div>
                                                <div class="tab-content" id="myTabContent2" >
                                                    <div class="tab-pane fade " id="contacts" role="tabpanel" aria-labelledby="home-tab3" style="height:100%; padding-bottom:0; padding-top:0">
                                                        <div class="card-body" style="height:80%; padding:0">
                                                            <ul class="list-unstyled list-unstyled-border">
                                                                @if($archived_threads->count())
                                                                    @foreach($archived_threads as $thread)
                                                                        <li class="media">
                                                                            <div class="media-cont">
                                                                                <div class="media-body">
                                                                                    <div class="chat-info">
                                                                                        <div class="chat-name">
                                                                                            {{ $thread->subject ? $thread->subject : ($thread->participantsWithTrashed()->count() >= 3 ? 'Диалог с пользователями' : 'Диалог с ' . $thread->participantsString(Auth::id())) }}
                                                                                        </div>
                                                                                        @if($thread->latest_message)
                                                                                            <div class="last-message-time">
                                                                                                {{ (\Carbon\Carbon::parse($thread->latest_message->created_at)->isToday() ? \Carbon\Carbon::parse($thread->latest_message->created_at)->format('H:i') : \Carbon\Carbon::parse($thread->latest_message->created_at)->format('d.m.Y H:i')) }}
                                                                                            </div>
                                                                                        @endif
                                                                                    </div>
                                                                                    <div class="meta-cont">
                                                                                        @if($thread->latest_message)
                                                                                            <div class="last-message-info">{{
                                                                                                ($thread->latest_message->user->id == Auth::id() ? 'Вы: ' : $thread->latest_message->user->full_name . ':')
                                                                                                . $thread->latest_message->body }}
                                                                                            </div>
                                                                                            @if($thread->userUnreadMessagesCount(Auth::id()))
                                                                                                <div class="message-number">
                                                                                                    {{ $thread->userUnreadMessagesCount(Auth::id()) }}
                                                                                                </div>
                                                                                            @endif
                                                                                            @if(! $thread->hasParticipant(Auth::id()))
                                                                                                <button class="btn btn-round btn-outline btn-sm btn-link" onclick="askToJoin({{ $thread->id }})">
                                                                                                    <i class="glyphicon fa fa-sign-in-alt"></i>
                                                                                                    Вернуться в диалог
                                                                                                </button>
                                                                                            @endif
                                                                                        @else
                                                                                            <div class="last-message-info">В диалоге нет сообщений</div>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </li>
                                                                    @endforeach
                                                                @else
                                                                    <li class="media">
                                                                        <div class="media-cont">
                                                                            Нет архивных диалогов
                                                                        </div>
                                                                    </li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane fade show active second-tab-flex" id="dialogs" role="tabpanel" aria-labelledby="profile-tab3" style="padding-top:0;">
                                                        <div class="card-body" style="padding:0">
                                                            <ul class="list-unstyled list-unstyled-border">
                                                                @if($threads->count())
                                                                    @foreach($threads as $thread)
                                                                        <li class="media li-active-chat list-thread-{{ $thread->id }}" onclick="loadThread({{ $thread->id }}, this)">
                                                                            <div class="media-cont">
                                                                                <div class="media-body">
                                                                                    <div class="chat-info">
                                                                                        <div class="chat-name">
                                                                                            {{ $thread->subject ? $thread->subject : ($thread->participantsWithTrashed()->count() >= 3 ? 'Диалог с пользователями' : 'Диалог с ' . $thread->participantsString(Auth::id())) }}
                                                                                        </div>
                                                                                        @if($thread->latest_message)
                                                                                            <div class="last-message-time">
                                                                                                {{ (\Carbon\Carbon::parse($thread->latest_message->created_at)->isToday() ? \Carbon\Carbon::parse($thread->latest_message->created_at)->format('H:i') : \Carbon\Carbon::parse($thread->latest_message->created_at)->format('d.m.Y H:i')) }}
                                                                                            </div>
                                                                                        @endif
                                                                                    </div>
                                                                                    <div class="meta-cont">
                                                                                        @if($thread->latest_message)
                                                                                            <div class="last-message-info">{{
                                                                                                ($thread->latest_message->user->id == Auth::id() ? 'Вы: ' : $thread->latest_message->user->full_name . ': ')
                                                                                                . $thread->latest_message->body }}
                                                                                            </div>
                                                                                            @if($thread->userUnreadMessagesCount(Auth::id()))
                                                                                                <div class="message-number mess-thread_{{ $thread->id }}">
                                                                                                    {{ $thread->userUnreadMessagesCount(Auth::id()) }}
                                                                                                </div>
                                                                                            @endif
                                                                                        @else
                                                                                            <div class="last-message-info">В диалоге нет сообщений</div>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </li>
                                                                    @endforeach
                                                                @else
                                                                    <li class="media">
                                                                        <div class="media-cont">
                                                                            Диалоги не найдены
                                                                        </div>
                                                                    </li>
                                                                @endif
                                                                <li>
                                                                    <div class="start-chat-cont">
                                                                        <button type="button" name="button" class="btn-start-chat" data-toggle="modal" data-target="#add-new-thread">
                                                                            Начать новый диалог
                                                                        </button>
                                                                    </div>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <div class="card-footer text-center" style="width:100%">

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>

            </div>
        </div>
        <div class="main-panel" >
            <div class="content" style="padding:0">
                <!-- Navbar -->
                <nav class="navbar navbar-expand-lg ">
                    <div class="container-fluid">
                        <div class="navbar-wrapper">
                            <div class="navbar-minimize">
                                <button id="minimizeSidebar" class="btn btn-warning btn-fill btn-round btn-icon d-none d-lg-block">
                                    <i class="fa fa-ellipsis-v visible-on-sidebar-regular"></i>
                                    <i class="fa fa-navicon visible-on-sidebar-mini"></i>
                                </button>
                            </div>
                            <a class="navbar-brand" href=@yield('url')>@yield('title')</a>
                        </div>
                        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-bar burger-lines"></span>
                            <span class="navbar-toggler-bar burger-lines"></span>
                            <span class="navbar-toggler-bar burger-lines"></span>
                        </button>
                        <div class="logo" style="border-right:0">
                            <a class="simple-text logo-mini" href="{{ route('tasks::index') }}">
                                <img src="{{ mix('img/logo-mini.png') }}" width="30">
                            </a>
                            <a class="simple-text logo-normal" href="{{ route('tasks::index') }}">
                                <img src="{{ mix('img/logo-normal.png') }}" width="85">
                            </a>
                        </div>

                    </div>
                </nav>
                <!-- End Navbar -->
                <div class="container-fluid pd-0-360">
                    @yield('content')

                    @include('support.support_modal')
                </div>
            </div>

            <!-- <footer class="footer" style="float: bottom;">
                <nav>
                    <p class="copyright text-center">
                        <span>© {{ date('Y') }}</span>
                        <a href="https://sk-gorod.com">ООО «СК ГОРОД»</a>
                    </p>
                </nav>
                <nav>
                    <p class="copyright text-center">
                        <a id="modal_open" data-toggle="modal" data-target="#support_modal" data-original-title="Написать в тех. поддержку" class="support-modal">Техническая поддержка</a>
                    </p>
                </nav>
            </footer> -->

        </div>
    </div>
</body>
<!--   Core JS Files   -->
<script src="{{ mix('js/core/jquery.3.2.1.min.js') }}" type="text/javascript"></script>
<script src="{{ mix('js/jquery.table.js') }}" type="text/javascript"></script>
<script src="{{ mix('js/core/popper.min.js') }}" type="text/javascript"></script>
<script src="{{ mix('js/core/bootstrap.min.js') }}" type="text/javascript"></script>
<!--  Plugin for Switches, full documentation here: http://www.jque.re/plugins/version3/bootstrap.switch/ -->
<script src="{{ mix('js/plugins/bootstrap-switch.js') }}"></script>
<!--  Google Maps Plugin    -->
<!-- <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?YOUR_KEY_HERE"></script> -->
<!--  Chartist Plugin  -->
<script src="{{ mix('js/plugins/chartist.min.js') }}"></script>
<!--  Notifications Plugin    -->
<script src="{{ mix('js/plugins/bootstrap-notify.js') }}"></script>
<!--  Plugin for Date Time Picker and Full Calendar Plugin-->
<script src="{{ mix('js/plugins/moment.min.js') }}"></script>
<!--  Sweet Alert  -->
<script src="{{ mix('js/plugins/sweetalert2.all.min.js') }}" type="text/javascript"></script>
<!--  Bootstrap Table Plugin -->
<script src="{{ mix('js/plugins/bootstrap-table.js') }}"></script>
<!-- Control Center for Now Ui Dashboard: parallax effects, scripts for the example pages etc -->
<script src="{{ mix('js/light-bootstrap-dashboard.js') }}" type="text/javascript"></script>
<!-- Select2 Essentials and Localization -->
<script src="{{ asset('js/select2.js') }}" type="text/javascript"></script>
<!-- Light Dashboard DEMO methods, don't include it in your project! -->
<script src="{{ mix('js/demo.js') }}"></script>
<script src="{{ mix('js/plugins/bootstrap-table-mobile.js') }}"></script>
<!--Main scripts-->
<script src="{{ mix('js/modal-window.js') }}"></script>

<!-- Fix Multi Submit -->
<script src="{{ mix('js/fixMultiSubmit.js') }}"></script>

<!-- Vue and Pusher to get fast Notifications -->
<script src="{{ asset('js/plugins/pusher_and_vue.js') }}"></script>

<!-- import JavaScript -->
<script src="{{ asset('js/elementui.js') }}"></script>
<script src="{{ mix('js/axios.min.js') }}"></script>
<script src="{{ asset('js/ru-RU.js') }}"></script>

<!-- import FontAwesome (fresh version of icons) -->
<script src="{{ asset('js/92abca6220.js') }}"></script>
<!-- import Lodash -->
<script src="{{ asset('js/plugins/lodash.js') }}"></script>

<script src="{{ asset('js/plugins/emojione.js') }}"></script>
<script src="{{ asset('js/plugins/emojionearea.min.js') }}"></script>
<meta name="csrf-token" content="{{ csrf_token() }}" />

 <script type="text/javascript">
     $('body').on('click', function () {
         if($('html').hasClass('nav-open')){
             $('.sidebar').addClass('s-mnr-50');
         } else {
             $('.sidebar').removeClass('s-mnr-50');
         }
     })
 </script>
<script type="text/javascript">

    function activeSidebar() {
        if ($(window).width() <=991.9 ){
            mobile_menu_visible == 0;
            if (mobile_menu_visible == 1) {
                $('html').removeClass('nav-open');

                $('.close-layer').remove();
                setTimeout(function() {
                    $toggle.removeClass('toggled');
                }, 10);

                mobile_menu_visible = 0;
            } else {
                setTimeout(function() {
                    $toggle.addClass('toggled');
                }, 430);

                main_panel_height = $('.main-panel')[0].scrollHeight;
                $layer = $('<div class="close-layer"></div>');
                $layer.css('height', main_panel_height + 'px');
                $layer.appendTo(".main-panel");

                setTimeout(function() {
                    $layer.addClass('visible');
                }, 100);

                $layer.click(function() {
                    $('html').removeClass('nav-open');
                    mobile_menu_visible = 0;

                    $layer.removeClass('visible');

                    setTimeout(function() {
                        $layer.remove();
                        $toggle.removeClass('toggled');

                    }, 400);
                });

                $('html').addClass('nav-open');
                mobile_menu_visible = 1;
            }
        }
    };

    $('#start-chat-mobile').click(function(){
        activeSidebar()
    });

    $('.btn-start-chat').click(function(){
        activeSidebar()
    });

    $('.li-active-chat').click(function(){
        activeSidebar()
    });

</script>
<script>
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    ELEMENT.locale(ELEMENT.lang.ruRU)

    $(document).on('submit','form',function(event){
        var form = $(event.currentTarget);
        if(form.find('[type="file"]').length > 0) {

            var show_swal = false;

            form.find('[type="file"]').each(function(){
                if($(this)[0].files.length > 0) {
                    show_swal = true;
                }
            });
            if(show_swal) {
                swal.fire({
                    title: 'Идёт загрузка',
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

    $('input').attr('autocomplete','off');

    function scrollToLatestMessage() {
        $('#thread_messages').scrollTop($('#thread_messages')[0].scrollHeight);
    }

    // Pusher.logToConsole = true;

    var pusher = new Pusher('13460ea482ed2f33ee58', {
        cluster: 'eu',
        forceTLS: true
    });

    Vue.component('span-message-count', {
        props: ['messages'],
        template: '<span class="notification" v-if="messages">@{{ messages }}</span>'
    });

    var message_notifications = new Vue({
        el: '#home-tab3',
        data: {
            messages: {{ $messages }}
        }
    });

    var channel = pusher.subscribe('{{config('app.env')}}' + '.App.User.' + {{ Auth::user()->id }});

    channel.bind('message-stored', function(data) {
        var thread = $('.' + data.messageData.div_class);

        if (thread.length) {
            // thread opened => append message to thread
            $.ajax({
                url:'{{ route('messages::message_render') }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    message_id: data.messageData.message_id
                },
                dataType: 'JSON',
                success: function(response) {
                    thread.append(response.html);
                    var list = $('.list-thread-' + response.thread);
                    list.empty().append(response.list_item);
                    scrollToLatestMessage();
                }
            });
        } else {
            // thread not currently opened => create notification
            $.ajax({
                url:'{{ route('messages::message_info') }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    message_id: data.messageData.message_id
                },
                dataType: 'JSON',
                success: function(response) {
                    var message = '<strong>' + response.sender_name + ': </strong><br>' + response.text + (response.text.length > 50 ? '...' : '') + '<br><a href="' + response.thread_url + '" class="text-right">Просмотреть</a>';
                    var thread_subject = response.thread_subject;

                    // notify user
                    message_notifications.$notify({
                        dangerouslyUseHTMLString: true,
                        message: 'Новое сообщение' + (thread_subject ? ' из диалога ' + thread_subject : '') + '. ' + '<br>' + message,
                        type: 'info',
                        duration: 5000
                    });
                }
            });

            // ajax for thread-list update
            $.ajax({
                url:'{{ route('messages::message_render') }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    message_id: data.messageData.message_id
                },
                dataType: 'JSON',
                success: function(response) {
                    var list = $('.list-thread-' + response.thread);
                    list.empty().append(response.list_item);
                }
            });
        }

        message_notifications.messages = data.messagesCount;
    });

    channel.bind('message-deleted', function(data) {
        var thread = $('.' + data.messageData.div_class);

        if (thread.length) {
            // thread opened => remove message from thread
            $('.msg_id_' + data.messageData.message_id).remove();
        }

        // -1 because event overtake message deleting
        message_notifications.messages = data.messagesCount - 1;
    });

    channel.bind('message-updated', function(data) {
        var thread = $('.' + data.messageData.div_class);
        var message = data.messageData.message_id;

        if (thread.length) {
            // thread opened => update message in thread
            $.ajax({
                url:'{{ route('messages::message_render') }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    message_id: data.messageData.message_id
                },
                dataType: 'JSON',
                success: function(response) {
                    $('.msg_id_' + message).replaceWith(response.html);
                }
            });
        }
    });
</script>

@yield('js_footer')
@stack('js_footer')

</html>
