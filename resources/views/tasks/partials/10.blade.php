<div class="card-body task-body">
    <div class="row">
        <div id="main_descr" class="col-md-12">
            <h6 style="margin-top:0">
                Описание
            </h6>
            <div id="description">
                <p>Необходимо приложить подписанный договор.</p>
                @if(Auth::id() == $task->responsible_user_id and ! $task->is_solved)
                    <hr style="border-color:#F6F6F6">
                    <div class="row" id="sign_accept_comm">
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
                <button id="accept_sign_btn" form="add_files" type="button" onclick="submit_file(this)" class="btn btn-info">Подтвердить</button>
            @endif
        </div>
    </div>
</div>
