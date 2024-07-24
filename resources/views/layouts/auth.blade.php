<!doctype html>
<html lang="ru">

<head>
    <meta charset="utf-8" />
    @vite('resources/img/apple-icon.png')
    @vite('resources/img/favicon.ico')
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>СК ГОРОД</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
    <!--     Fonts and icons     -->
    @vite('resources/css/google_fonts.css')
    @vite('resources/css/font-awesome.min.css')
    @vite('resources/css/v4-shims.min.css')

    <!-- CSS Files -->
    @vite('resources/css/bootstrap.min.css')
    @vite('resources/css/light-bootstrap-dashboard.css')
    <!-- CSS Just for demo purpose, don't include it in your project -->
    @vite('resources/css/demo.css')
    @include('sections.yandex_metrika')

</head>

<body>
    <div class="wrapper wrapper-full-page">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute">
            <div class="container">
                <div class="navbar-wrapper">
                    <a class="navbar-brand" href=""><img src="{{ mix('img/logo-mini.png') }}" width="20" style="margin-right:8px; margin-bottom:5px">СК ГОРОД</a>
                    <!-- <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-bar burger-lines"></span>
                        <span class="navbar-toggler-bar burger-lines"></span>
                        <span class="navbar-toggler-bar burger-lines"></span>
                    </button> -->
                </div>
                <div class="collapse navbar-collapse justify-content-end" id="navbar">
                    <ul class="navbar-nav">

                    </ul>
                </div>
            </div>
        </nav>
        <!-- End Navbar -->
        <div class="full-page  section-image" data-color="black" data-image="{{ mix('img/full-screen-image-2.jpg') }}" >
            <!--   you can change the color of the filter page using: data-color="blue | purple | green | orange | red | rose " -->
            <div class="content">
                <div class="container">
                    <div class="col-lg-4 col-md-6 col-sm-8 col-xs-8 ml-auto mr-auto">
                        <form class="form" method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="card card-login card-hidden">
                                <div class="card-header">
                                    <h3 class="header text-center">Вход</h3>
                                </div>
                                <div class="card-body">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Электронная почта</label>
                                            <input id="email" type="email" placeholder="example@sk-gorod.com" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>
                                        </div>
                                        <div class="form-group">
                                            <label>Пароль</label>
                                            <input id="password" placeholder="******" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input name="remember" class="form-check-input" type="checkbox" {{ old('remember') ? 'checked' : '' }}>
                                                    <span class="form-check-sign"></span>
                                                    Запомнить меня
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer ml-auto mr-auto">
                                    <button type="submit" class="btn btn-warning btn-wd">Войти</button>
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
@vite('resources/js/core/jquery.3.2.1.min.js')
@vite('resources/js/jquery.table.js')
@vite('resources/js/core/popper.min.js')
@vite('resources/js/core/bootstrap.min.js')
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
<!-- Light Dashboard DEMO methods, don't include it in your project! -->
@vite('resources/js/demo.js')
<script>
    $(document).ready(function() {
        demo.checkFullPageBackgroundImage();

        setTimeout(function() {
            // after 1000 ms we add the class animated to the login/register card
            $('.card').removeClass('card-hidden');
        }, 700)
    });
</script>

</html>
