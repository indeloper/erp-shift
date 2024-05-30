@extends('layouts.app')

@section('title', 'Задачи')

@section('css_top')
    <style>
        .select2-hidden-accessible {
            margin: 2.38em 0 0 140px !important;
        }

        .icon-margin {
            padding: 0;
            margin: 0 6px 0 6px !important;
            border: 0;
        }


        @media (min-width: 1450px) {
            .tooltip {
                left:15px!important;
            }
        }

        @media (min-width: 2500px) and (max-width: 3500px) {
            .tooltip {
                left:23px!important;
            }
        }

        @media (min-width: 3500px) and (max-width: 6000px) {
            .tooltip {
                left:38px!important;
            }
        }
    </style>
@endsection

@section('url', route('tasks::index'))

@section('content')
@php setlocale(LC_TIME, 'ru'); @endphp
<div class="row">
    <div class="col-md-12 col-xl-11 mr-auto ml-auto">
        <div class="row task-card">
            <div class="col-md-4 tasks-sidebar">
                <div class="card tasks-sidebar__item tasks-sidebar__item1">
                    <div class="card-body">
                        <div class="tasks-sidebar__text-unit">
                            <span class="tasks-sidebar__head-title">
                                Время создания
                            </span>
                            <span class="tasks-sidebar__body-title">
                               {{ $task->created_at }}
                            </span>
                        </div>
                        <div class="tasks-sidebar__text-unit">
                            <span class="tasks-sidebar__head-title">
                                Автор
                            </span>
                            <span class="tasks-sidebar__body-title">
                                @if($task->user_id) <a href="{{ route('users::card', $task->user_id) }}" class="tasks-sidebar__author">{{ $task->full_name }}</a> @else Система @endif
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card tasks-sidebar__item">
                    <div class="card-body">
                        <div class="">
                            @if($task->project_id)
                                <div class="tasks-sidebar__text-unit">
                                    <span class="tasks-sidebar__head-title">
                                        Проект
                                    </span>
                                    <span class="tasks-sidebar__body-title">
                                        <a class="tasks-sidebar__link" @if($task->project_id) href="{{ route('projects::card', $task->project_id) }}" @endif>{{ $task->project_name ? $task->project_name : 'Проект не выбран' }}</a>
                                    </span>
                                </div>
                            @endif
                            @if($task->project_address)
                                <div class="tasks-sidebar__text-unit">
                                    <span class="tasks-sidebar__head-title">
                                        Адрес объекта
                                    </span>
                                    <span class="tasks-sidebar__body-title">
                                        {{ $task->project_address }}
                                    </span>
                                </div>
                            @endif
                            @if($task->object_name)
                                <div class="tasks-sidebar__text-unit">
                                    <span class="tasks-sidebar__head-title">
                                        Объект
                                    </span>
                                    <span class="tasks-sidebar__body-title">
                                        {{ $task->object_name }}
                                    </span>
                                </div>
                            @endif
                            <div class="tasks-sidebar__text-unit">
                                <span class="tasks-sidebar__head-title">
                                    Контрагент
                                </span>
                                <span class="tasks-sidebar__body-title">
                                    <a class="tasks-sidebar__link" @if($task->contractor_id) href="{{ route('contractors::card', $task->contractor_id) }}" @endif>
                                        {{ $task->contractor_name ? $task->contractor_name : 'Контрагент не выбран' }}
                                    </a>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card tasks-sidebar__item">
                    <div class="card-body">
                        <div class="accordions" id="links-accordion">
                            <div class="card" style="margin-bottom:0">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <a class="collapsed tasks-sidebar__collapsed-link" data-target="#collapse1" href="#" data-toggle="collapse">
                                            Вспомогательные ссылки
                                            <b class="caret"></b>
                                        </a>
                                    </h5>
                                </div>
                                <div id="collapse1" class="card-collapse collapse">
                                    <div class="card-body tasks-sidebar__body-links">
                                        @if($task->project_id)
                                            <a class="tasks-sidebar__help-link" target="_blank" href="{{ route('projects::tasks', $task->project_id) }}">История проекта</a><br>
                                            <a class="tasks-sidebar__help-link" target="_blank" href="{{ route('project_documents::card', $task->project_id) }}">Проектная документация</a><br>
                                        @endif
                                        @if($task->status > 2 and $task->status != 17)
                                            @if(isset($work_volumes))
                                                @foreach($work_volumes as $wv)
                                                    <a class="tasks-sidebar__help-link" target="_blank" href="{{ route('projects::work_volume::card_' . ($wv->type ? 'pile': 'tongue') , [$task->project_id, $wv->id]) }}">Объем работ {{ $wv->type ?'(свая)': '(шпунт)' }}</a><br>
                                                @endforeach
                                            @endif
                                        @endif
                                        @if($task->status == 14 || $task->status == 15)
                                        @elseif($task->status > 4 and isset($project) and $project->commercial_offer_id)
                                            @foreach($com_offers->where('status', '!=', 3) as $offer)
                                                <a class="tasks-sidebar__help-link" target="_blank" href="{{ route('projects::commercial_offer::card_'. ($offer->is_tongue ? 'tongue' : 'pile'), [$task->project_id, $offer->id]) }}">Коммерческое предложение {{ "\"{$offer->option}\"" . ($offer->is_tongue ? ' (шпунт)' : ' (свая)') }}</a><br>
                                            @endforeach
                                        @endif
                                        @if($task->status != 14 || $task->status != 15)
                                        @elseif($task->status > 6 and $task->commercial_offer_file)
                                            <a class="tasks-sidebar__help-link" target="_blank" href="{{ asset('storage/docs/commercial_offers/' . $task->commercial_offer_file) }}">
                                                Подписанное коммерческое предложение (файл)
                                            </a><br>
                                        @endif
                                        @if(in_array($task->status, $task::CONTR_STATUS))
                                            <a class="tasks-sidebar__help-link" target="_blank" href="{{ $target }}">
                                                Карточка договора
                                            </a><br>
                                        @endif
                                        @if($task->status == 17)
                                            <a class="tasks-sidebar__help-link" target="_blank" href="{{ $target }}">Карточка объема работ</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if(in_array($task->status,[6,7,8,9,10,11,12,16,17]))
                    <div class="card tasks-sidebar__item">
                        <div class="card-body">
                            <div class="accordions" id="files-accordion">
                                <div class="card" style="margin-bottom:0">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <a class="collapsed tasks-sidebar__collapsed-link" data-target="#collapse2" href="#" data-toggle="collapse">
                                                Приложенные файлы
                                                <b class="caret"></b>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="collapse2" class="card-collapse collapse">
                                        <div class="card-body tasks-sidebar__body-links">
                                            @if($task->status == 17)
                                                @if($wv_request->files->isNotEmpty())
                                                    @if($wv_request->files->where('is_proj_doc', 0)->isNotEmpty())
                                                        @foreach($wv_request->files->where('is_proj_doc', 0) as $file)
                                                            <a target="_blank" href="{{ asset('storage/docs/work_volume_request_files/' . $file->file_name) }}" rel="tooltip"
                                                               data-original-title="{{ $wv_request->user->full_name .' '. '('. $wv_request->created_at .')'}}">
                                                                {{ $file->original_name }}
                                                            </a>
                                                            <br>
                                                        @endforeach
                                                    @endif
                                                    @if($wv_request->files->where('is_proj_doc', 1)->isNotEmpty())
                                                        @foreach($wv_request->files->where('is_proj_doc', 1) as $file)
                                                            <a target="_blank" href="{{ asset('storage/docs/project_documents/' . $file->file_name) }}" rel="tooltip"
                                                               data-original-title="{{ $wv_request->user->full_name .' '. '('. $wv_request->created_at .')'}}">
                                                                {{ $file->original_name }}
                                                            </a>
                                                        @endforeach
                                                    @endif
                                                @endif
                                            @endif
                                            @foreach($com_offers->where('status', 5) as $offer)
                                                @if($offer->file_name)
                                                    <a class="tasks-sidebar__help-link" target="_blank" href="{{ asset('storage/docs/commercial_offers/' . $offer->file_name) }}">Файл отправленного коммерческого предложения {{ $offer->is_tongue ? '(шпунт)' : '(свая)' }}</a><br>
                                                @endif
                                            @endforeach
                                            @foreach($com_offers->where('status', 2) as $offer)
                                                @if($offer->file_name)
                                                    <a class="tasks-sidebar__help-link" target="_blank" href="{{ asset('storage/docs/commercial_offers/' . $offer->file_name) }}">Файл согласуемого коммерческого предложения {{ $offer->is_tongue ? '(шпунт)' : '(свая)' }}</a><br>
                                                @endif
                                            @endforeach
                                            @if(Auth::user()->isInGroup(5)/*3*/)
                                                @foreach($com_offers->where('budget', '!=', null)->where('id', $task->target_id) as $offer)
                                                    <a class="tasks-sidebar__help-link" target="_blank" href="{{ asset('storage/docs/budget/' . $offer->budget) }}">
                                                        Файл бюджета {{ $offer->is_tongue ? '(шпунт)' : '(свая)' }}
                                                    </a><br>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-9 task-header__title">
                                <h4>{{ $task->name }}</h4>
                            </div>
                            <div class="col-md-3 text-right" style="margin-top:3px;">
                                @if(in_array($task->status,[7, 9, 10, 11, 12]))
                                    @if(Auth::id() == $task->responsible_user_id)
                                        <button type="button" id="postpone" class="task-time btn-link btn-xs btn mn-0 pd-0" data-toggle="modal" data-target="#postpone-task">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                    @endif
                                @endif
                                до
                                <span class="task-header__date">
                                     {{ \Carbon\Carbon::parse($task->expired_at)->isoFormat('Do MMMM') }}
                                </span>
                                <span class="task-header__time">
                                    {{ \Carbon\Carbon::parse($task->expired_at)->format('H:m') }}
                                </span>
                            </div>
                        </div>
                        <hr style="margin-top:7px;border-color:#F6F6F6">
                    </div>
                    @if(in_array($task->status, [3, 4]))
                        @include('tasks.partials.3or4')
                    @else
                        @include('tasks.partials.' . $task->status)
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<form id="postpone_task" method="post" action="{{ route('tasks::postpone', $task->id) }}">@csrf</form>

