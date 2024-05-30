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
                    <div class="card tasks-sidebar__item">
                        <div class="card-body">
                            <div class="tasks-sidebar__text-unit">
                            <span class="tasks-sidebar__head-title">
                                Операция
                            </span>
                                <span class="tasks-sidebar__body-title">
                                <a class="tasks-sidebar__link" href="{{ $operation->url }}">
                                    Операция {{ $operation->type_name }}
                                </a>
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
                                        <p>{{ $operation->author->full_name }} создал операцию типа {{ mb_strtolower($operation->type_name) }}.
                                            Вам необходимо ознакомиться с привёденными данными, отклонить или согласовать {{ mb_strtolower($operation->type_name) }}.
                                        </p>
                                        <p>
                                            @if($task->status == 38) Дата проведения операции будет выставлена: c {{ $operation->planned_date_from }} по {{ $operation->planned_date_to }} @endif

                                        </p>
                                        <h6 style="margin-top:0">
                                            Информация о операции
                                        </h6>
                                        <div class="table-responsive">
                                            <table class="table table-hover mobile-table">
                                                <thead>
                                                <tr>
                                                    @if($operation->type == 2)<th>Причина</th>@endif
                                                    @if($operation->type == 2)<th>Комментарий</th>@endif
                                                    <th>Дата</th>
                                                    <th class="text-right">Материалы</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr>
                                                    @if($operation->type == 2)
                                                        <td data-label="Причина">
                                                            {{ $operation->reason }}
                                                        </td>
                                                    @endif
                                                    @if($operation->type == 2)
                                                        <td data-label="Комментарий">{{ $operation->comment_author ?? 'Не указан' }}</td>
                                                    @endif
                                                    <td data-label="Дата">{{ $operation->planned_date_from }}</td>
                                                    <td data-label="Материалы" class="text-right">
                                                        Количество материалов: {{ $operation->materials->count() ?? 0 }}
                                                        @if($operation->materials->count())
                                                            <button rel="tooltip" class="btn-info btn-link btn-xs btn padding-actions mn-0" data-toggle="modal" data-target="#view-operation-materials" data-original-title="Просмотр материалов">
                                                                <i class="fa fa-eye"></i>
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <br>
                                        @if(! $task->is_solved)
                                            @if(Auth::id() == $task->responsible_user_id)
                                                <form id="write_off_control"  class="form-horizontal" action="{{ route('building::mat_acc::write_off::solve_control', $task->id) }}" method="post">
                                                    @csrf
                                                    <div class="form-group">
                                                        <div class="row">
                                                            <div class="col-md-7">
                                                                <label>Результат<star class="star">*</star></label>
                                                                <div class="form-group">
                                                                    <select name="status_result" class="selectpicker" data-title="Результат" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                                                        <option value="accept">Согласовать операцию</option>
                                                                        <option value="decline">Отклонить операцию</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group" id="comment">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <label>Комментарий</label>
                                                                <div class="form-group">
                                                                    <textarea id="result_note" class="form-control textarea-rows" name="description" maxlength="200"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            @endif
                                        @else
                                            <h6 style="margin-top:0">
                                                Результат
                                            </h6>
                                            <p style="font-size:14px;">{{ $task->final_note }}</p>
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
                                <div class="col-md-9 text-right btn-center">
                                    @if(Auth::id() == $task->responsible_user_id and !$task->is_solved)
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-sm-12 text-right">
                                                    <button form="write_off_control" class="btn btn-info btn-center">Отправить</button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@if($operation->materials->count())
    <!-- Modal for operation materials -->
    <div class="modal fade bd-example-modal-lg show" id="view-operation-materials" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Материалы операции</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <hr style="margin-top:0">
                    <div class="card border-0" >
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mobile-table">
                                    <thead>
                                    <tr>
                                        <th>Материал</th>
                                        <th>Единица измерения</th>
                                        <th>Количество</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($operation->materials as $material)
                                        <tr>
                                            <td data-label="Материал">
                                                {{ $material->manual->name }}
                                            </td>
                                            <td data-label="Единица измерения">{{ $material->unit_for_humans }}</td>
                                            <td data-label="Количество">{{ round($material->count, 3) }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
