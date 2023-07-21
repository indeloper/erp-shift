@extends('layouts.app')

@section('title', 'Сотрудники')

@section('url', route('users::index'))

@section('css_top')
<script>
    localStorage.setItem('userprofilevisited', true);
</script>
<style>
  .custom-tree-node {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 16px;
    padding-right: 8px;
  }

  .el-tree-node__content {
    height: 35px!important;
}

@media (min-width: 320px) and (max-width:768px){

    .el-transfer-panel {
       width: 100%!important;
   }

   .el-transfer__buttons {
       transform:rotate(90deg);
       align-self: center;
   }

   .el-transfer {
       display: flex;
       flex-direction: column;
   }
}

@media (min-width: 767.9px){
    .el-transfer-panel {
       width: 40%!important;
   }

   .el-transfer {
       display: flex;
       justify-content: space-between;
   }
   .el-transfer__buttons {
       align-self: center;
   }
}

.el-dialog {
    max-width: 900px!important;
}
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <div class="left-edge">
                            <div class="page-container">
                                <h4 class="card-title" style="margin-top: 5px">
                                    {{ $user->last_name }} {{ $user->first_name }} {{ $user->patronymic }}
                                </h4>
                            </div>
                        </div>
                    </div>
                    @canany(['users_vacations', 'user_job_category_change'])
                        <div class="col-md-6">
                            <div class="right-edge">
{{--                                @can('user_job_category_change')--}}
{{--                                    <button class="btn btn-sm btn-outline" data-toggle="modal" data-target="#change_job_category">--}}
{{--                                        Изменить должностную категорию--}}
{{--                                    </button>--}}
{{--                                @endcan--}}
                                @can('users_vacations')
                                    @if($user->in_vacation == 0)
                                        <a class="btn btn-sm btn-info btn-outline" href="#" data-toggle="modal" data-target="#vacation">
                                            Отпуск
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-info btn-outline" onclick="outFromVacation()">
                                            Выход из отпуска
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </div>
                    @endcanany
                </div>
                <hr>
            </div>
            <div class="card-body">
                <div class="float-left-container">
                    <div class="photo-container">
                        <div class="photocard">
                            @if($user->image)
                                <img src="{{ asset('storage/img/user_images/' . $user->image) }}" class="photo">
                            @else
                                <img src="{{ mix('img/user-male-black-shape.png') }}" class="photo" >
                            @endif
                        </div>
                        @if(Auth::user()->id == $user->id or Auth::user()->can('users_edit'))
                        <a class="btn btn-sm btn-info btn-outline edit-employee" href="{{ route('users::edit', $user->id) }}">
                            Редактировать
                        </a>
                        <a href="#" class="btn btn-sm btn-outline btn-password" data-toggle="modal" data-target="#password"><i class="fa fa-lock"></i></a>
                        @endif
                    </div>
                </div>
                <div class="staff-card">
                    <div class="row info-string line-bottom">
                        <label class="col-sm-3 col-form-label label-info-card">ФИО</label>
                        <div class="col-sm-9">
                            <p class="p-info-card">{{ $user->last_name }} {{ $user->first_name }} {{ $user->patronymic }}</p>
                        </div>
                    </div>
                    <div class="row info-string line-bottom">
                        <label class="col-sm-3 col-form-label label-info-card">Должность</label>
                        <div class="col-sm-9">
                            <p class="p-info-card"> {{ $group ? $group->name : 'Не указан'}} </p>
                        </div>
                    </div>
                    <div class="row info-string line-bottom">
                        <label class="col-sm-3 col-form-label label-info-card">Подразделение</label>
                        <div class="col-sm-9">
                            <p class="p-info-card"> {{ $department ? $department->name : 'Не указано'}} </p>
                        </div>
                    </div>
                    <div class="row info-string line-bottom">
                        <label class="col-sm-3 col-form-label label-info-card">Должностная категория</label>
                        <div class="col-sm-9">
                            <p class="p-info-card"> {{ $user->jobCategory ? $user->jobCategory->name : 'Не указана' }} </p>
                        </div>
                    </div>
                    <div class="row info-string line-bottom">
                        <label class="col-sm-3 col-form-label label-info-card">Компания</label>
                        <div class="col-sm-9">
                            <p class="p-info-card"> {{ $companies[$user->company] ?? 'ООО "СК ГОРОД"' }} </p>
                        </div>
                    </div>
                    <div class="row info-string line-bottom">
                        <label class="col-sm-3 col-form-label label-info-card">Дата рождения</label>
                        <div class="col-sm-9">
                            <p class="p-info-card">{{ $user->birthday }}</p>
                        </div>
                    </div>
                    <div class="row info-string line-bottom" style="padding-bottom:5px">
                        <label class="col-sm-3 col-form-label label-info-card">Телефон</label>
                        <div class="col-sm-9">
                            @if($user->person_phone)
                            {{ 'т. +' . substr($user->person_phone, 0, 1) . ' ('
                                     . substr($user->person_phone, 1, 3) . ') ' . substr($user->person_phone, 4, 3) . '-' . substr($user->person_phone, 7, 2)
                                     . '-' . substr($user->person_phone, 9, 2) }}
                            @endif
                        </div>
                    </div>
                    <div class="row info-string line-bottom">
                        <label class="col-sm-3 col-form-label label-info-card">Рабочий телефон</label>
                        <div class="col-sm-9">
                            <p class="p-info-card"> {{ $user->work_phone }} </p>
                        </div>
                    </div>
                    <div class="row info-string line-bottom">
                        <label class="col-sm-3 col-form-label label-info-card">email</label>
                        <div class="col-sm-9">
                            <p class="p-info-card"> {{ $user->email ? $user->email : 'Отсутствует' }} </p>
                        </div>
                    </div>
                    <div class="row info-string @if($user->in_vacation == 1 or ($user->in_vacation == 0 and isset($vacation))) line-bottom @endif">
                        <label class="col-sm-3 col-form-label label-info-card">Статус</label>
                        <div class="col-sm-9">
                            @if($user->status)
                                <p class="p-info-card"> Активен </p>
                            @else
                                <p class="p-info-card"> Не активен </p>
                            @endif
                        </div>
                    </div>
                    @if($user->in_vacation == 1)
                        <div class="row info-string">
                            <label class="col-sm-3 col-form-label label-info-card">Отпуск</label>
                            <div class="col-sm-9">
                                <p class="p-info-card">Пользователь в отпуске с {{ $vacation->from_date }} по {{ $vacation->by_date }}
                                    Заместитель: {{ $vacation->support_user->full_name }}
                                    @if($vacation->change_authority) <br>Заместителю на время отпуска передана должность сотрудника, уходящего в отпуск
                                    @endif
                                </p>
                            </div>
                        </div>
                    @elseif($user->in_vacation == 0 and isset($vacation))
                        <div class="row info-string">
                            <label class="col-sm-3 col-form-label label-info-card">Отпуск</label>
                            <div class="col-sm-9">
                                <p class="p-info-card">Пользователь будет в отпуске с {{ $vacation->from_date }} по {{ $vacation->by_date }}.
                                    Заместитель: {{ $vacation->support_user->full_name }}
                                    @if($vacation->change_authority) <br>Заместителю на время отпуска будет передана должность сотрудника, уходящего в отпуск
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
                @can('users_permissions')
                <div class="card-header" style="margin-bottom:30px">
                    <h4 class="card-title" style="margin-bottom:10px">
                        Список прав доступа
                        <button type="button" name="button" class="btn btn-link mn-0 pd-0"  data-toggle="modal" data-target="#permissions">
                            <i class="fa fa-cog"></i>
                        </button>
                    </h4>
                    <div id="access">
                        <el-tree :data="data" :props="defaultProps" @node-click="handleNodeClick"></el-tree>
                    </div>
                </div>
                @endcan

                @can('projects')
                <div class="card-header">
                    <h4 class="card-title"> Проекты </h4>
                </div>
                <div class="card-body card-body-table">

                    @if(!$projects->isEmpty())
                   <div class="card strpied-tabled-with-hover">
                        <div class="table-responsive">
                            <table class="table table-hover mobile-table">
                                <thead>
                                    <tr>
                                        <th>Проект</th>
                                        <th>Контрагент</th>
                                        <th>Руководитель проекта</th>
                                        <th>Шпунт
                                            @php $key = 1; @endphp
                                            <button type="button" name="button" class="btn btn-link btn-primary btn-xs pd-0" data-html="true" data-container="body" data-toggle="popover" data-placement="right"
                                                    data-content="@foreach($projects->first()->getModel()->project_status as $id => $status)
                                                    {{$key++ . '. ' . $status . ' - ' . $projects->first()->getModel()->project_status_description[$id] }}<br/><br/>
                                                    @endforeach">
                                                <i class="fa fa-info-circle"></i>
                                            </button>
                                        </th>
                                        <th>Сваи</th>
                                        <th class="text-center">Доп. информация</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($projects as $project)
                                    <tr>
                                        <td data-label="Проект">
                                            <a class="table-link" href="{{ route('projects::card', $project->id) }}">
                                                {{ $project->name }}
                                            </a>
                                        </td>
                                        <td data-label="Контрагент">
                                            <a class="table-link" href="{{ route('contractors::card', $project->contractor_id) }}">
                                                {{ $project->contractor_name }}
                                            </a>
                                        </td>
                                        <td data-label="Руководитель проекта">{{ $project->rp_names }}</td>
                                        <td  data-label="Шпунт">{!! $project->tongue_statuses !!}</td>
                                        <td  data-label="Сваи">{!! $project->pile_statuses !!}</td>
                                        <td data-label="Доп. информация" class="text-center">-</td>

                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @else
                        <p class="text-center">В этом разделе пока нет ни одного проекта</p>
                    @endif
                </div>

                @endcan
            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-primary" id="password" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <form class="" id="change_password" action="{{ route('users::change_password', $user->id) }}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-sm-12">
                            <label class="col-form-label">Старый пароль<star class="star">*</star></label>
                            <input class="form-control" pass="pass" type="password" name="old_password" minlength="7" onchange="checkOldPassword(this)" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <label class="col-form-label">Новый пароль<star class="star">*</star></label>
                            <input id="password_input" class="form-control" pass="pass" type="password" name="password" minlength="7" onchange="checkPassword(this)" required readonly="readonly">
                            <small class="text-muted" style="font-size: 12px">
                                Пароль должен иметь латинские символы верхнего и нижнего регистра, а также цифры
                            </small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <label class="col-form-label">Повторите пароль<star class="star">*</star></label>
                            <input id="password_confirmation" class="form-control" pass="pass" type="password" name="password_confirmation" minlength="7" onchange="checkConfirmation(this)" required readonly="readonly">
                            <small class="text-muted" style="font-size: 12px">
                                Пароли должны совпадать
                            </small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link btn-simple" data-dismiss="modal">Закрыть</button>
                <button id="update_user_submit" type="submit" form="change_password" class="btn btn-sm btn-info btn-outline" disabled="disabled">Сохранить</button>
            </div>
        </div>
    </div>
