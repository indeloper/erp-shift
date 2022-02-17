@extends('layouts.app')

@section('title', 'Проекты')

@section('url', route('projects::index'))

@section('css_top')
<link href="{{ mix('css/projects.css') }}" rel="stylesheet" />
<style>
    .rTable {
        display: table;
        width: 100%;
        margin-bottom: 1rem;
        background-color: transparent;
        border-collapse: collapse;
        border-spacing: 2px;
        border-color: grey;
    }
    .rTableRow {
        display: table-row;
        padding: .75rem;
        vertical-align: top;
        border-top: 1px solid #dee2e6;
        font-size: 14px;
    }
    .rTableHeading {
        display: table-header-group;
        background-color: #ddd;
    }
    .rTableCell, .rTableHead {
        display: table-cell;
    }
    .rTableHeading {
        display: table-header-group;
        background-color: #ddd;
        font-weight: bold;
    }
    .rTableFoot {
        display: table-footer-group;
        font-weight: bold;
        background-color: #ddd;
    }
    .rTableBody {
        display: table-row-group;
    }
    .rTableHead span {
        font-size: 16px;
        font-weight: 600;
    }

    .select2-hidden-accessible {
        margin: 2.38em 0 0 140px !important;
    }
    .bootstrap-select>.dropdown-toggle {
        padding-right: 15px;
        /*min-width: 75px;*/
        width: 100%;
    }
    .bootstrap-select:not([class*="col-"]):not([class*="form-control"]):not(.input-group-btn) {
        width: 100%;
    }
    .bootstrap-select {
        min-width: auto;
        width: 100%;
    }
    .bootstrap-select.btn-group .dropdown-toggle .filter-option {
        width: 100%;
    }
    .form-check input[type="checkbox"], .form-check-radio input[type="radio"] {
        margin-left: -7px;
    }

    @media (max-width: 769px) {
        .responsive-button {
            width: 100%;
        }
    }
</style>

@endsection

