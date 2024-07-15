@extends('layouts.app')

@section('title', 'Техническая поддержка')

@section('url', route('support::index'))
@section('css_top')
    <style>
        .popover {
            max-width: 50vw;
        }
        @media (min-width: 769px) {
            #useful-links-content {
                font-size: 16px;
                line-height: 1.3;
            }
        }
        @media (max-width: 768px) {
            ul.pagination {
                -ms-flex-wrap: wrap;
                -webkit-flex-wrap: wrap;
                flex-wrap: wrap;
            }
            div.fix-pagination {
                padding-right: 0;
            }
        }

        th.text-truncate {
            position: relative;
            overflow: visible;
            cursor: default !important;
        }
        @media (min-width: 768px) {
            span.text-truncate {
                max-width: 30px;
            }
        }
        @media (min-width: 1200px) {
            span.text-truncate {
                max-width: 50px;
            }
        }
        @media (min-width: 1360px) {
            span.text-truncate {
                max-width: 80px;
            }
        }
        @media (min-width: 1560px) {
            span.text-truncate {
                max-width: 140px;
            }
        }
        @media (min-width: 1920px) {
            span.text-truncate {
                max-width: 220px;
            }
        }

        [data-balloon],
        [data-balloon]:before,
        [data-balloon]:after {
            z-index: 9999;
        }
    </style>
