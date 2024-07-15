<!doctype html>
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
    <title>СК ГОРОД</title>
    <meta
        content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no'
        name='viewport'
    />
    <!--     Fonts and icons     -->
    <link
        href="{{ asset('css/google_fonts.css') }}"
        rel="stylesheet"
    />
    <link
        href="{{ asset('css/font-awesome.min.css') }}"
        rel="stylesheet"
    />
    <link
        href="{{ asset('css/v4-shims.min.css') }}"
        rel="stylesheet"
    />

    <!-- CSS Files -->
    <link
        href="{{ asset('css/bootstrap.min.css') }}"
        rel="stylesheet"
    />
    <link
        href="{{ asset('css/light-bootstrap-dashboard.css') }}"
        rel="stylesheet"
    />
    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link
        href="{{ asset('css/demo.css') }}"
        rel="stylesheet"
    />
    @include('sections.yandex_metrika')

</head>

<body>
<div class="wrapper wrapper-full-page">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute">
        <div class="container">
            <div class="navbar-wrapper">
                <a
                    class="navbar-brand"
                    href=""
                ><img
                        src="{{ asset('img/logo-mini.png') }}"
                        width="20"
                        style="margin-right:8px; margin-bottom:5px"
                    >СК ГОРОД</a>
                <!-- <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-bar burger-lines"></span>
                    <span class="navbar-toggler-bar burger-lines"></span>
                    <span class="navbar-toggler-bar burger-lines"></span>
                </button> -->
            </div>
            <div
                class="collapse navbar-collapse justify-content-end"
                id="navbar"
            >
                <ul class="navbar-nav">

                </ul>
            </div>
        </div>
    </nav>
    <!-- End Navbar -->
    <div
        class="full-page  section-image"
        data-color="black"
        data-image="{{ asset('img/full-screen-image-2.jpg') }}"
    >
        <!--   you can change the color of the filter page using: data-color="blue | purple | green | orange | red | rose " -->
        <div class="content">
            <div class="container">
                <div class="col-lg-4 col-md-6 col-sm-8 col-xs-8 ml-auto mr-auto">
                    <form
                        class="form"
                        method="POST"
                        action="{{ route('login') }}"
                    >
                        @csrf
                        <div class="card card-login card-hidden">
                            <div class="card-header">
                                <h3 class="header text-center">Вход</h3>
                            </div>
                            <div class="card-body">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Электронная почта</label>
                                        <input
                                            id="email"
                                            type="email"
                                            placeholder="example@sk-gorod.com"
                                            class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                            name="email"
                                            value="{{ old('email') }}"
                                            required
                                            autofocus
                                        >
                                    </div>
                                    <div class="form-group">
                                        <label>Пароль</label>
                                        <input
                                            id="password"
                                            placeholder="******"
                                            type="password"
                                            class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                            name="password"
                                            required
                                        >
                                    </div>
                                    <div class="form-group">
                                        <div class="form-check">
                                            <label class="form-check-label">
                                                <input
                                                    name="remember"
                                                    class="form-check-input"
                                                    type="checkbox" {{ old('remember') ? 'checked' : '' }}>
                                                <span class="form-check-sign"></span>
                                                Запомнить меня
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer ml-auto mr-auto">
                                <button
                                    type="submit"
                                    class="btn btn-warning btn-wd"
                                >Войти
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <footer class="footer">
        <div class="container">
            <nav>
                <p class="copyright text-center">
                    ©
                    <script>
                        document.write(new Date().getFullYear())
                    </script>
                    <a href="http://sk-gorod.com">ООО «СК ГОРОД»</a>
                </p>
            </nav>
        </div>
    </footer>
</div>
</body>
<!--   Core JS Files   -->
<script
    src="{{ asset('js/core/jquery.3.2.1.min.js') }}"
    type="text/javascript"
></script>
<script
    src="{{ asset('js/jquery.table.js') }}"
    type="text/javascript"
></script>
<script
    src="{{ asset('js/core/popper.min.js') }}"
    type="text/javascript"
></script>
<script
    src="{{ asset('js/core/bootstrap.min.js') }}"
    type="text/javascript"
></script>
<!--  Plugin for Switches, full documentation here: http://www.jque.re/plugins/version3/bootstrap.switch/ -->
<script src="{{ asset('js/plugins/bootstrap-switch.js') }}"></script>
<!--  Google Maps Plugin    -->
<!-- <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?YOUR_KEY_HERE"></script> -->
<!--  Chartist Plugin  -->
<script src="{{ asset('js/plugins/chartist.min.js') }}"></script>
<!--  Notifications Plugin    -->
<script src="{{ asset('js/plugins/bootstrap-notify.js') }}"></script>
<!--  jVector Map  -->
<script
    src="{{ asset('js/plugins/jquery-jvectormap.js') }}"
    type="text/javascript"
></script>
<!--  Plugin for Date Time Picker and Full Calendar Plugin-->
<script src="{{ asset('js/plugins/moment.min.js') }}"></script>
<!--  DatetimePicker   -->
<script src="{{ asset('js/plugins/bootstrap-datetimepicker.js') }}"></script>
<!--  Sweet Alert  -->
<script
    src="{{ asset('js/plugins/sweetalert2.all.min.js') }}"
    type="text/javascript"
></script>
<!--  Tags Input  -->
<script
    src="{{ asset('js/plugins/bootstrap-tagsinput.js') }}"
    type="text/javascript"
></script>
<!--  Sliders  -->
<script
    src="{{ asset('js/plugins/nouislider.js') }}"
    type="text/javascript"
></script>
<!--  Bootstrap Select  -->
<script
    src="{{ asset('js/plugins/bootstrap-selectpicker.js') }}"
    type="text/javascript"
></script>
<!--  jQueryValidate  -->
<script
    src="{{ asset('js/plugins/jquery.validate.min.js') }}"
    type="text/javascript"
></script>
<!--  Plugin for the Wizard, full documentation here: https://github.com/VinceG/twitter-bootstrap-wizard -->
<script src="{{ asset('js/plugins/jquery.bootstrap-wizard.js') }}"></script>
<!--  Bootstrap Table Plugin -->
<script src="{{ asset('js/plugins/bootstrap-table.js') }}"></script>
<!--  DataTable Plugin -->
<script src="{{ asset('js/plugins/jquery.dataTables.min.js') }}"></script>
<!--  Full Calendar   -->
<script src="{{ asset('js/plugins/fullcalendar.min.js') }}"></script>
<!-- Control Center for Now Ui Dashboard: parallax effects, scripts for the example pages etc -->
<script
    src="{{ asset('js/light-bootstrap-dashboard.js') }}"
    type="text/javascript"
></script>
<!-- Light Dashboard DEMO methods, don't include it in your project! -->
<script src="{{ asset('js/demo.js') }}"></script>
<script>
    $(document).ready(function () {
        demo.checkFullPageBackgroundImage();

        setTimeout(function () {
            // after 1000 ms we add the class animated to the login/register card
            $('.card').removeClass('card-hidden');
        }, 700)
    });
</script>

</html>
