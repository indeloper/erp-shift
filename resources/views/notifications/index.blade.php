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

@section('content')
    <div class="row">
        <div class="col-md-12 mobile-card">
            <div class="card strpied-tabled-with-hover">
                <div class="fixed-table-toolbar toolbar-for-btn">
                    <div class="row">
                        <div class="col-md-12">
                            @if(count($notifications->where('is_seen', 0)) > 0)
                                <form action="{{ route('notifications::view_all') }}" method="post">
                                    @csrf
                                    <button class="btn btn-round btn-outline btn-sm add-btn pull-right mt-10__mobile"
                                            style="margin-right: 10px;">
                                        Прочитать всё
                                    </button>
                                </form>
                            @endif
                            <button class="btn btn-round btn-outline btn-sm add-btn pull-right" style="margin-right: 10px;" data-toggle="modal" data-target="#notif_settings">
                                    <i class="fa fa-cog"></i>
                                Настройка уведомлений
                            </button>
                        </div>
                    </div>
                </div>
                @if($notifications->count())
                    <div class="table-responsive">
                        <table class="table table-hover mobile-table">
                            <thead>
                            <tr>
                                <th>Уведомления</th>
                                <th>Контрагент</th>
                                <th style="max-width: 500px">Адрес объекта</th>
                                <th>Дата</th>
                                <th class="text-right" id="actions">Действия</th>
                            </tr>
                            </thead>
                            <tbody class="notify_place">
                            @foreach($notifications as $key => $notify)
                                <tr class="notify {{ $notify->is_seen ? 'bg-color-snow' : 'notSeen' }}">
                                    <td data-label="Уведомления">{{ $notify->name }}</td>
                                    <td data-label="Контрагент">{{ $notify->short_name ? $notify->short_name : 'Не указан' }}</td>
                                    <td data-label="Адрес объекта" style="max-width: 500px">{{ $notify->address ? $notify->address : 'Не указан' }}</td>
                                    <td data-label="Дата">{{ $notify->created_at }}</td>
                                    <td data-label="" class="td-actions text-right actions">
                                        @if(isset($notify->task) && $notify->task->is_solved === 0)
                                            <a href="{{ $notify->task->task_route() }}" rel="tooltip"
                                               class="btn-info btn-link" data-original-title="Перейти к задаче">
                                                <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                            </a>
                                        @endif
                                        @if($notify->status == 1)
                                            <a href="{{ route('projects::card', $notify->project_id) }}" rel="tooltip"
                                               class="btn-info btn-link" data-original-title="Перейти к проекту">
                                                <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                            </a>
                                        @endif
                                        @if($notify->status == 2)
                                            <a href="{{ route('projects::card', [$notify->project_id, 'task' => $notify->task_id]) }}" rel="tooltip"
                                               class="btn-info btn-link" data-original-title="Перейти к проекту">
                                                <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                            </a>
                                        @endif
                                        @if($notify->status == 3 and $notify->wv_request && $notify->wv_request->wv)
                                            <a href="{{ route('projects::work_volume::card_' . ($notify->wv_request->wv->type ? 'pile': 'tongue'),
                                                [$notify->wv_request->wv->project_id, $notify->wv_request->wv->id, 'req' => $notify->wv_request->id]) }}"
                                               target="_blank" rel="tooltip" class="btn-info btn-link" data-original-title="Перейти к заявке">
                                                <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                            </a>
                                        @endif
                                        @if($notify->status == 4 && $notify->co_request && $notify->co_request->co)
                                            <a href="{{ route('projects::commercial_offer::card_'. ($notify->co_request->co->is_tongue ? 'tongue' : 'pile'),
                                                [$notify->co_request->co->project_id, $notify->co_request->co->id, 'req' => $notify->co_request->id]) }}"
                                               target="_blank" rel="tooltip" class="btn-info btn-link" data-original-title="Перейти к заявке">
                                                <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                            </a>
                                        @endif
                                        @if($notify->status == 5)
                                            <a href="{{ route('contractors::card', $notify->contractor_id) }}" target="_blank" rel="tooltip"
                                               class="btn-info btn-link" data-original-title="Перейти к контрагенту">
                                                <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                            </a>
                                        @endif
                                        @if(in_array($notify->status, [6, 7]) and \App\Models\q3wMaterial\operations\q3wMaterialOperation::find($notify->target_id))
                                            <a href="{{ \App\Models\q3wMaterial\operations\q3wMaterialOperation::find($notify->target_id)->getUrlAttribute() }}" target="_blank" rel="tooltip"
                                               class="btn-info btn-link" data-original-title="Перейти к операции">
                                                <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                            </a>
                                        @endif
                                        @if($notify->status == 8)
                                            <a href="{{ route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $notify->target_id]) }}" target="_blank" rel="tooltip"
                                               class="btn-info btn-link" data-original-title="Перейти к заявке">
                                                <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                            </a>
                                        @endif
                                        @if($notify->type == 106)
                                            <a href="{{ route('building::mat_acc::certificateless_operations', ['contract_id' => $notify->notificationable->id]) }}" target="_blank" rel="tooltip"
                                               class="btn-info btn-link" data-original-title="Перейти к списку операций">
                                                <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                            </a>
                                        @endif
                                        @if($notify->type == 111)
                                            <a href="{{ route('tasks::index') }}" target="_blank" rel="tooltip"
                                               class="btn-info btn-link" data-original-title="Перейти к списку задач">
                                                <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                            </a>
                                        @endif
                                        @if(!$notify->is_seen)
                                            <a rel="tooltip" class="btn-success btn-link"
                                               onclick="view_notify(this, {{ $notify->id }})"
                                               data-original-title="Пометить прочитанным">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        @endif
                                        <a rel="tooltip" class="btn-danger btn-link"
                                           onclick="delete_notify(this, {{ $notify->id }})"
                                           data-original-title="Удалить уведомление">
                                            <i class="fa fa-times"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div id="nothingTip" class="col-md-12 text-center" style="margin-top: 15px">
                        Не нашлось ни одного уведомления.
                    </div>
                @endif
                <div class="col-md-12" style="padding:0; margin-top:20px; margin-left:-2px">
                    <div class="right-edge fix-pagination">
                        <div class="page-container">
                            {{ $notifications->appends(['search' => Request::get('search')])->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification settings modal -->
    <div class="modal fade bd-example-modal-lg show" id="notif_settings" tabindex="-1" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title">Настройка уведомлений</h5>
                   <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">×</span>
                   </button>
               </div>
               <div class="modal-body">
                   <div class="row">
                       <div class="col-md-12 text-right mb-20">
                           <button type="button" name="button" class="btn btn-sm btn-round btn-outline" onclick="ban_notification()">
                               <i class="fa fa-ban"></i>
                               Отключить все уведомления
                           </button>
                       </div>
                   </div>
                   <form id="update_notifications" class="form-horizontal" action="{{ route('users::update_notifications') }}" method="post">
                       @csrf
                       <input type="hidden" name="disableAll" id="disableAll">
                       <div class="table-responsive">
                           <table class="table table-hover">
                               <thead>
                                   <tr>
                                       <th>Уведомления</th>
                                       <th class="text-right">
                                           Телеграм
                                           <div class="form-check" style="display:inline-block;margin-bottom:0;">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" type="checkbox" id="check_all_telegram">
                                                    <span class="form-check-sign th-check"></span>
                                                </label>
                                           </div>
                                       </th>
                                       <th class="text-right">
                                           Система
                                           <div class="form-check" style="display:inline-block;margin-bottom:0">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" type="checkbox" id="check_all_system">
                                                    <span class="form-check-sign th-check"></span>
                                                </label>
                                           </div>
                                       </th>
                                   </tr>
                               </thead>
                               <tbody>
                               @foreach($notification_types as $type)
                                   <tr>
                                       <td>{{ $type->name }}</td>
                                       <td class="text-right">
                                           <div class="form-check">
                                                <label class="form-check-label">
                                                    <input class="form-check-input telegram_check" type="checkbox" name="in_telegram[]" value="{{ $type->id }}" @if(! in_array($type->id, $disabled_in_telegram)) checked @endif>
                                                    <span class="form-check-sign"></span>
                                                </label>
                                           </div>
                                       </td>
                                       <td class="text-right">
                                           <div class="form-check">
                                                <label class="form-check-label">
                                                    <input class="form-check-input system_check" type="checkbox" name="in_system[]" value="{{ $type->id }}" @if(! in_array($type->id, $disabled_in_system)) checked @endif>
                                                    <span class="form-check-sign"></span>
                                                </label>
                                           </div>
                                       </td>
                                   </tr>
                               @endforeach
                               </tbody>
                           </table>
                       </div>
                   </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    <button type="submit" form="update_notifications" class="btn btn-info">Сохранить</button>
               </div>
            </div>
        </div>
    </div>
@endsection

@section('js_footer')
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <script type="text/javascript">
        function ban_notification() {
            swal({
                title: 'Вы уверены?',
                text: "Все уведомления будут отключены!",
                type: 'warning',
                showCancelButton: true,
                cancelButtonText: 'Назад',
                confirmButtonText: 'Подтвердить'
            }).then((result) => {
                if(result.value) {
                    $('.form-check-input').prop('checked', false);
                    $('#disableAll').val(1);
                    $('#update_notifications').submit();
                }
            });
        }
    </script>

    <script type="text/javascript">
        $('#check_all_system').change(function(){
            if($(this).prop("checked")){
                $('.system_check').prop('checked', true);
            } else {
                $('.system_check').prop('checked', false);
            }
        });

        $('#check_all_telegram').change(function(){
            if($(this).prop("checked")){
                $('.telegram_check').prop('checked', true);
            } else {
                $('.telegram_check').prop('checked', false);
            }
        });


    </script>
    <script>
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

        function delete_notify(e, notify_id) {
            $.ajax({
                url: '{{ route("notifications::delete") }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    notify_id: notify_id,
                },
                dataType: 'JSON',
                success: function () {
                    $(e).closest('.notify').remove();
                    $('.tooltip').tooltip('hide');
                    notify_length = $('.notify').length;
                    notSeen = $('.notify notSeen').length;
                    if (notify_length === 0) {
                        location.reload()
                    } else if (notSeen === 0) {
                        $('#actions').addClass('d-none');
                        $('.add-btn').addClass('d-none');
                    }
                }
            });
        }

        function view_notify(e, notify_id) {
            $.ajax({
                url: '{{ route("notifications::view") }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    notify_id: notify_id,
                },
                dataType: 'JSON',
                success: function () {
                    $(e).closest('.notify').appendTo('.notify_place');
                    $(e).closest('.notify').addClass('bg-color-snow');
                    $(e).hide();

                    notSeen = $('.bg-color-snow').length;
                    tr = $('.notify').length;

                    if (notSeen === tr) {
                        $('#actions').addClass('d-none');
                        $('.add-btn').addClass('d-none');
                    }
                }
            });
        }

    </script>
    <script type="text/javascript">
        function pagination (){
            if(screen.width<=769){
                if($('.pagination .page-item').length > 7){
                    $('.pagination .dot').remove();
                    first = $('.pagination .page-item:first-child');
                    last = $('.pagination .page-item:last-child');
                    active = $('.pagination .page-item.active');

                    $('.pagination .page-item').addClass('d-none');
                    $(first).removeClass('d-none');
                    $(last).removeClass('d-none');
                    $(active).removeClass('d-none');
                    $(first).next().removeClass('d-none');
                    $(last).prev().removeClass('d-none');

                    $(active).next().removeClass('d-none');
                    $(active).prev().removeClass('d-none');

                    if($(first).nextAll(':lt(2)').hasClass('d-none')){
                        $('<span class="dot" style="padding-top:5px">...</span>').insertBefore($(active).prev());
                    }

                    if($(last).prevAll(':lt(2)').hasClass('d-none')){
                        $('<span class="dot" style="padding-top:5px">...</span>').insertAfter($(active).next());
                    }
                }
                return true;
            } else {
                return false;
            }
        }

        $(document).ready(function(){
            if(screen.width<=769){
                pagination ();
            }
        });

        $(window).resize(function(){
            if(screen.width<=769){
                if($('.pagination .page-item').length > 7){
                    pagination ();
                }
            } else {
                $('.pagination .page-item').removeClass('d-none');
                $('.pagination .dot').remove();
            }
        });
    </script>
@endsection
