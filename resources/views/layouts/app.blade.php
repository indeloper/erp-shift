<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="utf-8"/>
    @vite('resources/img/apple-icon.png')
    @vite('resources/img/favicon.ico')
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <title>@yield('title')</title>

    @routes


    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no'
          name='viewport'/>
    <!--     Fonts and icons     -->
    @vite('resources/css/font-awesome.min.css')
    @vite('resources/css/v4-shims.min.css')
    @vite('resources/css/google_fonts.css')

    @vite('resources/css/pe-icon-7-stroke.css')
    <!-- CSS Files -->
    @vite('resources/css/bootstrap.min.css')
    @vite('resources/css/balloon.css')
    @vite('resources/css/light-bootstrap-dashboard.css')

    <!-- CSS Just for demo purpose, don't include it in your project -->
    @vite('resources/css/demo.css')
    @vite('resources/css/additionally.css')
    @vite('resources/css/tech.css')
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700&display=swap" rel="stylesheet">
    <link
            href="https://fonts.googleapis.com/css?family=Montserrat:300,300i,400,400i,500,500i,600,700,800,900&display=swap"
            rel="stylesheet">

    @vite('resources/css/index.css')
    @vite('resources/css/emojione.css')
    @vite('resources/css/emojionearea.min.css')
    @vite('resources/css/main.css')
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

<body @if(Session::has('sidebar_mini') && Session::get('sidebar_mini')) class="sidebar-mini dx-viewport"
      @else class="dx-viewport" @endif>
<div class="wrapper">
    <div class="sidebar"
         @if (config("app.env") == "local")
             data-color="red"
         @elseif (config("app.env") == "remote-dev")
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
<!--   Core JS Files   -->
@vite('resources/js/core/jquery.3.2.1.min.js')
@vite('resources/js/jquery.table.js')
@vite('resources/js/core/popper.min.js')
@vite('resources/js/core/bootstrap.min.js')
<script src="{{ asset('js/common.js') }}"></script>

@vite('resources/js/plugins/bootstable.js')
<!--  Plugin for Switches, full documentation here: http://www.jque.re/plugins/version3/bootstrap.switch/ -->
@vite('resources/js/plugins/bootstrap-switch.js')
<!--  Google Maps Plugin    -->
<!-- <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?YOUR_KEY_HERE"></script> -->
<!--  Chartist Plugin  -->
@vite('resources/js/plugins/chartist.min.js')
<!--  Notifications Plugin    -->
@vite('resources/js/plugins/bootstrap-notify.js')
<!--  jVector Map  -->
@vite('resources/js/plugins/jquery-jvectormap.js')
<!--  Plugin for Date Time Picker and Full Calendar Plugin-->
@vite('resources/js/plugins/moment.min.js')
<script>moment.locale('ru');</script>
<!--  DatetimePicker   -->
@vite('resources/js/plugins/bootstrap-datetimepicker.js')
<!--  Sweet Alert  -->
@vite('resources/js/plugins/sweetalert2.all.min.js')
<!--  Tags Input  -->
@vite('resources/js/plugins/bootstrap-tagsinput.js')
<!--  Sliders  -->
@vite('resources/js/plugins/nouislider.js')
<!--  Bootstrap Select  -->
@vite('resources/js/plugins/bootstrap-selectpicker.js')
<!--  jQueryValidate  -->
@vite('resources/js/plugins/jquery.validate.min.js')
<!--  Plugin for the Wizard, full documentation here: https://github.com/VinceG/twitter-bootstrap-wizard -->
@vite('resources/js/plugins/jquery.bootstrap-wizard.js')
<!--  Bootstrap Table Plugin -->
@vite('resources/js/plugins/bootstrap-table.js')
<!--  DataTable Plugin -->
@vite('resources/js/plugins/jquery.dataTables.min.js')
<!--  Full Calendar   -->
@vite('resources/js/plugins/fullcalendar.min.js')
<!-- Control Center for Now Ui Dashboard: parallax effects, scripts for the example pages etc -->
@vite('resources/js/light-bootstrap-dashboard.js')
<!-- Select2 Essentials and Localization -->
<script src="{{ asset('js/select2.js') }}" type="text/javascript"></script>
<!-- Light Dashboard DEMO methods, don't include it in your project! -->
@vite('resources/js/demo.js')
@vite('resources/js/plugins/bootstrap-table-mobile.js')
<!--Main scripts-->
@vite('resources/js/modal-window.js')
<!-- Validation-form-ru-locale -->
@vite('resources/js/form-validation.js')
<script src="{{ asset('js/plugins/jquery.mask.min.js') }}"></script>
<!-- AutoNumeric -->
<script src="{{ asset('js/plugins/autonumeric.min.js') }}"></script>

<!-- Fix Multi Submit -->
@vite('resources/js/fixMultiSubmit.js')

<!-- Vue and Pusher to get fast Notifications -->

{{--<script src="https://js.pusher.com/4.4/pusher.min.js"></script>--}}
<script src="{{ asset('js/plugins/pusher_and_vue.js') }}"></script>

<!-- import JavaScript -->
<script src="{{ asset('js/vue.js') }}"></script>
<script src="{{ asset('js/elementui.js') }}"></script>

<!-- fix select search-->
@vite('resources/js/vue-router.js')
@vite('resources/js/plugins/vee-validate.js')
@vite('resources/js/validation-rules.js')
<!--  -->

@vite('resources/js/axios.min.js')
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
        || Request::is('projects')
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