<form id="approve_contract" action="{{ route('projects::contract::approve', [$task->project_id, $task->target_id]) }}" method="post">
    @csrf
    <input name="contract_id" type="hidden" id="approved_contract_id">
</form>

@if($task->status === 9 or $task->status === 10)
   @if(Auth::id() == $task->responsible_user_id)
       <div class="modal fade bd-example-modal-lg show" id="signing-contract" role="dialog" aria-labelledby="modal-search" style="display: none;">
           <div class="modal-dialog modal-lg" role="document">
               <div class="modal-content">
                   <div class="modal-header">
                       <h5 class="modal-title">Подтверждение подписания</h5>
                       <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                           <span aria-hidden="true">×</span>
                       </button>
                   </div>
                   <div class="modal-body">
                       <hr style="margin-top:0">
                       <div class="card border-0" >
                           <div class="card-body ">
                               <form id="add_files" class="form-horizontal" action="{{ route('projects::contract::add_files', $task->target_id) }}" method="post" enctype="multipart/form-data">
                                   @csrf
                                   <input type="hidden" name="contract_id" value="{{ $task->target_id }}">
                                   <div class="row">
                                       <div class="col-md-3">
                                           <label>Вид документа<star class="star">*</star></label>
                                       </div>
                                       <div class="col-md-6">
                                           <div class="form-group">
                                               <select name="type" class="selectpicker" data-title="Выберите тип документа" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                                   <option value="1">Договор</option>
                                                   @if($task->status === 9) <option value="2">Гарантийное письмо</option> @endif
                                               </select>
                                           </div>
                                       </div>
                                   </div>
                                   <div class="row" >
                                       <label class="col-sm-3 col-form-label" for="" style="font-size:0.80">
                                           Приложенный документ<star class="star">*</star>
                                       </label>
                                       <div class="col-sm-6">
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
                               </form>
                           </div>
                       </div>
                   </div>
                   <div class="modal-footer">
                       <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                       <button id="" form="add_files" type="button" onclick="submit_file(this)" class="btn btn-info">Подтвердить</button>
                   </div>
               </div>
           </div>
       </div>
   @endif
