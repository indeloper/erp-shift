@extends('layouts.app')

@section('title', 'Сотрудники')

@section('url', route('users::index'))

@section('content')
    <div class="row">
        <div class="col-sm-12 col-xl-10 mr-auto ml-auto">
            <div class="card ">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="left-edge">
                                <div class="page-container">
                                    <h4
                                            class="card-title"
                                            style="margin-top: 5px"
                                    >
                                        Редактирование данных сотрудника</h4>
                                </div>
                            </div>
                        </div>
                        @can('users_delete')
                            <div class="col-md-6">
                                <div class="right-edge">
                                    <button
                                            class="btn btn-sm btn-danger btn-outline"
                                            data-toggle="modal"
                                            data-target="#deleteUser"
                                    >
                                        Удалить сотрудника
                                    </button>
                                </div>
                            </div>
                        @endcan
                    </div>
                    <hr>
                </div>
                <div class="card-body">
                    <div class="float-left-container">
                        <div class="photo-container">
                            <div class="photocard">
                                @if($user->image)
                                    <img
                                            src="{{ asset('storage/img/user_images/' . $user->image) }}"
                                            class="photo"
                                    >
                                @else
                                    <img
                                            src="{{ mix('img/user-male-black-shape.png') }}"
                                            class="photo"
                                    >
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="staff-card">
                        <form
                                id="form_create_user"
                                class="form-horizontal"
                                action="{{ route('users::update', $user->id) }}"
                                method="post"
                                enctype="multipart/form-data"
                        >
                            @csrf
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-sm-3 col-form-label">Фамилия
                                        <star class="star">*</star>
                                    </label>
                                    <div class="col-sm-7">
                                        <input
                                                class="form-control"
                                                type="text"
                                                name="last_name"
                                                value="{{ $user->last_name }}"
                                                required
                                                maxlength="50"
                                                placeholder="Фамилия" {{ Auth::user()->department_id == 5 ? '' : 'readonly' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-sm-3 col-form-label">Имя
                                        <star class="star">*</star>
                                    </label>
                                    <div class="col-sm-7">
                                        <input
                                                class="form-control"
                                                type="text"
                                                name="first_name"
                                                value="{{ $user->first_name }}"
                                                required
                                                maxlength="50"
                                                placeholder="Имя" {{ Auth::user()->department_id == 5 ? '' : 'readonly' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-sm-3 col-form-label">Отчество</label>
                                    <div class="col-sm-7">
                                        <input
                                                class="form-control"
                                                type="text"
                                                name="patronymic"
                                                value="{{ $user->patronymic }}"
                                                maxlength="50"
                                                placeholder="Отчество" {{ Auth::user()->department_id == 5 ? '' : 'readonly' }}>
                                    </div>
                                </div>
                            </div>

                            @can('users_edit')

                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label">Отчетная группа
                                            <star class="star">*</star>
                                        </label>
                                        <div class="col-sm-7">
                                            <select
                                                    id="reporting_group"
                                                    name="reporting_group_id"
                                                    style="width:100%;"
                                                    required
                                            >
                                                <option value="">Не выбрано</option>
                                                @foreach($reportingGroups as $reportingGroup)
                                                    <option
                                                            value="{{ $reportingGroup->id }}"
                                                            @if($user->reporting_group_id == $reportingGroup->id) selected @endif>{{ $reportingGroup->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label">Подразделение
                                            <star class="star">*</star>
                                        </label>
                                        <div class="col-sm-7">
                                            <select
                                                    id="department"
                                                    name="department_id"
                                                    style="width:100%;"
                                                    required
                                            >
                                                <option value="">Не выбрано</option>
                                                @foreach($departments as $department)
                                                    <option
                                                            value="{{ $department->id }}"
                                                            @if($user->department_id == $department->id) selected @endif>{{ $department->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label">Должность
                                            <star class="star">*</star>
                                        </label>
                                        <div class="col-sm-7">
                                            <select
                                                    class="js-groups"
                                                    id="group_id"
                                                    name="group_id"
                                                    style="width:100%;"
                                                    required
                                            >
                                                <option value="">Не выбрано</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label">Компания
                                            <star class="star">*</star>
                                        </label>
                                        <div class="col-sm-7">
                                            <select
                                                    id="company"
                                                    class="js-groups"
                                                    name="company"
                                                    style="width:100%;"
                                                    required
                                            >
                                                <option value="">Не выбрана</option>
                                                @foreach($companies as $key => $company)
                                                    <option
                                                            value="{{ $key }}"
                                                            @if($user->company == $key) selected @endif>{{ $company }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @endcan

                            <div class="form-group">
                                <div class="row">
                                    <label class="col-sm-3 col-form-label">Дата рождения</label>
                                    <div class="col-sm-4">
                                        <input
                                                id="birthday"
                                                name="birthday"
                                                type="text"
                                                value="{{ $birthday }}"
                                                class="form-control"
                                                placeholder="Выберите дату"
                                                autocomplete="off"
                                        >
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-sm-3 col-form-label">Телефон</label>
                                    <div class="col-sm-4">
                                        <input
                                                class="form-control raz"
                                                type="text"
                                                value="{{ $user->person_phone }}"
                                                id="person_phone"
                                                name="person_phone"
                                                placeholder="7 (999) 555 32-11"
                                                minlength="17"
                                                maxlength="17"
                                        >

                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-sm-3 col-form-label">Рабочий телефон</label>
                                    <div class="col-sm-4">
                                        <input
                                                class="form-control raz"
                                                id="work_phone"
                                                type="text"
                                                value="{{ $user->work_phone }}"
                                                name="work_phone"
                                                maxlength="5"
                                        >
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-sm-3 col-form-label">Email
                                        <star class="star">*</star>
                                    </label>
                                    <div class="col-sm-6">
                                        <input
                                                class="form-control"
                                                type="email"
                                                id="email"
                                                value="{{ $user->email }}"
                                                pattern="[a-zA-Z0-9\.-\s]+@[a-zA-Z0-9\-\s]+\.[a-zA-Z0-9]+"
                                                name="email"
                                                maxlength="50" {{ $user->email ? 'required' : '' }} {{ Auth::user()->can('users_create') ? '' : 'readonly' }}>
                                    </div>
                                </div>
                            </div>
                            @if(Auth::user()->is_su == 1)
                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label">Telegram Chat ID</label>
                                        <div class="col-sm-6">
                                            <input
                                                    class="form-control"
                                                    type="text"
                                                    value="{{ $user->chat_id }}"
                                                    name="chat_id"
                                                    maxlength="50"
                                            >
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @can('users_edit')
                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label">Пароль
                                            <star class="star">*</star>
                                        </label>
                                        <div class="col-sm-6">
                                            <input
                                                    class="form-control"
                                                    type="password"
                                                    autocomplete="off"
                                                    pass="pass"
                                                    id="password"
                                                    name="password"
                                                    minlength="7"
                                                    onchange="checkPassword(this)"
                                            >
                                            <small
                                                    class="text-muted"
                                                    style="font-size: 12px"
                                            >
                                                Пароль должен иметь латинские символы верхнего и нижнего регистра, а
                                                также цифры
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label">Повторите пароль
                                            <star class="star">*</star>
                                        </label>
                                        <div class="col-sm-6">
                                            <input
                                                    class="form-control"
                                                    type="password"
                                                    pass="pass"
                                                    id="password_confirmation"
                                                    name="password_confirmation"
                                                    onchange="checkConfirmation(this)"
                                                    readonly
                                            >
                                            <small
                                                    class="text-muted"
                                                    style="font-size: 12px"
                                            >
                                                Пароли должны совпадать
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endcan

                            @can('users_edit')
                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label">Статус
                                            <star class="star">*</star>
                                        </label>
                                        <div
                                                class="col-sm-4"
                                                style="padding-top: 0"
                                        >
                                            <select
                                                    name="status"
                                                    class="selectpicker"
                                                    data-style="btn-default btn-outline"
                                                    data-menu-style="dropdown-blue"
                                                    required
                                            >
                                                <option
                                                        value="1"
                                                        @if($user->status == 1) selected @endif>Активен
                                                </option>
                                                <option
                                                        value="0"
                                                        @if($user->status == 0) selected @endif>Не активен
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @endcan
                            <div class="form-group">
                                <div class="row">
                                    <label
                                            class="col-sm-3 col-form-label"
                                            for="exampleFormControlFile1"
                                    >Фото</label>
                                    <div
                                            class="col-sm-4"
                                            style="padding-top:0px;"
                                    >
                                        <div class="file-container">
                                            <div
                                                    id="fileName"
                                                    class="file-name"
                                            ></div>
                                            <div class="file-upload ">
                                                <label class="pull-right">
                                                    <span><i
                                                                class="nc-icon nc-attach-87"
                                                                style="font-size:25px; line-height: 40px"
                                                        ></i></span>
                                                    <input
                                                            type="file"
                                                            accept="image/*"
                                                            name="user_image"
                                                            onchange="getFileName(this)"
                                                            id="uploadedFile"
                                                    >
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <button
                                id="update_user_submit"
                                type="submit"
                                form="form_create_user"
                                class="btn btn-info btn-outline"
                        >Сохранить
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('users_delete')
        <div
                class="modal fade modal-primary"
                id="deleteUser"
                role="dialog"
                aria-labelledby="modal-search"
                aria-hidden="true"
        >
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-body">
                        <form
                                id="remove_user"
                                action="{{ route('users::remove', $user->id) }}"
                                method="post"
                        >
                            @csrf
                            <div class="row">
                                <div class="col-sm-12">
                                    <label class="col-form-label">Заместитель
                                        <star class="star">*</star>
                                    </label>
                                    <select
                                            id="js-select-user"
                                            name="support_user_id"
                                            style="width: 100%"
                                            required
                                            onchange="checkValue(this)"
                                    ></select>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button
                                type="button"
                                class="btn btn-link btn-simple"
                                data-dismiss="modal"
                        >Закрыть
                        </button>
                        <button
                                id="remove_submit"
                                class="btn btn-sm btn-info btn-outline"
                                disabled="disabled"
                                onclick="deleteUser()"
                        >Сохранить
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection

@section('js_footer')
    <script src="{{ asset('js/plugins/jquery.mask.min.js') }}"></script>

    <meta
            name="csrf-token"
            content="{{ csrf_token() }}"
    />

    <script>
      var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

      $('.js-groups').select2({
        language: 'ru',
      });
      $('#department').select2({
        language: 'ru',
      });

      $('#reporting_group').select2({
        language: 'ru',
      });

      $('#birthday').datetimepicker({
        format: 'DD.MM.YYYY',
        locale: 'ru',
        icons: {
          time: 'fa fa-clock-o',
          date: 'fa fa-calendar',
          up: 'fa fa-chevron-up',
          down: 'fa fa-chevron-down',
          previous: 'fa fa-chevron-left',
          next: 'fa fa-chevron-right',
          today: 'fa fa-screenshot',
          clear: 'fa fa-trash',
          close: 'fa fa-remove',
        },
        maxDate: moment(),
        date: {!! ($birthday == 'Не указан') ? 'null' : "'".$birthday."'" !!}
      });

      $('#work_phone').mask('00000');

      $('#person_phone').mask('7 (000) 000-00-00');

      $.ajax({
        url: '{{ route("users::department") }}',
        type: 'POST',
        data: {
          _token: CSRF_TOKEN,
          department_id: $('#department').val(),
        },
        dataType: 'JSON',
        success: function (data) {
          $('#group_id').html('');
          $.each(data, function (key, value) {
            var selected = '';

            if (value.id == {{ $user->group_id }}) {
              selected = 'selected';
            }

            $('#group_id').prepend($('<option ' + selected + '></option>')
              .attr('value', value.id)
              .text(value.name));
          });

        },
      });

      $('#department').change(function () {
        $.ajax({
          url: '{{ route("users::department") }}',
          type: 'POST',
          data: {
            _token: CSRF_TOKEN,
            department_id: $('#department').val(),
          },
          dataType: 'JSON',
          success: function (data) {
            $('#group_id').html('');
            $.each(data, function (key, value) {
              $('#group_id').prepend($('<option></option>')
                .attr('value', value.id)
                .text(value.name));
            });

          },
        });
      });

    </script>

    @vite('resources/js/form-validation.js')
    <script>
      $('#js-select-user').select2({
        language: 'ru',
        ajax: {
          url: '/tasks/get-users',
          dataType: 'json',
          delay: 250,
        },
      });
      @can('users_edit')
      function checkValue(e) {
        if (!$(e).val()) {
          $('#remove_submit').attr('disabled', 'disabled');
        } else {
          $('#remove_submit').removeAttr('disabled');
        }
      }

      function checkPassword(elem) {
        password = $(elem).val();

        if (password.length === 0) {
          $(elem).removeClass('is-invalid');
          $('#password_confirmation').attr('readonly', 'readonly');
          $('#password_confirmation').removeAttr('required');
          $('#password_confirmation').val('');
        } else {
          if (password.match(/(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])/)) {
            $(elem).removeClass('is-invalid');

            $('#password_confirmation').removeAttr('readonly');
            $('#password_confirmation').attr('required', 'required');
          } else {
            $(elem).addClass('is-invalid');
            $('#password_confirmation').attr('readonly', 'readonly');
            $('#password_confirmation').removeAttr('required');
          }
        }
      }

      function checkConfirmation(elem) {
        confirmation = $(elem).val();

        if (confirmation.match(/(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])/) && confirmation == $('#password').val()) {
          $(elem).removeClass('is-invalid');

          $('#update_user_submit').removeAttr('disabled');
        } else {
          $(elem).addClass('is-invalid');
          $('#update_user_submit').attr('disabled', 'disabled');
        }
      }
      @endcan

      @can('users_delete')
      function deleteUser() {
        swal({
          title: 'Внимание',
          text: 'Вы уверены, что хотите удалить сотрудника?',
          type: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          cancelButtonText: 'Назад',
          confirmButtonText: 'Удалить',
        }).then((result) => {
          if (result.value) {
            $('#remove_user').submit();
          }
        });
      }

      @endcan

      @if(session()->has('bad_request'))
      swal({
        title: 'Внимание',
        text: "{{ session()->get('bad_request') }}",
        type: 'warning',
        timer: 4000,
      });
      @endif

      @if(session()->has('too_much_vacations'))
      swal({
        title: 'Внимание',
        text: "{{ session()->get('too_much_vacations') }}",
        type: 'warning',
        timer: 4000,
      });
        @endif
    </script>

@endsection