@section('content')
<div class="row">
    <div class="col-sm-12 col-xl-10 mr-auto ml-auto">
        <div class="card @if ($project->is_important) card-important @endif">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="card-title" style="margin-top: 5px">{{ $project->name }}</h4>
                        @can('edit', $project)
                            <button class="btn btn-outline btn-sm" data-toggle="modal" data-target="#close_project">Отклонить направление</button>
                        @endcan
                        <!-- @cannot('edit', $project)
                            <button type="button" name="button" class="btn btn-link btn-primary btn-xs mn-0 pd-0" data-container="body" data-toggle="popover" data-placement="top" data-content="{{ $project->project_status_description[$project->status] }}">
                                <i class="fa fa-info-circle"></i>
                                {{ $project->project_status[$project->status] }}
                            </button>
                        @endcan -->
                    </div>
                    @can('edit', $project)
                        <div class="col-md-4">
                            <div class="pull-right" style="margin-top: 5px">
                                <a class="btn btn-outline btn-sm edit-btn" href="{{ route('projects::edit', $project->id) }}">
                                    <i class="glyphicon fa fa-pencil-square-o"></i>
                                    Редактировать
                                </a>
                                @includeWhen(auth()->user()->canWorkWithImportance(), 'projects.modules.importance_toggling')
                            </div>
                        </div>
                    @endcan
                </div>
                <hr style="margin-top:10px">
                <!-- <div class="relative" style="width:auto; height:auto;">
                    <div class="story">
                        <a href="#" class="story-link">История взаимодействий</a>
                    </div>
                </div> -->
            </div>
            <div class="card-body">
                <div class="accordions" id="accordion">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                <a data-target="#collapseOne" href="#" data-toggle="collapse">
                                    Основная информация
                                    <b class="caret"></b>
                                </a>
                            </h4>
                        </div>
                        <div id="collapseOne" class="card-collapse collapse show">
                            <div class="card-body">
                                <div class="row" style="margin-top:5px; margin-bottom:15px;">
                                    <label class="col-sm-3 col-form-label"> Контрагент </label>
                                    <div class="col-sm-9">
                                        <p class="form-control-static p-info-card"><a class="table-link">{{ $contractor->short_name }}</a></p>
                                    </div>
                                </div>
                                <div class="row" style="margin-top:5px; margin-bottom:15px;">
                                    <label class="col-sm-3 col-form-label"> Объект </label>
                                    <div class="col-sm-9">
                                        <p class="form-control-static p-info-card"><a class="table-link">{{ $object->name }}</a></p>
                                    </div>
                                </div>
                                <div class="row" style="margin-top:5px; margin-bottom:15px;">
                                    <label class="col-sm-3 col-form-label"> Адрес </label>
                                    <div class="col-sm-9">
                                        <p class="form-control-static p-info-card">{{ $object->address }}</p>
                                    </div>
                                </div>
                                <div class="row" style="margin-top:5px; margin-bottom:15px;">
                                    <label class="col-sm-3 col-form-label"> Юр. лицо </label>
                                    <div class="col-sm-9">
                                        <p class="form-control-static p-info-card">{{ $project::$entities[$project->entity] }}</p>
                                    </div>
                                </div>
                                @if ($project->description)
                                    <div class="row" style="margin-top:5px; margin-bottom:15px;">
                                        <label class="col-sm-3 col-form-label"> Описание </label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static p-info-card" align="justify">
                                                {{ $project->description }}
                                            </p>
                                        </div>
                                    </div>
                                @endif
                                @if(!isset($additional_contractors))
                                    @if(Gate::check('projects_create') or in_array(Auth::id(), $project->respUsers()->whereIn('role', [5, 6])->get()->pluck('user_id')->toArray() ) )
                                        <div class="pull-right">
                                            <button class="btn btn-round btn-outline btn-sm add-btn" data-toggle="modal" data-target="#select-contractors">
                                                <i class="glyphicon fa fa-plus"></i>
                                                Добавить контрагентов
                                            </button>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                    @if(isset($additional_contractors))
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <a data-target="#collapseContractors" href="#" data-toggle="collapse">
                                        Контрагенты
                                        <b class="caret"></b>
                                    </a>
                                </h4>
                            </div>
                            <div id="collapseContractors" class="card-collapse collapse @if(session('add_contractors')) show @endif">
                                <div class="card-body card-body-table">
                                    <div class="card striped-tabled-with-hover">
                                        @if(Gate::check('edit', $project) or in_array(Auth::id(), $project->respUsers()->whereIn('role', [5, 6])->get()->pluck('user_id')->toArray()) )
                                            <div class="fixed-table-toolbar toolbar-for-btn">
                                                <div class="pull-right">
                                                    <button class="btn btn-round btn-outline btn-sm add-btn" data-toggle="modal" data-target="#select-contractors">
                                                        <i class="glyphicon fa fa-plus"></i>
                                                        Добавить контрагентов
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="table-responsive">
                                            <table class="table table-hover mobile-table">
                                                <thead>
                                                    <tr>
                                                        <th>Контрагент</th>
                                                        <th>Тип</th>
                                                        @can('edit', $project)
                                                            <th class="text-right">Действия</th>
                                                        @endcan
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($additional_contractors as $relation)
                                                    <tr>
                                                        <td data-label="Контрагент">
                                                            <a href="{{ route('contractors::card', $relation->contractor->id) }}" class="table-link">
                                                                {{ $relation->contractor->short_name }}
                                                            </a>
                                                        </td>
                                                        <td data-label="Тип">
                                                            {{ $relation->contractor->type_name }}
                                                        </td>
                                                        @can('edit', $project)
                                                            <td data-label="" class="td-actions text-right actions">
                                                                <a href="#" class="btn btn-link mn-0 padding-actions" onclick="useAsMainContractor(this, {{ $relation->id }})"
                                                                   rel="tooltip" data-original-title="Сделать основным">
                                                                    <i class="far fa-check-circle" aria-hidden="true"></i>
                                                                </a>
                                                                <a href="#" class="btn btn-link btn-danger mn-0 padding-actions" onclick="removeAdditionalContractor(this, {{ $relation->id }})"
                                                                   rel="tooltip" data-original-title="Удалить связь">
                                                                    <i class="fa fa-times" aria-hidden="true"></i>
                                                                </a>
                                                            </td>
                                                        @endcan
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                <a data-target="#collapseThree" href="#" data-toggle="collapse">
                                    Ответственные лица
                                    <b class="caret"></b>
                                </a>
                            </h4>
                        </div>
                        <div id="collapseThree" class="card-collapse collapse @if(session('users') || Request::has('task_14')) show @endif">
                            <div class="card-body card-body-table">
                                <div class="card strpied-tabled-with-hover">
                                    @if(Gate::check('projects_responsible_users') or in_array(Auth::id(), $project->respUsers()->whereIn('role', [5, 6])->get()->pluck('user_id')->toArray()) )
                                        <div class="fixed-table-toolbar toolbar-for-btn">
                                            <div class="pull-right">
                                                <button class="btn btn-round btn-outline btn-sm add-btn" data-toggle="modal" data-target="#select-user">
                                                    <i class="glyphicon fa fa-plus"></i>
                                                    Выбрать из списка
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="table-responsive">
                                        <table class="table table-hover mobile-table">
                                            <thead>
                                                <tr>
                                                    <th>ФИО</th>
                                                    <th>Должность</th>
                                                    <th>Позиция</th>
                                                    @can('projects_responsible_users')
                                                    <th class="text-right">Действия</th>
                                                    @endcan
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td data-label="ФИО">
                                                        <a href="{{ route('users::card', $creater->id) }}" class="table-link">
                                                            {{ $creater->last_name }} {{ $creater->first_name }} {{ $creater->patronymic }}
                                                        </a>
                                                    </td>
                                                    <td data-label="Должность">{{ $creater->group_name }}</td>
                                                    <td data-label="Позиция">Автор проекта</td>
                                                    @can('projects_responsible_users')
                                                        <td data-label="" class="text-right actions"></td>
                                                        @else
                                                        <td></td>
                                                    @endcan
                                                </tr>
                                                @foreach($resp_users as $user)
                                                    <tr>
                                                        <td data-label="ФИО">
                                                            <a href="{{ route('users::card', $user->id) }}" class="table-link">
                                                                {{ $user->last_name }} {{ $user->first_name }} {{ $user->patronymic }}
                                                            </a>
                                                        </td>
                                                        <td data-label="Должность">{{ $user->group_name }}</td>
                                                        @if ($user->role)
                                                            <td data-label="Позиция">{{ $user->role_codes[$user->role] }}</td>
                                                        @else
                                                            <td data-label="Позиция">Не указана</td>
                                                        @endif
                                                        @if(Gate::check('projects_responsible_users') or (
                                                        in_array(Auth::id(), $project->respUsers()->where('role', 5)->get()->pluck('user_id')->toArray()) and $user->role == 8
                                                        )or (
                                                        in_array(Auth::id(), $project->respUsers()->where('role', 6)->get()->pluck('user_id')->toArray()) and $user->role == 9
                                                        )
                                                        )
                                                            <td data-label="" class="td-actions text-right actions">
                                                                <a href="#" class="btn btn-link btn-danger remove_responsible_user mn-0 padding-actions" user_id="{{ $user->id }}" project_id="{{ $project->id }}" role="{{ $user->role }}"><i class="fa fa-times"></i></a>
                                                            </td>
                                                            @else
                                                            <td></td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                                @if($project->timeResponsible)
                                                    <tr>
                                                        <td data-label="ФИО">
                                                            <a href="{{ $project->timeResponsible->card_route }}" class="table-link">
                                                                {{ $project->timeResponsible->full_name }}
                                                            </a>
                                                        </td>
                                                        <td data-label="Должность">{{ $project->timeResponsible->group_name }}</td>
                                                        <td data-label="Позиция">Отв. за учёт рабоч. врем.</td>
                                                        @can('human_resources_project_time_responsible_user_change')
                                                            <td data-label="" class="td-actions text-right actions">
                                                                <a href="#" class="btn btn-link btn-danger remove_responsible_time_user mn-0 padding-actions" user_id="{{ $user->id }}" project_id="{{ $project->id }}" role="{{ $user->role }}"><i class="fa fa-times"></i></a>
                                                            </td>
                                                        @else
                                                            <td></td>
                                                        @endcan
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                <a data-target="#collapseFour" href="#" data-toggle="collapse">
                                    Контакты
                                    <b class="caret"></b>
                                </a>
                            </h4>
                        </div>
                        <div id="collapseFour" class="card-collapse collapse @if(session('contacts')) show @endif">
                            <div class="card-body card-body-table">
                                <div class="card strpied-tabled-with-hover">
                                    @can('edit', $project)
                                    <div class="fixed-table-toolbar toolbar-for-btn">
                                        <div class="pull-right">
                                            <button class="btn btn-round btn-outline btn-sm add-btn" data-toggle="modal" data-target="#add-contact">
                                                <i class="glyphicon fa fa-plus"></i>
                                                Добавить
                                            </button>
                                            <button class="btn btn-round btn-outline btn-sm add-btn" data-toggle="modal" data-target="#select-contact">
                                                <i class="glyphicon fa fa-plus"></i>
                                                Выбрать из списка
                                            </button>
                                        </div>
                                    </div>
                                    @endcan
                                    @if(!$contacts->isEmpty())
                                    <div class="table-responsive">
                                        <table class="table table-hover mobile-table">
                                            <thead>
                                                <tr>
                                                    <th>ФИО</th>
                                                    <th>Должность</th>
                                                    <th>Контактный номер</th>
                                                    <th>email</th>
                                                    @can('edit', $project)
                                                    <th class="text-right">Действия</th>
                                                    @endcan
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($contacts as $contact)
                                                <tr style="cursor:default">
                                                    <td  data-label="ФИО" data-target="#collapse{{$contact->id}}" data-toggle="collapse" class="collapsed tr-pointer" aria-expanded="false">
                                                        {{ $contact->last_name }} {{ $contact->first_name }} {{ $contact->patronymic }}
                                                    </td>
                                                    <td  data-label="Должность" data-target="#collapse{{$contact->id}}" data-toggle="collapse" class="collapsed tr-pointer" aria-expanded="false">
                                                        {{ $contact->position }}
                                                    </td>
                                                    <td  data-label="Контактный номер">
                                                        @if ($contact->phones->where('is_main', 1)->count() > 0)
                                                            {{ $contact->phones->where('is_main', 1)->pluck('name')->first() . ': ' . $contact->phones->where('is_main', 1)->pluck('phone_number')->first() . ' ' . $contact->phones->where('is_main', 1)->pluck('dop_phone')->first() }}
                                                        @endif
                                                    </td>
                                                    <td  data-label="email">{{ $contact->email }}</td>
                                                    @can('edit', $project)
                                                    <td  data-label="" class="td-actions text-right actions">
                                                        <button href="#" rel="tooltip" onClick="edit_contact({{ $contact }})"  data-toggle="modal" data-target="#edit-contact" class="btn-success btn-link padding-actions mn-0 btn" data-original-title="Редактировать">
                                                            <i class="fa fa-edit"></i>
                                                        </button>
                                                        <a href="#" class="btn btn-link remove btn-danger padding-actions mn-0" contact_id="{{ $contact->id }}" project_id="{{ $project->id }}"><i class="fa fa-times"></i></a>
                                                    </td>
                                                    @endcan
                                                </tr>
                                                <tr id="collapse{{$contact->id}}" class="contact-note card-collapse collapse" style>
                                                    <td colspan="2">
                                                        <div class="comment-container">
                                                        Примечание: {{ $contact->proj_contact_note }}
                                                        </div>
                                                    </td>
                                                    <td colspan="2">
                                                        @foreach($contact->phones as $phone)
                                                            @if (!$phone->is_main)
                                                                <p class="p-info-card">
                                                                    {{ $phone->name . ': ' . $phone->phone_number . ' ' . $phone->dop_phone }}
                                                                </p>
                                                            @endif
                                                        @endforeach
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                        <p class="text-center">В этом разделе пока нет ни одного контакта</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                <a data-target="#collapseFive" href="#" data-toggle="collapse">
                                    Проектная документация
                                    <b class="caret"></b>
                                </a>
                            </h4>
                        </div>
                        <div id="collapseFive" class="card-collapse collapse @if(session('project_document')) show @endif">
                            <div class="card-body card-body-table">
                                <div class="card strpied-tabled-with-hover">
                                    @can('edit', $project)
                                    <div class="fixed-table-toolbar toolbar-for-btn">
                                        <div class="pull-right">
                                            <button class="btn btn-round btn-outline btn-sm add-btn" data-toggle="modal" data-target="#add-document">
                                                <i class="glyphicon fa fa-plus"></i>
                                                Добавить
                                            </button>
                                        </div>
                                    </div>
                                    @endcan
                                    @if(!$project_docs->isEmpty())
                                    <div class="table-responsive">
                                        <table class="table table-hover mobile-table">
                                            <thead>
                                                <tr>
                                                    <th>Название</th>
                                                    <th class="text-center">
                                                        Дата добавления</th>
                                                    <th>Автор</th>
                                                    <th class="text-center">
                                                        Версия
                                                    </th>
                                                    <th class="text-right">Действия</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                @foreach($project_docs as $document)
                                                    <tr style="cursor:default" class="header">
                                                        <td data-label="Название" data-target=".doc-collapse{{$document->id}}" data-toggle="collapse" class="collapsed tr-pointer" aria-expanded="false">
                                                            {{ $document->name }}
                                                        </td>
                                                        <td data-label="Дата добавления" class="text-center prerendered-date-time">{{ $document->updated_at }}</td>
                                                        <td data-label="Автор"><a href="{{ route('users::card', $document->user_id) }}" class="table-link">{{ $document->last_name }} {{ $document->first_name }} {{ $document->patronymic }}</a></td>
                                                        <td data-label="Версия" class="text-center">{{ $document->version }}</td>
                                                        <td data-label="" class="td-actions text-right actions">
                                                            @can('edit', $project)
                                                            <button rel="tooltip" onClick="updateDocId({{ $document->id }})" class="btn-success btn-link btn-xs btn padding-actions mn-0"  data-toggle="modal" data-target="#update-document" data-original-title="Добавить новую версию">
                                                                <i class="fa fa-plus"></i>
                                                            </button>
                                                            @endcan
                                                            <a target="_blank" href="{{ asset('storage/docs/project_documents/' . $document->file_name) }}" rel="tooltip" class="btn-info btn-link btn-xs btn padding-actions mn-0" data-original-title="Просмотр">
                                                                <i class="fa fa-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>

                                                        @foreach($extra_documents->where('project_document_id', $document->id) as $extra_document)
                                                            <tr class="doc-collapse{{$document->id}} contact-note card-collapse collapse">
                                                                <td></td>
                                                                <td data-label="Дата добавления" class="text-center prerendered-date-time">{{ $extra_document->created_at }}</td>
                                                                <td data-label="Автор">
                                                                    <a href="{{ route('users::card', $document->user_id) }}" class="table-link">{{ $extra_document->last_name }} {{ $extra_document->first_name }} {{ $extra_document->patronymic }}</a>
                                                                </td>
                                                                <td data-label="Версия"  class="text-center">{{ $extra_document->version }}</td>
                                                                <td data-label="" class="td-actions text-right actions">
                                                                    <a target="_blank" href="{{ asset('storage/docs/project_documents/' . $extra_document->file_name) }}" rel="tooltip" class="btn-info btn-link btn-xs btn padding-actions mn-0" data-original-title="Просмотр">
                                                                        <i class="fa fa-eye"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        @endforeach

                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                        <p class="text-center">Документы не найдены</p>
                                    @endif
                                    <!-- <div class="col-md-12">
                                        <div class="right-edge">
                                            <div class="page-container">
                                                <button class="btn btn-sm show-all">
                                                    Показать все
                                                </button>
                                            </div>
                                        </div>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                <a data-target="#collapseSeven" href="#" data-toggle="collapse">
                                    События
                                    <b class="caret"></b>
                                </a>
                            </h4>
                        </div>
                        <div id="collapseSeven" class="card-collapse collapse">
                            <div class="card-body card-body-table">
                                <div class="card strpied-tabled-with-hover">
                                    @if(Gate::check('tasks_default_myself') || Gate::check('tasks_default_others'))
                                    <div class="fixed-table-toolbar toolbar-for-btn">
                                        <div class="pull-right">
                                            <button class="btn btn-round btn-outline btn-sm add-btn" data-toggle="modal" data-target="#add-new-task">
                                                <i class="glyphicon fa fa-plus"></i>
                                                Добавить
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="card table-with-links">
                                        @if(!$solved_tasks->isEmpty())
                                        <div class="table-responsive">
                                            <table class="table table-hover mobile-table">
                                                <thead>
                                                    <tr>
                                                        <th>Дата создания</th>
                                                        <th>Дата исполнения</th>
                                                        <th>Наименование</th>
                                                        <th>Исполнитель</th>
                                                        <th>Автор</th>
                                                    </tr>
                                                </thead>
                                                @include('sections.history_for_tasks')
                                            </table>
                                        </div>
                                        @else
                                            <p class="text-center">События не найдены</p>
                                        @endif
                                    </div>
                                    @if(!$solved_tasks->isEmpty())
                                        <div class="col-md-12">
                                            <div class="right-edge">
                                                <div class="page-container">
                                                    <a class="btn btn-sm show-all" href="{{ route('projects::tasks', $project->id) }}">
                                                        Показать все
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @include('projects.modules.work_volume_module')

                    @include('projects.modules.commercial_offer_module')

                    @include('projects.modules.contracts_module')

