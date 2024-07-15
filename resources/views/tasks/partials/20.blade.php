<div class="card-body task-body">
    <div class="row">
        <div id="main_descr" class="col-md-12">
            <h6 style="margin-top:0">
                Описание
            </h6>
            <div id="description">
                <p>{{ $task->description }}</p>
                @if(Auth::id() == $task->responsible_user_id and ! $task->is_solved)
                    <form id="solve_remove" action="{{ route('tasks::solve_task', $task->id) }}" class="form-horizontal" method="post">
                        @csrf
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-7">
                                    <label>Результат<star class="star">*</star></label>
                                    <div class="form-group">
                                        <select name="status_result" class="selectpicker" data-title="Результат" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                            <option value="accept">Согласовать удаление</option>
                                            <option value="decline">Отклонить удаление</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-12">
                                    <label>Комментарий</label>
                                    <div class="form-group">
                                        <textarea class="form-control textarea-rows" name="description" maxlength="200"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                @elseif($task->is_solved)
                    <div class="row">
                        <div class="col-sm-3">
                            <p style="font-size:16px;">
                                <b>Комментарий: </b></p>
                        </div>
                        <div class="col-sm-8">
                            <p style="font-size:14px; padding-left:20px;">
                                {{ $task->final_note ? $task->final_note : 'Не указан' }}
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