</div>

@can('users_vacations')
@if($user->in_vacation == 0)
<div class="modal fade modal-primary" id="vacation" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-update">Отпуск сотрудника</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="send_vacation" action="{{ route('users::to_vacation', $user->id) }}" method="post">
                    @csrf
                    <input type="hidden" name="vacation_user_id" value="{{ $user->id }}">
                    <div class="row">
                        <div class="col-sm-12">
                            <label class="col-form-label">Заместитель<star class="star">*</star></label>
                            <select id="js-select-user" name="support_user_id" style="width: 100%" required></select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <label class="col-form-label">Период действия отпуска</label>
                        </div>
                        <div class="col-sm-6">
                            <label class="col-form-label">С даты<star class="star">*</star></label>
                            <input id="from" name="from_date" class="form-control datepicker" autocomplete="off" required onchange="checkDates()">
                        </div>
                        <div class="col-sm-6">
                            <label class="col-form-label">По дату<star class="star">*</star></label>
                            <input id="by" name="by_date" class="form-control datepicker" autocomplete="off" required onchange="checkDates()">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-check pull-right" style="display:inline-block">
                                <label class="form-check-label" style="margin-top:12px">
                                    <input class="form-check-input" type="checkbox" name="change_authority">
                                    <span class="form-check-sign"></span>
                                    Передать полномочия
                                </label>
                                <button type="button" name="button" class="btn btn-link btn-primary btn-xs mn-0 pd-0" data-container="body"
                                        data-toggle="popover" data-placement="top" data-content="Заместителю на время отпуска будет дана должность сотрудника, уходящего в отпуск">
                                    <i class="fa fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm" data-dismiss="modal">Закрыть</button>
                <button id="vacation_submit" type="submit" form="send_vacation" class="btn btn-sm btn-info btn-outline" disabled="disabled">Сохранить</button>
            </div>
        </div>
    </div>