{{--                    @include('projects.modules.users_module')--}}
                </div>
            </div>
        </div>
    </div>
</div>

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
                  <form id="form_add_contact" class="form-horizontal" action="{{ route('projects::add_contact', $contractor->id) }}" method="post">
                      @csrf
                      <input type="hidden" name="project_id" value="{{ $project->id }}">
                      <div class="row">
                          <label class="col-sm-3 col-form-label">Фамилия<span class="star">*</span></label>
                          <div class="col-sm-9">
                              <div class="form-group">
                                  <input class="form-control" type="text" name="last_name" required maxlength="50">
                              </div>
                          </div>
                      </div>
                      <div class="row">
                          <label class="col-sm-3 col-form-label">Имя<span class="star">*</span></label>
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
                          <label class="col-sm-3 col-form-label">Должность<span class="star">*</span></label>
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
        <button type="button" form="form_add_contact" onclick="contact_submit(this)" class="btn btn-info">Добавить</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade bd-example-modal-lg" id="edit-contact" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal-edit">Изменить контактное лицо</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <div class="card border-0">
              <div class="card-body">
                  <!--
                  Контактное лицо, добавленное в контрагента, сохраняется только в карточке контрагенте и становится доступным для выбора при добавлении контакта в карточке проекта.
              -->
                  <form id="form_edit_contact" class="form-horizontal" action="{{ route('contractors::edit_contact') }}" method="post">
                      @csrf
                      <input type="hidden" id="edit_contact_id" name="id">
                      <input type="hidden" value="{{ $project->id }}" name="project_id">
                      <div class="row">
                          <label class="col-sm-3 col-form-label">Фамилия<span class="star">*</span></label>
                          <div class="col-sm-9">
                              <div class="form-group">
                                  <input class="form-control" type="text" name="last_name" id="edit_last_name" required maxlength="50">
                              </div>
                          </div>
                      </div>
                      <div class="row">
                          <label class="col-sm-3 col-form-label">Имя<span class="star">*</span></label>
                          <div class="col-sm-9">
                              <div class="form-group">
                                  <input class="form-control" type="text" name="first_name" id="edit_first_name" required maxlength="50">
                              </div>
                          </div>
                      </div>
                      <div class="row">
                          <label class="col-sm-3 col-form-label">Отчество</label>
                          <div class="col-sm-9">
                              <div class="form-group">
                                  <input class="form-control" type="text" name="patronymic" id="edit_patronymic" maxlength="50">
                              </div>
                          </div>
                      </div>
                      <div class="row">
                          <label class="col-sm-3 col-form-label">Должность<span class="star">*</span></label>
                          <div class="col-sm-9">
                              <div class="form-group">
                                  <input class="form-control" type="text" name="position" id="edit_position" maxlength="50" required>
                              </div>
                          </div>
                      </div>
                      <div class="row">
                          <label class="col-sm-3 col-form-label">Телефон</label>
                          <div class="col-sm-9" id="edit_phones">
                          </div>
                      </div>
                      <div class="row">
                          <label class="col-sm-3 col-form-label">Email</label>
                          <div class="col-sm-9">
                              <div class="form-group">
                                  <input class="form-control" type="text" name="email" id="edit_email" maxlength="50">
                              </div>
                          </div>
                      </div>
                      <div class="row">
                          <label class="col-sm-3 col-form-label">Общее примечание</label>
                          <div class="col-sm-9">
                              <div class="form-group">
                                  <textarea class="form-control textarea-rows" name="note" id="edit_note" maxlength="150"></textarea>
                              </div>
                          </div>
                      </div>
                      <div class="row">
                          <label class="col-sm-3 col-form-label">Примечание к проекту</label>
                          <div class="col-sm-9">
                              <div class="form-group">
                                  <textarea class="form-control textarea-rows" name="project_note" id="edit_project_note" maxlength="150"></textarea>
                              </div>
                          </div>
                      </div>
                  </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="button" form="form_edit_contact" onclick="contact_submit(this)" class="btn btn-info">Сохранить</button>
              </div>
          </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade bd-example-modal-lg" id="select-contact" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить контакт</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card border-0">
                    <div class="card-body">
                        <form id="form_select_contacts" class="form-horizontal" action="{{ route('projects::select_contacts', $project->id) }}" method="post">
                            @csrf
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Контакты<span class="star">*</span></label>
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
@if(Gate::check('projects_responsible_users') or in_array(Auth::id(), $project->respUsers()->whereIn('role', [5, 6])->get()->pluck('user_id')->toArray()) )
<div class="modal fade bd-example-modal-lg" id="select-user" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить сотрудника</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card border-0 m-0">
                    <div class="card-body">
                        <validation-observer ref="observer" :key="observer_key">
                            <div>
                                <label class="d-block" for="">Позиция<span class="star">*</span></label>
                                <validation-provider rules="required" vid="position-select"
                                                     ref="position-select" v-slot="v">
                                    <el-select v-model="position" clearable filterable
                                               :class="v.classes"
                                               id="position-select"
                                               placeholder="Выберите позицию">
                                        <el-option
                                            v-for="item in positionOptions"
                                            :key="item.id"
                                            :label="item.name"
                                            :value="item.id">
                                        </el-option>
                                    </el-select>
                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                </validation-provider>
                            </div>
                            <div class="mt-2">
                                <label class="d-block" for="">Сотрудник<span class="star">*</span></label>
                                <validation-provider rules="required" vid="related-user-select"
                                                     ref="related-user-select" v-slot="v">
                                    <el-select v-model="relatedUser" clearable filterable
                                               :disabled="!position"
                                               remote
                                               :remote-method="searchRelatedUsers"
                                               @clear="searchRelatedUsers('')"
                                               :class="v.classes"
                                               id="related-user-select"
                                               placeholder="Выберите сотрудника">
                                        <el-option
                                            v-for="item in relatedUsers"
                                            :key="item.id"
                                            :label="item.name"
                                            :value="item.id">
                                        </el-option>
                                    </el-select>
                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                </validation-provider>
                            </div>
                            <div class="mt-2 d-md-flex justify-content-md-between">
                                <el-button @click="closeModal"
                                           type="warning"
                                           class="responsive-button mx-0"
                                >Закрыть</el-button>
                                <el-button @click.stop="submit"
                                           :loading="loading"
                                           type="primary"
                                           class="responsive-button mt-10__mobile mx-0"
                                >Добавить</el-button>
                            </div>
                        </validation-observer>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@if(Gate::check('projects_create') or in_array(Auth::id(), $project->respUsers()->whereIn('role', [5, 6])->get()->pluck('user_id')->toArray() ) )

