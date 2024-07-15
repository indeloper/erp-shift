<div class="card-body task-body">
    <div class="row">
        <div id="main_descr" class="col-md-12">
            <h6 style="margin-top:0">
                Описание
            </h6>
            <div id="description">
                <p>Необходимо произвести расчет объемов работ по проекту</p>
                @if($task->is_solved)
                    <div class="row">
                        <div class="col-sm-3">
                            <p style="font-size:16px;">
                                <b>Комментарий: </b></p>
                        </div>
                        <div class="col-sm-8">
                            <p style="font-size:14px; padding-left:20px;">
                                Произведён расчёт объёмов работ
                            </p>
                        </div>
                    </div>
                @endif
                <hr style="border-color:#F6F6F6">
                <!-- Таблица заявок ОР -->
                @if($work_volume_requests->count())
                    <div class="table-responsive">
                        <table class="table table-hover mobile-table">
                            <thead>
                            <tr>
                                <th>Автор</th>
                                <th>Тип</th>
                                <th>Дата</th>
                                <th class="text-right">
                                    Действия</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($work_volume_requests as $wv_request)
                                <tr>
                                    <td data-label="Автор">
                                        @if($wv_request->user_id)
                                            {{ $wv_request->last_name }}
                                            {{ $wv_request->first_name }}
                                            {{ $wv_request->patronymic }}
                                        @else
                                            Система
                                        @endif
                                    </td>
                                    <td data-label="Тип">{{ $wv_request->tongue_pile ? 'Свайное направление' : 'Шпунтовое направление' }}</td>
                                    <td data-label="Дата">{{ $wv_request->updated_at }}</td>
                                    <td data-label="Действия" class="text-right">
                                        <button rel="tooltip" onclick="" class="btn-info btn-link btn-xs btn padding-actions mn-0" data-toggle="modal" data-target="#view-request{{ $wv_request->id }}" data-original-title="Просмотр">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
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
                <a href="{{ $target }}" name="button" class="btn btn-wd btn-success">Перейти к выполнению</a>
            @endif
        </div>
    </div>
</div>

<!-- Модалка для заявок на ОР -->
@foreach ($work_volume_requests as $wv_request)
    <div class="modal fade bd-example-modal-lg show" id="view-request{{ $wv_request->id }}" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Заявка {{ $wv_request->tongue_pile ? 'Свайное направление' : 'Шпунтовое направление' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <hr style="margin-top:0">
                    <div class="card border-0" >
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h6 style="margin:5px 0 0 0">
                                                    Описание
                                                </h6>
                                                <p class="form-control-static">{{ $wv_request->description }}</p>
                                            </div>
                                        </div>
                                        @if ($wv_request->excavation_description !== null)
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label class="control-label">Земляные работы</label>
                                                    <p>{{ $wv_request->excavation_description }}</p>
                                                </div>
                                            </div>
                                        @endif
                                        @if ($wv_request->files->where('is_result', 0)->count() > 0)
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label class="control-label">Приложенные файлы</label>
                                                    <br>
                                                    @foreach($wv_request->files->where('is_result', 0)->where('is_proj_doc', 0) as $file)
                                                        <a target="_blank" href="{{ asset('storage/docs/work_volume_request_files/' . $file->file_name) }}">
                                                            {{ $file->original_name }}
                                                        </a>
                                                        <br>
                                                    @endforeach

                                                    @foreach($wv_request->files->where('is_result', 0)->where('is_proj_doc', 1) as $file)
                                                        <a target="_blank" href="{{ asset('storage/docs/project_documents/' . $file->file_name) }}">
                                                            {{ $file->original_name }}
                                                        </a>
                                                        <br>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
