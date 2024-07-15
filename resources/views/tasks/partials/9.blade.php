<div class="card-body task-body">
    <div class="row">
        <div id="main_descr" class="col-md-12">
            <h6 style="margin-top:0">
                Описание
            </h6>
            <div id="description">
                <p>Необходимо приложить подписанный договор или гарантийное письмо.</p>
                @if(Auth::id() == $task->responsible_user_id and ! $task->is_solved)
                    <hr style="border-color:#F6F6F6">
                    <div class="row">
                        <div class="col-md-5">
                            <label class="">Результат<star class="star">*</star></label>
                            <div class="form-group">
                                <select id="sign_status" name="" class="selectpicker" data-title="Результат" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                    <option value="accept">Подтвердить</option>
                                    <option value="decline">Отклонить</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="sign_decline_comm" style="display:none">
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
                    <div class="row" id="sign_accept_comm" style="display:none">
                        <div class="col-md-12">
                            <form id="add_files" class="form-horizontal" action="{{ route('projects::contract::add_files', $task->target_id) }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="contract_id" value="{{ $task->target_id }}">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Вид документа<star class="star">*</star></label>
                                        <div class="form-group">
                                            <select name="type" class="selectpicker" data-title="Выберите тип документа" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                                <option value="1">Договор</option>
                                                <option value="2">Гарантийное письмо</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" >
                                    <div class="col-md-6">
                                        <label for="" style="font-size:0.80">
                                            Приложенный документ<star class="star">*</star>
                                        </label>
                                        <div class="file-container">
                                            <div id="fileName" class="file-name"></div>
                                            <div class="file-upload ">
                                                <label class="pull-right">
                                                    <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                    <input type="file" name="document" accept="*" id="upload_document" class="form-control-file file" onchange="getFileName(this)">
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if ($contract->type == 1)
                                    <div class="row" >
                                        <div class="col-sm-6">
                                            <label>Число сдачи КС <star class="star">*</star>
                                                <button type="button" name="button" class="btn btn-link btn-primary btn-xs mn-0 pd-0" data-container="body"
                                                        data-toggle="popover" data-placement="top" data-content="Каждый месяц до указанного числа необходимо прикрепить сертификаты на поставленный материал" style="position:absolute;">
                                                    <i class="fa fa-info-circle"></i>
                                                </button>
                                            </label>
                                            <div class="form-group">
                                                <input name="ks_date" min="1" max="31" type="number" value="{{ $contract->ks_date }}" class="form-control" placeholder="Укажите число" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" >
                                        <div class="col-sm-6">
                                            <label>За сколько дней  начинать уведомлять
                                                <button type="button" name="button" class="btn btn-link btn-primary btn-xs mn-0 pd-0" data-container="body"
                                                        data-toggle="popover" data-placement="top" data-content="Число показывает, за сколько дней до дня сдачи КС система начнёт присылать уведомления об отсутствующих сертификатах (каждый день)" style="position:absolute;">
                                                    <i class="fa fa-info-circle"></i>
                                                </button>
                                            </label>
                                            <div class="form-group">
                                                <input name="start_notifying_before" min="1" max="31" type="number" class="form-control" placeholder="Укажите число" value="{{ $contract->start_notifying_before ?? 10 }}">
                                            </div>
                                        </div>
                                    </div>
                                @endif
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
                <button id="accept_sign_btn" style="display:none" form="add_files" type="button" onclick="submit_file(this)" class="btn btn-info">Подтвердить</button>
                <button onclick="decline_contract({{ $task->target_id }})" rel="tooltip" class="btn btn-danger" style="display:none" id="decline_sign_btn">
                    Отклонить
                </button>
            @endif
        </div>
    </div>
</div>