<div class="modal fade bd-example-modal-lg" id="select-contractors" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить контрагентов</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card border-0">
                    <div class="card-body">
                        <form id="add_contractors" class="form-horizontal" action="{{ route('projects::add_contractors', $project->id) }}" method="post">
                            @csrf
                            <div class="form-group">
                                <div id="contractors_row">
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label">Доп. контрагент<span class="star">*</span></label>
                                        <div class="col-sm-7">
                                            <div class="form-group select-accessible-140">
                                                <select name="contractor_ids[]" class="js-select-contractor slct" style="width:100%;" required>
                                                    <option value="default">Выберите контрагента</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="form-group">
                                                <div class="form-check form-check-radio">
                                                    <button type="button" class="btn-success btn-link btn-xs btn pd-0" onclick="addContractorInput(this)" style="margin-top: 8px">
                                                        <i class="fas fa-plus" aria-hidden="true"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                        <button type="submit" form="add_contractors" class="btn btn-info">Добавить</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-none">
    <div class="row" id="new_contractor_input">
        <label class="col-sm-3 col-form-label">Доп. контрагент<span class="star">*</span></label>
        <div class="col-sm-7">
            <div class="form-group select-accessible-140">
                <select name="contractor_ids[]" class="js-select-more-contractors slct" style="width:100%;" required>
                    <option value="default">Выберите контрагента</option>
                </select>
            </div>
        </div>
        <div class="col-sm-2">
            <div class="form-group">
                <div class="form-check form-check-radio">
                    <button type="button" class="btn-danger btn-link btn-xs btn pd-0" onclick="removeContractorInput(this)" style="margin-top: 8px">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="modal fade bd-example-modal-lg" id="update-document" tabindex="-1" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-update">Обновить документ</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card border-0">
                    <div class="card-body">
                        <form id="form_update_document" class="form-horizontal" action="{{ route('project_documents::update') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <input name="project_document_id" id="project_document_id" type="hidden">
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-sm-3 col-form-label">Документ<span class="star">*</span></label>
                                    <div class="col-sm-6" style="padding-top:0px;">
                                        <div class="file-container">
                                            <div id="fileName" class="file-name"></div>
                                            <div class="file-upload ">
                                                <label class="pull-right">
                                                    <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                    <input type="file" name="document" id="uploadedFile" onchange="$upload = getFileName(this); CheckUpload($upload, $(this).closest('.modal-body').find(':submit'))" class="form-control-file file">
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                        <button type="submit" form="form_update_document" class="btn btn-info btn-outline" disabled="disabled">Добавить</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade bd-example-modal-lg" id="close_project" tabindex="-1" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-update">Перевести направления в статус "Не реализовно"</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card border-0">
                    <div class="card-body">
                        <form id="form_close_project" class="form-horizontal" action="{{ route('projects::close_project', $project->id) }}" method="post" enctype="multipart/form-data">
                            <p>Вы не можете отклонить направление с подписанным договором.</p>

                            @csrf
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-sm-3 col-form-label">Наименования<span class="star">*</span></label>
                                    <div class="col-sm-9">
                                        <select name="wv_ids[]" multiple class="selectpicker" title="Выберите наименования" style="width:100%;">
                                            @foreach($work_volumes_options as $work_volume)
                                            <option value="{{ $work_volume->id }}">{{ $work_volume->type == 0 ? "Шпунт: " : 'Сваи: ' }} {{ $work_volume->option ? $work_volume->option: ' id: ' . $work_volume->id  }} </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                             </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                        <button type="submit" form="form_close_project" class="btn btn-info btn-outline">Сохранить</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade bd-example-modal-lg" id="add-document" tabindex="-1" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-add">Добавить документ</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card border-0">
                    <div class="card-body">
                        <form id="form_add_document" class="form-horizontal" action="{{ route('project_documents::store', $project->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-sm-3 col-form-label">Документы<span class="star">*</span></label>
                                    <div class="col-sm-6" style="padding-top:0px;">
                                        <div class="file-container">
                                            <div id="fileName" class="file-name"></div>
                                            <div class="file-upload ">
                                                <label class="pull-right">
                                                    <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                    <input type="file" name="documents[]" id="uploadedFile" multiple onchange="$upload = getFileName(this); CheckUpload($upload, $(this).closest('.modal-body').find(':submit'))" class="form-control-file file">
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                        <button type="submit" form="form_add_document" class="btn btn-info btn-outline" disabled="disabled">Добавить</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@if(Gate::check('tasks_default_myself') || Gate::check('tasks_default_others'))
<div class="modal fade bd-example-modal-lg show" id="add-new-task" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Новая задача</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <div class="card border-0">
                   <div class="card-body ">
                       <form id="create_task_form" class="form-horizontal" action="{{ route('tasks::store') }}" method="post" enctype="multipart/form-data">
                           @csrf
                           <input type="hidden" name="from_project" value="{{ $project->id }}">
                           <div class="row">
                               <label class="col-sm-3 col-form-label">Название<span class="star">*</span></label>
                               <div class="col-sm-9">
                                   <div class="form-group">
                                       <input class="form-control" type="text" name="name" required maxlength="50">
                                   </div>
                               </div>
                           </div>
                           <div class="row">
                               <label class="col-sm-3 col-form-label">Описание<span class="star">*</span></label>
                               <div class="col-sm-9">
                                   <div class="form-group">
                                       <textarea class="form-control textarea-rows" name="description" required maxlength="250"></textarea>
                                   </div>
                               </div>
                           </div>
                           <div class="row">
                               <label class="col-sm-3 col-form-label">Контрагент<span class="star">*</span></label>
                               <div class="col-sm-6">
                                   <div class="form-group">
                                       <select id="js-select-contractor" name="contractor_id"  style="width:100%;" required>
                                           @if(Request::has('contractor_id'))
                                               <option value="{{ $contractor->id }}" selected>{{ $contractor->short_name }}. ИНН: {{ $contractor->inn }}</option>
                                           @endif
                                           <option value=""></option>
                                       </select>
                                   </div>
                               </div>
                           </div>
                           <div id="project_choose" class="row d-none">
                               <label class="col-sm-3 col-form-label">Проект</label>
                               <div class="col-sm-6">
                                   <div class="form-group">
                                       <select name="project_id" id="js-select-project" style="width:100%;">
                                           @if(Request::has('project_id'))
                                               <option value="{{ $project->id }}" selected>Название: {{ $project->name }}</option>
                                           @endif
                                           <option value="">Не выбрано</option>
                                       </select>
                                   </div>
                               </div>
                           </div>
                           <div class="row">
                               <label class="col-sm-3 col-form-label">Ответственное лицо<span class="star">*</span></label>
                               <div class="col-sm-6">
                                   <div class="form-group">
                                       <select id="js-select-user" name="responsible_user_id"  style="width:100%;" required>
                                       </select>
                                   </div>
                               </div>
                           </div>
                           <div class="row">
                               <label class="col-sm-3 col-form-label">Срок выполнения<span class="star">*</span></label>
                               <div class="col-sm-6">
                                   <div class="form-group">
                                       <input id="datetimepicker" name="expired_at" type="text" min="{{ \Carbon\Carbon::now()->addMinutes(30) }}" class="form-control datetimepicker" placeholder="Укажите дату" required>
                                   </div>
                               </div>
                           </div>
                           <div class="row">
                               <label class="col-sm-3 col-form-label" for="" style="font-size:0.80">Приложенные файлы</label>
                               <div class="col-sm-6">
                                   <div class="file-container">
                                       <div id="fileName" class="file-name"></div>
                                       <div class="file-upload ">
                                           <label class="pull-right">
                                               <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                               <input type="file" name="documents[]" accept="*" id="uploadedFile" onchange="getFileName(this)" class="form-control-file file" multiple>
                                           </label>
                                       </div>
                                   </div>
                               </div>
                           </div>
                       </form>
                   </div>
               </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button id="submit" type="submit" form="create_task_form" class="btn btn-info">Сохранить</button>
           </div>
        </div>
    </div>