</div>
@endif
@endcan
@can('user_job_category_change')
<div class="modal fade modal-primary" id="change_job_category" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-update">Изменение должностной категории сотрудника</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-0">
                <div class="card border-0 m-0">
                    <div class="card-body">
                        <validation-observer ref="observer" :key="observer_key">
                            <form id="job_category_form" class="form-horizontal">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="">Должностная категория<span class="star">*</span></label>
                                        <validation-provider rules="required" vid="job-category-select"
                                                             ref="job-category-select" v-slot="v">
                                            <el-select v-model="job_category_id"
                                                       :class="v.classes"
                                                       clearable filterable
                                                       id="job-category-select"
                                                       :remote-method="search_job_categories"
                                                       @clear="search_job_categories('')"
                                                       remote
                                                       placeholder="Поиск должностной категории"
                                            >
                                                <el-option
                                                    v-for="item in job_categories"
                                                    :key="item.id"
                                                    :label="item.name"
                                                    :value="item.id">
                                                </el-option>
                                            </el-select>
                                            <div class="error-message">@{{ v.errors[0] }}</div>
                                        </validation-provider>
                                    </div>
                                </div>
                            </form>
                        </validation-observer>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="col-md-12">
                    <div class="row justify-content-center mb-2">
                        <el-button type="info" class="btn btn-secondary" data-dismiss="modal">Закрыть</el-button>
                        <el-button @click.stop="submit" :loading="loading_submit">Сохранить</el-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endcan
