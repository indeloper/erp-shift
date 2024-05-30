<div class="card-body task-body">
    <div class="row">
        <div id="main_descr" class="col-md-12">
            <h6 style="margin-top:0">
                Описание
            </h6>
            <div id="description">
                <p>Ознакомьтесь с приведенным
                    <a class="tasks-sidebar__help-link" target="_blank" href="{{ route('projects::work_volume::card_' . ($wv_request->type ? 'pile': 'tongue') , [$task->project_id, $task->target_id]) }}">объемом работ</a>
                    , согласуйте или отклоните его</p>
                @if(! $task->is_solved)
                    @if(Auth::id() == $task->responsible_user_id)
                        <form id="checkWV" class="form-horizontal" method="post" enctype="multipart/form-data">
                            @csrf
                            @if (!isset($sop))
                                <input type="hidden" name="noSOP" value="noSOP">
                            @endif
                            <input type="hidden" name="is_tongue" value="{{ $wv_request->type ? 0 : 1 }}">
                            <input type="hidden" name="@if($wv_request->type){{ trim('add_pile') }}@else{{ trim('add_tongue') }}@endif" value="{{ $wv_request->type ? 0 : 1 }}">
                            <input type="hidden" name="from_task_18" value="{{ $task->id }}">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-7">
                                        <label>Результат<star class="star">*</star></label>
                                        <div class="form-group">
                                            <select id="status_result" name="status_result" class="selectpicker" data-title="Результат" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                                <option value="accepted">Согласовать ОР</option>
                                                <option value="declined">Вернуть ОР в работу</option>
                                                <option value="close">Отклонить ОР</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group collapse" id="accept_comment">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>Комментарий для КП<star class="star">*</star></label>
                                        <div class="form-group">
                                            <textarea class="form-control textarea-rows" name="final_note" maxlength="500"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group collapse" id="comment">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>Комментарий к заявке<star class="star">*</star></label>
                                        <div class="form-group">
                                            <textarea id="result_note" class="form-control textarea-rows" name="{{ ($wv_request->type ? 'pile' : 'tongue') . '_description' }}" maxlength="300"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-8">
                                        <label>
                                            Приложенные файлы
                                        </label>
                                        <div class="file-container">
                                            <div id="fileName" class="file-name"></div>
                                            <div class="file-upload ">
                                                <label class="pull-right">
                                                    <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                    <input type="file" name="{{ $wv_request->type ? 'pile_documents[]' : 'tongue_documents[]' }}" accept="*" id="uploadedTongueFiles" class="form-control-file file" onchange="getFileName(this)" multiple>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-8">
                                        <label>
                                            Проектная документация
                                        </label>
                                        <select class="js-select-proj-doc" name="{{ $wv_request->type ? 'project_documents_pile[]' : 'project_documents_tongue[]' }}" data-title="Выберите документ" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" multiple style="width:100%;">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif
                @endif
                @if($task->is_solved)
                    <div class="row">
                        <div class="col-sm-3">
                            <p style="font-size:16px;">
                                <b>Комментарий: </b></p>
                        </div>
                        <div class="col-sm-8">
                            <p style="font-size:14px; padding-left:20px;">
                                {{ $task->final_note }}
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
                <div class="form-group collapse" id="sendForm">
                    <div class="row">
                        <div class="col-sm-12 text-right">
                            <button form="checkWV" class="btn btn-info btn-center">Отправить</button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