@endif

@if(Auth::id() == $task->responsible_user_id and in_array($task->status,[7, 9, 10, 11, 12]))
   <div class="modal fade bd-example-modal-lg show" id="postpone-task" role="dialog" aria-labelledby="modal-search" style="display: none;">
       <div class="modal-dialog modal-lg" role="document">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title">Подтверждение переноса</h5>
                   <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">×</span>
                   </button>
               </div>
               <div class="modal-body">
                   <hr style="margin-top:0">
                   <div class="card border-0" >
                       <div class="card-body ">
                           <form id="postpone_task" class="form-horizontal" action="{{ route('tasks::postpone', $task->id) }}" method="post" enctype="multipart/form-data">
                               @csrf
                               <div class="row">
                                   <div class="col-md-3">
                                       <label>Дата переноса<star class="star">*</star></label>
                                   </div>
                                   <div class="col-md-6">
                                       <div class="form-group">
                                           <input id="datetimepicker" form="postpone_task" name="revive_at" autocomplete="off" type="text" class="form-control datetimepicker" placeholder="Укажите дату" required>
                                       </div>
                                   </div>
                               </div>
                               <div class="row">
                                   <label class="col-sm-3 col-form-label" for="" style="font-size:0.80">
                                       Комментарий<star class="star">*</star>
                                   </label>
                                   <div class="col-sm-9">
                                       <textarea class="form-control textarea-rows" form="postpone_task" name="description" required maxlength="250"></textarea>
                                   </div>
                               </div>
                           </form>
                       </div>
                   </div>
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                   <button id="" form="postpone_task" type="submit" class="btn btn-info">Перенести</button>
               </div>
           </div>
       </div>
   </div>