</div>
@endif
<div class="modal fade bd-example-modal-lg show" id="signing-contract" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Подтверждение подписания</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <hr style="margin-top:0">
               <div class="card border-0" >
                   <div class="card-body ">
                       <form id="" class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                           <div class="row">
                               <div class="col-md-3">
                                   <label>Вид документа<span class="star">*</span></label>
                               </div>
                               <div class="col-md-6">
                                   <div class="form-group">
                                       <select name="none" class="selectpicker" data-title="Выберите тип документа" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                           <option value="none">Договор</option>
                                           <option value="none">Гарантийное письмо</option>
                                       </select>
                                    </div>
                               </div>
                           </div>
                           <div class="row" >
                               <label class="col-sm-3 col-form-label" for="" style="font-size:0.80">
                                   Подписанный договор<span class="star">*</span>
                               </label>
                               <div class="col-sm-6">
                                   <div class="file-container">
                                       <div id="fileName4" class="file-name"></div>
                                       <div class="file-upload ">
                                           <label class="pull-right">
                                               <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                               <input type="file" name="" accept="*" id="" class="form-control-file file" required>
                                           </label>
                                       </div>
                                   </div>
                               </div>
                           </div>
                       </form>
                   </div>
               </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button id="" type="submit" class="btn btn-info">Подтвердить</button>
           </div>
        </div>
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
            <div class="col-1" id="base">
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
@endsection

@push('js_footer')
<script src="{{ mix('js/form-validation.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/plugins/jquery.mask.min.js') }}"></script>
<script type="text/javascript">
    Vue.component('validation-provider', VeeValidate.ValidationProvider);
    Vue.component('validation-observer', VeeValidate.ValidationObserver);
</script>

