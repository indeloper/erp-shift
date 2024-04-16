@extends('layouts.app')

@section('title', 'Уведомления')

@section('url', route('notifications::index'))

@section('css_top')
    <style media="screen">
        .form-check .form-check-sign::after {
            margin-left: -20px;
        }

        .th-check:after,
        .th-check:before {
            margin-top:-17px!important;
        }
    </style>
@endsection

@section('js_footer')
    <script type="text/javascript" src="{{ asset('js/notifications.js')}}"></script>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 mobile-card">
            <div class="card strpied-tabled-with-hover">
                <div class="fixed-table-toolbar toolbar-for-btn">
                    <div class="row">
                        <div class="col-md-12">
{{--                            @include('notifications.shared.read-all')--}}
                            @include('notifications.shared.settings')
                        </div>
                    </div>
                </div>
{{--                @if($notifications->count())--}}
{{--                    --}}
{{--                    <div class="table-responsive">--}}
{{--                        <table class="table table-hover mobile-table">--}}
{{--                            <thead>--}}
{{--                            <tr>--}}
{{--                                <th>Уведомления</th>--}}
{{--                                <th>Контрагент</th>--}}
{{--                                <th style="max-width: 500px">Адрес объекта</th>--}}
{{--                                <th>Дата</th>--}}
{{--                                <th class="text-right" id="actions">Действия</th>--}}
{{--                            </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody class="notify_place">--}}
{{--                            @foreach($notifications as $key => $notify)--}}
{{--                                <tr class="notify {{ $notify->is_seen ? 'bg-color-snow' : 'notSeen' }}">--}}
{{--                                    --}}
{{--                                    --}}
{{--                                    <td data-label="Уведомления">{{ $notify->name }}</td>--}}
{{--                                    --}}
{{--                                    <td data-label="Контрагент">{{ $notify->short_name ? $notify->short_name : 'Не указан' }}</td>--}}
{{--                                    <td data-label="Адрес объекта" style="max-width: 500px">{{ $notify->address ? $notify->address : 'Не указан' }}</td>--}}
{{--                                    <td data-label="Дата">{{ $notify->created_at }}</td>--}}
{{--                                    <td data-label="" class="td-actions text-right actions">--}}
{{--                                        @if(isset($notify->task) && $notify->task->is_solved === 0)--}}
{{--                                            <a href="{{ $notify->task->task_route() }}" rel="tooltip"--}}
{{--                                               class="btn-info btn-link" data-original-title="Перейти к задаче">--}}
{{--                                                <i class="fa fa-arrow-right" aria-hidden="true"></i>--}}
{{--                                            </a>--}}
{{--                                        @endif--}}
{{--                                        @if($notify->status == 1)--}}
{{--                                            <a href="{{ route('projects::card', $notify->project_id) }}" rel="tooltip"--}}
{{--                                               class="btn-info btn-link" data-original-title="Перейти к проекту">--}}
{{--                                                <i class="fa fa-arrow-right" aria-hidden="true"></i>--}}
{{--                                            </a>--}}
{{--                                        @endif--}}
{{--                                        @if($notify->status == 2)--}}
{{--                                            <a href="{{ route('projects::card', [$notify->project_id, 'task' => $notify->task_id]) }}" rel="tooltip"--}}
{{--                                               class="btn-info btn-link" data-original-title="Перейти к проекту">--}}
{{--                                                <i class="fa fa-arrow-right" aria-hidden="true"></i>--}}
{{--                                            </a>--}}
{{--                                        @endif--}}
{{--                                        @if($notify->status == 3 and $notify->wv_request && $notify->wv_request->wv)--}}
{{--                                            <a href="{{ route('projects::work_volume::card_' . ($notify->wv_request->wv->type ? 'pile': 'tongue'),--}}
{{--                                                [$notify->wv_request->wv->project_id, $notify->wv_request->wv->id, 'req' => $notify->wv_request->id]) }}"--}}
{{--                                               target="_blank" rel="tooltip" class="btn-info btn-link" data-original-title="Перейти к заявке">--}}
{{--                                                <i class="fa fa-arrow-right" aria-hidden="true"></i>--}}
{{--                                            </a>--}}
{{--                                        @endif--}}
{{--                                        @if($notify->status == 4 && $notify->co_request && $notify->co_request->co)--}}
{{--                                            <a href="{{ route('projects::commercial_offer::card_'. ($notify->co_request->co->is_tongue ? 'tongue' : 'pile'),--}}
{{--                                                [$notify->co_request->co->project_id, $notify->co_request->co->id, 'req' => $notify->co_request->id]) }}"--}}
{{--                                               target="_blank" rel="tooltip" class="btn-info btn-link" data-original-title="Перейти к заявке">--}}
{{--                                                <i class="fa fa-arrow-right" aria-hidden="true"></i>--}}
{{--                                            </a>--}}
{{--                                        @endif--}}
{{--                                        @if($notify->status == 5)--}}
{{--                                            <a href="{{ route('contractors::card', $notify->contractor_id) }}" target="_blank" rel="tooltip"--}}
{{--                                               class="btn-info btn-link" data-original-title="Перейти к контрагенту">--}}
{{--                                                <i class="fa fa-arrow-right" aria-hidden="true"></i>--}}
{{--                                            </a>--}}
{{--                                        @endif--}}
{{--                                        @if(in_array($notify->status, [6, 7]) and \App\Models\q3wMaterial\operations\q3wMaterialOperation::find($notify->target_id))--}}
{{--                                            <a href="{{ \App\Models\q3wMaterial\operations\q3wMaterialOperation::find($notify->target_id)->getUrlAttribute() }}" target="_blank" rel="tooltip"--}}
{{--                                               class="btn-info btn-link" data-original-title="Перейти к операции">--}}
{{--                                                <i class="fa fa-arrow-right" aria-hidden="true"></i>--}}
{{--                                            </a>--}}
{{--                                        @endif--}}
{{--                                        @if($notify->status == 8)--}}
{{--                                            <a href="{{ route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $notify->target_id]) }}" target="_blank" rel="tooltip"--}}
{{--                                               class="btn-info btn-link" data-original-title="Перейти к заявке">--}}
{{--                                                <i class="fa fa-arrow-right" aria-hidden="true"></i>--}}
{{--                                            </a>--}}
{{--                                        @endif--}}
{{--                                        @if($notify->type == 106)--}}
{{--                                            <a href="{{ route('building::mat_acc::certificateless_operations', ['contract_id' => $notify->notificationable->id]) }}" target="_blank" rel="tooltip"--}}
{{--                                               class="btn-info btn-link" data-original-title="Перейти к списку операций">--}}
{{--                                                <i class="fa fa-arrow-right" aria-hidden="true"></i>--}}
{{--                                            </a>--}}
{{--                                        @endif--}}
{{--                                        @if($notify->type == 111)--}}
{{--                                            <a href="{{ route('tasks::index') }}" target="_blank" rel="tooltip"--}}
{{--                                               class="btn-info btn-link" data-original-title="Перейти к списку задач">--}}
{{--                                                <i class="fa fa-arrow-right" aria-hidden="true"></i>--}}
{{--                                            </a>--}}
{{--                                        @endif--}}
{{--                                        @if(str_contains($notify->name, 'notificationHook_'))--}}
{{--                                            <a href="#" --}}
{{--                                               class="btn-info btn-link" --}}
{{--                                               data-original-title="Перейти к задаче"--}}
{{--                                               id="{{$hookTypeAndId}}"--}}
{{--                                               onclick='hookHandlerDispatcher("{{$hookTypeAndId}}")'--}}
{{--                                            >--}}
{{--                                                <i class="fa fa-arrow-right" ></i>--}}
{{--                                            </a>--}}
{{--                                        @endif--}}
{{--                                        @if(!$notify->is_seen)--}}
{{--                                            <a rel="tooltip" class="btn-success btn-link"--}}
{{--                                               onclick="view_notify(this, {{ $notify->id }})"--}}
{{--                                               data-original-title="Пометить прочитанным">--}}
{{--                                                <i class="fa fa-eye"></i>--}}
{{--                                            </a>--}}
{{--                                        @endif--}}
{{--                                        <a rel="tooltip" class="btn-danger btn-link"--}}
{{--                                           onclick="delete_notify(this, {{ $notify->id }})"--}}
{{--                                           data-original-title="Удалить уведомление">--}}
{{--                                            <i class="fa fa-times"></i>--}}
{{--                                        </a>--}}
{{--                                    </td>--}}
{{--                                </tr>--}}
{{--                            @endforeach--}}
{{--                            </tbody>--}}
{{--                        </table>--}}
{{--                    </div>--}}
{{--                @else--}}
{{--                    <div id="nothingTip" class="col-md-12 text-center" style="margin-top: 15px">--}}
{{--                        Не нашлось ни одного уведомления.--}}
{{--                    </div>--}}
{{--                @endif--}}
{{--                <div class="col-md-12" style="padding:0; margin-top:20px; margin-left:-2px">--}}
{{--                    <div class="right-edge fix-pagination">--}}
{{--                        <div class="page-container">--}}
{{--                            {{ $notifications->appends(['search' => Request::get('search')])->links() }}--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
            </div>
        </div>
    </div>

@endsection