@endif

@endsection

@section('js_footer')
<meta name="csrf-token" content="{{ csrf_token() }}" />

<script>
    @if(Auth::id() == $task->responsible_user_id)
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        add_datetime();

        setInterval(function(){
            $('#datetimepicker').datetimepicker('destroy');
            add_datetime();
        }, 55000);

        function submit_file(e) {
            var form  = $('#' + $(e).attr('form'));
            if (form[0].reportValidity()) {
                if (form.find("[name='document']")[0].files.length > 0) {
                    form.submit();
                }
                else {
                    swal({
                        title: 'Внимание',
                        text: "Необходимо прикрепить файл",
                        type: 'warning',
                        confirmButtonText: 'Ок',
                        timer: '3000'
                    });
                }
            }
        }

        function add_datetime() {
            $('#datetimepicker').datetimepicker({
                format: 'DD/MM/YYYY HH:mm:ss',
                minDate: moment().add(1,'day'),
                locale: "ru",
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
        }

        $('document').ready(function () {
            if ($('#description')[0].innerHTML.trim() == '') {
                $('#main_descr').addClass('d-none');
                $('#empty_hint').removeClass('d-none');
            }
        });

        function decline_contract(contract_id) {
            if ($('#final_note').val()) {
                swal({
                    title: 'Вы уверены?',
                    text: "Договор будет отклонён! \n Будет создана новая версия",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    cancelButtonText: 'Назад',
                    confirmButtonText: 'Отклонить'
                }).then((result) => {
                    if(result.value) {
                        $('#declined_contract_id').val(contract_id);
                        $('#decline_contract').submit();
                    }
                });
            } else {
                swal({
                    title: 'Внимание',
                    text: "Укажите причину отклонения согласования",
                    type: 'warning',
                });
            }
        }

        function approve_contract(contract_id) {
            swal({
                title: 'Вы уверены?',
                text: "Перевести договор в статус 'согласовано'?",
                type: 'question',
                showCancelButton: true,
                cancelButtonText: 'Назад',
                confirmButtonText: 'Перевести'
            }).then((result) => {
                if(result.value) {
                    $('#approved_contract_id').val(contract_id);
                    $('#approve_contract').submit();
                }
            })
        }

        $('#status_result').change(function() {
            opt = $(this).val();
            if (opt == "archive" || opt == "transfer" || opt == "decline") {
                $('#comment').show();
                $('#result_note').attr('required', 'required');
                $('#result_note').closest('.col-sm-12').find('star').first().show();
                if (opt == "transfer") {
                    $('#fromСontainer').removeClass('d-none');
                    $('#from').attr('required', 'required');
                } else if (opt == "archive") {
                    $('#fromСontainer').addClass('d-none');
                    $('#from').removeAttr('required', 'required');
                }
            }
            @if($task->status == 18)
                else if (opt == "declined") {
                    $('#comment').show();
                    $('#accept_comment').hide();
                    $('#result_note').attr('required', 'required');
                    $('#checkWV').attr('action', '{{ route('projects::work_volume_request::store', $project->project_id) }}');
                } else if (opt == 'accepted') {
                    $('#checkWV').attr('action', '{{ route('projects::work_volume::send', $task->target_id) }}');
                    $('#result_note').removeAttr('required');
                    $('#comment').hide();
                    $('#accept_comment').show();
                } else if (opt == 'close') {
                    $('#checkWV').attr('action', '{{ route('projects::work_volume::close', $task->target_id) }}');
                    $('#result_note').removeAttr('required');
                    $('#comment').hide();
                    $('#accept_comment').hide();
                }
            @endif
            else {
                @if($task->status == 16)
                    $('#comment').show();
                    $('#result_note').removeAttr('required');
                    $('#result_note').closest('.col-sm-12').find('star').first().hide();
                @else
                    $('#fromСontainer').addClass('d-none');
                    $('#from').removeAttr('required', 'required');
                    $('#result_note').removeAttr('required');
                    $('#comment').hide();
                @endif
            }

            $('#sendForm').collapse('show');
        });

        $('#contract_status').change(function(){
            option = $(this).val();
            if (option == "decline") {
                $('#contract_decline_comm').show();
                $('#decline_contract_btn').show();
                $('#accept_contract_btn').hide();
                $('#final_note').attr('required', 'required');
            } else {
                $('#contract_decline_comm').hide();
                $('#decline_contract_btn').hide();
                $('#accept_contract_btn').show();
                $('#final_note').removeAttr('required');
            }
        });

        $('#sign_status').change(function(){
            option = $(this).val();
            if (option == "decline") {
                $('#sign_decline_comm').show();
                $('#decline_sign_btn').show();
                $('#sign_accept_comm').hide();
                $('#accept_sign_btn').hide();
            } else {
                $('#sign_decline_comm').hide();
                $('#decline_sign_btn').hide();
                $('#sign_accept_comm').show();
                $('#accept_sign_btn').show();
            }
        });

        $('#js-select-position').select2({
            language: "ru"
        });

        $('#js-select-users').select2({
            language: "ru",
            maximumSelectionLength: 10,
            ajax: {
                url: '/projects/ajax/get-users',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        role: $('#js-select-position').val(),
                        q: params.term,
                    };
                }
            },
            disabled: true
        });

        $("#js-select-position").on("change", function () {
            result = $("#js-select-position").val();
            if (result) {
                $("#js-select-users").prop("disabled", false);
            } else {
                $("#js-select-users").prop("disabled", true);
            }
            $("#js-select-users").val(null).trigger("change");
        });

        $('#from').datetimepicker({
            format: 'DD.MM.YYYY',
            locale: 'ru',
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
            minDate: moment().add(1, 'd'),
            date: null
        });

        $('.js-select-proj-doc').select2({
            language: "ru",
            closeOnSelect: false,
            ajax: {
                url: '/projects/ajax/get_project_documents/' + {{ $task->project_id }},
                dataType: 'json',
                delay: 250,
            }
        }).on("select2:unselecting", function(e) {
            $(this).data('state', 'unselected');
        }).on("select2:open", function(e) {
            if ($(this).data('state') === 'unselected') {
                $(this).removeData('state');
                var self = $(this);
                setTimeout(function() {
                    self.select2('close');
                }, 1);
            }
        });

        function declineRequest(task)
        {
            $.ajax({
                url: '{{ route('tasks::decline_request') }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    task_id: task.id
                },
                dataType: 'JSON',
                success: function() {
                    location.reload();
                }
            });
        }
    @endif
</script>
@endsection
