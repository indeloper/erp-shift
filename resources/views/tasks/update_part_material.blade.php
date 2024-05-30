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
                                @if($task->user_id) <a href="{{ route('users::card', $task->user_id) }}" class="tasks-sidebar__author">{{ $task->author->full_name }}</a> @else Система @endif
                            </span>
                        </div>
                    </div>
                </div>
                @if ($task->contractor_id)
                    <div class="card tasks-sidebar__item">
                        <div class="card-body">
                            <div class="tasks-sidebar__text-unit">
                                <span class="tasks-sidebar__head-title">
                                    Контрагент
                                </span>
                                <span class="tasks-sidebar__body-title">
                                    <a class="tasks-sidebar__link" @if($task->contractor_id) href="{{ route('contractors::card', $task->contractor_id) }}" @endif>
                                        {{ $task->contractor_name ? $task->contractor_name : 'Контрагент не выбран' }}
                                    </a>
                                </span>
                            </div>
                        </div>
                    </div>

                @endif
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
                                        <a class="tasks-sidebar__help-link" target="_blank" href="{{ $operation_url ?? '' }}">Операция</a><br>
                                    </div>
                                </div>
                            </div>
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
                                    <p>{{ $task->description }}</p>
                                    <p>До: {{ $task->target->manual->name }} {{ round($task->target->count, 3) }} {{ $task->target->units_name[$task->target->unit] }}</p>
                                    <p>После: {{ $task->target->updated_material->manual->name }} {{ round($task->target->updated_material->count, 3) }} {{ $task->target->units_name[$task->target->updated_material->unit] }}</p>
                                    @if(!$task->is_solved)
                                        @if(Auth::id() == $task->responsible_user_id)
                                            <form id="solve_remove" action="{{ route('building::mat_acc::update_part_operation', $task->id) }}" class="form-horizontal" method="post">
                                                @csrf
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-7">
                                                            <label>Результат<star class="star">*</star></label>
                                                            <div class="form-group">
                                                                <select id="status_result" name="status_result" class="selectpicker" data-title="Результат" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                                                    <option value="accept">Согласовать редактирование</option>
                                                                    <option value="decline">Отклонить редактирование</option>
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
                                            <button form="solve_remove" class="btn btn-info btn-center">Отправить</button>
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
@endsection
@push('js_footer')
    <script>
        @if (Session::has('no_soft'))
        $('document').ready(function () {
            swal.fire({
                title: "Внимание",
                text: '{{ Session::get('no_soft') }}',
                type: 'warning',
            });
        });
        @endif
    </script>
@endpush