@can('user_permissions')
<div class="modal fade bd-example-modal-lg" id="permissions" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg permission-modal" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Настройка прав пользователя</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card border-0">
                    <div class="card-body">
                        <div>
                            <el-transfer
                              filterable
                              :filter-method="filterMethod"
                              filter-placeholder=""
                              v-model="value"
                              :titles="['Список прав', 'Назначенные права']"
                              :data="permissions">
                            </el-transfer>
                        </div>
                    </div>
                </div>
                <div class="row" style="margin-top:50px">
                    <div class="col-md-12 text-center">
                        <el-button type="primary" @click="add_permissions" :loading="is_loading">Добавить права</el-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endcan
@endsection

@section('js_footer')
<meta name="csrf-token" content="{{ csrf_token() }}" />
<script>
    var permission = new Vue({
        el: '#permissions',
        data: {
            permissions: {!! json_encode($permissions) !!},
            value: {{ $user->permissions()->pluck('permission_id') }},
            is_loading: false,
            user_id: {{ $user->id }}
        },
        methods: {
            filterMethod(query, item) {
              return item.label.toLowerCase().indexOf(query.toLowerCase()) > -1;
            },
            add_permissions() {
                permission.is_loading = true;

                axios.post("{{ route('users::add_permissions') }}", {type: 'user', permission_ids: permission.value, user_id: permission.user_id }).then(function (response) {
                    permission.is_loading = false;
                    location.reload();
                })
            }
        }
    });

@can('users_permissions')
    var access = new Vue ({
        el:'#access',
        data() {
          return {
            data: [{
              label: 'Права отдела',
              children: {!! json_encode($department_perms) !!} //not done
            }, {
              label: 'Права должности',
              children: {!! json_encode($group_permissions) !!}
            }, {
              label: 'Индивидуальные права',
              children:  {!! json_encode($user->permissions()) !!}
            }],
            defaultProps: {
              children: 'children',
              label: 'label'
            }
          };
        },
        methods: {
          handleNodeClick(data) {

          }
        }
    });
@endcan
{{--@can('user_job_category_change')--}}
{{--    Vue.component('validation-provider', VeeValidate.ValidationProvider);--}}
{{--    Vue.component('validation-observer', VeeValidate.ValidationObserver);--}}
{{--    var jobCategoryForm = new Vue({--}}
{{--        el: '#change_job_category',--}}
{{--        data: {--}}
{{--            user_id: '{{ $user->id }}',--}}
{{--            job_category_id: '',--}}
{{--            job_categories: [],--}}
{{--            observer_key: 1,--}}
{{--            loading_submit: false--}}
{{--        },--}}
{{--        mounted() {--}}
{{--            this.search_job_categories('');--}}
{{--        },--}}
{{--        methods: {--}}
{{--            search_job_categories(query) {--}}
{{--                if (query) {--}}
{{--                    axios.get('{{ route('human_resources.job_category.get') }}', {params: { q: query }})--}}
{{--                    .then(response => this.job_categories = response.data.map(el => ({--}}
{{--                        name: el.name,--}}
{{--                        id: el.id--}}
{{--                    })))--}}
{{--                    .catch(error => console.log(error));--}}
{{--                } else {--}}
{{--                    axios.get('{{ route('human_resources.job_category.get') }}')--}}
{{--                    .then(response => this.job_categories = response.data.map(el => ({--}}
{{--                        name: el.name,--}}
{{--                        id: el.id--}}
{{--                    })))--}}
{{--                    .catch(error => console.log(error));--}}
{{--                }--}}
{{--            },--}}
{{--            submit() {--}}
{{--                this.$refs.observer.validate().then(success => {--}}
{{--                    if (!success) {--}}
{{--                        const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);--}}
{{--                        $('.modal').animate({--}}
{{--                            scrollTop: $('#' + error_field_vid).offset().top--}}
{{--                        }, 1200);--}}
{{--                        $('#' + error_field_vid).focus();--}}
{{--                        return;--}}
{{--                    }--}}

{{--                    this.loading_submit = true;--}}
{{--                    const payload = {};--}}
{{--                    payload.user_id = this.user_id;--}}
{{--                    payload.job_category_id = this.job_category_id;--}}
{{--                    axios.post('{{ route('users::update_job_category') }}', payload)--}}
{{--                    .then((response) => {--}}
{{--                        location.reload();--}}
{{--                    }).catch(error => this.handleError(error));--}}
{{--                });--}}
{{--            }--}}
{{--        }--}}
{{--    });--}}
{{--@endcan--}}

