@extends('layouts.app')

@section('title', 'Задачи')

@section('css_top')
    <style>
        .select2-hidden-accessible {
            margin: 2.38em 0 0 140px !important;
        }

        .icon-margin {
            padding: 0;
            margin: 0 6px 0 6px !important;
            border: 0;
        }


        @media (min-width: 1450px) {
            .tooltip {
                left:15px!important;
            }
        }

        @media (min-width: 2500px) and (max-width: 3500px) {
            .tooltip {
                left:23px!important;
            }
        }

        @media (min-width: 3500px) and (max-width: 6000px) {
            .tooltip {
                left:38px!important;
            }
        }
    </style>
@endsection

@section('url', route('tasks::index'))

@section('content')
@php setlocale(LC_TIME, 'ru'); @endphp
<div class="row">
    <div class="col-md-12 col-xl-11 mr-auto ml-auto">
        <div class="row task-card">
            <div class="col-md-4 tasks-sidebar">
                <div class="card tasks-sidebar__item tasks-sidebar__item1">
                    <div class="card-body">
                        <div class="tasks-sidebar__text-unit">
                            <span class="tasks-sidebar__head-title">
                                Время создания задачи
                            </span>
                            <span class="tasks-sidebar__body-title">
                               {{ $task->created_at }}
                            </span>
                        </div>
                        <div class="tasks-sidebar__text-unit">
                            <span class="tasks-sidebar__head-title">
                                Автор
                            </span>
                            <span class="tasks-sidebar__body-title">
                                @if($task->user_id) <a href="{{ route('users::card', $task->user_id) }}" class="tasks-sidebar__author">{{ $task->full_name }}</a> @else Система @endif
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card tasks-sidebar__item">
                    <div class="card-body">
                        <div class="">
                            @if($task->project_id)
                                <div class="tasks-sidebar__text-unit">
                                    <span class="tasks-sidebar__head-title">
                                        Проект
                                    </span>
                                    <span class="tasks-sidebar__body-title">
                                        <a class="tasks-sidebar__link" @if($task->project_id) href="{{ route('projects::card', $task->project_id) }}" @endif>{{ $task->project ? $task->project->name : 'Проект не выбран' }}</a>
                                    </span>
                                </div>
                            @endif
                            @if($task->target_id and $task->status == 45 and $operation)
                                <div class="tasks-sidebar__text-unit">
                                    <span class="tasks-sidebar__head-title">
                                        Операция
                                    </span>
                                        <span class="tasks-sidebar__body-title">
                                        <a class="tasks-sidebar__link"  href="{{ route('building::mat_acc::redirector', $task->target_id) }}">Перейти к операции</a>
                                    </span>
                                </div>
                                <div class="tasks-sidebar__text-unit">
                                    <span class="tasks-sidebar__head-title">
                                        Объект
                                    </span>
                                        <span class="tasks-sidebar__body-title">
                                        {{ $operation->object_from->address ?? '' }}<span style="font-size: 18px;">→</span>{{ $operation->object_to->address ?? '' }}
                                    </span>
                                </div>
                                    <div class="tasks-sidebar__text-unit">
                                    <span class="tasks-sidebar__head-title">
                                        Дата создания операции
                                    </span>
                                            <span class="tasks-sidebar__body-title">
                                        {{ $operation->created_at }}
                                    </span>
                                </div>
                                <div class="tasks-sidebar__text-unit">
                                    <span class="tasks-sidebar__head-title">
                                        Тип операции
                                    </span>
                                    <span class="tasks-sidebar__body-title">
                                        {{ $operation->type_name }}
                                    </span>
                                </div>
                            @endif
                            @if($task->project_address)
                                <div class="tasks-sidebar__text-unit">
                                    <span class="tasks-sidebar__head-title">
                                        Адрес объекта
                                    </span>
                                    <span class="tasks-sidebar__body-title">
                                        {{ $task->project_address }}
                                    </span>
                                </div>
                            @endif
                            @if($task->object_name)
                                <div class="tasks-sidebar__text-unit">
                                    <span class="tasks-sidebar__head-title">
                                        Объект
                                    </span>
                                    <span class="tasks-sidebar__body-title">
                                        {{ $task->object_name }}
                                    </span>
                                </div>
                            @endif
                            @if($task->contractor)
                            <div class="tasks-sidebar__text-unit">
                                <span class="tasks-sidebar__head-title">
                                    Контрагент
                                </span>
                                <span class="tasks-sidebar__body-title">
                                    <a class="tasks-sidebar__link" @if($task->contractor_id) href="{{ route('contractors::card', $task->contractor_id) }}" @endif>
                                        {{ $task->contractor ? $task->contractor->short_name : 'Контрагент не выбран' }}
                                    </a>
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
{{--                TODO need create smth universal to do links and files--}}
                @if(false)
                <div class="card tasks-sidebar__item">
                    <div class="card-body">
                        <div class="accordions" id="links-accordion">
                            <div class="card" style="margin-bottom:0">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <a class="collapsed tasks-sidebar__collapsed-link" data-target="#collapse1" href="#" data-toggle="collapse">
                                            Вспомогательные ссылки
                                            <b class="caret"></b>
                                        </a>
                                    </h5>
                                </div>
                                <div id="collapse1" class="card-collapse collapse">
                                    <div class="card-body tasks-sidebar__body-links">
                                        @if($task->project_id)
                                            <a class="tasks-sidebar__help-link" target="_blank" href="{{ route('projects::tasks', $task->project_id) }}">История проекта</a><br>
                                            <a class="tasks-sidebar__help-link" target="_blank" href="{{ route('project_documents::card', $task->project_id) }}">Проектная документация</a><br>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card tasks-sidebar__item">
                    <div class="card-body">
                        <div class="accordions" id="files-accordion">
                            <div class="card" style="margin-bottom:0">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <a class="collapsed tasks-sidebar__collapsed-link" data-target="#collapse2" href="#" data-toggle="collapse">
                                            Приложенные файлы
                                            <b class="caret"></b>
                                        </a>
                                    </h5>
                                </div>
                                <div id="collapse2" class="card-collapse collapse">
                                    <div class="card-body tasks-sidebar__body-links">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-9 task-header__title">
                                <h4>{{ $task->name }}</h4>
                            </div>
                            <div class="col-md-3 text-right" style="margin-top:3px;">
                                до
                                <span class="task-header__date">
                                     {{ \Carbon\Carbon::parse($task->expired_at)->isoFormat('Do MMMM') }}
                                </span>
                                <span class="task-header__time">
                                    {{ \Carbon\Carbon::parse($task->expired_at)->format('H:m') }}
                                </span>
                            </div>
                        </div>
                        <hr style="margin-top:7px;border-color:#F6F6F6">
                    </div>
                    @include('tasks.partials.' . $task->status)
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js_footer')
<meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection
