<div class="card-body task-body">
    <div class="row">
        <div id="main_descr" class="col-md-12">
            <h6 style="margin-top:0">
                Описание
            </h6>
            <div id="description">
                <p>Необходимо проверить информацию о контрагенте {{ $task->contractor->short_name }}.</p>
                <p>Задача была создана после автоматической проверки информации о контрагентах</p>
                @if(! $task->is_solved)
                    @if(Auth::id() == $task->responsible_user_id)
                        <br>
                        <hr style="border-color:#F6F6F6">
                        <div>
                            @foreach($task->changing_fields as $field)
                                <p><b>@lang('fields.contractor.' . $field->field_name):</b></p>
                                <p>{{ $field->old_value ?? 'Не заполнено' }} <i class="fas fa-arrow-right"></i> {{ $field->value }}</p>
                            @endforeach
                        </div>
                    @endif
                @elseif($task->is_solved)
                    <div class="row">
                        <div class="col-sm-12">
                            <div>
                                @foreach($task->changing_fields as $field)
                                    <p><b>@lang('fields.contractor.' . $field->field_name):</b></p>
                                    <p>{{ $field->old_value ?? 'Не заполнено' }} <i class="fas fa-arrow-right"></i> {{ $field->value }}</p>
                                @endforeach
                            </div>
                            <br>
                                <p style="font-size:16px;">
                                    <b>Результат:</b>
                                    {{ $task->getResult }}
                                </p>
                        </div>
                    </div>
                @endif
                <form id="form_agree" action="{{ route('contractors::solve_task_check_contractor', $task->id) }}" method="post">
                    @csrf
                    <input type="hidden" name="change_fields" value="1">
                </form>
                <form id="form_disagree" action="{{ route('contractors::solve_task_check_contractor', $task->id) }}" method="post">
                    @csrf
                    <input type="hidden" name="change_fields" value="0">
                </form>
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
                <button class="btn btn-danger" form="form_disagree">Отклонить изменения</button>

                <button class="btn btn-info" form="form_agree">Принять новые изменения</button>
            @endif
        </div>
    </div>
</div>
