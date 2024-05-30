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
<div class="row">
    <div class="col-md-12 col-xl-11 mr-auto ml-auto">
        <div class="row task-card">
            <div class="col-md-4 tasks-sidebar">
                <div class="card tasks-sidebar__item tasks-sidebar__item1">
                    <div class="card-body">
                        <div class="tasks-sidebar__text-unit">
                            <span class="tasks-sidebar__head-title">
                                Время создания
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
                                     {{ strftime('%d %B', strtotime($task->expired_at)) }}
                                </span>
                                <span class="task-header__time">
                                    {{ \Carbon\Carbon::parse($task->expired_at)->format('h:m') }}
                                </span>
                            </div>
                        </div>
                        <hr style="margin-top:7px;border-color:#F6F6F6">
                    </div>
                    <div class="card-body task-body">
                        <div class="row">
                            <div id="main_descr" class="col-md-12">
                                <h6 style="margin-top:0">
                                    Описание
                                </h6>
                                <div id="description">
                                    <p>
                                        Была создана <a href="{{ $task->taskable->general_url }}">операция</a> без сертификатов. Вам необходимо прикрепить сертификаты ко всем частичным закрытиям операции, в которых они отсутствуют.
                                    </p>
                                    <p>
                                        Также вы можете ознакомиться со всеми <a href="{{ route('building::mat_acc::certificateless_operations') }}">операциями без сертификатов</a>.
                                    </p>
                                    @if($task->is_solved)
                                        <p style="font-size:14px;">В операцию были добавлены сертификаты</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row" style="margin-top:25px">
                            <div class="col-md-3 btn-center">
                                <a href="{{ route('tasks::index') }}" class="btn btn-wd">Назад</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
