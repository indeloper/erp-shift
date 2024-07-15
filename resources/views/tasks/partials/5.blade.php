<div class="card-body task-body">
    <div class="row">
        <div id="main_descr" class="col-md-12">
            <h6 style="margin-top:0">
                Описание
            </h6>
            <div id="description">
                <p>Необходимо сформировать коммерческое предложение на основании данных объёмов работ</p>
                @if($task->is_solved)
                    <div class="row">
                        <div class="col-sm-3">
                            <p style="font-size:16px;">
                                <b>Комментарий: </b></p>
                        </div>
                        <div class="col-sm-8">
                            <p style="font-size:14px; padding-left:20px;">
                                Коммерческое предложение сформировано
                            </p>
                        </div>
                    </div>
                @endif
                @if ($show_comments)
                    @if (count($comments))
                        <h6 style="margin-bottom: 10px">Комментарии ответственного за ОР ({{ $wv_responsible->full_name ?? 'Ответственный не найден' }})</h6>
                        @foreach($comments as $comment)
                            <p>{{$comment}}</p>
                        @endforeach
                    @endif
                    @if (isset($task->prev_task->description) && isset($task->prev_task->responsible_user_id))
                        @if($task->prev_task->description != '' and $task->prev_task->description != null)
                        <h6 style="margin-bottom: 10px">Комментарий согласующего ОР ({{$task->prev_task->responsible_user->full_name}})</h6>
                        <p>{{$task->prev_task->description}}</p>

                            @endif
                    @endif
                @endif

                <hr style="border-color:#F6F6F6">
                <!-- Таблица заявок КП -->
                @if($commercial_offer_requests->count())
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
                            @foreach ($commercial_offer_requests as $offer_request)
                                <tr>
                                    <td data-label="Автор">
                                        @if($offer_request->user_id)
                                            {{ $offer_request->last_name }}
                                            {{ $offer_request->first_name }}
                                            {{ $offer_request->patronymic }}
                                        @else
                                            Система
                                        @endif
                                    </td>
                                    <td data-label="Тип">{{ $offer_request->is_tongue ? $offer_request->is_tongue == 2 ? 'Объединённое' : 'Шпунтовое направление' : 'Свайное направление' }}</td>
                                    <td data-label="Дата">{{ $offer_request->updated_at }}</td>
                                    <td data-label="Действия" class="text-right">
                                        <button rel="tooltip" class="btn-info btn-link btn-xs btn padding-actions mn-0" data-toggle="modal" data-target="#view-request-offer{{ $offer_request->id }}" data-original-title="Просмотр">
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

<!-- Модалки для заявок КП -->
@if($task->status == 5)
    @foreach ($commercial_offer_requests as $offer_request)
        <div class="modal fade bd-example-modal-lg show" id="view-request-offer{{ $offer_request->id }}" role="dialog" aria-labelledby="modal-search" style="display: none;">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Заявка на КП</h5>
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
                                        <label class="control-label">Описание</label>
                                        <p>
                                            {{ $offer_request->description }}
                                        </p>
                                    </div>
                                </div>
                                @if ($offer_request->files->where('is_result', 0)->count() > 0)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label class="control-label">Приложенные файлы</label>
                                            <br>
                                            @foreach($offer_request->files->where('is_result', 0)->where('is_proj_doc', 0) as $file)
                                                <a target="_blank" href="{{ asset('storage/docs/commercial_offer_request_files/' . $file->file_name) }}">
                                                    {{ $file->original_name }}
                                                </a>
                                                <br>
                                            @endforeach

                                            @foreach($offer_request->files->where('is_result', 0)->where('is_proj_doc', 1) as $file)
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif
