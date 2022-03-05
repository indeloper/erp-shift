@extends('layouts.app')

@section('title', 'Проекты')

@section('url', route('projects::index'))

@section('content')
<div class="row">
    <div class="col-sm-12 col-xl-9 mr-auto ml-auto">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-5">
                        <h4 class="card-title" style="margin-top: 5px">Новый проект</h4>
                    </div>
                </div>
                <hr>
            </div>
            <div class="card-body">
                <h5 style="margin-bottom:20px">Основная информация</h5>
                <form id="form_create_project" class="form-horizontal" action="{{ route('projects::store', ['task_id' => Request::get('task_id'), 'contractor_id' => Request::get('contractor_id'), 'contact_id' => Request::get('contact_id')]) }}" method="post">
                    @csrf
                    <input id="checked" name="main_contractor" type="hidden">
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Название проекта<star class="star">*</star></label>
                        <div class="col-sm-9">
                            <div class="form-group">
                                <input class="form-control" type="text" name="name" required maxlength="200">
                            </div>
                        </div>
                    </div>
                    <div id="contractors_row">
                        <div class="row">
                            <label class="col-sm-3 col-form-label  mb-10__mobile">Контрагент<star class="star">*</star></label>
                            <div class="col-sm-3 col-md-2">
                                <div class="form-group">
                                    <div class="form-check form-check-radio">
                                        <label class="form-check-label">
                                            <input class="form-check-input" type="radio" name="main" onclick="useAsMain(this)" @if(!Request::has('contractor_id')) disabled @endif required>
                                            <span class="form-check-sign"></span>
                                            Основной
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-5 col-md-6">
                                <div class="form-group select-accessible-140">
                                    <select name="contractor_ids[]" class="js-select-contractors slct" style="width:100%;" required>
                                        @if(Request::has('contractor_id'))
                                            <option value="{{ $contractor->id }}" selected>{{ $contractor->short_name }}. ИНН: {{ $contractor->inn }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3"></div>
                        <div class="col-sm-9" style="margin-bottom:20px">
                            <button type="button" class="btn-success btn btn-sm btn-round btn-outline" onclick="addContractorInput(this)" style="margin-top: 8px">
                                <i class="fas fa-plus" aria-hidden="true"></i>
                                Добавить контрагента
                            </button>
                        </div>
                    </div>
                    <div id="contacts_row">
                        <hr>
                        <div class="row">
                            <label class="col-sm-3 col-form-label  mb-10__mobile" style="padding-top: 12px;">Контакты<star class="star">*</star></label>
                            <div class="col-sm-9">
                                <div class="fixed-table-toolbar toolbar-for-btn" style="margin-top:0; padding-left:0">
                                    <button type="button" id="selectFromContacts" class="btn btn-round btn-outline btn-sm d-none" data-toggle="modal" data-target="#select-contact">
                                        <i class="glyphicon fa fa-search"></i>
                                        Выбрать из списка
                                    </button>
                                    <button type="button" class="btn btn-round btn-sm btn-outline" data-toggle="modal" data-target="#add-contact">
                                        <i class="glyphicon fa fa-plus"></i>
                                        Добавить контакт
                                    </button>

                                </div>
                                <div class="table-responsive contracts-table d-none">
                                    <table class="table table-hover mobile-table">
                                        <thead>
                                        <tr>
                                            <th>ФИО</th>
                                            <th>Должность</th>
                                            <th>Контактный номер</th>
                                            <th>email</th>
                                            <th class="text-right"></th>
                                        </tr>
                                        </thead>
                                        <tbody id="contacts">

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <hr>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Юр. лицо<star class="star">*</star></label>
                        <div class="col-sm-9">
                            <div class="form-group select-accessible-140">
                                <select name="entity" class="selectpicker" data-title="Выберите юр. лицо" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                    @foreach($entities as $key => $entity)
                                        <option value="{{ $key }}" {{ $key == 1 ? 'selected' : '' }}>{{ $entity }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Направления<star class="star">*</star></label>
                        <div class="col-sm-9">
                            <div class="form-group">
                                <select name="type[]" class="selectpicker" multiple data-title="Выберите направление" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                        <option value="is_tongue">Шпунтовое направление</option>
                                        <option value="is_pile">Свайное направление</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Объект<star class="star">*</star></label>
                        <div class="col-sm-9">
                            <div class="form-group select-accessible-140">
                                <select name="object_id" id="js-select-objects" style="width:100%;" required>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Описание</label>
                        <div class="col-sm-9">
                            <div class="form-group">
                                <textarea class="form-control textarea-rows" name="description" maxlength="200"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                <button type="submit" form="form_create_project" class="btn btn-info">Создать</button>
            </div>
        </div>
    </div>
</div>

<!-- Модалка на добавление объекта -->
@can('objects_create')
<div class="modal fade bd-example-modal-lg show" id="create-new-project" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Создать новый объект</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card" style="border: none;">
                    <div class="card-body">
                        <form id="form_create_object" class="form-horizontal" method="post">
                            @csrf
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-sm-3 col-form-label">Название<star class="star">*</star></label>
                                    <div class="col-sm-9">
                                        <input class="form-control" id="object_name" type="text" name="name" required maxlength="150">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-sm-3 col-form-label">
                                        Сокращенное наименование
                                        <button type="button" name="button" class="btn btn-link btn-primary btn-xs mn-0 pd-0" data-container="body"
                                                data-toggle="popover" data-placement="top" data-content="Поле необходимо для улучшения формирования материального отчёта">
                                            <i class="fa fa-info-circle"></i>
                                        </button>
                                    </label>
                                    <div class="col-sm-9">
                                        <input class="form-control" id="short_name" type="text" name="short_name" maxlength="500">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-sm-3 col-form-label">Адрес объекта<star class="star">*</star></label>
                                    <div class="col-sm-9">
                                        <input class="form-control" id="set_address" type="text" name="address" required maxlength="250">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-sm-3 col-form-label">Кадастровый номер</label>
                                    <div class="col-sm-9">
                                        <input class="form-control cadastral_number" id="cadastral_number" pattern="[0-9]{2}:[0-9]{2}:[0-9]{6,7}:[0-9]{1,5}" type="text" name="cadastral_number" minlength="14" maxlength="19">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="submit" form="form_create_object" class="btn btn-info pull-right">Создать</button>
            </div>
        </div>
    </div>
</div>
@endcan

<!-- Modal for project contact select -->
<div class="modal fade bd-example-modal-lg" id="select-contact" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить контакта</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card border-0">
                    <div class="card-body">
                        <form id="form_select_contacts" class="form-horizontal" action="" method="post">
                            @csrf
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Контакты<star class="star">*</star></label>
                                <div class="col-sm-9">
                                    <div class="form-group">
                                        <select name="contact_id" id="js-select-contacts" style="width:100%;" data-title="Выберите контрагента" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Примечание к проекту</label>
                                <div class="col-sm-9">
                                    <div class="form-group">
                                        <textarea class="form-control textarea-rows" name="project_note" maxlength="150"></textarea>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                        <button type="submit" form="form_select_contacts" class="btn btn-info">Добавить</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for project contract create -->
<div class="modal fade bd-example-modal-lg" id="add-contact" tabindex="-1" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-add">Добавить контактное лицо</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card border-0">
                    <div class="card-body ">
                        <!--
                        Контактное лицо, добавленное в контрагента, сохраняется только в карточке контрагенте и становится доступным для выбора при добавлении контакта в карточке проекта.
                    -->
                        <form id="form_add_contact" class="form-horizontal" action="" method="post">
                            @csrf
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Фамилия<star class="star">*</star></label>
                                <div class="col-sm-9">
                                    <div class="form-group">
                                        <input class="form-control" type="text" name="last_name" required maxlength="50">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Имя<star class="star">*</star></label>
                                <div class="col-sm-9">
                                    <div class="form-group">
                                        <input class="form-control" type="text" name="first_name" required maxlength="50">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Отчество</label>
                                <div class="col-sm-9">
                                    <div class="form-group">
                                        <input class="form-control" type="text" name="patronymic" maxlength="50">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Должность<star class="star">*</star></label>
                                <div class="col-sm-9">
                                    <div class="form-group">
                                        <input class="form-control" type="text" name="position" maxlength="50" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Телефон</label>
                                <div class="col-sm-9" id="telephones">
                                    <div class="row new_phone" style="margin: -6px">
                                        <input id="phone_count" hidden name="phone_count[]" value="0">
                                        <div class="col-sm-12">
                                            <div class="row">
                                                <div class="col-6">
                                                    <select id="phone_name" name="phone_name[]" style="width:100%;">
                                                        <option value="Моб.">Моб.</option>
                                                        <option value="Рабочий">Рабочий</option>
                                                        <option value="Основной">Основной</option>
                                                        <option value="Раб. факс">Раб. факс</option>
                                                        <option value="Дом. факс">Дом. факс</option>
                                                        <option value="Пейджер">Пейджер</option>
                                                    </select>
                                                </div>
                                                <div class="col-5">
                                                    <div class="form-check form-check-radio">
                                                        <label class="form-check-label" style="text-transform:none;font-size:13px">
                                                            <input class="form-check-input" type="radio" name="main" id="" value="0">
                                                            <span class="form-check-sign"></span>
                                                            Основной
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-1">
                                                    <button type="button" class="btn-success btn-link btn-xs btn pd-0" onclick="add_phone(this)">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-8">
                                                    <input class="form-control phone_number" type="text" name="phone_number[]" minlength="14" maxlength="17" placeholder="Введите номер">
                                                </div>
                                                <div class="col-3">
                                                    <input class="form-control phone_dop" type="text" name="phone_dop[]" maxlength="5" placeholder="Добавочный">
                                                </div>
                                                <div class="col-1">
                                                    <button type="button" class="btn-danger btn-link btn-xs btn pd-0" onclick="del_phone(this)">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Email</label>
                                <div class="col-sm-9">
                                    <div class="form-group">
                                        <input class="form-control" type="text" name="email" maxlength="50">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Общее примечание</label>
                                <div class="col-sm-9">
                                    <div class="form-group">
                                        <textarea class="form-control textarea-rows" name="note" maxlength="150"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Примечание к проекту</label>
                                <div class="col-sm-9">
                                    <div class="form-group">
                                        <textarea class="form-control textarea-rows" name="project_note" maxlength="150"></textarea>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="submit" form="form_add_contact" class="btn btn-info">Добавить</button>
            </div>
        </div>
    </div>
</div>

<div class="d-none">
    <div class="row contractor-container" id="new_contractor_input">

        <div class="col-sm-3 mb-10__mobile">
            <label>Доп. контрагент<star class="star">*</star></label>
        </div>
        <div class="col-sm-3 col-md-2">
            <div class="form-group">
                <div class="form-check form-check-radio">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="main" onclick="useAsMain(this)" disabled>
                        <span class="form-check-sign"></span>
                        Основной
                    </label>
                </div>
            </div>
        </div>
        <div class="col-sm-5 col-md-6 col-10" style="padding-right:15px">
            <div class="form-group select-accessible-140">
                <select name="contractor_ids[]" class="js-select-more-contractors slct" style="width:100%;" required>
                </select>
            </div>
        </div>
        <div class="col-1">
            <button type="button" class="btn-danger btn-link btn-xs btn pd-0" onclick="removeContractorInput(this)" style="margin-top: 8px">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <div class="row new_phone d-none" style="margin: -6px">
        <input id="phone_count" hidden name="phone_count[]" value="">
        <div class="col-sm-12">
            <hr>
            <div class="row">
                <div class="col-6">
                    <select name="phone_name[]" style="width:100%;">
                        <option value="Моб.">Моб.</option>
                        <option value="Рабочий">Рабочий</option>
                        <option value="Основной">Основной</option>
                        <option value="Раб. факс">Раб. факс</option>
                        <option value="Дом. факс">Дом. факс</option>
                        <option value="Пейджер">Пейджер</option>
                    </select>
                </div>
                <div class="col-5">
                    <div class="form-check form-check-radio">
                        <label class="form-check-label" style="text-transform:none;font-size:13px">
                            <input class="form-check-input" type="radio" name="main" id="check" value="">
                            <span class="form-check-sign"></span>
                            Основной
                        </label>
                    </div>
                </div>
                <div class="col-1">
                    <button type="button" class="btn-success btn-link btn-xs btn pd-0" onclick="add_phone(this)">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="row">
                <div class="col-8">
                    <input class="form-control phone_number" type="text" name="phone_number[]" minlength="14" maxlength="17" placeholder="Введите номер">
                </div>
                <div class="col-3">
                    <input class="form-control phone_dop" type="text" name="phone_dop[]"  maxlength="4" placeholder="Добавочный">
                </div>
                <div class="col-1">
                    <button type="button" class="btn-danger btn-link btn-xs btn pd-0" onclick="del_phone(this)">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}" />

@endsection

@section('js_footer')
<script src="//api-maps.yandex.ru/2.1/?lang=ru_RU&load=SuggestView&onload=onLoad"></script>
<script type='text/javascript' src="https://rawgit.com/RobinHerbots/jquery.inputmask/3.x/dist/jquery.inputmask.bundle.js"></script>

<script>
    var selectedContacts = [];
    var contractor_ids = '';
    var count_phone = 1;
    var count_edit_phone = 1;
    var key = 0;

    $('.cadastral_number').mask('00:00:000000Z:0ZZZZ', { translation: { 'Z': { pattern: /[0-9]/, optional: true } }, placeholder: "__:__:______:____" });


    function onLoad(ymaps) {
        var suggestView = new ymaps.SuggestView('set_address', {results: 5, offset: [0, 0]});
    }

    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    @can('objects_create')
    $("#form_create_object").on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url:"{{ route('objects::store') }}",
            type: 'POST',
            data: {
                _token: CSRF_TOKEN,
                name: $('#object_name').val(),
                cadastral_number: $('#cadastral_number').val(),
                address: $('#set_address').val(),
                short_name: $('#short_name').val(),
                material_accounting_type: 1,
                task_id: 1
            },
            dataType: 'JSON',
            success: function (data) {
                var new_option = $("<option selected='selected'></option>").val(data).text($('#object_name').val() + '. Адрес: ' + $('#set_address').val());
                $('#js-select-objects').append(new_option).trigger('change');

                $('#object_name').val('');
                $('#cadastral_number').val('');
                $('#set_address').val('');
                $('#short_name').val('');
                $('.close').click();
            }
        });
    });
    @endcan

    $('.js-select-contractors').select2({
        language: "ru",
        ajax: {
            url: '/projects/ajax/get-contractors',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                var contractor_ids = $('.slct').map(function(idx, elem) {
                    return $(elem).val() == '' ? null : $(elem).val();
                }).get();

                return {
                    contractor_ids: contractor_ids,
                    q: params.term
                };
            }
        }
    }).on("select2:select", function(e) {
        $('.js-select-contractors').closest('.row').first().find('.form-check-input').first().removeAttr('disabled');
    });

    $('#js-select-objects').select2({
        language: "ru",
        ajax: {
            url: '/projects/ajax/get-objects?create_project=1'
        }
    }).on('select2:select', function (e) {
        var data = e.params.data.id;
        $.ajax({
            url:"{{ route('objects::get_object_projects') }}", //SET URL
            type: 'GET', //CHECK ACTION
            data: {
                _token: CSRF_TOKEN,
                object_id: data, //SET ATTRS
            },
            dataType: 'JSON',
            success: function (projects) {
                if (projects.length > 0) {
                    var links = '';
                    projects.forEach(project => {
                        links += '<a href="' + project.link + '" target="_blank">' + project.name + '</a><br>';
                    });
                    var project_swal = swal({
                        title: 'По данному объекту уже существуют проекты',
                        type: 'info',
                        html:
                            'Пожалуйста, прежде чем создавать новый проект, ознакомьтесь с существующими, <hr>' +
                            links,
                        showCancelButton: false,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                    });
                    setTimeout(function () {
                        $(".swal2-close").show();
                        $(".swal2-close")[0].innerHTML = '<i class="fas fa-times fa-xs"></i>';
                    }, 2000);
                }
            }
        });
        @can('objects_create')
        if (data == 'create_object') {
            $('#create-new-project').modal();
        }
        @endcan
    });

    function addContractorInput(elem) {
        var new_elem = $('#new_contractor_input').clone().attr('id', '');

        $(new_elem).appendTo('#contractors_row');

        $(new_elem).find('.js-select-more-contractors').first().select2({
            language: "ru",
            ajax: {
                url: '/projects/ajax/get-contractors',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    contractor_ids = $('.slct').map(function(idx, elem) {
                        return $(elem).val() == '' ? null : $(elem).val();
                    }).get();

                    return {
                        contractor_ids: contractor_ids,
                        q: params.term
                    };
                }
            }
        }).on("select2:select", function(e) {
            $(new_elem).find('.form-check-input').first().removeAttr('disabled');
            update_select();
        });
    }

    function removeContractorInput(elem) {
        $(elem).closest('.contractor-container').first().remove();
    }

    function useAsMain(elem) {
        // find closest select value
        selectVal = $(elem).closest('.row').first().find('.slct').first().val();
        $('#checked').val(selectVal);

        $('#selectFromContacts').removeClass('d-none');

        update_select();
    }

    $('#form_select_contacts').submit(function (e) {
        e.preventDefault();

        request_info = new FormData(this);
        request_info.append('key', key);

        $('#select-contact').modal('hide');
        $('.nothing-tip').addClass('d-none');
        $('.contracts-table').removeClass('d-none');
        $('#form_select_contacts').trigger('reset');

        $.ajax({
            url: '{{ route('projects::store_temp_contact') }}',
            type: 'POST',
            data: request_info,
            processData: false,
            contentType: false,
            dataType: 'JSON',
            success: function (response) {
                selectedContacts.push(response[0]);
                $('#contacts').append(response.html);
                key++;
            }
        }).then(function () {
            update_select();
        });
    });

    $('#form_add_contact').submit(function (e) {
        e.preventDefault();

        request_info = new FormData(this);
        request_info.append('key', key);

        $('#add-contact').modal('hide');
        $('.nothing-tip').addClass('d-none');
        $('.contracts-table').removeClass('d-none');
        $('#form_add_contact').trigger('reset');

        $.ajax({
            url: '{{ route('projects::store_temp_contact') }}',
            type: 'POST',
            data: request_info,
            processData: false,
            contentType: false,
            success: function (response) {
                selectedContacts.push(response[0]);
                $('#contacts').append(response.html);
                key++;
            }
        });
    });

    function add_phone(e) {
        var next_phone = count_phone;
        if ($(e).parents('.col-sm-9')[0].getAttribute('id') == 'edit_phones') {
            next_phone = count_edit_phone;
            count_edit_phone++;
        } else {
            count_phone++;
        }
        $(".new_phone.d-none").find('.form-check-input').val(next_phone);
        $(".new_phone.d-none").find('#phone_count').val(next_phone);

        clone = $(".new_phone.d-none").clone().removeClass('d-none').appendTo($(e).parents('.col-sm-9'));
        clone.find('select').select2({
            tags: true,
        });

        clone.find('.phone_dop').mask('00000');
        clone.find('.phone_number').mask('7 (000) 000-00-00');
    }


    function del_phone(e) {
        if ($(e).parents('.col-sm-9').find('.new_phone').length > 1) {
            $(e).closest('.new_phone').remove();
        }
    }

    $('#phone_name').select2({
        tags: true,
    });

    $('.phone_dop').mask('00000');
    $('.phone_number').mask('7 (000) 000-00-00');

    function update_select() {
        $('#js-select-contacts').empty();

        $('#js-select-contacts').select2({
            language: "ru",
            ajax: {
                type: 'GET',
                url: '/projects/ajax/get-contractors-contacts',
                data: {
                    contractor_ids: $('.slct').map(function(idx, elem) {
                        return $(elem).val() == '' ? null : $(elem).val();
                    }).get(),
                    contact_ids: _.union(_.map(selectedContacts, 'contractor_contact_id')),
                },
                dataType: 'json',
                delay: 250
            }
        });
    }

    function removeContact(index) {
        selectedContacts.splice(index, 1);
        $('.key-' + index).remove();

        if (selectedContacts.length == 0) {
            $('.nothing-tip').removeClass('d-none');
            $('.contracts-table').addClass('d-none');
            $('#form_add_contact').trigger('reset');
            $('#form_select_contacts').trigger('reset');

            update_select();
        }
    }

    $('#form_create_project').submit(function (e) {
        e.preventDefault();

        // if (selectedContacts.length < 1) {
        //     notify.$message({
        //         showClose: true,
        //         message: 'Вам необходимо добавить как минимум одного контакта проекту',
        //         type: 'error',
        //         duration: 5000
        //     });
        // }

        if ($('js-select-objects').val() == "create_object") {
            notify.$message({
                showClose: true,
                message: 'Контакты не указаны. Необходимо добавить хотя бы одно контактное лицо в проект.',
                type: 'error',
                duration: 5000
            });
        }

        request_info = new FormData(this);
        $.each(selectedContacts, function (index, request) {
            request_info.append('contractor_contact_ids[]', request.contractor_contact_id);
            request_info.append('project_contact_ids[]', request.project_contact_id);
        });

        $.ajax({
            url: '{{ route('projects::store') }}',
            type: 'POST',
            data: request_info,
            processData: false,
            contentType: false,
            success: function (response) {
                window.location.replace(response.url);
            },
            error: function (data, jqXHR, exception) {
                var errors = Object.values((JSON.parse(data.responseText)).errors);

                errors.forEach(function (error, key) {
                    if (key == 0) {
                        setTimeout(function () {
                            notify.$message({
                              showClose: true,
                              message: error[0],
                              type: 'error',
                              duration: 5000
                            });
                        }, (key + 1) * 100);
                    }
                });
            }
        });
    })
</script>

@endsection
