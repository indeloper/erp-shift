<div class="card-body task-body">
    <div class="row">
        <div id="main_descr" class="col-md-12">
            <h6 style="margin-top:0">
                Описание
            </h6>
            <div id="description">
                <p>Необходимо согласовать  договор</p>
                <!-- Таблица заявок на договор -->
                @if ($contract_requests->count() > 0)
                    <div class="card-body">
                        <div class="strpied-tabled-with-hover">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>Название</th>
                                        <th>Автор</th>
                                        <th>Дата</th>
                                        <th class="text-right">
                                            Действия</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($contract_requests as $contract_request)
                                        @if ($contract_request->status === 2)
                                            <tr class="confirm">
                                        @elseif ($contract_request->status === 3)
                                            <tr class="reject">
                                        @else
                                            <tr>
                                        @endif
                                                <td>{{ $contract_request->name }}</td>
                                                <td>
                                                    @if(!$contract_request->last_name)
                                                        Система
                                                    @else
                                                        {{ $contract_request->last_name }}
                                                        {{ $contract_request->first_name }}
                                                        {{ $contract_request->patronymic }}
                                                    @endif
                                                </td>
                                                <td>{{ $contract_request->updated_at }}</td>
                                                <td class="text-right">
                                                    <button rel="tooltip" type="button" class="btn btn-info btn-link btn-xs padding-actions mn-0" data-toggle="modal" data-target="#view-contract-request-{{ $contract_request->id }}" data-original-title="Просмотр">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
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
                <a href="{{ $target }}" name="button" class="btn btn-wd btn-success">Перейти к выполнению</a>
            @endif
        </div>
    </div>
</div>
