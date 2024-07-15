<div class="card-body task-body">
    <div class="row">
        <div id="main_descr" class="col-md-12">
            <h6 style="margin-top:0">
                Описание
            </h6>
            <div id="description">
                <p>Необходимо назначить ответственного за учёт рабочего времени</p>
                @if(! $task->is_solved)
                    @if(Auth::id() == $task->responsible_user_id)
                        <br>
                        <hr style="border-color:#F6F6F6">
                        <div class="text-center">
                            <form id="form_select_user" class="form-horizontal" action="{{ route('projects::update_time_responsible', $task->project_id) }}" method="post">
                                @csrf
                                <input type="hidden" name="task_id" value="{{ $task->id }}">
                                <input type="hidden" name="project_id" value="{{ $task->project_id }}">
                                <div class="row">
                                    <div class="col-md-12 text-left">
                                        <label>Позиция<star class="star">*</star></label>
                                        <div class="form-group">
                                            <select name="role" id="js-select-position" style="width:100%;" data-title="Выберите позицию" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                                <option value="">Выберите роль</option>
                                                <option value="6">Ответственный за учёт рабочего времени</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 text-left">
                                        <label class="">Сотрудники<star class="star">*</star></label>
                                        <div class="form-group">
                                            <select name="time_responsible_user_id" id="js-select-users" style="width:100%;" data-title="Выберите сотрудника" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif
                @elseif($task->is_solved)
                    <div class="row">
                        <div class="col-sm-12">
                            <p style="font-size:16px;">
                                <b>Комментарий: </b><br>
                                {{ $task->final_note }}
                            </p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <p style="font-size:14px; padding-left:20px;">
                                Новым ответственным назначен пользователь
                                <a href="{{ route('users::card', $sop->user_id) }}">
                                    {{ $sop->user->long_full_name }}
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