<script>
    var count_phone = 1;
    var count_edit_phone = 1;
    var page_contractors = {!! json_encode(array_merge([$project->contractor_id], isset($additional_contractors) ? $additional_contractors->pluck('contractor_id')->toArray() : [])) !!};

    function removeAdditionalContractor(elem, relation_id) {
        swal({
            title: 'Вы уверены?',
            text: "Связь проекта с контрагентом будет удалена!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            cancelButtonText: 'Назад',
            confirmButtonText: 'Удалить'
        }).then((result) => {
            if(result.value) {
                $.ajax({
                    url:"{{ route('projects::remove_relation') }}",
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        relation_id: relation_id
                    },
                    dataType: 'JSON',
                    success: function () {
                        location.reload();
                    }
                });
            }
        });
    }

    function useAsMainContractor(elem, relation_id) {
        swal({
            title: 'Внимание',
            text: "Выбрать контрагента как основного?",
            type: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#FF243D',
            cancelButtonText: 'Назад',
            confirmButtonText: 'Выбрать'
        }).then((result) => {
            if(result.value) {
                $.ajax({
                    url:"{{ route('projects::use_as_main') }}",
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        relation_id: relation_id
                    },
                    dataType: 'JSON',
                    success: function () {
                       location.reload();
                    }
                });
            }
        });
    }

    var userSelect = new Vue({
        el: "#select-user",
        data: {
            POSITIONS: [
                { id: 1, name: 'Ответственный специалист по КП (сваи)'},
                { id: 2, name: 'Ответственный специалист по КП (шпунт)'},
                { id: 3, name: 'Ответственный специалист по объёмам работ (сваи)'},
                { id: 4, name: 'Ответственный специалист по объёмам работ (шпунт)'},
                { id: 5, name: 'Руководитель проектов (сваи)'},
                { id: 6, name: 'Руководитель проектов (шпунт)'},
                { id: 7, name: 'Ответственный специалист по договорной работе'},
                { id: 8, name: 'Ответственный производитель работ (сваи)'},
                { id: 9, name: 'Ответственный производитель работ (шпунт)'},
                { id: 10, name: 'Ответственный за учёт рабочего времени'},
            ],
            visiblePositions: [
            @if(Auth::user()->isInGroup(8)/*5*/ || Auth::user()->isInGroup(13))
                5, 6,
            @elseif(Auth::user()->isInGroup(19))
                5,
            @elseif(Auth::user()->isInGroup(27))
                6,
            @elseif(Auth::user()->isInGroup(50)/*7*/)
            {{-- @elseif(Auth::user()->group_id == 50/*7*/) example of new vacation logic --}}
                1, 2, 3, 4, 5, 6,
            @elseif(Auth::user()->isInGroup(53)/*16*/)
                4, 7,
            @elseif(Auth::user()->isInGroup(54)/*26*/)
                1, 3, 7,
            @elseif(Auth::user()->isInGroup(49)/*32*/)
                7,
            @elseif(in_array(Auth::user()->group_id, [5, 6]))
                1, 2, 3, 4, 5, 6, 7,
            @endif
            @can('human_resources_project_time_responsible_user_change')
                10,
            @endcan
            @if(in_array(Auth::id(), $project->respUsers->where('role', 5)->pluck('user_id')->toArray()))
                8,
            @endif
            @if(in_array(Auth::id(), $project->respUsers->where('role', 6)->pluck('user_id')->toArray()))
                9,
            @endif
            ],
            relatedUsers: [],

            position: '',
            relatedUser: '',

            loading: false,
            observer_key: 1,
        },
        computed: {
            positionOptions() {
                return this.POSITIONS.filter(el => this.visiblePositions.indexOf(el.id) !== -1);
            }
        },
        watch: {
            position(val) {
                this.searchRelatedUsers('');
            }
        },
        methods: {
            closeModal() {
                $('#select-user').modal('hide');
            },
            submit() {
                this.$refs.observer.validate().then(success => {
                    if (!success) {
                        const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                        $('#' + error_field_vid).focus();
                        return;
                    }
                    if (this.position !== 10) {
                        const payload = {
                            role: String(this.position),
                            user: String(this.relatedUser),
                        };
                        this.loading = true;
                        axios.post('{{ route('projects::select_user', $project->id) }}', payload)
                            .then(() => {
                                window.location.reload();
                            })
                            .catch((error) => {
                                console.log(error);
                                this.loading = false;
                            })
                    } else {
                        const payload = {
                            time_responsible_user_id: String(this.relatedUser),
                            project_id: {{ $project->id }},
                        };
                        this.loading = true;
                        axios.post('{{ route('projects::update_time_responsible', $project->id) }}', payload)
                            .then(() => {
                                window.location.reload();
                            })
                            .catch((error) => {
                                console.log(error);
                                this.loading = false;
                            })
                    }
                });
            },
            searchRelatedUsers(query) {
                axios.get('/projects/ajax/get-users', {params: {
                        role: this.position,
                        q: query,
                    }})
                    .then(response => {
                        this.relatedUsers = response.data.results.map(el => ({ id: el.id, name: el.text, })).filter(el => el.id !== 1);
                    })
                    .catch(error => {
                        console.log(error);
                    });
            },
        }
    })

    $('.js-select-contractor').select2({
        language: "ru",
        ajax: {
            url: '/projects/ajax/get-contractors',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                var contractor_ids = $('.slct').map(function(idx, elem) {
                    return $(elem).val() == 'default' ? null : $(elem).val();
                }).get();

                return {
                    contractor_ids: _.union(contractor_ids, page_contractors),
                    q: params.term
                };
            }
        }
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
                    var contractor_ids = $('.slct').map(function(idx, elem) {
                        return $(elem).val() == 'default' ? null : $(elem).val();
                    }).get();

                    return {
                        contractor_ids: _.union(contractor_ids, page_contractors),
                        q: params.term
                    };
                }
            }
        });
    }

    function removeContractorInput(elem) {
        $(elem).closest('.row').first().remove();
    }

    function edit_contact(data) {
        $('#edit_contact_id').val(data.id);
        $('#edit_first_name').val(data.first_name);
        $('#edit_last_name').val(data.last_name);
        $('#edit_patronymic').val(data.patronymic);
        $('#edit_position').val(data.position);
        $('#edit_email').val(data.email);
        $('#edit_note').val(data.note);
        $('#edit_project_note').val(data.proj_contact_note);

        $('#edit_phones').children().remove();
        count_edit_phone = 1;
        if (data.phones.length == 0) {
            clone = $(".new_phone.d-none").clone().removeClass('d-none').appendTo("#edit_phones");
            clone.find('select').select2({
                tags: true,
            });
            count_edit_phone++;
        }
        data.phones.forEach(function (phone) {
            clone = $(".new_phone.d-none").clone().removeClass('d-none').appendTo("#edit_phones");
            clone.find('.form-check-input').val(count_edit_phone);
            clone.find(`[name='phone_count[]']`).val(count_edit_phone);
            clone.find(`[name='phone_number[]']`).val(phone.phone_number);
            clone.find(`[name='phone_dop[]']`).val(phone.dop_phone);
            clone.find(`[name='main']`).attr('checked', (phone.is_main == 1));
            clone.find('select').select2({
                tags: true,
            });
            if (clone.find('select').find("option[value='" + phone.name + "']").length) {
                clone.find('select').val(phone.name).trigger('change');
            } else {
                var newOption = new Option(phone.name, phone.name, true, true);
                clone.find('select').append(newOption).trigger('change');
            }

            count_edit_phone++;
        });
        clone.find('.phone_dop').mask('00000');
        clone.find('.phone_number').mask('7 (000) 000-00-00');
    }

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

    function contact_submit(e) {
        var form = $(e).attr('form');
        var phones = [];
        var names = [];
        $('#'+form).find(`[name='phone_number[]']`).map(function(key,el) {
            phones.push(el.value);
        });
        $('#'+form).find(`[name='phone_name[]']`).map(function(key,el) {
            names.push(el.value.trim());
        });
        var unique_phones = [...new Set(phones)];
        if ($('#'+form)[0].reportValidity()) {
            if (unique_phones.length == phones.length) { //unique
                if (phones.length == 1) {
                    if (names.indexOf('') != -1 && phones[0] != '') {
                        swal({
                            title: "Внимание",
                            text: "Заполните названия телефонов",
                            type: 'warning',
                            timer: 2500,
                        });
                    } else {
                        $('#'+form).submit();
                    }
                } else if (names.indexOf('') != -1) {
                    swal({
                        title: "Внимание",
                        text: "Заполните названия телефонов",
                        type: 'warning',
                        timer: 2500,
                    });
                } else {
                    $('#'+form).submit();
                }

            } else {
                swal({
                    title: "Внимание",
                    text: "Некоторые номера повторяются.",
                    type: 'warning',
                    timer: 2500,
                });
            }
        }
    }


    function CheckUpload(upload, buttons)
    {
        if (upload) {
            $(buttons[0]).removeAttr('disabled');
        } else {
            $(buttons[0]).attr('disabled', 'disabled');
        }
    }

    $('.phone_number').mask('7 (000) 000-00-00');

    $('#js-select-contacts').select2({
        language: "ru",
        ajax: {
            url: '/projects/ajax/get-contacts/' + {{ $project->contractor_id }} + '?project_id=' + {{ $project->id }},
            dataType: 'json',
            delay: 250
        }
    });


    $('#js-select-users').select2({
        language: "ru",
        maximumSelectionLength: 10,
        ajax: {
            url: '/projects/ajax/get-users',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    role: $('#js-select-position').val(),
                    q: params.term,
                };
            }
        },
        disabled: true
    });

    $("#project_status").on("change", function() {
        $.ajax({
            url:"{{ route('projects::change_status', [$project->id]) }}", //SET URL
            type: 'POST', //CHECK ACTION
            data: {
                _token: CSRF_TOKEN,
                new_status: $("#project_status").val(), //SET ATTRS
            },
            dataType: 'JSON',
            success: function (data) {
                swal({
                    title: "Успешно",
                    text: "Статус был изменён",
                    type: 'success',
                    timer: 1000,
                });
            }
        });
    });

    function updateDocId(id) {
        $('#project_document_id').val(id);
    }

    function updateComOffId(id) {
        $('#commercial_offer_id').val(id);
    }

    // create new default task


    $('#js-select-contractor').select2({
        language: "ru",
        ajax: {
            url: '/tasks/get-contractors',
            dataType: 'json',
            delay: 250
        }
    });

    $('#js-select-contractor').on('change', function() {
        $('#project_choose').removeClass('d-none');
        $('#js-select-project').select2({
            language: "ru",
            ajax: {
                url: '/tasks/get-projects?contractor_id=' + $('#js-select-contractor').select2('val'),
                dataType: 'json',
                delay: 250
            }
        });
    });

    $('#js-select-user').select2({
        language: "ru",
        ajax: {
            url: '/tasks/get-users',
            dataType: 'json',
            delay: 250
        }
    });

    $('#select-contract').select2({
        language: "ru",
        placeholder: "Выберите договор",
    });

    Date.prototype.addMinutes = function(m) {
        this.setMinutes(this.getMinutes() + m);
        return this;
    };

    add_datetime();

    setInterval(function(){
        $('#datetimepicker').datetimepicker('destroy');
        add_datetime();
    }, 55000);

    function add_datetime() {
        $('#datetimepicker').datetimepicker({
            minDate: new Date().addMinutes(32),
            locale: "ru",
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
        });
    }

