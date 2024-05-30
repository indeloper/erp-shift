@extends('layouts.app')

@section('title', 'Задачи')

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
                                @if($task->user_id)
                                <a href="{{ route('users::card', $task->user_id) }}" class="tasks-sidebar__author">
                                    {{ $task->full_name }}
                                </a>
                                @else
                                    Система
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                @if($task->project_id || $task->contractor_id)
                <div class="card tasks-sidebar__item">
                    <div class="card-body">
                        <div class="">
                            <div class="tasks-sidebar__text-unit">
                                <span class="tasks-sidebar__head-title">
                                    Проект
                                </span>
                                <span class="tasks-sidebar__body-title">
                                    <a class="tasks-sidebar__link" @if($task->project_id) href="{{ route('projects::card', $task->project_id) }}" @endif>{{ $task->project_name ? $task->project_name : 'Проект не выбран' }}</a>
                                </span>
                            </div>
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
                            <div class="tasks-sidebar__text-unit">
                                <span class="tasks-sidebar__head-title">
                                    Контрагент
                                </span>
                                <span class="tasks-sidebar__body-title">
                                    <a class="tasks-sidebar__link"  @if($task->contractor_id) href="{{ route('contractors::card', $task->contractor_id) }}" @endif>{{ $task->contractor_name ? $task->contractor_name : 'Контрагент не выбран' }}</a>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if($task_files->isNotEmpty() || $ticket_files)
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
                                            @foreach($task_files as $task_file)
                                                <a href="{{ asset('storage/docs/task_files/' . $task_file->file_name) }}" class="tasks-sidebar__help-link" target="_blank" rel="tooltip" data-original-title="{{ $task->user->full_name .' '. '('. $task_file->created_at .')'}}">
                                                    {{ $task_file->original_name }}
                                                </a><br>
                                            @endforeach
                                            @if($ticket_files)
                                                @foreach($ticket_files as $ticket_file)
                                                    <a target="_blank" href="{{ asset($ticket_file->path) }}" class="tasks-sidebar__help-link" rel="tooltip" data-original-title="Открыть файл">
                                                        {{ $ticket_file->original_name }}
                                                    </a><br>
                                                @endforeach
                                            @endif
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
                            <div class="col-md-12">
                                <h6 style="margin-top:0">
                                    Описание
                                </h6>
                                <p>
                                    {!! $task->description !!}
                                </p>
                            </div>
                        </div>
                        @if(!$task_redirects->isEmpty())
                            @foreach($task_redirects as $task_redirect)
                            <div class="row">
                                <div class="col-sm-2">
                                    <p style="font-size:12px;">
                                    <b>Исполнитель изменен {{ $task_redirect->created_at }}</b></p>
                                </div>
                                <div class="col-sm-9">
                                    <p style="font-size:12px; padding-left:20px;">
                                        Предыдущий исполнитель : {{ $users_redirects->where('id', $task_redirect->old_user_id)->first()->full_name }}
                                    <p style="font-size:12px; padding-left:20px;">
                                        Новый исполнитель : {{ $users_redirects->where('id', $task_redirect->responsible_user_id)->first()->full_name }}
                                    </p>
                                    <p style="font-size:12px; padding-left:20px;">Комментарий: {{ $task_redirect->redirect_note }}</p>
                                </div>
                            </div>
                            @endforeach
                        @endif
                        <hr style="border-color:#F6F6F6">
                        @if(!$task->is_solved)
                            @if(Auth::id() == $task->responsible_user_id and $task->user_id)
                                <form id="form_solve_task" class="form-horizontal" action="{{ route('tasks::solve', $task->id) }}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group" >
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label>Комментарий<star class="star">*</star></label>
                                                <div class="form-group">
                                                    <textarea class="form-control textarea-rows" maxlength="250" name="final_note" required placeholder="Опишите результат выполнения задачи"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <label for="exampleFormControlFile1">Вложить файлы</label>
                                                <div class="file-container">
                                                    <div id="fileName" class="file-name"></div>
                                                    <div class="file-upload ">
                                                        <label class="pull-right">
                                                            <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                            <input type="file" name="documents[]" accept="*" id="uploadedFile" class="form-control-file file" onchange="getFileName(this)" multiple>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            @else
                            <form id="form_solve_task" class="form-horizontal" action="{{ route('support::task_agreed', $task->id) }}" method="post">
                                @csrf
                                <select name="result" class="selectpicker" data-title="Результат" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                    <option value="accept">Согласовано</option>
                                    <option value="decline">Отклонено</option>
                                </select>
                                <div class="form-group" >
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label>Комментарий</label>
                                            <div class="form-group">
                                                <textarea class="form-control textarea-rows" maxlength="250" name="final_note" placeholder="Опишите результат выполнения задачи"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            @endif
                        @else
                            <div class="row">
                                <div class="col-md-12">
                                    <h6>Комментарий</h6>
                                    <p>{{ $task->final_note }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        <div class="row" style="margin-top:25px">
                            <div class="col-md-3 btn-center">
                                <a href="{{ route('tasks::index') }}" class="btn btn-secondary btn-wd">Назад</a>
                            </div>
                            <div class="col-md-9 btn-center text-right">
                                @if(!$task->is_solved)
                                    @if(Auth::id() == $task->responsible_user_id)
                                        <button form="form_solve_task" id="submit" class="btn btn-wd btn-info">
                                            Ответить
                                        </button>
                                        @if($task->user_id)
                                        <button type="submit" class="btn btn-warning" data-toggle="modal" data-target="#reject-project">
                                            Переадресовать
                                        </button>
                                        @endif
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!$task->is_solved)
    @if(Auth::id() == $task->responsible_user_id)
        <div class="modal fade bd-example-modal-lg show" id="reject-project" tabindex="-1" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
               <div class="modal-content">
                   <div class="modal-header">
                       <h5 class="modal-title" id="">Переадресовать задачу</h5>
                       <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                           <span aria-hidden="true">×</span>
                       </button>
                   </div>
                   <div class="modal-body">
                       <div class="card border-0">
                           <div class="card-body ">
                                <form id="form_update_resp_user" class="form-horizontal" action="{{ route('tasks::update_resp_user', $task->id) }}" method="post">
                                    @csrf
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label">Комментарий<star class="star">*</star></label>
                                        <div class="col-sm-9">
                                            <div class="form-group">
                                                <textarea class="form-control textarea-rows" name="redirect_note" maxlength="250" required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label">Ответственное лицо<star class="star">*</star></label>
                                        <div class="col-sm-9">
                                            <div class="form-group">
                                                <select name="responsible_user_id" id="js-select-user" style="width:100%;" required>
                                                    <option value="">Не выбрано</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                             </div>
                         </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                        <button type="submit" form="form_update_resp_user" class="btn btn-danger">Подтвердить</button>
                   </div>
                </div>
            </div>
        </div>
    @endif
@endif

@endsection

@section('js_footer')
@if(!$task->is_solved)
    @if(Auth::id() == $task->responsible_user_id)
        <script>
            $('#js-select-user').select2({
                language: "ru",
                ajax: {
                    url: '/tasks/get-responsible-user/' + {{ $task->responsible_user_id }},
                    dataType: 'json',
                    delay: 250
                }
            });
        </script>
    @endif
@endif
@endsection
