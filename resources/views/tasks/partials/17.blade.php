<div class="card-body task-body">
    <div class="row">
        <div id="main_descr" class="col-md-12">
            <h6 style="margin-top:0">
                Описание
            </h6>
            <div id="description">
                <p>Ваша заявка на объём работ была отклонена. Вы можете отказаться от заявки или отправить её повторно (заявку можно изменить).</p>
                <hr style="border-color:#F6F6F6">
                <span class="task-info_label" style="margin-top:5px;">{{ $wv_request->name }}</span>
                <form id="create_request_form" class="form-horizontal"  action="{{ route('projects::work_volume_request::store', $task->project_id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="{{ $wv_request->wv->type ? 'add_pile' : 'add_tongue' }}" value="1">
                    <input type="hidden" name="from_task_17" value="{{ $task->id }}">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-12">
                                    <label>
                                        Описание заявки <star class="star">*</star>
                                    </label>
                                    <div class="form-group">
                                        <textarea class="form-control textarea-rows" name="{{ ($wv_request->wv->type ? 'pile' : 'tongue') . '_description' }}" maxlength="500" required @if($task->is_solved == 1) readonly @endif>{{ $wv_request->description }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label>
                                        Ответ на заявку
                                    </label>
                                    <div class="form-group">
                                        <textarea class="form-control textarea-rows" maxlength="500" readonly>{{ $wv_request->result_comment }}</textarea>
                                    </div>
                                </div>
                            </div>
                            @if(! $task->is_solved)
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
                                                    <input type="file" name="{{ $wv_request->wv->type ? 'pile_documents[]' : 'tongue_documents[]' }}" accept="*" id="uploadedTongueFiles" class="form-control-file file" onchange="getFileName(this)" multiple>
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
                                        <select class="js-select-proj-doc" name="{{ $wv_request->wv->type ? 'project_documents_pile[]' : 'project_documents_tongue[]' }}" data-title="Выберите документ" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" multiple style="width:100%;">
                                        </select>
                                    </div>
                                </div>
                                <div class="row"  style="margin-top: 35px">
                                    <div class="col-md-3 btn-center">
                                        <a href="{{ route('tasks::index') }}" class="btn btn-wd">Назад</a>
                                    </div>
                                    <div class="col-md-9 text-right btn-center">
                                        <button type="button" class="btn btn-danger mr-15-d" onclick="declineRequest({{ $task }})">Отказаться от заявки</button>
                                        <button id="submit_wv" type="submit" form="create_request_form" class="btn btn-wd btn-info">Отправить</button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