</script>

<meta name="csrf-token" content="{{ csrf_token() }}" />

<script type="text/javascript">

    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

    $('.remove_responsible_user').on('click', function() {
        swal({
            title: 'Вы уверены?',
            text: "Ответственное лицо будет удалено!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            cancelButtonText: 'Назад',
            confirmButtonText: 'Удалить'
        }).then((result) => {
            if(result.value) {
                $.ajax({
                    url:"{{ route('projects::delete_resp_user') }}",
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        user_id: $(this).attr("user_id"),
                        project_id: $(this).attr("project_id"),
                        role: $(this).attr("role")
                    },
                    dataType: 'JSON',
                    success: function (data) {
                        if (data) {
                            setTimeout(function() {
                                location.reload()
                            }, 1000);
                        } else {
                            swal({
                                title: "Внимание",
                                text: "Вы не можете удалить ответственное лицо, так как у него есть невыполненные задачи",
                                type: 'warning',
                            });
                        }
                    }
                });
            }
        })
    });

    $('.remove_responsible_time_user').on('click', function() {
        swal({
            title: 'Вы уверены?',
            text: "Ответственное лицо будет удалено!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            cancelButtonText: 'Назад',
            confirmButtonText: 'Удалить'
        }).then((result) => {
            if(result.value) {
                axios.post('{{ route('projects::update_time_responsible', $project->id) }}', {
                    project_id: {{ $project -> id }},
                    time_responsible_user_id: null,
                })
                    .then(() => {
                        window.location.reload();
                    })
                    .catch((error) => {
                        swal({
                            title: "Внимание",
                            text: "Вы не можете удалить ответственное лицо, так как у него есть невыполненные задачи",
                            type: 'warning',
                        });
                    })
            }
        })
    });
</script>

<script type="text/javascript">

    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

    $('.remove').on('click', function() {
        swal({
            title: 'Вы уверены?',
            text: "Контакт будет удален из проекта!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            cancelButtonText: 'Назад',
            confirmButtonText: 'Удалить'
        }).then((result) => {
            if(result.value) {
                $.ajax({
                    url:"{{ route('projects::contact_delete') }}",
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        contact_id: $(this).attr("contact_id"),
                        project_id: $(this).attr("project_id")
                    },
                    dataType: 'JSON',
                    success: function (data) {
                        setTimeout(function() {
                            location.reload()
                        }, 1000);
                    }
                });
            }
        })
    });
</script>

@if(Request::has('project_id') or Request::has('contractor_id'))
    <script>
        $('#project_choose').removeClass('d-none');
        $('#js-select-project').select2({
            language: "ru",
            ajax: {
                url: '/tasks/get-projects?contractor_id=' + $('#js-select-contractor').select2('val'),
                dataType: 'json',
                delay: 250
            }
        });
    </script>
@endif

<script>
// показать шпунт
    $('#addSp').click(function(){
        if ($(this).is(':checked')){
            $('#spInfo').show(100);
            $('#option_block_tongue_input').attr('required', 'true');
            $(this).val(1);
        } else {
            $('#spInfo').hide(100);
            $('#option_block_tongue_input').removeAttr('required');
            $(this).val(0);
        }
    });

// Показать свайи
    $('#addSv').click(function(){
        if ($(this).is(':checked')){
            $('#svInfo').show(100);
            $('#option_block_pile_input').attr('required', 'true');
            $(this).val(1);
        } else {
            $('#svInfo').hide(100);
            $('#option_block_pile_input').removeAttr('required');
            $(this).val(0);
        }
    });
// Показать земляные для свай
    $('#addZm').click(function(){
        if ($(this).is(':checked')){
            $('#zmInfo').show(100);
            $(this).val(1);
        } else {
            $('#zmInfo').hide(100);
            $(this).val(0);
        }
    });
// Показать земляные для шпунта
    $('#addZmSp').click(function(){
        if ($(this).is(':checked')){
            $('#zmSp').show(100);
            $(this).val(1);
        } else {
            $('#zmSp').hide(100);
            $(this).val(0);
        }
    });

