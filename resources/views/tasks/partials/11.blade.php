<div class="card-body task-body">
    <div class="row">
        <div id="main_descr" class="col-md-12">
            <h6 style="margin-top:0">
                Описание
            </h6>
            <div id="description">
                <p>Необходимо подтвердить согласование договора с заказчиком.</p>
                @if(Auth::id() == $task->responsible_user_id and ! $task->is_solved)
                    <hr style="border-color:#F6F6F6">
                    <div class="row">
                        <div class="col-md-5">
                            <label class="">Результат<star class="star">*</star></label>
                            <div class="form-group">
                                <select id="contract_status" name="" class="selectpicker" data-title="Результат" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                    <option value="accept">Подтвердить</option>
                                    <option value="decline">Отклонить</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="contract_decline_comm" style="display:none">
                        <div class="col-md-12">
                            <form id="decline_contract" action="{{ route("projects::contract::decline", [$task->project_id, $task->target_id]) }}" method="post">
                                @csrf
                                <input name="contract_id" type="hidden" id="declined_contract_id">
                                <input name="task_id" type="hidden" value="{{ $task->id }}">
                                <label>
                                    Комментарий <star class="star">*</star>
                                </label>
                                <div class="form-group">
                                    <textarea id="final_note" class="form-control textarea-rows" name="final_note" maxlength="500"></textarea>
                                </div>
                            </form>
                        </div>
                    </div>
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
                <button
                    type="button" rel="tooltip" class="btn btn-info" id="accept_contract_btn" style="display:none"
                    @if($contract->get_requests->where('status', 1)->count()) disabled title="Сначала нужно ответить на заявки" @endif
                    onclick="approve_contract({{ $task->target_id }})">
                    Выполнить
                </button>
                <button onclick="decline_contract({{ $task->target_id }})" rel="tooltip" class="btn btn-danger" style="display:none" id="decline_contract_btn">
                    Отклонить
                </button>
            @endif
        </div>
    </div>
</div>
