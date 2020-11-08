<div class="card-body task-body">
    <div class="row">
        <div id="main_descr" class="col-md-12">
            <h6 style="margin-top:0">
                Описание
            </h6>
            <div id="description">
                <p>Укажите результат согласования коммерческого предложения. </p>
                @if($task->description)
                    <p>{{ $task->description }}</p>
                @endif
                @if(! $task->is_solved)
                    @if(Auth::id() == $task->responsible_user_id)
                        <hr style="border-color:#F6F6F6">
                        <form id="form_solve_task" class="form-horizontal" action="{{ route('tasks::solve_task', $task->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <label class="">Результат<star class="star">*</star></label>
                                        <div class="form-group">
                                            <select id="status_result" name="status_result" class="selectpicker" data-title="Результат" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                                <option value="accept">Согласовано</option>
                                                <option value="archive">В архив</option>
                                                <option value="transfer">Перенести дату</option>
                                                <option value="change">Требуются изменения</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group collapse" id="comment">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <label class="">Комментарий<star class="star">*</star></label>
                                        <div class="form-group">
                                            <textarea id="result_note" class="form-control textarea-rows" maxlength="1000" name="final_note" placeholder="Укажите комментарий" required></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="fromСontainer" class="row d-none">
                                <label class="col-sm-3 col-form-label">Перенести на дату<star class="star">*</star></label>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <input id="from" name="revive_at" class="form-control datepicker" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif
                @else
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
            @if(! $task->is_solved)
                @if(Auth::id() == $task->responsible_user_id)
                    <button form="form_solve_task" class="btn btn-info">Выполнить</button>
                @endif
            @endif
        </div>
    </div>
</div>