// показать шпунт в сваях
$('#addSpPile').click(function(){
    if ($(this).is(':checked')){
        $('#spInfoPile').show(100);
        $(this).val(1);
    } else {
        $('#spInfoPile').hide(100);
        $(this).val(0);
    }
});
// Показать свай в сваях
$('#addSvPile').click(function(){
    if ($(this).is(':checked')){
        $('#svInfoPile').show(100);
        $(this).val(1);
    } else {
        $('#svInfoPile').hide(100);
        $(this).val(0);
    }
});
</script>

<script> //script for contracts module
    function approve_contract(contract_id) {
        swal({
            title: 'Вы уверены?',
            text: "Перевести договор в статус 'согласовано'?",
            type: 'question',
            showCancelButton: true,
            cancelButtonText: 'Назад',
            confirmButtonText: 'Перевести'
        }).then((result) => {
            if(result.value) {
                $('#approved_contract_id').val(contract_id);
                $('#approve_contract').submit();
            }
        })
    }

</script>
<script>
    // for new agreeKP logic
    function alertRespUser(elem, offer_id)
    {
        var result = {{ $resp_users->where('role', 7)->count() ? 1 : 0 }};
        if (result == 0) {
            swal({
                title: "Внимание",
                text: "Добавьте ответственного по договорной работе в проект",
                type: 'warning',
            });
        } else {
            $('#agreeKP' + offer_id).modal('show');
        }
    }

    $('[id^=status_result]').change(function() {
        var offer_id = $(this).attr('offer_id');

        opt = $(this).val();
        if (opt == "archive" || opt == "transfer" || opt == "decline") {
            $('#comment' + offer_id).show();
            $('#result_note' + offer_id).attr('required', 'required');
            $('#from' + offer_id).removeAttr('required');
            if (opt == "transfer") {
                $('#fromСontainer' + offer_id).removeClass('d-none');
                $('#from' + offer_id).attr('required', 'required');
            } else if (opt == "archive") {
                $('#fromСontainer' + offer_id).addClass('d-none');
                $('#from' + offer_id).removeAttr('required', 'required');
            }
        } else {
            $('#fromСontainer' + offer_id).addClass('d-none');
            $('#from' + offer_id).removeAttr('required', 'required');
            $('#result_note' + offer_id).removeAttr('required');
            $('#comment' + offer_id).hide();
        }

        $('#sendForm' + offer_id).removeClass('d-none');
        $('#sendForm' + offer_id).removeAttr('disabled');
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
        minDate: moment().add(1, 'd'),
        maxDate: moment().add(7, 'd'),
        date: null
    });
</script>

<script>
    function createDoubleKP()
    {
        swal({
            title: 'Объединить коммерческие предложения?',
            type: 'question',
            showCancelButton: true,
            cancelButtonText: 'Назад',
            confirmButtonText: 'Объединить'
        }).then((result) => {
            if(result.value) {
                firstKPid = $('#firstKP').val();
                secondKPid = $('#secondKP').val();
                if (firstKPid != 0 && secondKPid != 0) {
                    $('#doubleKP').submit();
                    setTimeout(function () {location.reload()}, 4000);
                }
            }
        })
    }
</script>

<script>
    $('#js-select-subcontractor').select2({
        language: "ru",
        placeholder: "Выберите исполнителя",
        disabled: true
    });

    $('.js-select-type').select2({
        language: "ru"
    });

    $('.js-select-main-contract').select2({
        language: "ru",
        placeholder: "Выберите основной договор",
        disabled: true
    });

    $(".js-select-type").on("change", function () {
        let $contractorSelectRow = $('#contractor_select_row');
        let $subcontractorSelect = $("#js-select-subcontractor");
        let $mainContractSelectRow = $('#main_contract_select_row');
        let $selectMainContract = $('.js-select-main-contract');
        let contractor_toggle = $('#contractor_needed');
        let type_name = $('#contract_type_name');

        result = $(this).val();

        $contractorSelectRow.addClass('d-none');
        $subcontractorSelect.removeAttr('required');
        $subcontractorSelect.val('').trigger('change');

        $mainContractSelectRow.addClass('d-none');
        $selectMainContract.removeAttr('required');
        $selectMainContract.val('').trigger('change');

        if(result === 'Доп. соглашение') {
            $mainContractSelectRow.removeClass('d-none');
            $selectMainContract.prop("disabled", false);
            $selectMainContract.attr("required", 'required');
        } else if(!['Договор с заказчиком', 'Доп. соглашение', 'Иное'].includes(result)) {
            $contractorSelectRow.removeClass('d-none');
            $subcontractorSelect.prop("disabled", false);
            $subcontractorSelect.attr("required", 'required');
        }

        if (result === 'Иное') {
            $selectMainContract.removeAttr('required');
            type_name.removeClass('d-none');
            $selectMainContract.attr("required", 'required');
        } else {
            type_name.addClass('d-none');
            $('#material_needed')[0].checked = false;
        }

        // this if statement works with select2 data
        if (['Договор поставки', 'Договор на аренду техники'].includes(result)) {
            updateSelect2(3);
        } else if ('Договор на оформление проекта' === result) {
            updateSelect2(1);
        } else if (['Договор субподряда', 'Договор услуг'].includes(result)) {
            updateSelect2(2);
        } else if (['Иное']) {
            updateSelect2(0);
        } else {
            updateSelect2();
        }
    });

    function updateSelect2(contractor_type = null) {
        let $subcontractorSelect = $("#js-select-subcontractor");

        // clear all options from select2 and DOM
        $subcontractorSelect.val(null).empty().trigger('change');

        if (contractor_type == null) return;

        $subcontractorSelect.select2({
            language: "ru",
            placeholder: "Выберите исполнителя",
            ajax: {
                url: '{{ route('contractors::get_by_type') }}',
                type: 'POST',
                data: function (params) {
                    return {
                        q: params.term,
                        _token: CSRF_TOKEN,
                        contractor_type: contractor_type,
                    };
                },
                dataType: 'JSON',
                delay: 250
            }
        });
    }

    @if(session()->has('name_repeat'))
        swal({
            title: "Внимание",
            text: "Объем работ с таким наименованием уже существует.",
            type: 'error',
            timer: 5000,
        });
    @endif
</script>
<script type="text/javascript">
    var vm = new Vue ({
        el: '#base',
        mounted() {
            const that = this;
            $('.prerendered-date-time').each(function() {
                const date = $(this).text();
                const content = that.isValidDate(date, 'DD.MM.YYYY HH:mm:ss') ? that.weekdayDate(date, 'DD.MM.YYYY HH:mm:ss', 'DD.MM.YYYY dd HH:mm:ss') : '-';
                const innerSpan = $('<span/>', {
                    'class': that.isWeekendDay(date, 'DD.MM.YYYY HH:mm:ss') ? 'weekend-day' : ''
                });
                innerSpan.text(content);
                $(this).html(innerSpan);
            });
            $('.prerendered-date').each(function() {
                const date = $(this).text();
                const content = that.isValidDate(date, 'DD.MM.YYYY') ? that.weekdayDate(date, 'DD.MM.YYYY') : '-';
                const innerSpan = $('<span/>', {
                    'class': that.isWeekendDay(date, 'DD.MM.YYYY') ? 'weekend-day' : ''
                });
                innerSpan.text(content);
                $(this).html(innerSpan);
            });
        },
        methods: {
            isWeekendDay(date, format) {
                return [5, 6].indexOf(moment(date, format).weekday()) !== -1;
            },
            isValidDate(date, format) {
                return moment(date, format).isValid();
            },
            weekdayDate(date, inputFormat, outputFormat) {
                return moment(date, inputFormat).format(outputFormat ? outputFormat : 'DD.MM.YYYY dd');
            },
        }
    })
</script>
@endpush
