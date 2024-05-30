@extends('layouts.app')

@section('title', 'Сотрудники')

@section('url', route('users::index'))

@section('css_top')
<style>
    /* .bootstrap-datetimepicker-widget .dropdown-menu .bottom {
        top: 10px !important;
    } */
</style>

@endsection

@section('content')
<div class="row">
    <div class="col-sm-12 col-xl-10 mr-auto ml-auto">
        <div class="card ">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <div class="left-edge">
                            <div class="page-container">
                                <h4 class="card-title" style="margin-top: 5px">
                                    Добавление сотрудника
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
            </div>
            <div class="card-body">
                <div class="float-left-container">
                    <div class="photo-container">
                        <div class="photocard">
                            <img src="{{ mix('img/user-male-black-shape.png') }}" class="photo" >
                            <!--<button type="button" class="btn btn-wd btn-info btn-outline edit-employee">
                                Редактировать
                            </button>-->
                        </div>
                    </div>
                </div>
                <div class="staff-card">
                    <form id="form_create_user" class="form-horizontal" action="{{ route('users::store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Фамилия<star class="star">*</star></label>
                                <div class="col-sm-7">
                                    <input class="form-control" type="text" name="last_name" required maxlength="50" placeholder="Фамилия">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Имя<star class="star">*</star></label>
                                <div class="col-sm-7">
                                    <input class="form-control" type="text" name="first_name" required maxlength="50" placeholder="Имя">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Отчество</label>
                                <div class="col-sm-7">
                                    <input class="form-control" type="text" name="patronymic" maxlength="50" placeholder="Отчество">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Подразделение<star class="star">*</star></label>
                                <div class="col-sm-7" >
                                    <select id="department" class="js-groups" name="department_id" style="width:100%;" required>
                                        <option value="">Не выбрано</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Должность<star class="star">*</star></label>
                                <div class="col-sm-7">
                                    <select class="js-groups" id="group_id" name="group_id" style="width:100%;" required>
                                        <option value="">Не выбрано</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                               <!--
                                    Селект для выбора должности.
                                    При выборе ДОЛЖНОСТИ из списка, ОТДЕЛ должен выбираться автоматически.
                                    Если сначала выбрали ОТДЕЛ, в списке ДОЛЖНОСТЕЙ показаны только те, которые относятся к выбранному отделу.

                                    Администрация: [ Генеральный директор; Секретарь ]
                                    Бухгалтерия:  [ Бухгалтер; Главный бухгалетер; Финансовый директор ]
                                    Материально-технический: [ Заведующий складом; Кладовщик; Машинист крана; Стропальщик; Электрогазосварщик; Электросварщик; Экономист по МТО;]
                                    Отдел качества: [ Инженер по техническому надзору ]
                                    Отдел персонала: [ Менеджер по подбору персонала ]
                                    Отдел продаж: [ Директор по развитию; Специалист по продажам и ведению клиентов ]
                                    Претензионно-договорной: [ Юрист; Экономист по договорной работе ]
                                    Проектный: [ Проектировщик ]
                                    ПТО: [ Инженер ПТО; Начальник ПТО ]
                                    Строительный: [ Геодезист; Главный инженер; Машинист копра; Машинист крана; Производитель работ; Руководитель проектов; Стропальщик; Электрогазосварщик; ]
                                    УМиТ: [ Главный механик; Машинист крана; Электрослесарь по ремонту оборудования ]


                                    <select name="position" class="selectpicker" data-title="Укажите должность" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                        <option value="accountant">Бухгалтер</option>
                                        <option value="geodesist">Геодезист</option>
                                        <option value="generalManager">Генеральный директор</option>
                                        <option value="сhiefAccountant">Главный бухгалетер</option>
                                        <option value="chiefEngineer">Главный инженер</option>
                                        <option value="ptd">Главный механик</option>
                                        <option value="developmentDirector">Директор по развитию</option>
                                        <option value="warehouseManager">Заведующий складом</option>
                                        <option value="technicalVision">Инженер по техническому надзору</option>
                                        <option value="ptdEngineer">Инженер ПТО</option>
                                        <option value="storekeeper">Кладовщик</option>
                                        <option value="coprOperator">Машинист копра</option>
                                        <option value="craneOperator">Машинист крана</option>
                                        <option value="HR">Менеджер по подбору персонала</option>
                                        <option value="ptdChief">Начальник ПТО</option>
                                        <option value="projector">Проектировщик</option>
                                        <option value="workman">Производитель работ</option>
                                        <option value="projectManager">Руководитель проектов</option>
                                        <option value="secretary">Секретарь</option>
                                        <option value="salesManager">Специалист по продажам и ведению клиентов</option>
                                        <option value="slinger">Стропальщик</option>
                                        <option value="technicalDirector">Технический директор</option>
                                        <option value="financialDirector">Финансовый директор</option>
                                        <option value="contractEconomist">Экономист по договорной работе</option>
                                        <option value="mtoEconomist">Экономист по МТО</option>
                                        <option value="electricGasWelder">Электрогазосварщик</option>
                                        <option value="electricWelder">Электросварщик</option>
                                        <option value="equipmentRepair">Электрослесарь по ремонту оборудования</option>
                                        <option value="lawyer">Юрист</option>
                                    </select>
                                -->

                                <!--Селект для поля Отдел

                                <select name="department" class="selectpicker" data-title="Укажите отдел" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                    <option value="administration">Администрация</option>
                                    <option value="accounting">Бухгалтерия</option>
                                    <option value="technical">Материально-технический</option>
                                    <option value="quality">Отдел качества</option>
                                    <option value="staff">Отдел персонала</option>
                                    <option value="sales">Отдел продаж</option>
                                    <option value="claim-contractual">Претензионно-договорной</option>
                                    <option value="project">Проектный</option>
                                    <option value="ptd">ПТО</option>
                                    <option value="building">Строительный</option>
                                    <option value="umit">УМиТ</option>
                                </select>-->
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Компания<star class="star">*</star></label>
                                <div class="col-sm-7">
                                    <select id="company" class="js-groups" name="company" style="width:100%;" required>
                                        <option value="">Не выбрана</option>
                                        @foreach($companies as $key => $company)
                                            <option value="{{ $key }}">{{ $company }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Дата рождения</label>
                                <div class="col-sm-4">
                                    <input id="birthday" name="birthday" type="text" class="form-control datepicker" placeholder="Выберите дату" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Телефон</label>
                                <div class="col-sm-4">
                                    <input class="form-control raz" id="person_phone" type="text" name="person_phone" placeholder="7 (999) 555 32-11" minlength="17" maxlength="17">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Рабочий телефон</label>
                                <div class="col-sm-4">
                                    <input class="form-control raz" id="work_phone" type="text" name="work_phone" maxlength="5">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Email<star class="star">*</star></label>
                                <div class="col-sm-6">
                                    <input class="form-control" id="email" type="email" name="email" maxlength="50">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Пароль<star class="star">*</star></label>
                                <div class="col-sm-6">
                                    <input class="form-control" id="password" pass="pass" type="password" name="password" minlength="7" onchange="checkPassword(this)">
                                    <small class="text-muted" style="font-size: 12px">
                                        Пароль должен иметь латинские символы верхнего и нижнего регистра, а также цифры
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Повторите пароль<star class="star">*</star></label>
                                <div class="col-sm-6">
                                    <input class="form-control" id="password_confirmation" pass="pass" type="password" name="password_confirmation" onchange="checkConfirmation(this)" readonly="readonly">
                                    <small class="text-muted" style="font-size: 12px">
                                        Пароли должны совпадать
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Статус<star class="star">*</star></label>
                                <div class="col-sm-4" style="padding-top: 0">
                                    <select name="status" class="selectpicker" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                        <option value="1">Активен</option>
                                        <option value="0">Не активен</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 col-form-label" for="exampleFormControlFile1">Фото</label>
                                <div class="col-sm-4" style="padding-top:0px;">
                                    <div class="file-container">
                                        <div id="fileName" class="file-name"></div>
                                        <div class="file-upload ">
                                            <label class="pull-right">
                                                <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                <input type="file" accept="image/*" name="user_image" onchange="getFileName(this)" id="uploadedFile">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <button id="create_user_submit" type="submit" form="form_create_user" class="btn btn-info btn-outline">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js_footer')
<script src="{{ asset('js/plugins/jquery.mask.min.js') }}"></script>

<meta name="csrf-token" content="{{ csrf_token() }}" />

<script>
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

    $('.js-groups').select2({
        language: "ru",
    });

    $("#birthday").datetimepicker({
        format: 'DD.MM.YYYY',
        locale: 'ru',
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-chevron-up",
            down: "fa fa-chevron-down",
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-screenshot',
            clear: 'fa fa-trash',
            close: 'fa fa-remove'
        },
        maxDate: moment(),
        date: null
    });

    $('#work_phone').mask('00000');

    $('#person_phone').mask('7 (000) 000-00-00');

    $.ajax({
        url:'{{ route("users::department") }}',
        type: 'POST',
        data: {
            _token: CSRF_TOKEN,
            department_id:  $("#department").val(),
        },
        dataType: 'JSON',
        success: function (data) {
            $('#group_id').html('');
            $.each(data, function(key, value) {
                $('#group_id').prepend($("<option></option>")
                    .attr("value", value.id)
                    .text(value.name));
            });
        }
    });

    $("#department").change(function() {
        $.ajax({
            url:'{{ route("users::department") }}',
            type: 'POST',
            data: {
                _token: CSRF_TOKEN,
                department_id:  $("#department").val(),
            },
            dataType: 'JSON',
            success: function (data) {
                $('#group_id').html('');
                $.each(data, function(key, value) {
                    $('#group_id').prepend($("<option></option>")
                        .attr("value", value.id)
                        .text(value.name));
                });
            }
        });
    });

    function checkPassword(elem) {
        password = $(elem).val();

        if (password.match(/(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])/)) {
            $(elem).removeClass('is-invalid');

            $('#password_confirmation').removeAttr('readonly');
        } else {
            $(elem).addClass('is-invalid');
            $('#password_confirmation').attr('readonly', 'readonly');
        }
    }

    function checkConfirmation(elem) {
        confirmation = $(elem).val();

        if (confirmation.match(/(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])/) && confirmation == $('#password').val()) {
            $(elem).removeClass('is-invalid');

            $('#create_user_submit').removeAttr('disabled');
        } else {
            $(elem).addClass('is-invalid');
            $('#create_user_submit').attr('disabled', 'disabled');
        }
    }

    $('#email').on('change', function (){
        if ($(this).val() == '') {
            $('#password').removeAttr('required');
            $('#password_confirmation').removeAttr('required');
            $('#create_user_submit').removeAttr('disabled');
        } else {
            $('#email').attr('required', 'true');
            $('#password').attr('required', 'true');
            $('#password_confirmation').attr('required', 'true');
            $('#create_user_submit').attr('disabled', 'disabled');
        }
    });

    $('#password').on('change', function (){
        if ($(this).val() == '') {
            $('#email').removeAttr('required');
            $('#password_confirmation').removeAttr('required');
            $('#create_user_submit').removeAttr('disabled');
        } else {
            $('#email').attr('required', 'true');
            $('#password').attr('required', 'true');
            $('#password_confirmation').attr('required', 'true');
            $('#create_user_submit').attr('disabled', 'disabled');
        }
    });
</script>
<script src="{{ mix('js/form-validation.js') }}" type="text/javascript"></script>

@endsection