var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

function checkOldPassword(elem) {
    old_password = $(elem).val();

    if (old_password.match(/(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])/)) {
        $(elem).removeClass('is-invalid');
        $('#password_input').removeAttr('readonly');
    } else {
        $(elem).addClass('is-invalid');
        $('#password_input').attr('readonly', 'readonly');
        $('#password_confirmation').attr('readonly', 'readonly');
    }
}

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

    if (confirmation.match(/(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])/) && confirmation == $('#password_input').val()) {
        $(elem).removeClass('is-invalid');
        $('#update_user_submit').removeAttr('disabled');
    } else {
        $(elem).addClass('is-invalid');
        $('#update_user_submit').attr('disabled', 'disabled');
    }
}

    @if(Auth::user()->department_id == 5 || Auth::user()->isInGroup(10)/*14*/ | auth()->id() == 1)
        @if($user->in_vacation == 0)
            $('#js-select-user').select2({
                language: "ru",
                ajax: {
                    url: '/tasks/get-users?not=' + '{{ $user->id }}',
                    dataType: 'json',
                    delay: 250
                }
            });

            $('#from').datetimepicker({
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
                minDate: moment().subtract(1, 'w'),
                date: null,
            }).on('dp.change', function(e) {
                checkDates()
            });

            $('#by').datetimepicker({
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
                minDate: moment().add(1, 'd'),
                date: null,

            }).on('dp.change', function(e) {
                checkDates()
            });

            function checkDates()
            {
                var from = moment($('#from').val(),"DD.MM.YYYY");
                var by = moment($('#by').val(),"DD.MM.YYYY");

                if (from > by || from === by) {
                    $('#vacation_submit').attr('disabled', 'disabled');
                } else {
                    $('#vacation_submit').removeAttr('disabled');
                }
            }
        });

        $('#from').datetimepicker({
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
            minDate: moment(),
            date: null,
        }).on('dp.change', function(e) {
            checkDates()
        });

        $('#by').datetimepicker({
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
            minDate: moment().add(1, 'd'),
            date: null,

        }).on('dp.change', function(e) {
            checkDates()
        });

        function checkDates()
        {
            var from = moment($('#from').val(),"DD.MM.YYYY");
            var by = moment($('#by').val(),"DD.MM.YYYY");

            if (from > by || from === by) {
                $('#vacation_submit').attr('disabled', 'disabled');
            } else {
                $('#vacation_submit').removeAttr('disabled');
            }
        }
    @else
    function outFromVacation()
    {
        swal({
            title: 'Подтверждение',
            text: "Подтвердите, что пользователь вышел из отпуска",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            cancelButtonText: 'Назад',
            confirmButtonText: 'Подтвердить'
        }).then((result) => {
            if(result.value) {
                $.ajax({
                    url:"{{ route('users::from_vacation', $user->id) }}",
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        vacation_id: {{ $vacation->id }}
                    },
                    dataType: 'JSON',
                    success: function () {
                        location.reload()
                    }
                });
            }
        });
    }
    @endif
@endif

@if(session()->has('pass'))
    swal({
        title: "Внимание",
        text: "Пароль не верен",
        type: 'warning',
        timer: 1000
    });
@endif

@if(session()->has('bad_request'))
    swal({
        title: "Внимание",
        text: "{{ session()->get('bad_request') }}",
        type: 'warning',
        timer: 4000
    });
@endif

@if(session()->has('too_much_vacations'))
    swal({
        title: "Внимание",
        text: "{{ session()->get('too_much_vacations') }}",
        type: 'warning',
        timer: 4000
    });
@endif
</script>
@endsection