@endsection
@section('content')
    <div class="row" id="useful-links-mobile">
        <div class="col-md-12">
            <div class="card mb-1">
                <div class="card-header">
                    <a class="pd-0 task-info__link"
                       style="font-weight: inherit; cursor: pointer; color: #23CCEF; display: inline-block; height: 2rem"
                       data-target="#collapse" href="#" data-toggle="collapse">Полезные ссылки
                    </a>
                </div>
                <div id="collapse"
                     class="card-collapse collapse">
                    <div class="card-body card-body-table">
                        <div id="useful-links-inner">
                            <ul id="useful-links-content" class="px-1 m-0 py-1">
                                <span style="font-weight: 500">Anydesk</span> -
                                <a href="https://anydesk.com/ru/downloads" target="_blank">Скачать</a>
                                <br>
                                <hr>
                                <span style="font-weight: 500">Telegram (настольный компьютер)</span> -
                                <a href="{{ asset('storage/tsetup.1.8.15.exe') }}" target="_blank">Скачать</a>
                                <br>
                                <hr>
                                <span style="font-weight: 500">Telegram (android)</span> -
                                <a href="https://urlka.xyz/Gqccxx4" target="_blank">Скачать</a>
                                <i class="fab fa-android"></i>
                                <br>
                                <hr>
                                <span style="font-weight: 500">Telegram (ios)</span> -
                                <a href="https://urlka.xyz/O9QvbbD" target="_blank">Скачать</a>
                                <i class="fab fa-apple"></i>
                                <br>
                                @if(in_array(Auth::user()->id, [1, 5, 6, 13]))
                                    <hr>
                                    <span style="font-weight: 500">Отчет по задачам</span> -
                                    <a href="{{ route('support::report') }}" target="_blank">Скачать</a>
                                    <br>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card table-big-boy">
                <div class="fixed-table-toolbar toolbar-for-btn">
                    <div class="fixed-search">
                        <form action="{{ route('support::index') }}">
                            <input class="form-control" type="text" value="{{ Request::get('search') }}" name="search" placeholder="Поиск">
                        </form>
                    </div>
                    <div class="pull-right">
                        <div class="d-inline-block mr-3 mb-2 mt-2">
                            <a class="pd-0 task-info__link d-none"
                               id="useful-links"
                                  style="font-weight: inherit; cursor: pointer; color: #23CCEF"
                                  data-html="true" data-container="body" data-toggle="popover" data-placement="bottom">Полезные ссылки</a>
                        </div>
                        {{--<div class="d-inline-block mr-3 mb-2 mt-2" id="link_to_anydesk">
                            <a href="https://anydesk.com/ru/downloads" target="_blank">Скачать Anydesk</a>
                        </div>--}}
                        <button class="btn btn-round btn-outline btn-sm add-btn" data-toggle="modal" data-target="#support_modal">
                            <i class="glyphicon fa fa-plus"></i>
                            Добавить
                        </button>
                    </div>
                </div>
                @if($support_tickets->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table mobile-table table-bigboy">
                            <thead>
                            <tr>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="ID"><span class="text-truncate d-inline-block">ID</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Тема"><span class="text-truncate d-inline-block">Тема</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Описание"><span class="text-truncate d-inline-block">Описание</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Пользователь"><span class="text-truncate d-inline-block">Пользователь</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Дата"><span class="text-truncate d-inline-block">Дата</span></th>
                                @if(Auth::user()->id == 1)
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Время исполнения"><span class="text-truncate d-inline-block">Время исполнения</span></th>
                                @endif
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Дата исполнения"><span class="text-truncate d-inline-block">Дата исполнения</span></th>
                                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Статус"><span class="text-truncate d-inline-block">Статус</span></th>
                                <th class="text-truncate text-right" data-balloon-pos="up-left" aria-label="Действия"><span class="text-truncate d-inline-block">Действия</span></th>
                                <th class="td-0" style="display:none"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($support_tickets as $ticket)
                                <tr style="cursor:default" @if($ticket->status == 'resolved') class="resolved-request" @endif>
                                    <td data-label="ID">{{ $ticket->id }}</td>
                                    <td data-label="Тема" style="max-width:205px">{{ $ticket->title }}</td>
                                    <td data-label="Описание" style="max-width:500px">{{ $ticket->description }}</td>
                                    <td data-label="Пользователь">{{ $ticket->sender->full_name }}</td>
                                    <td data-label="Дата">{{ \Carbon\Carbon::parse($ticket->created_at)->format('d.m.Y') }}</td>
                                    @if(Auth::user()->id == 1)
                                    <td class="text-center" data-label="Время исполнения">{{ $ticket->estimate ? $ticket->estimate . ' ч.' : '' }}</td>
                                    @endif
                                    <td data-label="Дата исполнения">
                                        <div class="request-solved_at-container">
                                            <span id="solved_at_text{{$ticket->id}}">{{ $ticket->solved_at ? \Carbon\Carbon::parse($ticket->solved_at)->format('d.m.Y H:i') : 'Не назначена' }}</span>
                                            @if($ticket->status != 'resolved')
                                                @if(Auth::id() == 1)
                                                    <button type="button" name="button" class="btn btn-link mn-0 pd-0 edit-solved_at"><i class="far fa-clock"></i></button>
                                                @endif
                                            @endif
                                        </div>
                                        @if($ticket->status != 'resolved')
                                            @if(Auth::id() == 1)
                                                <div class="solved_at-cont" style="white-space:nowrap; display:none">
                                                    <input id="picker{{ $ticket->id }}" name="solved_at" type="text" style="max-width: 140px" class="form-control datetimepicker pull-left">
                                                    <button type="button" name="button" class="btn btn-link mn-0 pd-0 btn-success accept_solved_at" onclick="updateSolvedAt({{ $ticket->id }})"><i class="fa fa-check" style="margin-top: 10px" ></i></button>
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                    <td data-label="Статус">
                                        <div class="request-status-container">
                                            <span id="status_text{{$ticket->id}}">@if($ticket->status){{ $ticket->status_name }} @else Новая @endif</span>
                                            @if($ticket->status != 'resolved')
                                                @if(Auth::id() == 1)
                                                    <button type="button" name="button" class="btn btn-link mn-0 pd-0 btn-success edit-status"><i class="fa fa-edit"></i></button>
                                                @endif
                                            @endif
                                        </div>
                                        @if($ticket->status != 'resolved')
                                            @if(Auth::id() == 1)
                                                <div class="request-cont" style="white-space:nowrap; display:none">
                                                    <select id="status_select{{ $ticket->id }}" class="selectpicker" data-title="Статус" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                                        @foreach($ticket->statuses as $key => $status)
                                                            <option value="{{ $key }}">{{ $status }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="button" name="button" class="btn btn-link mn-0 pd-0 btn-success accept_request_status" onclick="updateStatus({{ $ticket->id }})"><i class="fa fa-check"></i></button>
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                    <td data-label="Действия" class="text-right">
                                        @if(auth()->id() === 1)
                                            @if($ticket->gitlab_link)
                                                <a href="{{ $ticket->gitlab_link }}" target="_blank" type="button" rel="tooltip" data-placement="top" class="btn btn-link btn-icon" data-original-title="Ссылка на задачу">
                                                    <i class="fa fa-link"></i>
                                                </a>
                                            @endif
                                            <button type="button" rel="tooltip" data-placement="top" class="btn btn-warning btn-link btn-icon" data-original-title="Изменить ссылку на задачу" onclick="vm.addLink({{ $ticket->id }})">
                                                <i class="fa fa-pen"></i>
                                            </button>
                                        @endif
                                        @if($ticket->files->isNotEmpty())
                                            <button type="button" rel="tooltip" data-placement="top" class="btn btn-info btn-link btn-icon" data-toggle="modal" data-target="#view-images{{ $ticket->id }}" data-original-title="Приложенные файлы">
                                                <i class="fa fa-image"></i>
                                            </button>
                                        @endif
                                    </td>
                                    <td lass="td-0" style="display:none"></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-12 fix-pagination">
                        <div class="right-edge">
                            <div class="page-container">
                                {{ $support_tickets->appends(['search' => Request::get('search')])->links() }}
                            </div>
                        </div>
                    </div>
                @elseif(Request::has('search'))
                    <p class="text-center">По вашему запросу ничего не найдено</p>
                @endif
            </div>
        </div>
    </div>



    <div class="modal fade bd-example-modal-lg show" id="estimate_modal" role="dialog" aria-labelledby="modal-search" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Необходимое количество часов</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <hr style="margin-top:0">
                    <div class="card border-0" >
                        <div class="card-body">
                            <form id="form_for_estimating">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>Кол-во часов<span class="star">*</span></label>
                                        <div class="form-group">
                                            <input class="form-control" id="form_estimate" type="number" name="title" required max="10000000">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>Описание</label>
                                        <div class="form-group">
                                            <textarea class="form-control textarea-rows" id="form_result_description" name="result_description" maxlength="1000"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    <button type="button" onclick="send_form()" class="btn btn-info">Отправить</button>
                </div>
            </div>
        </div>
    </div>

    @foreach($support_tickets as $ticket_with_file)
        @if($ticket_with_file->files->isNotEmpty())
            <div class="modal fade bd-example-modal-lg show" id="view-images{{ $ticket_with_file->id }}" role="dialog" aria-labelledby="modal-search" style="display: none;">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Приложенные файлы</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <hr style="margin-top:0">
                            <div class="card border-0" >
                                <div class="card-body">
                                    @foreach($ticket_with_file->files as $ticket_file)
                                        <a target="_blank" href="{{ asset($ticket_file->path) }}" class="contract-file btn btn-social btn-link btn-facebook" rel="tooltip" data-original-title="Открыть файл">
                                            <i class="fa fa-file" style="font-size:13px; top:-1"></i>
                                            {{ $ticket_file->original_name }}
                                        </a>
                                        <br>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    @if(Auth::id() == 1)
        <form id="update_ticket">
            @csrf
            <input id="ticket_id" type="hidden" name="ticket_id">
            <input id="status" type="hidden" name="status">
            <input id="estimate" type="hidden" name="estimate">
            <input id="result_description" type="hidden" name="result_description">
        </form>

        <form id="update_solved_at">
            @csrf
            <input id="solved_at_ticket_id" type="hidden" name="ticket_id">
            <input id="solved_at" type="hidden" name="solved_at">
        </form>

        <div class="modal fade bd-example-modal-lg show" id="link-update" role="dialog" aria-labelledby="modal-search" style="display: none;">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Обновление ссылки на задачу</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-20 mt-20">
                                <label for="">Ссылка на задачу<span class="star">*</span></label>
                                <el-input type="textarea"
                                    minlength="0"
                                    placeholder="Укажите ссылку"
                                    maxlength="300"
                                    v-model="gitlab_link"
                                    :autosize="{ minRows:1, maxRows:3 }"
                                    clearable
                                ></el-input>
                            </div>
                        </div>
                        <div class="row mt-30">
                            <div class="col-md-6">
                                <el-button type="secondary" data-dismiss="modal">Закрыть</el-button>
                            </div>
                            <div class="col-md-6 text-right btn-center">
                                <el-button type="primary" @click.stop="submit" :loading="submit_loading">Сохранить</el-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('support.support_modal')
@endsection
@if(Auth::id() == 1)
    @section('js_footer')
        <script type="text/javascript">
            $('document').ready(function() {
                $('#useful-links').attr('data-content', document.getElementById('useful-links-inner').innerHTML);

                $(function () {
                    $('[data-toggle="popover"]').popover()
                });

                if ($(window).width() > 769) {
                    $('#useful-links-mobile').addClass('d-none');
                    $('#useful-links').removeClass('d-none');
                } else {
                    $('#useful-links').addClass('d-none');
                }

                $(window).resize(function() {
                    if ($(window).width() <= 769) {
                        $('#useful-links-mobile').removeClass('d-none');
                        $('#useful-links').addClass('d-none');
                    } else {
                        $('#useful-links-mobile').addClass('d-none');
                        $('#useful-links').removeClass('d-none');
                    }
                });
            });

            $('.edit-status').click(function(){
                $(this).parents('.request-status-container').hide();
                $(this).parents('.request-status-container').siblings('.request-cont').show()
            });

            $('.accept_request_status').click(function() {
                $(this).parents('.request-cont').hide();
                $(this).parents('.request-cont').siblings('.request-status-container').show();
            });

            function updateStatus(ticket_id) {
                var status = $('#status_select' + ticket_id).val();
                $('#status').val(status);
                $('#ticket_id').val(ticket_id);
                if (status == 'matching') {
                    $('#estimate_modal').appendTo('body').modal('show');
                } else {
                    updateTicket()
                }
            }

            function send_form() {
                var hoursValue = $('#form_estimate').val();
                if (hoursValue < 0 || hoursValue == '') {
                    $('#form_estimate').addClass('is-invalid');
                    return false;
                }
                $('#estimate').val(hoursValue);
                $('#result_description').val($('#form_result_description').val());

                $('#estimate_modal').modal('hide');
                $('#status_text' + $('#ticket_id').val()).text('Обновление статуса');
                updateTicket();
            }

            $('.edit-solved_at').click(function(){
                $(this).parents('.request-solved_at-container').hide();
                $(this).parents('.request-solved_at-container').siblings('.solved_at-cont').show();
                console.log($(this).parents('.request-solved_at-container').siblings('.solved_at-cont'))
            });

            $('.accept_solved_at').click(function() {
                $(this).parents('.solved_at-cont').hide();
                $(this).parents('.solved_at-cont').siblings('.request-solved_at-container').show();
                $(this).parents('.solved_at-cont').siblings('.request-solved_at-container').first().text($('#pikert' + $))

                console.log($(this).parents('.solved_at-cont'));
            });

            $('.datetimepicker').datetimepicker({
                locale: "ru",
                format: 'D.M.YYYY H:00',
                icons: {
                    time: "fa fa-clock-o",
                    date: "fa fa-calendar",
                    up: "fa fa-chevron-up",
                    down: "fa fa-chevron-down",
                    previous: 'fa fa-chevron-left',
                    next: 'fa fa-chevron-right',
                    today: 'fa fa-screenshot',
                    clear: 'fa fa-trash',
                    close: 'fa fa-remove'
                },
            });

            function updateSolvedAt(ticket_id) {
                var solved_at = $('#picker' + ticket_id).val();
                if (solved_at.length) {
                    $('#solved_at').val(solved_at);
                    $('#solved_at_ticket_id').val(ticket_id);

                    updateTicketSolvedAt(ticket_id);
                }
            }

            function updateTicket() {
                $.ajax({
                    url:"{{ route('support::update_ticket_async') }}",
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        ticket_id: $('#ticket_id').val(),
                        estimate: $('#estimate').val(),
                        status: $('#status').val(),
                        result_description: $('#result_description').val(),
                    },
                    dataType: 'JSON',
                    success: function (data) {
                        $('#status_text' + data.id).text(data.status_name);
                        location.reload();
                    }
                });
            }

            function updateTicketSolvedAt(ticket_id) {
                $.ajax({
                    url:"{{ route('support::update_solved_at') }}",
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        ticket_id: $('#solved_at_ticket_id').val(),
                        solved_at: $('#solved_at').val(),
                    },
                    dataType: 'JSON',
                    success: function (data) {
                        $("#solved_at_text" + ticket_id).text(data.solved_at);
                    }
                });
            }

            var vm = new Vue({
                el: '#link-update',
                data: {
                    gitlab_link: '',
                    ticket_id: '',
                    submit_loading: false
                },
                methods: {
                    addLink(ticket_id) {
                        this.ticket_id = ticket_id;
                        $('#link-update').modal('show');
                        $('.modal').css('overflow-y', 'auto');
                        $('#link-update').focus();
                    },
                    submit() {
                        if (this.gitlab_link.length > 0) {
                            this.loading_submit = true;
                            axios.post('{{ route('support::update_link') }}', {
                                ticket_id: this.ticket_id,
                                gitlab_link: this.gitlab_link,
                            }).then(() => {
                                swal({
                                    type: 'success',
                                    title: "Успех",
                                    html: 'Ссылка обновлена',
                                }).then(function () {
                                    setTimeout(() => {
                                        location.reload();
                                    }, 200)
                                });
                            });
                        } else {
                            swal({
                                type: 'error',
                                title: "Ай",
                                html: 'Укажите ссылку',
                            });
                        }
                    }
                }
            });
        </script>
    @endsection
@endif

@push('js_footer')
<script>
function detectmob() {
   if(window.innerWidth <= 800 && window.innerHeight <= 600) {
     return true;
   } else {
     return false;
   }
}

if (detectmob()) {
    $('#link_to_anydesk').remove();
}
</script>
@endpush
