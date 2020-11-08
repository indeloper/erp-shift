<div class="card-body task-body">
    <div class="row">
        <div id="main_descr" class="col-md-12">
            <h6 style="margin-top:0">
                Описание
            </h6>
            <div id="description">
                <p>Необходимо назначить исполнителя на формирование коммерческого предложения.</p>
                @if(! $task->is_solved)
                    @if(Auth::id() == $task->responsible_user_id)
                        <br>
                        <hr style="border-color:#F6F6F6">
                        <div class="text-center">
                            <form id="form_select_user" class="form-horizontal" action="{{ route('projects::select_user', $task->project_id) }}" method="post">
                                @csrf
                                <input type="hidden" name="task14" value="1">
                                <input type="hidden" name="wv_id" value="{{ $task->target_id }}">
                                <input type="hidden" name="task_id" value="{{ $task->id }}">
                                <div class="row">
                                    <div class="col-md-12 text-left">
                                        <label>Позиция<star class="star">*</star></label>
                                        <div class="form-group">
                                            <select name="role" id="js-select-position" style="width:100%;" data-title="Выберите позицию" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                                <option value="">Выберите позицию</option>
                                                <option value="2">Ответственный специалист по коммерческим предложениям (шпунт)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 text-left">
                                        <label class="">Сотрудники<star class="star">*</star></label>
                                        <div class="form-group">
                                            <select name="user" id="js-select-users" style="width:100%;" data-title="Выберите сотрудника" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif
                @elseif($task->is_solved)
                    <div class="row">
                        <div class="col-sm-3">
                            <p style="font-size:16px;">
                                <b>Комментарий: </b></p>
                        </div>
                        <div class="col-sm-8">
                            <p style="font-size:14px; padding-left:20px;">
                                Новым ответственным назначен пользователь
                                <a href="{{ route('users::card', $project->user_id) }}">
                                    {{ $project->last_name . ' ' . $project->first_name . ($project->patronymic ? ' ' . $project->patronymic : '') }}
                                </a>
                            </p>
                        </div>
                    </div>
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
            @if(Auth::id() == $task->responsible_user_id and ! $task->is_solved)
                <button class="btn btn-info" form="form_select_user">Выбрать ответственного</button>
            @endif
        </div>
    </div>
</div>
